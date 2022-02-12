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

class EntryDeleteController extends AbstractController
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
     * @IsGranted("delete", subject="entry")
     */
    public function delete(Magazine $magazine, Entry $entry, Request $request): Response
    {
        $this->validateCsrf('entry_delete', $request->request->get('token'));

        $this->manager->delete($this->getUserOrThrow(), $entry);

        $this->addFlash(
            'danger',
            'flash_thread_delete_success'
        );

        return $this->redirectToMagazine($magazine);
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("delete", subject="entry")
     */
    public function restore(Magazine $magazine, Entry $entry, Request $request): Response
    {
        $this->validateCsrf('entry_restore', $request->request->get('token'));

        $this->manager->restore($this->getUserOrThrow(), $entry);

        return $this->redirectToMagazine($magazine);
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("purge", subject="entry")
     */
    public function purge(Magazine $magazine, Entry $entry, Request $request): Response
    {
        $this->validateCsrf('entry_purge', $request->request->get('token'));

        $this->manager->purge($entry);

        return $this->redirectToMagazine($magazine);
    }
}
