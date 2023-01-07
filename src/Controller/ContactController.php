<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ContactDto;
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
            /**
             * @var ContactDto $dto
             */
            $dto = $form->getData();
            $dto->ip = $ipResolver->resolve();

            if (!$dto->surname) {
                $manager->send($dto);
            }

            $this->addFlash('success', 'email_was_sent');

            return $this->redirectToRefererOrHome($request);
        }

        return $this->render(
            'page/contact.html.twig', [
                'form' => $form->createView(),
            ]
        );
    }
}
