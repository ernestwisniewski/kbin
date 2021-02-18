<?php

namespace App\Markdown\Factory;

use League\CommonMark\ConfigurableEnvironmentInterface;
use Psr\Container\ContainerInterface;

class EnvironmentFactory
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function createConfigurableEnvironment(): ConfigurableEnvironmentInterface
    {
        return $this->container->get(ConfigurableEnvironmentInterface::class);
    }
}
