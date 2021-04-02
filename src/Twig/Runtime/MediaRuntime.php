<?php declare(strict_types=1);

namespace App\Twig\Runtime;

use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

class MediaRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private FilesystemInterface $publicUploadsFilesystem,
        private RequestStack $requestStack,
        private string $uploadedAssetsBaseUrl
    ) {
    }

    public function getPublicPath(string $path): string
    {
        return $this->requestStack->getCurrentRequest()->getBasePath().$this->uploadedAssetsBaseUrl.'/'.$path;
    }
}
