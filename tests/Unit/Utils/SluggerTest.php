<?php

declare(strict_types=1);

namespace App\Tests\Unit\Utils;

use App\Utils\Slugger;
use PHPUnit\Framework\TestCase;

class SluggerTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function testCamelCase(string $input, string $output): void
    {
        $this->assertEquals($output, Slugger::camelCase($input));
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
