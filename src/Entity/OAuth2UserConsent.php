<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OAuth2UserConsentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OAuth2UserConsentRepository::class)]
class OAuth2UserConsent
{
    /**
     * An associative array with translation keys for each available oauth2 grant.
     */
    public const SCOPE_DESCRIPTIONS = [
        // Grants read permissions on all public resources - pretty much everything will use this
        'read' => 'oauth2.grant.read.general',
        // Grants all content create and edit permissions
        'write' => 'oauth2.grant.write.general',
        // Grants all content delete permissions
        'delete' => 'oauth2.grant.delete.general',
        // Grants all report permissions
        'report' => 'oauth2.grant.report.general',
        // Grants all vote/boost permissions
        'vote' => 'oauth2.grant.vote.general',
        // Grants all subscription/follow permissions
        'subscribe' => 'oauth2.grant.subscribe.general',
        // Grants all block permissions
        'block' => 'oauth2.grant.block.general',
        // Grants allowing applications to (un)subscribe or (un)block domains on behalf of the user
        'domain' => 'oauth2.grant.domain.all',
        'domain:subscribe' => 'oauth2.grant.domain.subscribe',
        'domain:block' => 'oauth2.grant.domain.block',
        // Grants allowing the application to create, edit, delete, (up/down)vote, boost, or report entries on behalf of the user
        'entry' => 'oauth2.grant.entry.all',
        'entry:create' => 'oauth2.grant.entry.create',
        'entry:edit' => 'oauth2.grant.entry.edit',
        'entry:delete' => 'oauth2.grant.entry.delete',
        'entry:vote' => 'oauth2.grant.entry.vote',
        'entry:report' => 'oauth2.grant.entry.report',
        // Grants allowing the application to create, edit, delete, (up/down)vote, boost, or report entry comments on behalf of the user
        'entry_comment' => 'oauth2.grant.entry_comment.all',
        'entry_comment:create' => 'oauth2.grant.entry_comment.create',
        'entry_comment:edit' => 'oauth2.grant.entry_comment.edit',
        'entry_comment:delete' => 'oauth2.grant.entry_comment.delete',
        'entry_comment:vote' => 'oauth2.grant.entry_comment.vote',
        'entry_comment:report' => 'oauth2.grant.entry_comment.report',
        // Grants allowing the application to (un)subscribe or (un)block magazines on behalf of the user
        'magazine' => 'oauth2.grant.magazine.all',
        'magazine:subscribe' => 'oauth2.grant.magazine.subscribe',
        'magazine:block' => 'oauth2.grant.magazine.block',
        // Grants allowing the application to create, edit, delete, (up/down)vote, boost, or report posts on behalf of the user
        'post' => 'oauth2.grant.post.all',
        'post:create' => 'oauth2.grant.post.create',
        'post:edit' => 'oauth2.grant.post.edit',
        'post:delete' => 'oauth2.grant.post.delete',
        'post:vote' => 'oauth2.grant.post.vote',
        'post:report' => 'oauth2.grant.post.report',
        // Grants allowing the application to create, edit, delete, (up/down)vote, boost, or report post comments on behalf of the user
        'post_comment' => 'oauth2.grant.post_comment.all',
        'post_comment:create' => 'oauth2.grant.post_comment.create',
        'post_comment:edit' => 'oauth2.grant.post_comment.edit',
        'post_comment:delete' => 'oauth2.grant.post_comment.delete',
        'post_comment:vote' => 'oauth2.grant.post_comment.vote',
        'post_comment:report' => 'oauth2.grant.post_comment.report',
        // Various grants related to reading and writing information about the current user,
        //   messages they've sent and received, notifications they have, who they follow, and who they block
        'user' => 'oauth2.grant.user.all',
        'user:profile' => 'oauth2.grant.user.profile.all',
        'user:profile:read' => 'oauth2.grant.user.profile.read',
        'user:profile:edit' => 'oauth2.grant.user.profile.edit',
        'user:message' => 'oauth2.grant.user.message.all',
        'user:message:read' => 'oauth2.grant.user.message.read',
        'user:message:create' => 'oauth2.grant.user.message.create',
        'user:notification' => 'oauth2.grant.user.notification.all',
        'user:notification:read' => 'oauth2.grant.user.notification.read',
        'user:notification:delete' => 'oauth2.grant.user.notification.delete',
        'user:oauth_clients' => 'oauth2.grant.user.oauth_clients.all',
        'user:oauth_clients:read' => 'oauth2.grant.user.oauth_clients.read',
        'user:oauth_clients:edit' => 'oauth2.grant.user.oauth_clients.edit',
        'user:follow' => 'oauth2.grant.user.follow',
        'user:block' => 'oauth2.grant.user.block',
        // Moderation grants
        'moderate' => 'oauth2.grant.moderate.all',
        // Entry moderation grants
        'moderate:entry' => 'oauth2.grant.moderate.entry.all',
        'moderate:entry:language' => 'oauth2.grant.moderate.entry.change_language',
        'moderate:entry:pin' => 'oauth2.grant.moderate.entry.pin',
        'moderate:entry:set_adult' => 'oauth2.grant.moderate.entry.set_adult',
        'moderate:entry:trash' => 'oauth2.grant.moderate.entry.trash',
        // Entry comment moderation grants
        'moderate:entry_comment' => 'oauth2.grant.moderate.entry_comment.all',
        'moderate:entry_comment:language' => 'oauth2.grant.moderate.entry_comment.change_language',
        'moderate:entry_comment:set_adult' => 'oauth2.grant.moderate.entry_comment.set_adult',
        'moderate:entry_comment:trash' => 'oauth2.grant.moderate.entry_comment.trash',
        // Post moderation grants
        'moderate:post' => 'oauth2.grant.moderate.post.all',
        'moderate:post:language' => 'oauth2.grant.moderate.post.change_language',
        'moderate:post:pin' => 'oauth2.grant.moderate.post.pin',
        'moderate:post:set_adult' => 'oauth2.grant.moderate.post.set_adult',
        'moderate:post:trash' => 'oauth2.grant.moderate.post.trash',
        // Post comment moderation grants
        'moderate:post_comment' => 'oauth2.grant.moderate.post_comment.all',
        'moderate:post_comment:language' => 'oauth2.grant.moderate.post_comment.change_language',
        'moderate:post_comment:set_adult' => 'oauth2.grant.moderate.post_comment.set_adult',
        'moderate:post_comment:trash' => 'oauth2.grant.moderate.post_comment.trash',
        // Magazine moderation grants
        'moderate:magazine' => 'oauth2.grant.moderate.magazine.all',
        'moderate:magazine:ban' => 'oauth2.grant.moderate.magazine.ban.all',
        'moderate:magazine:ban:read' => 'oauth2.grant.moderate.magazine.ban.read',
        'moderate:magazine:ban:create' => 'oauth2.grant.moderate.magazine.ban.create',
        'moderate:magazine:ban:delete' => 'oauth2.grant.moderate.magazine.ban.delete',
        'moderate:magazine:list' => 'oauth2.grant.moderate.magazine.list',
        'moderate:magazine:reports' => 'oauth2.grant.moderate.magazine.reports.all',
        'moderate:magazine:reports:read' => 'oauth2.grant.moderate.magazine.reports.read',
        'moderate:magazine:reports:action' => 'oauth2.grant.moderate.magazine.reports.action',
        'moderate:magazine:trash:read' => 'oauth2.grant.moderate.magazine.trash.read',
        // Magazine owner moderation grants
        'moderate:magazine_admin' => 'oauth2.grant.moderate.magazine_admin.all',
        'moderate:magazine_admin:create' => 'oauth2.grant.moderate.magazine_admin.create',
        'moderate:magazine_admin:delete' => 'oauth2.grant.moderate.magazine_admin.delete',
        'moderate:magazine_admin:update' => 'oauth2.grant.moderate.magazine_admin.update',
        'moderate:magazine_admin:theme' => 'oauth2.grant.moderate.magazine_admin.edit_theme',
        'moderate:magazine_admin:moderators' => 'oauth2.grant.moderate.magazine_admin.moderators',
        'moderate:magazine_admin:badges' => 'oauth2.grant.moderate.magazine_admin.badges',
        'moderate:magazine_admin:tags' => 'oauth2.grant.moderate.magazine_admin.tags',
        'moderate:magazine_admin:stats' => 'oauth2.grant.moderate.magazine_admin.stats',
        // Admin grants
        'admin' => 'oauth2.grant.admin.all',
        // Purge content entirely from the instance
        'admin:entry:purge' => 'oauth2.grant.admin.entry.purge',
        'admin:entry_comment:purge' => 'oauth2.grant.admin.entry_comment.purge',
        'admin:post:purge' => 'oauth2.grant.admin.post.purge',
        'admin:post_comment:purge' => 'oauth2.grant.admin.post_comment.purge',
        // Administrate magazines
        'admin:magazine' => 'oauth2.grant.admin.magazine.all',
        'admin:magazine:move_entry' => 'oauth2.grant.admin.magazine.move_entry',
        'admin:magazine:purge' => 'oauth2.grant.admin.magazine.purge',
        // Administrate users
        'admin:user' => 'oauth2.grant.admin.user.all',
        'admin:user:ban' => 'oauth2.grant.admin.user.ban',
        'admin:user:verify' => 'oauth2.grant.admin.user.verify',
        'admin:user:delete' => 'oauth2.grant.admin.user.delete',
        'admin:user:purge' => 'oauth2.grant.admin.user.purge',
        // Administrate site information
        'admin:instance' => 'oauth2.grant.admin.instance.all',
        'admin:instance:stats' => 'oauth2.grant.admin.instance.stats',
        'admin:instance:settings' => 'oauth2.grant.admin.instance.settings.all',
        'admin:instance:settings:read' => 'oauth2.grant.admin.instance.settings.read',
        'admin:instance:settings:edit' => 'oauth2.grant.admin.instance.settings.edit',
        // Update About, FAQ, Contact, ToS, and Privacy Policy
        'admin:instance:information:edit' => 'oauth2.grant.admin.instance.information.edit',
        // Administrate federation with other instances
        'admin:federation' => 'oauth2.grant.admin.federation.all',
        'admin:federation:read' => 'oauth2.grant.admin.federation.read',
        'admin:federation:update' => 'oauth2.grant.admin.federation.update',
        // Administrate oauth applications
        'admin:oauth_clients' => 'oauth2.grant.admin.oauth_clients.all',
        'admin:oauth_clients:read' => 'oauth2.grant.admin.oauth_clients.read',
        'admin:oauth_clients:revoke' => 'oauth2.grant.admin.oauth_clients.revoke',
    ];
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'oAuth2UserConsents')]
    #[ORM\JoinColumn(name: 'user_id', nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'oAuth2UserConsents')]
    #[ORM\JoinColumn(name: 'client_identifier', referencedColumnName: 'identifier', nullable: false)]
    private ?Client $client = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column(type: Types::JSON)]
    private array $scopes = [];

    #[ORM\Column]
    private ?string $ipAddress = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function setScopes(array $scopes): self
    {
        $this->scopes = $scopes;

        return $this;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }
}
