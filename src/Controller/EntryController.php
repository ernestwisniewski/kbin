<?php declare(strict_types=1);

namespace App\Controller;

use App\Event\EntryHasBeenSeenEvent;
use App\PageView\EntryCommentPageView;
use App\PageView\EntryPageView;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\EntryCommentRepository;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\EntryArticleType;
use App\Service\EntryManager;
use App\Repository\Criteria;
use App\Form\EntryLinkType;
use App\Entity\Magazine;
use App\DTO\EntryDto;
use App\Entity\Entry;

class EntryController extends AbstractController
{
    public function __construct(
        private EntryManager $entryManager,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     */
    public function single(
        Magazine $magazine,
        Entry $entry,
        ?string $sortBy,
        EntryCommentRepository $commentRepository,
        EventDispatcherInterface $eventDispatcher,
        Request $request
    ): Response {
        $criteria = (new EntryCommentPageView((int) $request->get('strona', 1)))
            ->showEntry($entry);

        if ($sortBy) {
            $criteria->showSortOption($sortBy);
        }

        $comments = $commentRepository->findByCriteria($criteria);

        $commentRepository->hydrate(...$comments);
        $commentRepository->hydrateChildren(...$comments);

        $eventDispatcher->dispatch((new EntryHasBeenSeenEvent($entry)));

        return $this->render(
            'entry/single.html.twig',
            [
                'magazine' => $magazine,
                'comments' => $comments,
                'entry'    => $entry,
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function create(?Magazine $magazine, ?string $type, Request $request): Response
    {
        $entryDto = new EntryDto();

        if ($magazine) {
            $entryDto->magazine = $magazine;
        }

        $form = $this->createFormByType($entryDto, $type);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entry = $this->entryManager->create($entryDto, $this->getUserOrThrow());

            return $this->redirectToRoute(
                'entry_single',
                [
                    'magazine_name' => $entry->magazine->getName(),
                    'entry_id'      => $entry->getId(),
                ]
            );
        }

        return $this->render(
            $this->getTemplateName($type),
            [
                'magazine' => $magazine,
                'form'     => $form->createView(),
            ]
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("edit", subject="entry")
     */
    public function edit(Magazine $magazine, Entry $entry, Request $request): Response
    {
        $entryDto = $this->entryManager->createDto($entry);

        $form = $this->createFormByType($entryDto, $entryDto->getType());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entry = $this->entryManager->edit($entry, $entryDto);

            return $this->redirectToRoute(
                'entry_single',
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

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("delete", subject="entry")
     */
    public function delete(Magazine $magazine, Entry $entry, Request $request): Response
    {
        $this->validateCsrf('entry_delete', $request->request->get('token'));

        $this->entryManager->delete($entry, !$entry->isAuthor($this->getUserOrThrow()));

        return $this->redirectToRoute(
            'front_magazine',
            [
                'name' => $magazine->getName(),
            ]
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("purge", subject="entry")
     */
    public function purge(Magazine $magazine, Entry $entry, Request $request): Response
    {
        $this->validateCsrf('entry_purge', $request->request->get('token'));

        $this->entryManager->purge($entry);

        return $this->redirectToRoute(
            'front_magazine',
            [
                'name' => $magazine->getName(),
            ]
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("moderate", subject="magazine")
     */
    public function pin(Magazine $magazine, Entry $entry, Request $request): Response
    {
        $this->validateCsrf('entry_pin', $request->request->get('token'));

        $this->entryManager->pin($entry);

        return $this->redirectToRefererOrHome($request);
    }

    private function createFormByType(EntryDto $entryDto, ?string $type): FormInterface
    {
        if (!$type || $type === Entry::ENTRY_TYPE_LINK) {
            return $this->createForm(EntryLinkType::class, $entryDto);
        }

        return $this->createForm(EntryArticleType::class, $entryDto);
    }

    private function getTemplateName(?string $type, ?bool $edit = false): string
    {
        $prefix = $edit ? 'edit' : 'create';

        if (!$type || $type === Entry::ENTRY_TYPE_LINK) {
            return "entry/{$prefix}_link.html.twig";
        }

        return "entry/{$prefix}_article.html.twig";
    }
}
