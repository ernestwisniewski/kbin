<?php declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\EntryCommentRepository;
use Symfony\Component\Form\FormInterface;
use App\PageView\EntryCommentPageView;
use App\Event\EntryHasBeenSeenEvent;
use App\Form\EntryArticleType;
use App\Service\EntryManager;
use App\Form\EntryLinkType;
use App\Entity\Magazine;
use App\DTO\EntryDto;
use App\Entity\Entry;

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

        $this->manager->pin($entry);

        return $this->redirectToRefererOrHome($request);
    }
}
