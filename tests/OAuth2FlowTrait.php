<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait OAuth2FlowTrait
{
    protected const JWT_REGEX = '/[0-9a-zA-Z\-_]+(\.[0-9a-zA-Z\-_]+){2}/';
    protected const CODE_REGEX = '/[0-9a-f]{104,}/';

    protected static function buildPrivateAuthCodeQuery(string $clientId, string $scopes, string $state, string $redirectUri): string
    {
        return strtr(
            http_build_query(
                [
                    'response_type' => 'code',
                    'client_id' => $clientId,
                    'redirect_uri' => $redirectUri,
                    'scope' => $scopes,
                    'state' => $state,
                ],
                encoding_type: PHP_QUERY_RFC3986
            ),
            [
                '%3A' => ':',
                '%2F' => '/',
            ]
        );
    }

    protected static function runAuthorizationCodeFlowToConsentPage(KernelBrowser $client, string $scopes, string $state, string $clientId = 'testclient', string $redirectToUri = 'https://localhost:3001'): void
    {
        $query = self::buildPrivateAuthCodeQuery($clientId, $scopes, $state, $redirectToUri);

        $uri = '/authorize?'.$query;

        $client->request('GET', $uri);

        $redirectUri = '/consent?'.$query;

        self::assertResponseRedirects($redirectUri);
        $client->followRedirect();
    }

    protected static function runAuthorizationCodeFlowToRedirectUri(KernelBrowser $client, string $scopes, string $consent, string $state, string $clientId = 'testclient', string $redirectUri = 'https://localhost:3001'): void
    {
        $crawler = $client->getCrawler();

        $client->submit(
            $crawler->selectButton('consent')->form(
                [
                    'consent' => $consent,
                ]
            )
        );

        $query = self::buildPrivateAuthCodeQuery($clientId, $scopes, $state, $redirectUri);

        $redirectUri = '/authorize?'.$query;

        self::assertResponseRedirects($redirectUri);

        $client->followRedirect();

        self::assertResponseRedirects();
    }

    public static function runAuthorizationCodeFlow(KernelBrowser $client, string $consent = 'yes', string $scopes = 'read write', string $state = 'oauth2state', string $clientId = 'testclient', string $redirectUri = 'https://localhost:3001'): void
    {
        self::runAuthorizationCodeFlowToConsentPage($client, $scopes, $state, $clientId, $redirectUri);
        self::runAuthorizationCodeFlowToRedirectUri($client, $scopes, $consent, $state, $clientId, $redirectUri);
    }

    public static function runAuthorizationCodeTokenFlow(KernelBrowser $client, string $clientId = 'testclient', string $clientSecret = 'testsecret', string $redirectUri = 'https://localhost:3001'): array
    {
        $response = $client->getResponse();
        $parsedUrl = parse_url($response->headers->get('Location'));

        $result = [];
        parse_str($parsedUrl['query'], $result);

        self::assertArrayHasKey('code', $result);
        self::assertMatchesRegularExpression(self::CODE_REGEX, $result['code']);
        self::assertArrayHasKey('state', $result);
        self::assertEquals('oauth2state', $result['state']);

        $client->request('POST', '/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $result['code'],
            'redirect_uri' => $redirectUri,
        ]);

        $response = $client->getResponse();

        self::assertJson($response->getContent());

        return json_decode($response->getContent(), associative: true);
    }

    private const VERIFIER_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~';

    protected static function getPKCECodes(): array
    {
        $toReturn = [];
        $toReturn['verifier'] = implode(array_map(fn (string $byte) => self::VERIFIER_ALPHABET[\ord($byte) % \strlen(self::VERIFIER_ALPHABET)], str_split(random_bytes(64))));
        $hash = hash('sha256', $toReturn['verifier'], binary: true);
        $toReturn['challenge'] = rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');

        return $toReturn;
    }

    protected static function buildPublicAuthCodeQuery(string $clientId, string $challenge, string $challengeMethod, string $scopes, string $state, string $redirectUri): string
    {
        return strtr(
            http_build_query(
                [
                    'response_type' => 'code',
                    'client_id' => $clientId,
                    'code_challenge' => $challenge,
                    'code_challenge_method' => $challengeMethod,
                    'redirect_uri' => $redirectUri,
                    'scope' => $scopes,
                    'state' => $state,
                ],
                encoding_type: PHP_QUERY_RFC3986
            ),
            [
                '%3A' => ':',
                '%2F' => '/',
            ]
        );
    }

    protected static function runPublicAuthorizationCodeFlowToConsentPage(KernelBrowser $client, string $scopes, string $state, string $challenge, string $challengeMethod = 'S256', string $clientId = 'testpublicclient', string $redirectUri = 'https://localhost:3001'): void
    {
        $query = self::buildPublicAuthCodeQuery($clientId, $challenge, $challengeMethod, $scopes, $state, $redirectUri);

        $uri = '/authorize?'.$query;

        $client->request('GET', $uri);

        $redirectUri = '/consent?'.$query;

        self::assertResponseRedirects($redirectUri);
        $client->followRedirect();
    }

    protected static function runPublicAuthorizationCodeFlowToRedirectUri(KernelBrowser $client, string $scopes, string $consent, string $state, string $challenge, string $challengeMethod = 'S256', string $clientId = 'testpublicclient', string $redirectUri = 'https://localhost:3001'): void
    {
        $crawler = $client->getCrawler();

        $client->submit(
            $crawler->selectButton('consent')->form(
                [
                    'consent' => $consent,
                ]
            )
        );

        $query = self::buildPublicAuthCodeQuery($clientId, $challenge, $challengeMethod, $scopes, $state, $redirectUri);

        $redirectUri = '/authorize?'.$query;

        self::assertResponseRedirects($redirectUri);

        $client->followRedirect();

        self::assertResponseRedirects();
    }

    /**
     * @return array Array with PKCE challenge and verifier codes in the 'challenge' and 'verifier' keys. Verifier needs to be passed when retrieving token
     */
    public static function runPublicAuthorizationCodeFlow(KernelBrowser $client, string $consent = 'yes', string $scopes = 'read write', string $state = 'oauth2state', string $clientId = 'testpublicclient', string $redirectUri = 'https://localhost:3001'): array
    {
        $codes = self::getPKCECodes();
        self::runPublicAuthorizationCodeFlowToConsentPage($client, $scopes, $state, $codes['challenge'], clientId: $clientId, redirectUri: $redirectUri);
        self::runPublicAuthorizationCodeFlowToRedirectUri($client, $scopes, $consent, $state, $codes['challenge'], clientId: $clientId, redirectUri: $redirectUri);

        return $codes;
    }

    public static function getAuthorizationCodeTokenResponse(KernelBrowser $client, string $clientId = 'testclient', string $clientSecret = 'testsecret', string $redirectUri = 'https://localhost:3001', string $scopes = 'read write'): array
    {
        self::runAuthorizationCodeFlow($client, 'yes', $scopes, clientId: $clientId, redirectUri: $redirectUri);

        return self::runAuthorizationCodeTokenFlow($client, $clientId, $clientSecret, $redirectUri);
    }

    public static function getRefreshTokenResponse(KernelBrowser $client, string $refreshToken, string $clientId = 'testclient', string $clientSecret = 'testsecret', string $redirectUri = 'https://localhost:3001', string $scopes = 'read write'): array
    {
        $client->request('POST', '/token', [
            'grant_type' => 'refresh_token',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
        ]);

        $response = $client->getResponse();

        self::assertJson($response->getContent());

        return json_decode($response->getContent(), associative: true);
    }

    public static function runPublicAuthorizationCodeTokenFetch(KernelBrowser $client, string $verifier, string $clientId = 'testpublicclient', string $redirectUri = 'https://localhost:3001'): void
    {
        $response = $client->getResponse();
        $parsedUrl = parse_url($response->headers->get('Location'));

        $result = [];
        parse_str($parsedUrl['query'], $result);

        self::assertArrayHasKey('code', $result);
        self::assertMatchesRegularExpression(self::CODE_REGEX, $result['code']);
        self::assertArrayHasKey('state', $result);
        self::assertEquals('oauth2state', $result['state']);

        $client->request('POST', '/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $clientId,
            'code_verifier' => $verifier,
            'code' => $result['code'],
            'redirect_uri' => $redirectUri,
        ]);
    }

    public static function getPublicAuthorizationCodeTokenResponse(KernelBrowser $client, string $clientId = 'testpublicclient', string $redirectUri = 'https://localhost:3001', string $scopes = 'read write'): array
    {
        $pkceCodes = self::runPublicAuthorizationCodeFlow($client, 'yes', $scopes, clientId: $clientId);

        self::runPublicAuthorizationCodeTokenFetch($client, $pkceCodes['verifier'], $clientId, $redirectUri);

        $response = $client->getResponse();

        self::assertJson($response->getContent());

        return json_decode($response->getContent(), associative: true);
    }
}
