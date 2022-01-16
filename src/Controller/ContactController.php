<?php declare(strict_types=1);

namespace App\Controller;

use App\Form\ContactType;
use App\Service\CloudflareIpResolver;
use App\Service\ContactManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends AbstractController
{
    public function __invoke(ContactManager $manager, CloudflareIpResolver $ipResolver, Request $request): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();
            $dto->ip = $ipResolver->resolve();

            $manager->send($dto);

            return $this->redirectToRefererOrHome($request);
        }

        return $this->render(
            'page/contact.html.twig', [
                'form' => $form->createView(),
            ]
        );
    }
}
