<?php

declare(strict_types=1);

namespace App\Markdown\Factory;

use League\CommonMark\Environment\EnvironmentInterface;
use Psr\Container\ContainerInterface;

class EnvironmentFactory
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    public function createEnvironment(): EnvironmentInterface
    {
        return $this->container->get(EnvironmentInterface::class);
    }
}
