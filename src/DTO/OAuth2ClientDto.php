<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\OAuth2UserConsent;
use App\Utils\RegPatterns;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[OA\Schema()]
class OAuth2ClientDto extends ImageUploadDto implements \JsonSerializable
{
    public const AVAILABLE_GRANTS = [
        'client_credentials',
        'authorization_code',
        'refresh_token',
    ];

    public const AVAILABLE_SCOPES = [
        'read',
        'write',
        'delete',
        'subscribe',
        'block',
        'vote',
        'report',
        'domain',
        'domain:subscribe',
        'domain:block',
        'entry',
        'entry:create',
        'entry:edit',
        'entry:delete',
        'entry:vote',
        'entry:report',
        'entry_comment',
        'entry_comment:create',
        'entry_comment:edit',
        'entry_comment:delete',
        'entry_comment:vote',
        'entry_comment:report',
        'magazine',
        'magazine:subscribe',
        'magazine:block',
        'post',
        'post:create',
        'post:edit',
        'post:delete',
        'post:vote',
        'post:report',
        'post_comment',
        'post_comment:create',
        'post_comment:edit',
        'post_comment:delete',
        'post_comment:vote',
        'post_comment:report',
        'user',
        'user:profile',
        'user:profile:read',
        'user:profile:edit',
        'user:message',
        'user:message:read',
        'user:message:create',
        'user:notification',
        'user:notification:read',
        'user:notification:delete',
        'user:oauth_clients',
        'user:oauth_clients:read',
        'user:oauth_clients:edit',
        'user:follow',
        'user:block',
        'moderate',
        'moderate:entry',
        'moderate:entry:language',
        'moderate:entry:pin',
        'moderate:entry:set_adult',
        'moderate:entry:trash',
        'moderate:entry_comment',
        'moderate:entry_comment:language',
        'moderate:entry_comment:set_adult',
        'moderate:entry_comment:trash',
        'moderate:post',
        'moderate:post:language',
        'moderate:post:pin',
        'moderate:post:set_adult',
        'moderate:post:trash',
        'moderate:post_comment',
        'moderate:post_comment:language',
        'moderate:post_comment:set_adult',
        'moderate:post_comment:trash',
        'moderate:magazine',
        'moderate:magazine:ban',
        'moderate:magazine:ban:read',
        'moderate:magazine:ban:create',
        'moderate:magazine:ban:delete',
        'moderate:magazine:list',
        'moderate:magazine:reports',
        'moderate:magazine:reports:read',
        'moderate:magazine:reports:action',
        'moderate:magazine:trash:read',
        'moderate:magazine_admin',
        'moderate:magazine_admin:create',
        'moderate:magazine_admin:delete',
        'moderate:magazine_admin:update',
        'moderate:magazine_admin:theme',
        'moderate:magazine_admin:moderators',
        'moderate:magazine_admin:badges',
        'moderate:magazine_admin:tags',
        'moderate:magazine_admin:stats',
        'admin',
        'admin:entry:purge',
        'admin:entry_comment:purge',
        'admin:post:purge',
        'admin:post_comment:purge',
        'admin:magazine',
        'admin:magazine:move_entry',
        'admin:magazine:purge',
        'admin:user',
        'admin:user:ban',
        'admin:user:verify',
        'admin:user:delete',
        'admin:user:purge',
        'admin:instance',
        'admin:instance:stats',
        'admin:instance:settings',
        'admin:instance:settings:read',
        'admin:instance:settings:edit',
        'admin:instance:information:edit',
        'admin:federation',
        'admin:federation:read',
        'admin:federation:update',
        'admin:oauth_clients',
        'admin:oauth_clients:read',
        'admin:oauth_clients:revoke',
    ];

