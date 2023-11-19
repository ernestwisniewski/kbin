<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Exception\UserBannedException;
use App\Kbin\Entry\DTO\EntryDto;
use App\Kbin\Entry\EntryCreate;
use App\Kbin\Entry\EntryPageView;
use App\Repository\Criteria;
use App\Service\IpResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntryCreateController extends AbstractController
{
    use EntryTemplateTrait;
    use EntryFormTrait;

    public function __construct(
        private readonly EntryCreate $entryCreate,
        private readonly ValidatorInterface $validator,
        private readonly IpResolver $ipResolver
    ) {
    }

    #[IsGranted('ROLE_USER')]
    public function __invoke(?Magazine $magazine, ?string $type, Request $request): Response
    {
        $dto = new EntryDto();
        $dto->magazine = $magazine;

        $form = $this->createFormByType((new EntryPageView(1))->resolveType($type), $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();
            $dto->ip = $this->ipResolver->resolve();

            if (!$this->isGranted('create_content', $dto->magazine)) {
                throw new AccessDeniedHttpException();
            }

            try {
                $entry = ($this->entryCreate)($dto, $this->getUser());
            } catch (UserBannedException $e) {
                // @todo add flash message
                throw $e;
            }

            $this->addFlash(
                'success',
                'flash_thread_new_success'
            );

            return $this->redirectToMagazine(
                $entry->magazine,
                Criteria::SORT_NEW
            );
        }

        return $this->render(
            $this->getTemplateName((new EntryPageView(1))->resolveType($type)),
            [
                'magazine' => $magazine,
                'form' => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }
}
