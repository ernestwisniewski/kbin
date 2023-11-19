<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Kbin\Entry\EntryEdit;
use App\Kbin\Entry\EntryPageView;
use App\Kbin\Entry\Factory\EntryFactory;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntryEditController extends AbstractController
{
    use EntryTemplateTrait;
    use EntryFormTrait;

    public function __construct(
        private readonly EntryEdit $entryEdit,
        private readonly EntryFactory $entryFactory
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('edit', subject: 'entry')]
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        Request $request
    ): Response {
        $dto = $this->entryFactory->createDto($entry);

        $form = $this->createFormByType((new EntryPageView(1))->resolveType($entry->type), $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('create_content', $dto->magazine)) {
                throw new AccessDeniedHttpException();
            }

            $entry = ($this->entryEdit)($entry, $dto);

            $this->addFlash(
                'success',
                'flash_thread_edit_success'
            );

            return $this->redirectToEntry($entry);
        }

        return $this->render(
            $this->getTemplateName((new EntryPageView(1))->resolveType($entry->type), true),
            [
                'magazine' => $magazine,
                'entry' => $entry,
                'form' => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }
}
