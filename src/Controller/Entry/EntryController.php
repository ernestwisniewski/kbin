<?php declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\DTO\EntryDto;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Event\Entry\EntryHasBeenSeenEvent;
use App\Form\EntryArticleType;
use App\Form\EntryImageType;
use App\Form\EntryLinkType;
use App\PageView\EntryCommentPageView;
use App\PageView\EntryPageView;
use App\Repository\EntryCommentRepository;
use App\Service\EntryManager;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class EntryController extends AbstractController
{
    public function __construct(
        private EntryManager $manager,
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
        EntryCommentRepository $repository,
        EventDispatcherInterface $dispatcher,
        Request $request
    ): Response {
        $criteria = new EntryCommentPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy));
        $criteria->entry = $entry;

        $comments = $repository->findByCriteria($criteria);

        $repository->hydrate(...$comments);
        $repository->hydrateChildren(...$comments);

        $dispatcher->dispatch((new EntryHasBeenSeenEvent($entry)));

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
        $dto           = new EntryDto();
        $dto->ip       = $request->getClientIp();
        $dto->magazine = $magazine;

        $form = $this->createFormByType($dto, (new EntryPageView(1))->resolveType($type));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('create_content', $dto->magazine)) {
                throw new AccessDeniedHttpException();
            }

            $entry = $this->manager->create($dto, $this->getUserOrThrow());

            return $this->redirectToEntry($entry);
        }

        return $this->render(
            $this->getTemplateName((new EntryPageView(1))->resolveType($type)),
            [
                'magazine' => $magazine,
                'form'     => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }

    private function createFormByType(EntryDto $dto, ?string $type): FormInterface
    {
        if ($type === Entry::ENTRY_TYPE_ARTICLE) {
            return $this->createForm(EntryArticleType::class, $dto);
        }

        if ($type === Entry::ENTRY_TYPE_IMAGE) {
            return $this->createForm(EntryImageType::class, $dto);
        }

        return $this->createForm(EntryLinkType::class, $dto);
    }

    private function getTemplateName(?string $type, ?bool $edit = false): string
    {
        $prefix = $edit ? 'edit' : 'create';

        if (!$type || $type === Entry::ENTRY_TYPE_ARTICLE) {
            return "entry/{$prefix}_article.html.twig";
        }

        if ($type === Entry::ENTRY_TYPE_IMAGE) {
            return "entry/{$prefix}_image.html.twig";
        }

        return "entry/{$prefix}_link.html.twig";
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
        $dto = $this->manager->createDto($entry);

        $form = $this->createFormByType($dto, (new EntryPageView(1))->resolveType($entry->type));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('create_content', $dto->magazine)) {
                throw new AccessDeniedHttpException();
            }

            $entry = $this->manager->edit($entry, $dto);

            return $this->redirectToEntry($entry);
        }

        return $this->render(
            $this->getTemplateName((new EntryPageView(1))->resolveType($entry->type), true),
            [
                'magazine' => $magazine,
                'entry'    => $entry,
                'form'     => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
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

        $this->manager->delete($this->getUserOrThrow(), $entry);

        return $this->redirectToMagazine($magazine);
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

        $this->manager->purge($this->getUserOrThrow(), $entry);

        return $this->redirectToMagazine($magazine);
    }
}
