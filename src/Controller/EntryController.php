<?php declare(strict_types = 1);

namespace App\Controller;

use App\Entity\Entry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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

    /**
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     */
    public function front(Entry $entry, Magazine $magazine)
    {
        return $this->render(
            'entry/front.html.twig',
            [
                'entry'    => $entry,
                'magazine' => $magazine,
            ]
        );
    }

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

        if ($magazine) {
            $entryDto->setMagazine($magazine);
        }

        $form = $this->createForm(EntryType::class, $entryDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entry = $this->entryManager->createEntry($entryDto, $this->getUserOrThrow());
            $this->entityManager->flush();

            return $this->redirectToRoute('entry', [
                'magazine_name' => $entry->getMagazine()->getName(),
                'entry_id' => $entry->getId()
            ]);
        }

        return $this->render(
            'entry/create.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
