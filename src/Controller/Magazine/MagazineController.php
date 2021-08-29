<?php declare(strict_types=1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\DTO\MagazineDto;
use App\Entity\Magazine;
use App\Form\MagazineType;
use App\Repository\MagazineRepository;
use App\Service\MagazineManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MagazineController extends AbstractController
{
    public function __construct(
        private MagazineManager $manager,
    ) {
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request): Response
    {
        $dto = new MagazineDto();

        $form = $this->createForm(MagazineType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $magazine = $this->manager->create($dto, $this->getUserOrThrow());

            return $this->redirectToMagazine($magazine);
        }

        return $this->render(
            'magazine/create.html.twig',
            [
                'form' => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("delete", subject="magazine")
     */
    public function delete(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('magazine_delete', $request->request->get('token'));

        $this->manager->delete($magazine);

        return $this->redirectToRoute('front');
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("purge", subject="magazine")
     */
    public function purge(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('magazine_purge', $request->request->get('token'));

        $this->manager->purge($magazine);

        return $this->redirectToRoute('front');
    }

    public function listAll(MagazineRepository $repository, Request $request)
    {
        return $this->render(
            'magazine/list_all.html.twig',
            [
                'magazines' => $repository->findAllPaginated($this->getPageNb($request)),
            ]
        );
    }

    public function featuredList(?Magazine $magazine, MagazineRepository $repository): Response
    {
        $magazines = $repository->findBy([], null, 45);

        if ($magazine && !in_array($magazine, $magazines)) {
            array_unshift($magazines, $magazine);
        }

        return $this->render(
            'magazine/_featured.html.twig',
            [
                'magazine' => $magazine,
                'magazines' => $magazines,
            ]
        );
    }
}
