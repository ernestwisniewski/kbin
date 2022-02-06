<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Form\UserNoteType;
use App\Service\UserNoteManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserNoteController extends AbstractController
{
    public function __construct(private UserNoteManager $manager)
    {
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function __invoke(User $user, Request $request): Response
    {
        $dto         = $this->manager->createDto($this->getUserOrThrow(), $user);

        $form = $this->createForm(UserNoteType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();

            if($dto->body) {
                $this->manager->save($this->getUserOrThrow(), $user, $dto->body);
            } else {
                $this->manager->clear($this->getUserOrThrow(), $user);
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonSuccessResponse();
        }

        return $this->redirectToRefererOrHome($request);
    }

}
