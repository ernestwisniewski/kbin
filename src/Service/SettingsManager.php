<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\SettingsDto;
use App\Entity\Settings;
use App\Repository\SettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Pure;

class SettingsManager
{
    private ?SettingsDto $dto = null;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private SettingsRepository $repository,
        private string $kbinDomain,
        private string $kbinMetaTitle,
        private string $kbinMetaDescription,
        private string $kbinMetaKeywords,
        private string $kbinDefaultLang,
        private string $kbinContactEmail,
        private string $kbinMarkdownHowtoUrl,
        private bool $kbinJsEnabled,
        private bool $kbinFederationEnabled,
        private bool $kbinRegistrationsEnabled,
    ) {
        if (!$this->dto) {
            $results = $this->repository->findAll();

            $this->dto = new SettingsDto(
                $this->kbinDomain,
                $this->find($results, 'KBIN_META_TITLE') ?? $this->kbinMetaTitle,
                $this->find($results, 'KBIN_META_KEYWORDS') ?? $this->kbinMetaKeywords,
                $this->find($results, 'KBIN_META_DESCRIPTION') ?? $this->kbinMetaDescription,
                $this->find($results, 'KBIN_DEFAULT_LANG') ?? $this->kbinDefaultLang,
                $this->find($results, 'KBIN_CONTACT_EMAIL') ?? $this->kbinContactEmail,
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

    public function get(string $name)
    {
        return $this->dto->{$name};
    }

    public function getDto(): SettingsDto
    {

        return $this->dto;
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
            $this->entityManager->flush();
        }
    }

    #[Pure] public function isLocalUrl(string $url): bool
    {
        return parse_url($url, PHP_URL_HOST) === $this->get('KBIN_DOMAIN');
    }

    private function find(array $results, string $name, ?int $filter = null): string|bool|null
    {
        $res = array_values(array_filter($results, fn($s) => $s->name === $name));

        if (count($res)) {
            $res = $res[0]->value;

            if ($filter) {
                $res = filter_var($res, $filter);
            }

            return $res;
        }

        return null;
    }
}
