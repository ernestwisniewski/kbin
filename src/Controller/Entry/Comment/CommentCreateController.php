<?php declare(strict_types=1);

namespace App\Controller\Entry\Comment;

use App\Controller\AbstractController;
use App\DTO\EntryCommentDto;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Form\EntryCommentType;
use App\PageView\EntryCommentPageView;
use App\Repository\EntryCommentRepository;
use App\Service\CloudflareIpResolver;
use App\Service\EntryCommentManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CommentCreateController extends AbstractController
{
    use CommentResponseTrait;

    public function __construct(
        private EntryCommentManager $manager,
        private EntryCommentRepository $repository,
        private CloudflareIpResolver $ipResolver
    ) {
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     * @ParamConverter("parent", options={"mapping": {"parent_comment_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("comment", subject="entry")
     */
    public function __invoke(
        Magazine $magazine,
        Entry $entry,
        ?EntryComment $parent,
        Request $request,
    ): Response {
        $form = $this->getForm($entry, $parent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto           = $form->getData();
            $dto->magazine = $magazine;
            $dto->entry    = $entry;
            $dto->parent   = $parent;
            $dto->ip       = $this->ipResolver->resolve();

            if (!$this->isGranted('create_content', $dto->magazine)) {
                throw new AccessDeniedHttpException();
            }

            return $this->handleValidRequest($dto, $request);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonFormResponse($form, 'entry/comment/_form.html.twig', ['parent' => $parent, 'entry' => $entry]);
        }

        $criteria        = new EntryCommentPageView($this->getPageNb($request));
        $criteria->entry = $entry;

        return $this->getEntryCommentPageResponse('entry/comment/create.html.twig', $criteria, $form, $request, $parent);
    }

    private function getForm(Entry $entry, ?EntryComment $parent = null): FormInterface
    {
        return $this->createForm(
            EntryCommentType::class,
            null,
            [
                'action' => $this->generateUrl(
                    'entry_comment_create',
                    [
                        'magazine_name'     => $entry->magazine->name,
                        'entry_id'          => $entry->getId(),
                        'parent_comment_id' => $parent?->getId(),
                    ]
                ),
            ]
        );
    }

    private function handleValidRequest(EntryCommentDto $dto, Request $request): Response
    {
        $comment = $this->manager->create($dto, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonCommentSuccessResponse($comment);
        }

        return $this->redirectToEntry($comment->entry);
    }
}
