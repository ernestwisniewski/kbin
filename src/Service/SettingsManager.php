<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\SettingsDto;
use App\Entity\Settings;
use App\Repository\SettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Pure;

class SettingsManager
{
    private static ?SettingsDto $dto = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SettingsRepository $repository,
        private readonly string $kbinDomain,
        private readonly string $kbinTitle,
        private readonly string $kbinMetaTitle,
        private readonly string $kbinMetaDescription,
        private readonly string $kbinMetaKeywords,
        private string $kbinDefaultLang,
        private readonly string $kbinContactEmail,
        private readonly string $kbinSenderEmail,
        private readonly string $kbinMarkdownHowtoUrl,
        private readonly bool $kbinJsEnabled,
        private readonly bool $kbinFederationEnabled,
        private readonly bool $kbinRegistrationsEnabled,
    ) {
        if (!self::$dto) {
            $results = $this->repository->findAll();

            self::$dto = new SettingsDto(
                $this->kbinDomain,
                $this->find($results, 'KBIN_TITLE') ?? $this->kbinTitle,
                $this->find($results, 'KBIN_META_TITLE') ?? $this->kbinMetaTitle,
                $this->find($results, 'KBIN_META_KEYWORDS') ?? $this->kbinMetaKeywords,
                $this->find($results, 'KBIN_META_DESCRIPTION') ?? $this->kbinMetaDescription,
                $this->find($results, 'KBIN_DEFAULT_LANG') ?? $this->kbinDefaultLang,
                $this->find($results, 'KBIN_CONTACT_EMAIL') ?? $this->kbinContactEmail,
                $this->find($results, 'KBIN_SENDER_EMAIL') ?? $this->kbinSenderEmail,
                $this->find($results, 'KBIN_MARKDOWN_HOWTO_URL') ?? $this->kbinMarkdownHowtoUrl,
                $this->find($results, 'KBIN_JS_ENABLED', FILTER_VALIDATE_BOOLEAN) ?? $this->kbinJsEnabled,
                $this->find(
                    $results,
                    'KBIN_FEDERATION_ENABLED',
                    FILTER_VALIDATE_BOOLEAN
                ) ?? $this->kbinFederationEnabled,
                $this->find(
                    $results,
                    'KBIN_REGISTRATIONS_ENABLED',
                    FILTER_VALIDATE_BOOLEAN
                ) ?? $this->kbinRegistrationsEnabled,
            );
        }
    }

    private function find(array $results, string $name, ?int $filter = null): string|bool|null
    {
        $res = array_values(array_filter($results, fn ($s) => $s->name === $name));

        if (count($res)) {
            $res = $res[0]->value;

            if ($filter) {
                $res = filter_var($res, $filter);
            }

            return $res;
        }

        return null;
    }

    public function getDto(): SettingsDto
    {
        return self::$dto;
    }

    public function save(SettingsDto $dto): void
    {
        foreach ($dto as $name => $value) {
            $s = $this->repository->findOneByName($name);

            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            if (!$s) {
                $s = new Settings($name, $value);
            }

            $s->value = $value;

            $this->entityManager->persist($s);
        }

        $this->entityManager->flush();
    }

    #[Pure]
 public function isLocalUrl(string $url): bool
 {
     return parse_url($url, PHP_URL_HOST) === $this->get('KBIN_DOMAIN');
 }

    public function get(string $name)
    {
        return self::$dto->{$name};
    }
    
    public static function getValue(string $name): string
    {
        return self::$dto->{$name};
    }
}