    #[Assert\NotBlank(groups: ['deleting'])]
    #[Groups(['created', 'deleting'])]
    public ?string $identifier = null;
    #[Assert\NotBlank(groups: ['deleting'])]
    #[Groups(['created', 'deleting'])]
    public ?string $secret = null;
    #[Assert\NotBlank(groups: ['creating'])]
    #[Groups(['creating', 'created'])]
    #[OA\Property(nullable: false)]
    public ?string $name = null;
    #[Assert\NotBlank(groups: ['creating'])]
    #[Assert\Email]
    #[Groups(['creating', 'created'])]
    #[OA\Property(nullable: false)]
    public ?string $contactEmail = null;
    #[Groups(['creating', 'created'])]
    public ?string $description = null;
    #[Groups(['creating'])]
    #[OA\Property(description: 'Native applications installed on user devices and web apps are considered public since they cannot store secrets securely, so they should use PKCE. https://www.oauth.com/oauth2-servers/pkce/')]
    public ?bool $public = null;
    #[Groups(['creating'])]
    #[Assert\Regex(pattern: RegPatterns::USERNAME, match: true, groups: ['client_credentials'])]
    #[OA\Property(description: 'Required if using the client_credentials grant type. Will attempt to create a bot user with the given username.')]
    public ?string $username = null;
    #[Groups(['created'])]
    public ?UserSmallResponseDto $user = null;
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'), example: ['http://localhost:3000/redirect'])]
    #[Groups(['creating', 'created'])]
    public array $redirectUris = [];
    #[Assert\NotBlank(groups: ['creating'])]
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string', enum: self::AVAILABLE_GRANTS), minItems: 1, example: ['authorization_code', 'refresh_token'])]
    #[Groups(['creating', 'created'])]
    public array $grants = [];
    #[Assert\NotBlank(groups: ['creating'])]
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string', enum: self::AVAILABLE_SCOPES), minItems: 1, example: ['read'])]
    #[Groups(['creating', 'created'])]
    public array $scopes = ['read'];
    #[Groups(['created'])]
    public ?ImageDto $image = null;

    #[Assert\Callback]
    public function validate(
        ExecutionContextInterface $context,
        $payload
    ) {
        $validUris = array_filter($this->redirectUris, fn (string $uri) => filter_var($uri, FILTER_VALIDATE_URL) && !parse_url($uri, PHP_URL_QUERY));
        $invalidUris = array_diff($this->redirectUris, $validUris);
        foreach ($invalidUris as $invalid) {
            $context->buildViolation('Invalid redirect uri "'.$invalid.'"'.(parse_url($invalid, PHP_URL_QUERY) ? ' - the query must be empty' : ''))
                ->atPath('redirectUris')
                ->addViolation();
        }

        $validScopes = array_filter($this->scopes, fn (string $scope) => \array_key_exists($scope, OAuth2UserConsent::SCOPE_DESCRIPTIONS));
        $invalidScopes = array_diff($this->scopes, $validScopes);
        foreach ($invalidScopes as $invalid) {
            $context->buildViolation('Invalid scope "'.$invalid.'"')
                ->atPath('scopes')
                ->addViolation();
        }

        $validGrants = array_filter($this->grants, fn (string $grant) => false !== array_search($grant, ['client_credentials', 'authorization_code', 'refresh_token']));
        $invalidGrants = array_diff($this->grants, $validGrants);
        foreach ($invalidGrants as $invalid) {
            $context->buildViolation('Invalid grant "'.$invalid.'"')
                ->atPath('grants')
                ->addViolation();
        }

        if (false !== array_search('client_credentials', $validGrants) && null === $this->username) {
            $context->buildViolation('client_credentials grant type requires a username for the bot user representing your application.')
                ->atPath('username')
                ->addViolation();
        }

        if (false !== array_search('client_credentials', $validGrants) && $this->public) {
            $context->buildViolation('client_credentials grant type requires a confidential client.')
                ->atPath('username')
                ->addViolation();
        }
    }

    public static function create(string $identifier, string $secret, string $name, UserSmallResponseDto $user = null, string $contactEmail = null, string $description = null, array $redirectUris = [], array $grants = [], array $scopes = ['read'], ImageDto $image = null): OAuth2ClientDto
    {
        $dto = new OAuth2ClientDto();
        $dto->identifier = $identifier;
        $dto->secret = $secret;
        $dto->name = $name;
        $dto->user = $user;
        $dto->contactEmail = $contactEmail;
        $dto->description = $description;
        $dto->redirectUris = $redirectUris;
        $dto->grants = $grants;
        $dto->scopes = $scopes;
        $dto->image = $image;

        return $dto;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'identifier' => $this->identifier,
            'secret' => $this->secret,
            'name' => $this->name,
            'contactEmail' => $this->contactEmail,
            'description' => $this->description,
            'user' => $this->user?->jsonSerialize(),
            'redirectUris' => $this->redirectUris,
            'grants' => $this->grants,
            'scopes' => $this->scopes,
            'image' => $this->image?->jsonSerialize(),
        ];
    }
}
