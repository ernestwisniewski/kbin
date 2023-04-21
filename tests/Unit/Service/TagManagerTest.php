<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\TagManager;
use App\Tests\WebTestCase;

class TagManagerTest extends WebTestCase
{
    /**
     * @dataProvider provider
     */
    public function testExtract(string $input, ?array $output): void
    {
        $this->createClient();

        $manager = $this->getContainer()->get(TagManager::class);
        $this->assertEquals($output, $manager->extract($input, 'kbin'));
    }

    public function provider(): array
    {
        return [
            ['Lorem #acme ipsum', ['acme']],
            ['#acme lorem ipsum', ['acme']],
            ['Lorem #acme #kbin #acme2 ipsum', ['acme', 'acme2']],
            ['Lorem ipsum#example', null],
            ['Lorem #acme#example', ['acme']],
            ['Lorem #Acme #acme ipsum', ['acme']],
            ['Lorem ipsum', null],
        ];
    }
}
