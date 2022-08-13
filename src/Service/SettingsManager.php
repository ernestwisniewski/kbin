<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\SettingsDto;
use App\Repository\SettingsRepository;

class SettingsManager
{
    private ?SettingsDto $dto = null;

    public function __construct(
        private SettingsRepository $repository,
        private string $kbinDomain,
        private string $kbinMetaTitle,
        private string $kbinMetaDescription,
        private string $kbinMetaKeywords,
        private string $kbinDefaultLang,
        private string $kbinContactEmail,
        private string $kbinMarkdownHowtoUrl,
        private bool $kbinJsEnabled,
    ) {
        if (!$this->dto) {
            $results = $this->repository->findAll();

            $this->dto = new SettingsDto(
                $this->kbinDomain,
                array_filter($results, fn($s) => $s->name === 'KBIN_META_TITLE')[0]->value ?? $this->kbinMetaTitle,
                array_filter($results, fn($s) => $s->name === 'KBIN_META_KEYWORDS')[0]->value ?? $this->kbinMetaKeywords,
                array_filter($results, fn($s) => $s->name === 'KBIN_META_DESCRIPTION')[0]->value ?? $this->kbinMetaDescription,
                array_filter($results, fn($s) => $s->name === 'KBIN_DEFAULT_LANG')[0]->value ?? $this->kbinDefaultLang,
                array_filter($results, fn($s) => $s->name === 'KBIN_CONTACT_EMAIL')[0]->value ?? $this->kbinContactEmail,
                array_filter($results, fn($s) => $s->name === 'KBIN_MARKDOWN_HOWTO_URL')[0]->value ?? $this->kbinMarkdownHowtoUrl,
                isset(array_filter($results, fn($s) => $s->name === 'KBIN_JS_ENABLED')[0])
                    ? filter_var(array_filter($results, fn($s) => $s->name === 'KBIN_JS_ENABLED')[0]->value, FILTER_VALIDATE_BOOLEAN)
                    : $this->kbinJsEnabled,
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
}
