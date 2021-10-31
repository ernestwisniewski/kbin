<?php declare(strict_types = 1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\DTO\EntryDto;
use App\Entity\Magazine;
use App\PageView\EntryPageView;
use App\Service\EntryManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EntryCreateController extends AbstractController
{
    use EntryTemplateTrait;
    use EntryFormTrait;

    public function __construct(
        private EntryManager $manager,
    ) {
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function __invoke(?Magazine $magazine, ?string $type, Request $request): Response
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
}
