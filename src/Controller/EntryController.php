<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\EntryManager;
use App\Entity\Magazine;
use App\Form\EntryType;
use App\DTO\EntryDto;

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
     * @IsGranted("ROLE_USER")
     */
    public function createEntry(Request $request, ?Magazine $magazine): Response
    {
        $entryDto = new EntryDto();

        if($magazine) {
            $entryDto->setMagazine($magazine);
        }

        $form = $this->createForm(EntryType::class, $entryDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entry = $this->entryManager->createEntry($entryDto, $this->getUserOrThrow());
            $this->entityManager->flush();

            return $this->redirectToRoute('front');
        }

        return $this->render('entry/create.html.twig', [
            'form' => $form->createView(),
            'magazine' => $magazine,
        ]);
    }
}
