<?php declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\DTO\EntryCommentDto;
use App\DTO\EntryDto;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\PageView\EntryPageView;
use App\Repository\Criteria;
use App\Service\CloudflareIpResolver;
use App\Service\EntryCommentManager;
use App\Service\EntryManager;
use JetBrains\PhpStorm\Pure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntryCreateController extends AbstractController
{
    use EntryTemplateTrait;
    use EntryFormTrait;

    public function __construct(
        private EntryManager $manager,
        private EntryCommentManager $commentManager,
        private ValidatorInterface $validator,
        private CloudflareIpResolver $ipResolver
    ) {
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function __invoke(?Magazine $magazine, ?string $type, Request $request): Response
    {
        $dto           = new EntryDto();
        $dto->magazine = $magazine;

        $form = $this->createFormByType((new EntryPageView(1))->resolveType($type), $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto     = $form->getData();
            $dto->ip = $this->ipResolver->resolve();

            if (!$this->isGranted('create_content', $dto->magazine)) {
                throw new AccessDeniedHttpException();
            }

            $dto->lang = $form->get('isEng')->getData() ? 'en' : null;

            $entry = $this->manager->create($dto, $this->getUserOrThrow());

            $this->createComment($form, $entry);

            $this->addFlash(
                'success',
                'flash_thread_new_success'
            );

            return $this->redirectToMagazine($entry->magazine);
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

    #[Pure] private function createCommentDto(Entry $entry, string $body): EntryCommentDto
    {
        $comment           = new EntryCommentDto();
        $comment->magazine = $entry->magazine;
        $comment->entry    = $entry;
        $comment->user     = $entry->user;
        $comment->body     = $body;

        return $comment;
    }

    private function createComment(FormInterface $form, Entry $entry): void
    {
        if ($form->has('comment') && $form->get('comment')->getData()) {
            $comment = $this->createCommentDto($entry, $form->get('comment')->getData());
            $errors  = $this->validator->validate($comment);
            if (!count($errors)) {
                $this->commentManager->create($comment, $this->getUserOrThrow());
            }
        }
    }
}
