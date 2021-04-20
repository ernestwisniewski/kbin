<?php declare(strict_types=1);

namespace App\Tests\Command;

use App\Utils\Slugger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SluggerTest extends KernelTestCase
{
    /**
     * @dataProvider provider
     */
    public function testCamelCase(string $input, string $output)
    {
        $this->assertEquals($output, (new Slugger())->camelCase($input));
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
