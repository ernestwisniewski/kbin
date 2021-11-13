<?php declare(strict_types=1);

namespace App\Controller\User\Profile;

use App\Controller\AbstractController;
use App\DTO\CardanoWalletAddressDto;
use App\Form\CardanoWalletAddressType;
use App\Service\UserManager;
use App\Service\UserSettingsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserTipController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     */
    public function __invoke(UserSettingsManager $manager, Request $request, UserManager $userManager): Response
    {
        $dto = new CardanoWalletAddressDto($this->getUserOrThrow());

        $form = $this->createForm(CardanoWalletAddressType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->attachWallet($this->getUserOrThrow(), $dto);

            $this->redirectToRefererOrHome($request);
        }

        return $this->render(
            'user/profile/tips.html.twig',
            [
                'form'         => $form->createView(),
                'transactions' => [],
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }
}
