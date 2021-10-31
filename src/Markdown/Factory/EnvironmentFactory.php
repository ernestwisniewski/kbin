<?php declare(strict_types = 1);

namespace App\Markdown\Factory;

use League\CommonMark\ConfigurableEnvironmentInterface;
use Psr\Container\ContainerInterface;

class EnvironmentFactory
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function createConfigurableEnvironment(): ConfigurableEnvironmentInterface
    {
        return $this->container->get(ConfigurableEnvironmentInterface::class);
    }
}
