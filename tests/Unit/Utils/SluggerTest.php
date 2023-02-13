<?php

declare(strict_types=1);

namespace App\Tests\Unit\Utils;

use App\Tests\WebTestCase;
use App\Utils\Slugger;

class SluggerTest extends WebTestCase
{
    /**
     * @dataProvider provider
     */
    public function testCamelCase(string $input, string $output): void
    {
        $this->createClient();
        $slugger = static::getContainer()->get(Slugger::class);
        $this->assertEquals($output, $slugger->camelCase($input));
    }

    public function provider(): array
    {
        return [
            ['Lorem ipsum', 'loremIpsum'],
            ['LOremIpsum', 'lOremIpsum'],
            ['LORemIpsum', 'lORemIpsum'],
        ];
    }
}
