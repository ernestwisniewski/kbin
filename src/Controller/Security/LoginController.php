<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Controller\AbstractController;
use App\Entity\Client;
use App\Entity\OAuth2UserConsent;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    public function __invoke(AuthenticationUtils $utils, Request $request): Response
    {
        if ($user = $this->getUser()) {
            return $this->redirectToRoute($user->homepage);
        }

        $error = $utils->getLastAuthenticationError();
        $lastUsername = $utils->getLastUsername();

        return $this->render('user/login.html.twig', ['last_username' => $lastUsername, 'error' => $error, 'not_sso_only_mode' => !$this->getParameter('sso_only_mode')]);
    }

    public function consent(Request $request, EntityManagerInterface $entityManager): Response
    {
        $clientId = $request->query->get('client_id');
        if (!$clientId || !ctype_alnum($clientId) || !$this->getUser()) {
            return $this->redirectToRoute('front');
        }

        /** @var Client $appClient */
        $appClient = $entityManager->getRepository(Client::class)->findOneBy(['identifier' => $clientId]);
        if (!$appClient) {
            $this->addFlash('danger', 'oauth.client_identifier.invalid');

            return $this->redirectToRoute('front');
        }

        $appName = $appClient->getName();

        // Get the client scopes
        $requestedScopes = explode(' ', $request->query->get('scope'));
        // Get the client scopes in the database
        $clientScopes = $appClient->getScopes();

        // Check all requested scopes are in the client scopes, if not return an error
        if (0 < \count(array_diff($requestedScopes, $clientScopes))) {
            $request->getSession()->set('consent_granted', false);

            return $this->redirectToRoute('oauth2_authorize', $request->query->all());
        }

        // Check if the user has already consented to the scopes
        /** @var User $user */
        $user = $this->getUser();
        /** @var ?OAuth2UserConsent $userConsents */
        $userConsents = $user->getOAuth2UserConsents()->filter(
            fn (OAuth2UserConsent $consent) => $consent->getClient() === $appClient
        )->first() ?: null;
        if ($userConsents) {
            $userScopes = $userConsents->getScopes();
        } else {
            $userScopes = [];
        }
        $hasExistingScopes = \count($userScopes) > 0;

        // If user has already consented to the scopes, give consent
        if (0 === \count(array_diff($requestedScopes, $userScopes))) {
            $request->getSession()->set('consent_granted', true);

            return $this->redirectToRoute('oauth2_authorize', $request->query->all());
        }

        // Remove the scopes to which the user has already consented
        $requestedScopes = array_diff($requestedScopes, $userScopes);

        // Get all the scope translation keys in the requested scopes.
        $requestedScopeNames = array_map(fn ($scope) => OAuth2UserConsent::SCOPE_DESCRIPTIONS[$scope], $requestedScopes);
        $existingScopes = array_map(fn ($scope) => OAuth2UserConsent::SCOPE_DESCRIPTIONS[$scope], $userScopes);

        if ($request->isMethod('POST')) {
            if ('yes' === $request->request->get('consent')) {
                $request->getSession()->set('consent_granted', true);
                // Add the requested scopes to the user's scopes
                $consents = $userConsents ?? new OAuth2UserConsent();
                $consents->setScopes(array_merge($requestedScopes, $userScopes));
                $consents->setClient($appClient);
                $consents->setCreatedAt(new \DateTimeImmutable());
                $consents->setExpiresAt(new \DateTimeImmutable('+30 days'));
                $consents->setIpAddress($request->getClientIp());
                $user->addOAuth2UserConsent($consents);
                $entityManager->persist($consents);
                $entityManager->flush();
            }
            if ('no' === $request->request->get('consent')) {
                $request->getSession()->set('consent_granted', false);
            }

            return $this->redirectToRoute('oauth2_authorize', $request->query->all());
        }

        return $this->render('user/consent.html.twig', [
            'app_name' => $appName,
            'scopes' => $requestedScopeNames,
            'has_existing_scopes' => $hasExistingScopes,
            'existing_scopes' => $existingScopes,
            'image' => $appClient->getImage(),
        ]);
    }
}
