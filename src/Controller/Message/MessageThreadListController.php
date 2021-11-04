<?php declare(strict_types=1);

namespace App\Controller\Message;

use App\Controller\AbstractController;
use App\Repository\MessageThreadRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MessageThreadListController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     */
    public function __invoke(MessageThreadRepository $repository, Request $request): Response
    {
        return $this->render(
            'user/profile/messages.html.twig',
            [
                'threads' => $repository->findUserMessages($this->getUser(), $this->getPageNb($request)),
            ]
        );
    }

}
