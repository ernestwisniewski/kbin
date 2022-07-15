<?php declare(strict_types=1);

namespace App\Controller\ActivityPub;

use App\Entity\Entry;
use App\Entity\Magazine;
use App\Factory\ActivityPub\PageFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntryController
{
    public function __construct(private PageFactory $pageFactory)
    {
    }

    #[ParamConverter('magazine', options: ['mapping' => ['magazine_name' => 'name']])]
    #[ParamConverter('entry', options: ['mapping' => ['entry_id' => 'id']])]
    public function __invoke(
        Magazine $magazine,
        Entry $entry,
        Request $request
    ): Response {
        $response = new JsonResponse($this->pageFactory->create($entry));

        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
