<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\OAuth2;

use App\Controller\Api\BaseApi;
use App\DTO\ImageUploadDto;
use App\DTO\OAuth2ClientDto;
use App\Entity\Client;
use App\Factory\ClientFactory;
use App\Kbin\User\DTO\UserDto;
use App\Kbin\User\UserCreate;
use App\Repository\UserRepository;
use App\Service\ImageManager;
use App\Service\SettingsManager;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use League\Bundle\OAuth2ServerBundle\ValueObject\Grant;
use League\Bundle\OAuth2ServerBundle\ValueObject\RedirectUri;
use League\Bundle\OAuth2ServerBundle\ValueObject\Scope;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateClientApi extends BaseApi
{
    #[OA\Response(
        response: 201,
        description: 'Returns the created oauth2 client. Be sure to save the identifier and secret since these will be how you obtain tokens for the API.',
        content: new Model(type: OAuth2ClientDto::class, groups: ['created', 'common']),
        headers: [
            new OA\Header(
                header: 'X-RateLimit-Remaining',
                schema: new OA\Schema(type: 'integer'),
                description: 'Number of requests left until you will be rate limited'
            ),
            new OA\Header(
                header: 'X-RateLimit-Retry-After',
                schema: new OA\Schema(type: 'integer'),
                description: 'Unix timestamp to retry the request after'
            ),
            new OA\Header(
                header: 'X-RateLimit-Limit',
                schema: new OA\Schema(type: 'integer'),
                description: 'Number of requests available'
            ),
        ]
    )]
    #[OA\Response(
        response: 400,
        description: 'Grant type(s), scope(s), redirectUri(s) were invalid, or username was taken',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'This instance only allows admins to create clients',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 429,
        description: 'You are being rate limited',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\TooManyRequestsErrorSchema::class)),
        headers: [
            new OA\Header(
                header: 'X-RateLimit-Remaining',
                schema: new OA\Schema(type: 'integer'),
                description: 'Number of requests left until you will be rate limited'
            ),
            new OA\Header(
                header: 'X-RateLimit-Retry-After',
                schema: new OA\Schema(type: 'integer'),
                description: 'Unix timestamp to retry the request after'
            ),
            new OA\Header(
                header: 'X-RateLimit-Limit',
                schema: new OA\Schema(type: 'integer'),
                description: 'Number of requests available'
            ),
        ]
    )]
    #[OA\RequestBody(content: new Model(
        type: OAuth2ClientDto::class,
        groups: ['creating']
    ))]
    #[OA\Tag(name: 'oauth')]
    /**
     * This endpoint can create an OAuth2 client for your application.
     *
     * You can create a public or confidential client with any of 3 flows available. It's
     * recommended that you pick **either** `client_credentials`, **or** `authorization_code` *and* `refresh_token`.
     *
     * When creating clients with the client_credentials grant type, you must provide a unique
     * username and contact email. The username and email will be used to create a new bot user,
     * which your client authenticates as during the client_credentials flow. This user will be
     * tagged as a bot on all of their posts, comments, and on their profile. In addition, the bot
     * will not be allowed to use the API to vote on content.
     *
     * If you are creating a client that will be used on a native app or webapp, the client
     * should be marked as public. This will skip generation of a client secret and will require
     * the client to use the PKCE (https://www.oauth.com/oauth2-servers/pkce/) extension during
     * authorization_code flow. A public client cannot use the client_credentials flow. Public clients
     * are recommended because apps running on user devices technically cannot store secrets safely -
     * if they're determined enough, the user could retrieve the secret from their device's memory.
     */
    public function __invoke(
        ClientManagerInterface $manager,
        ClientFactory $clientFactory,
        UserCreate $userCreate,
        UserRepository $userRepository,
        SettingsManager $settingsManager,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        RateLimiterFactory $apiOauthClientLimiter,
    ): JsonResponse {
        if ($settingsManager->get('KBIN_ADMIN_ONLY_OAUTH_CLIENTS') && !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException('This instance only allows admins to create oauth clients');
        }

        $headers = $this->rateLimit($apiOauthClientLimiter, $apiOauthClientLimiter);

        $request = $this->request->getCurrentRequest();
        /** @var OAuth2ClientDto $dto */
        $dto = $serializer->deserialize(
            $request->getContent(),
            OAuth2ClientDto::class,
            'json',
            ['groups' => ['creating']]
        );

        $validatorGroups = ['Default', 'creating'];
        // If the client being requested wishes to use the client_credentials flow,
        //   validate that it has a username.
        if (false !== array_search('client_credentials', $dto->grants)) {
            $validatorGroups[] = 'client_credentials';
        }

        $errors = $validator->validate($dto, groups: $validatorGroups);
        if (0 < \count($errors)) {
            throw new BadRequestHttpException((string) $errors);
        }

        $identifier = hash('md5', random_bytes(16));
        // If a public client is requested, use null for the secret
        $secret = $dto->public ? null : hash('sha512', random_bytes(32));
        $client = new Client($dto->name, $identifier, $secret);

        if (false !== array_search('client_credentials', $dto->grants)) {
            if ($userRepository->findOneByUsername($dto->username)) {
                throw new BadRequestHttpException('That username/email is taken!');
            }
            if ($userRepository->findOneBy(['email' => $dto->contactEmail])) {
                throw new BadRequestHttpException('That username/email is taken!');
            }
            $userDto = new UserDto();
            $userDto->username = $dto->username;
            $userDto->email = $dto->contactEmail;
            // Only way to authenticate as this user will be to use client_credentials, unless they guess the very random password
            $userDto->plainPassword = hash('sha512', random_bytes(32));
            // This user is a bot user.
            $userDto->isBot = true;
            // Rate limiting is handled by the apiClientLimiter
            $user = $userCreate($userDto, false, false);
            $client->setUser($user);
        }
        $client->setDescription($dto->description);
        $client->setContactEmail($dto->contactEmail);
        $client->setGrants(...array_map(fn (string $grant) => new Grant($grant), $dto->grants));
        $client->setScopes(...array_map(fn (string $scope) => new Scope($scope), $dto->scopes));
        $client->setRedirectUris(
            ...array_map(fn (string $redirectUri) => new RedirectUri($redirectUri), $dto->redirectUris)
        );

        $manager->save($client);

        $dto = $clientFactory->createDto($client);

        return new JsonResponse(
            $dto,
            status: 201,
            headers: $headers
        );
    }

    #[OA\Response(
        response: 201,
        description: 'Returns the created oauth2 client. Be sure to save the identifier and secret since these will be how you obtain tokens for the API.',
        content: new Model(type: OAuth2ClientDto::class, groups: ['Default', 'created', 'common']),
        headers: [
            new OA\Header(
                header: 'X-RateLimit-Remaining',
                schema: new OA\Schema(type: 'integer'),
                description: 'Number of requests left until you will be rate limited'
            ),
            new OA\Header(
                header: 'X-RateLimit-Retry-After',
                schema: new OA\Schema(type: 'integer'),
                description: 'Unix timestamp to retry the request after'
            ),
            new OA\Header(
                header: 'X-RateLimit-Limit',
                schema: new OA\Schema(type: 'integer'),
                description: 'Number of requests available'
            ),
        ]
    )]
    #[OA\Response(
        response: 400,
        description: 'Grant type(s), scope(s), redirectUri(s) were invalid, or username/email was taken',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'This instance only allows admins to create clients',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 429,
        description: 'You are being rate limited',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\TooManyRequestsErrorSchema::class)),
        headers: [
            new OA\Header(
                header: 'X-RateLimit-Remaining',
                schema: new OA\Schema(type: 'integer'),
                description: 'Number of requests left until you will be rate limited'
            ),
            new OA\Header(
                header: 'X-RateLimit-Retry-After',
                schema: new OA\Schema(type: 'integer'),
                description: 'Unix timestamp to retry the request after'
            ),
            new OA\Header(
                header: 'X-RateLimit-Limit',
                schema: new OA\Schema(type: 'integer'),
                description: 'Number of requests available'
            ),
        ]
    )]
    #[OA\RequestBody(content: new OA\MediaType(
        'multipart/form-data',
        schema: new OA\Schema(
            ref: new Model(
                type: OAuth2ClientDto::class,
                groups: [
                    'creating',
                    ImageUploadDto::IMAGE_UPLOAD_NO_ALT,
                ]
            )
        ),
        encoding: [
            'imageUpload' => [
                'contentType' => ImageManager::IMAGE_MIMETYPE_STR,
            ],
        ]
    ))]
    #[OA\Tag(name: 'oauth')]
    /**
     * This endpoint can create an OAuth2 client with a logo for your application.
     *
     * The image uploaded to this endpoint will be shown to users on the consent page as your application's logo.
     *
     * You can create a public or confidential client with any of 3 flows available. It's
     * recommended that you pick **either** `client_credentials`, **or** `authorization_code` *and* `refresh_token`.
     *
     * When creating clients with the client_credentials grant type, you must provide a unique
     * username and contact email. The username and email will be used to create a new bot user,
     * which your client authenticates as during the client_credentials flow. This user will be
     * tagged as a bot on all of their posts, comments, and on their profile. In addition, the bot
     * will not be allowed to use the API to vote on content.
     *
     * If you are creating a client that will be used on a native app or webapp, the client
     * should be marked as public. This will skip generation of a client secret and will require
     * the client to use the PKCE (https://www.oauth.com/oauth2-servers/pkce/) extension during
     * authorization_code flow. A public client cannot use the client_credentials flow. Public clients
     * are recommended because apps running on user devices technically cannot store secrets safely -
     * if they're determined enough, the user could retrieve the secret from their device's memory.
     */
    public function uploadImage(
        ClientManagerInterface $manager,
        ClientFactory $clientFactory,
        UserCreate $userCreate,
        UserRepository $userRepository,
        SettingsManager $settingsManager,
        ValidatorInterface $validator,
        RateLimiterFactory $apiOauthClientLimiter,
    ): JsonResponse {
        if ($settingsManager->get('KBIN_ADMIN_ONLY_OAUTH_CLIENTS') && !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException('This instance only allows admins to create oauth clients');
        }

        $headers = $this->rateLimit($apiOauthClientLimiter, $apiOauthClientLimiter);

        $image = $this->handleUploadedImage();

        $dto = $this->deserializeClientFromForm();

        $validatorGroups = ['Default'];
        // If the client being requested wishes to use the client_credentials flow,
        //   validate that it has a username.
        if (false !== array_search('client_credentials', $dto->grants)) {
            $validatorGroups[] = 'client_credentials';
        }

        $errors = $validator->validate($dto, groups: $validatorGroups);
        if (0 < \count($errors)) {
            throw new BadRequestHttpException((string) $errors);
        }

        $identifier = hash('md5', random_bytes(16));
        // If a public client is requested, use null for the secret
        $secret = $dto->public ? null : hash('sha512', random_bytes(32));
        $client = new Client($dto->name, $identifier, $secret);

        if (false !== array_search('client_credentials', $dto->grants)) {
            if ($userRepository->findOneByUsername($dto->username)) {
                throw new BadRequestHttpException('That username/email is taken!');
            }
            if ($userRepository->findOneBy(['email' => $dto->contactEmail])) {
                throw new BadRequestHttpException('That username/email is taken!');
            }
            $userDto = new UserDto();
            $userDto->username = $dto->username;
            $userDto->email = $dto->contactEmail;
            // Only way to authenticate as this user will be to use client_credentials, unless they guess the very random password
            $userDto->plainPassword = hash('sha512', random_bytes(32));
            // This user is a bot user.
            $userDto->isBot = true;
            // Rate limiting is handled by the apiClientLimiter
            $user = $userCreate($userDto, false, false);
            $client->setUser($user);
        }
        $client->setDescription($dto->description);
        $client->setContactEmail($dto->contactEmail);
        $client->setGrants(...array_map(fn (string $grant) => new Grant($grant), $dto->grants));
        $client->setScopes(...array_map(fn (string $scope) => new Scope($scope), $dto->scopes));
        $client->setRedirectUris(
            ...array_map(fn (string $redirectUri) => new RedirectUri($redirectUri), $dto->redirectUris)
        );
        $client->setImage($image);

        $manager->save($client);

        $dto = $clientFactory->createDto($client);

        return new JsonResponse(
            $dto,
            status: 201,
            headers: $headers
        );
    }

    protected function deserializeClientFromForm(OAuth2ClientDto $dto = null): OAuth2ClientDto
    {
        $request = $this->request->getCurrentRequest();
        $dto = $dto ? $dto : new OAuth2ClientDto();
        $dto->name = $request->get('name', $dto->name);
        $dto->contactEmail = $request->get('contactEmail', $dto->contactEmail);
        $dto->description = $request->get('description', $dto->description);
        $dto->public = filter_var($request->get('public', $dto->public), FILTER_VALIDATE_BOOL);
        $dto->username = $request->get('username', $dto->username);

        $redirectUris = $request->get('redirectUris', $dto->redirectUris);
        if (\is_string($redirectUris)) {
            $redirectUris = preg_split('/(,| )/', $redirectUris, flags: PREG_SPLIT_NO_EMPTY);
        }
        $dto->redirectUris = $redirectUris;

        $grants = $request->get('grants', $dto->grants);
        if (\is_string($grants)) {
            $grants = preg_split('/(,| )/', $grants, flags: PREG_SPLIT_NO_EMPTY);
        }
        $dto->grants = $grants;

        $scopes = $request->get('scopes', $dto->scopes);
        if (\is_string($scopes)) {
            $scopes = preg_split('/(,| )/', $scopes, flags: PREG_SPLIT_NO_EMPTY);
        }
        $dto->scopes = $scopes;

        return $dto;
    }
}
