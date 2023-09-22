# OAuth2 Guide
## Available Grants
1. `client_credentials`
    * [documentation here](https://www.oauth.com/oauth2-servers/access-tokens/client-credentials/)
    * Best used for bots and clients that only ever need to authenticate as a single user, from a trusted device.
    * Note that bots authenticating with this grant type will be distinguished as bots and will not be allowed to vote on content.
2. `authorization_code`
    * [documentation here](https://www.oauth.com/oauth2-servers/access-tokens/authorization-code-request/)
    * public clients must use [PKCE](https://www.oauth.com/oauth2-servers/pkce/) to authenticate.
    * A public client is any client that will be installed on a device that is not controlled by the client's creator
        * Native apps
        * Single page web apps
        * Or similar
3. `refresh_token`
    * [documentation here](https://www.oauth.com/oauth2-servers/making-authenticated-requests/refreshing-an-access-token/)
    * Refresh tokens are used with the `authorization_code` grant type to reduce the number of times the user must log in.
## Endpoints
```php
// TODO
```

## Obtaining OAuth2 credentials from a new server

*Note: some of these structures contain comments that need to be removed before making the API calls. Copy/paste with care.*

1. Create a private OAuth2 client (for use in secure environments that you control)
```
POST /api/client

{
  "name": "My OAuth2 Authorization Code Client",
  "contactEmail": "contact@some.dev",
  "description": "A client that I will be using to authenticate to /kbin's API",
  "public": false,
  "redirectUris": [
    "https://localhost:3000/redirect",
    "myapp://redirect"
  ],
  "grants": [
    "authorization_code",
    "refresh_token"
  ],
  # All the scopes the client will be allowed to request
  # See following section for a list of available fine grained scopes.
  "scopes": [
    "read"
  ]
}
```
2. Save the identifier and secret returned by this API call - this will be the only time you can access the secret for a private client.
```
{
    "identifier": "someRandomString",
    "secret": "anEvenLongerRandomStringThatYouShouldKeepSafe",
    ... # more info about the client that just confirms what you've created
}
```
3. Use the OAuth2 client id and secret you just created to obtain credentials for a user (This is a standard authorization_code OAuth2 flow, which is supported by many libraries for your preferred language)
    1. Begin authorization_code OAuth2 flow
    ```
    GET /authorize?response_type=code&client_id=(the client id generated at client creation)&redirect_uri=(One of the URIs added during client creation)&scope=(space-delimited list of scopes)&state=(random string for CSRF protection)
    ```
    2. The user will be directed to log in to their account and grant their consent for the scopes you have requested.
    3. When the user grants their consent, their browser will be redirected to the given redirect_uri with a `code` query parameter, as long as it matches one of the URIs provided when the client was created.
    4. After obtaining the code, obtain an authorization token with a multipart/form-data POST request:
    ```
    POST /token

    grant_type=authorization_code
    client_id=(the client id generated at client creation)
    client_secret=(the client secret generated at client creation)
    code=(OAuth2 code received from redirect)
    redirect_uri=(One of the URIs added during client creation)
    ```
    5. The token endpoint will respond with the token and information about it
    ```json
    {
        "token_type": "Bearer",
        "expires_in": 3600, // seconds
        "access_token": "aLargeEncodedTokenToBeUsedInTheAuthorizationHeader",
        "refresh_token": "aLargeEncodedTokenToBeUsedInTheRefreshTokenFlow"
    }
    ```


## Available Scopes
### Scope tree
1. `read` - Allows retrieval of threads from the user's subscribed magazines/domains and viewing the user's favorited entries.
2. `write` - Provides all the following nested scopes
    * `entry:create`
    * `entry:edit`
    * `entry_comment:create`
    * `entry_comment:edit`
    * `post:create`
    * `post:edit`
    * `post_comment:create`
    * `post_comment:edit`
3. `delete` - Provides all the following nested scopes, for deleting the current user's content
    * `entry:delete`
    * `entry_comment:delete`
    * `post:delete`
    * `post_comment:delete`
4. `subscribe` - Provides the following nested scopes
    * `domain:subscribe`
        * Allows viewing and editing domain subscriptions
    * `magazine:subscribe`
        * Allows viewing and editing magazine subscriptions
    * `user:follow`
        * Allows viewing and editing user follows
5. `block` - Provides the following nested scopes
    * `domain:block`
        * Allows viewing and editing domain blocks
    * `magazine:block`
        * Allows viewing and editing magazine blocks
    * `user:block`
        * Allows viewing and editing user blocks
6. `vote` - Provides the following nested scopes, for up/down voting and boosting content
    * `entry:vote`
    * `entry_comment:vote`
    * `post:vote`
    * `post_comment:vote`
7. `report` - Provides the following nested scopes
    * `entry:report`
    * `entry_comment:report`
    * `post:report`
    * `post_comment:report`
8. `domain` - Provides all domain scopes
    * `domain:subscribe`
    * `domain:block`
9. `entry` - Provides all entry scopes
    * `entry:create`
    * `entry:edit`
    * `entry:delete`
    * `entry:vote`
    * `entry:report`
10. `entry_comment` - Provides all entry comment scopes
    * `entry_comment:create`
    * `entry_comment:edit`
    * `entry_comment:delete`
    * `entry_comment:vote`
    * `entry_comment:report`
11. `magazine` - Provides all magazine user level scopes
    * `magazine:subscribe`
    * `magazine:block`
12. `post` - Provides all post scopes
    * `post:create`
    * `post:edit`
    * `post:delete`
    * `post:vote`
    * `post:report`
13. `post_comment` - Provides all post comment scopes
    * `post_comment:create`
    * `post_comment:edit`
    * `post_comment:delete`
    * `post_comment:vote`
    * `post_comment:report`
14. `user` - Provides all user access scopes
    * `user:profile`
        * `user:profile:read`
            * Allows access to the current user's settings and profile via the `/api/user/me` endpoint
        * `user:profile:edit`
            * Allows updating the current user's settings and profile
    * `user:message`
        * `user:message:read`
            * Allows the client to view the current user's messages
            * Also allows the client to mark unread messages as read or read messages as unread
        * `user:message:create`
            * Allows the client to create new messages to other users or reply to existing messages
    * `user:notification`
        * `user:notification:read`
            * Allows the client to read notifications about threads, posts, or comments being replied to, as well as moderation notifications.
            * Does not allow the client to read the content of messages. Message notifications will have their content censored unless the `user:message:read` scope is granted.
            * Allows the client to read the number of unread notifications, and mark them as read/unread
        * `user:notification:delete`
            * Allows the client to clear notifications
15. `moderate` - grants all moderation permissions. The user must be a moderator to perform these actions
    * `moderate:entry` - Allows the client to retrieve a list of threads from magazines moderated by the user
        * `moderate:entry:language`
            * Allows changing the language of threads moderated by the user
        * `moderate:entry:pin`
            * Allows pinning/unpinning threads to the top of magazines moderated by the user
        * `moderate:entry:set_adult`
            * Allows toggling the NSFW status of threads moderated by the user
        * `moderate:entry:trash`
            * Allows soft deletion or restoration of threads moderated by the user
    * `moderate:entry_comment`
        * `moderate:entry_comment:language`
            * Allows changing the language of comments in threads moderated by the user
        * `moderate:entry_comment:set_adult`
            * Allows toggling the NSFW status of comments in threads moderated by the user
        * `moderate:entry_comment:trash`
            * Allows soft deletion or restoration of comments in threads moderated by the user
    * `moderate:post`
        * `moderate:post:language`
            * Allows changing the language of posts moderated by the user
        * `moderate:post:set_adult`
            * Allows toggling the NSFW status of posts moderated by the user
        * `moderate:post:trash`
            * Allows soft deletion or restoration of posts moderated by the user
    * `moderate:post_comment`
        * `moderate:post_comment:language`
            * Allows changing the language of comments on posts moderated by the user
        * `moderate:post_comment:set_adult`
            * Allows toggling the NSFW status of comments on posts moderated by the user
        * `moderate:post_comment:trash`
            * Allows soft deletion or restoration of comments on posts moderated by the user
    * `moderate:magazine`
        * `moderate:magazine:ban`
            * `moderate:magazine:ban:read`
                * Allows viewing the users banned from the magazine
            * `moderate:magazine:ban:create`
                * Allows the client to ban a user from the magazine
            * `moderate:magazine:ban:delete`
                * Allows the client to unban a user from the magazine
        * `moderate:magazine:list`
            * Allows the client to view a list of magazines the user moderates
        * `moderate:magazine:reports`
            * `moderate:magazine:reports:read`
                * Allows the client to read reports about content from magazines the user moderates
            * `moderate:magazine:reports:action`
                * Allows the client to act on reports, either accepting or rejecting them
        * `moderate:magazine:trash:read`
            * Allows viewing the removed content of a moderated magazine
    * `moderate:magazine_admin`
        * `moderate:magazine_admin:create`
            * Allows the creation of new magazines
        * `moderate:magazine_admin:delete`
            * Allows the deletion of magazines the user has permission to delete
        * `moderate:magazine_admin:update`
            * Allows magazine rules, description, settings, title, etc. to be updated
        * `moderate:magazine_admin:theme`
            * Allows updates to the magazine theme
        * `moderate:magazine_admin:moderators`
            * Allows the addition or removal of moderators to/from an owned magazine
        * `moderate:magazine_admin:badges`
            * Allows the addition or removal of badges to/from an owned magazine
        * `moderate:magazine_admin:tags`
            * Allows the addition or removal of tags to/from an owned magazine
        * `moderate:magazine_admin:stats`
            * Allows the client to view stats from an owned magazine
16. `admin` - All scopes require the instance admin role to perform
    * `admin:entry:purge`
        * Allows threads to be completely removed from the instance
    * `admin:entry_comment:purge`
        * Allows comments in threads to be completely removed from the instance
    * `admin:post:purge`
        * Allows posts to be completely removed from the instance
    * `admin:post_comment:purge`
        * Allows post comments to be completely removed from the instance
    * `admin:magazine`
        * `admin:magazine:move_entry`
            * Allows an admin to move an entry to another magazine
        * `admin:magazine:purge`
            * Allows an admin to completely purge a magazine from the instance
    * `admin:user`
        * `admin:user:ban`
            * Allows the admin to ban or unban users from the instance
        * `admin:user:verify`
            * Allows the admin to verify a user on the instance
        * `admin:user:purge`
            * Allows the admin to completely purge a user from the instance
    * `admin:instance`
        * `admin:instance:settings`
            * `admin:instance:settings:read`
                * Allows the admin to read instance settings
            * `admin:instance:settings:edit`
                * Allows the admin to update instance settings
        * `admin:instance:information:edit`
            * Allows the admin to update information on the About, Contact, FAQ, Privacy Policy, and Terms of Service pages.
    * `admin:federation`
        * `admin:federation:read`
            * Allows the admin to read a list of defederated instances
        * `admin:federation:update`
            * Allows the admin to edit the list of defederated instances
    * `admin:oauth_clients`
        * `admin:oauth_clients:read`
            * Allows the admin to read usage stats of OAuth clients, as well as list clients on the instance
        * `admin:oauth_clients:revoke`
            * Allows the admin to revoke a client's permission to access the instance

