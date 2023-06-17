<?php

declare(strict_types=1);

namespace App\Markdown;

use App\Markdown\CommonMark\ExternalImagesRenderer;
use App\Markdown\CommonMark\ExternalLinkRenderer;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\ConfigurableExtensionInterface;
use League\Config\ConfigurationBuilderInterface;

final class MarkdownExtension implements ConfigurableExtensionInterface
{
    public function __construct(
        private readonly ExternalLinkRenderer $linkRenderer,
        private readonly ExternalImagesRenderer $imagesRenderer,
    ) {
    }

    public function configureSchema(ConfigurationBuilderInterface $builder): void
    {
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addRenderer(Link::class, $this->linkRenderer, 1);
        $environment->addRenderer(Image::class, $this->imagesRenderer, 1);
    }
}