<?php declare(strict_types = 1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\PageView\EntryPageView;
use App\Service\EntryManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EntryEditController extends AbstractController
{
    use EntryTemplateTrait;
    use EntryFormTrait;

    public function __construct(
        private EntryManager $manager,
    ) {
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("edit", subject="entry")
     */
    public function __invoke(Magazine $magazine, Entry $entry, Request $request): Response
    {
        $dto = $this->manager->createDto($entry);

        $form = $this->createFormByType((new EntryPageView(1))->resolveType($entry->type), $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('create_content', $dto->magazine)) {
                throw new AccessDeniedHttpException();
            }

            $dto->lang = $form->get('isEng')->getData() ? 'en' : null;

            $entry = $this->manager->edit($entry, $dto);

            $this->addFlash(
                'success',
                'flash_thread_edit_success'
            );

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
}
