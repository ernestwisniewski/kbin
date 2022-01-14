<?php declare(strict_types=1);

namespace App\Controller;

use App\Form\ContactType;
use App\Service\ContactManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends AbstractController
{
    public function __construct(private ContactManager $manager)
    {
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();

            $this->manager->send(
                $dto->name,
                $dto->email,
                $dto->message
            );

            return $this->redirectToRefererOrHome($request);
        }

        return $this->render(
            'pages/contact.html.twig', [
                'form' => $form->createView(),
            ]
        );
    }
}
