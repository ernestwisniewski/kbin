<?php declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\EntryArticleType;
use App\Service\EntryManager;
use App\Form\EntryLinkType;
use App\Entity\Magazine;
use App\DTO\EntryDto;
use App\Entity\Entry;

class EntryController extends AbstractController
{
    /**
     * @var EntryManager
     */
    private $entryManager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntryManager $entryManager, EntityManagerInterface $entityManager)
    {
        $this->entryManager  = $entryManager;
        $this->entityManager = $entityManager;
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     */
    public function front(Magazine $magazine, Entry $entry): Response
    {
        return $this->render(
            'entry/front.html.twig',
            [
                'magazine' => $magazine,
                'entry'    => $entry,
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function createEntry(?Magazine $magazine, ?string $type, Request $request): Response
    {
        $entryDto = new EntryDto();

        if ($magazine) {
            $entryDto->setMagazine($magazine);
        }

        $form = $this->createFormByType($entryDto, $type);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entry = $this->entryManager->createEntry($entryDto, $this->getUserOrThrow());
            $this->entityManager->flush();

            return $this->redirectToRoute(
                'entry',
                [
                    'magazine_name' => $entry->getMagazine()->getName(),
                    'entry_id'      => $entry->getId(),
                ]
            );
        }

        return $this->render(
            $this->getTemplateName($type),
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     */
    public function editEntry(Magazine $magazine, Entry $entry, Request $request): Response
    {
        $entryDto = $this->entryManager->createEntryDto($entry);

        $form = $this->createFormByType($entryDto, $entryDto->getType());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entry = $this->entryManager->editEntry($entry, $entryDto);

            $this->entityManager->flush();

            return $this->redirectToRoute(
                'entry',
                [
                    'magazine_name' => $magazine->getName(),
                    'entry_id'      => $entry->getId(),
                ]
            );
        }

        return $this->render(
            $this->getTemplateName($entryDto->getType(), true),
            [
                'magazine' => $magazine,
                'entry'    => $entry,
                'form'     => $form->createView(),
            ]
        );
    }

    private function createFormByType(EntryDto $entryDto, ?string $type): FormInterface
    {
        switch ($type) {
            case Entry::ENTRY_TYPE_ARTICLE:
                return $this->createForm(EntryArticleType::class, $entryDto);
            default:
                return $this->createForm(EntryLinkType::class, $entryDto);
        }
    }

    private function getTemplateName(?string $type, ?bool $edit = false): string
    {
        $prefix = $edit ? 'edit' : 'create';

        switch ($type) {
            case Entry::ENTRY_TYPE_ARTICLE:
                return "entry/{$prefix}_article.html.twig";
                break;
            default:
                return "entry/{$prefix}_link.html.twig";
        }
    }
}
