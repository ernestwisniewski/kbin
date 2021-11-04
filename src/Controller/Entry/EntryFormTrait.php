<?php declare(strict_types=1);

namespace App\Controller\Entry;

use App\DTO\EntryDto;
use App\Entity\Entry;
use App\Form\EntryArticleType;
use App\Form\EntryImageType;
use App\Form\EntryLinkType;
use Symfony\Component\Form\FormInterface;

/**
 * @method createForm(string $class)
 */
trait EntryFormTrait
{
    private function createFormByType(string $type, ?EntryDto $dto = null): FormInterface
    {
        if ($type === Entry::ENTRY_TYPE_ARTICLE) {
            return $this->createForm(EntryArticleType::class, $dto);
        }

        if ($type === Entry::ENTRY_TYPE_IMAGE) {
            return $this->createForm(EntryImageType::class, $dto);
        }

        return $this->createForm(EntryLinkType::class, $dto);
    }
}
