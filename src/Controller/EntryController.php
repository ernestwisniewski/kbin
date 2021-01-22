<?php declare(strict_types=1);

namespace App\Controller;

use App\Form\EntryArticleType;
use App\Form\EntryLinkType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\EntryManager;
use App\Entity\Magazine;
use App\Form\EntryType;
use App\DTO\EntryDto;
use App\Entity\Entry;

class EntryController extends AbstractController
{
    const ENTRY_TYPE_ARTICLE = 'artykul';
    const ENTRY_TYPE_LINK = 'link';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     */
    public function front(Magazine $magazine, Entry $entry)
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
    public function createEntry(?Magazine $magazine, ?string $type, Request $request, EntryManager $entryManager): Response
    {
        $entryDto = new EntryDto();

        if ($magazine) {
            $entryDto->setMagazine($magazine);
        }

        switch ($type) {
            case self::ENTRY_TYPE_ARTICLE:
                $form         = $this->createForm(EntryArticleType::class, $entryDto);
                $templateName = 'create_article';
                break;
            default:
                $form         = $this->createForm(EntryLinkType::class, $entryDto);
                $templateName = 'create_link';
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entry = $entryManager->createEntry($entryDto, $this->getUserOrThrow());
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
            "entry/$templateName.html.twig",
            [
                'form' => $form->createView(),
            ]
        );
    }
}
