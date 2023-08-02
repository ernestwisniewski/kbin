<?php

namespace App\Tests\Functional\Controller\Admin;

use App\Repository\SettingsRepository;
use App\Service\SettingsManager;
use App\Tests\WebTestCase;

class AdminFederationControllerTest extends WebTestCase
{
    public function testAdminCanClearBannedInstances(): void
    {
        $client = $this->createClient();

        $this->getService(SettingsManager::class)->set('KBIN_BANNED_INSTANCES', ['www.example.com']);

        $client->loginUser($this->getUserByUsername('admin', isAdmin: true));

        $crawler = $client->request('GET', '/admin/federation');

        $client->submit($crawler->filter('#content form[name=instances] button')->form(
            ['instances[instances]' => ''],
        ));

        $this->assertSame(
            [],
            $this->getService(SettingsRepository::class)->findOneBy(['name' => 'KBIN_BANNED_INSTANCES'])->json,
        );
    }
}
