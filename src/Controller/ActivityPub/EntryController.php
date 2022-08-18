<?php declare(strict_types=1);

namespace App\Controller\ActivityPub;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Factory\ActivityPub\EntryPageFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntryController extends AbstractController
{
    public function __construct(private EntryPageFactory $pageFactory)
    {
    }

    #[ParamConverter('magazine', options: ['mapping' => ['magazine_name' => 'name']])]
    #[ParamConverter('entry', options: ['mapping' => ['entry_id' => 'id']])]
    public function __invoke(
        Magazine $magazine,
        Entry $entry,
        Request $request
    ): Response {
        if ($entry->apId) {
            $this->redirect($entry->apId);
        }

        $response = new JsonResponse($this->pageFactory->create($entry, true));

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
