<?php declare(strict_types = 1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Service\EntryManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntryPinController extends AbstractController
{
    public function __construct(
        private EntryManager $manager,
    ) {
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("moderate", subject="magazine")
     */
    public function __invoke(Magazine $magazine, Entry $entry, Request $request): Response
    {
        $this->validateCsrf('entry_pin', $request->request->get('token'));

        $entry = $this->manager->pin($entry);

        $this->addFlash(
            'success',
            $entry->sticky ? 'flash_thread_pin_success' : 'flash_thread_unpin_success'
        );

        return $this->redirectToRefererOrHome($request);
    }
}
