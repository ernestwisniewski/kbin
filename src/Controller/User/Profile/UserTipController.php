<?php declare(strict_types=1);

namespace App\Controller\User\Profile;

use App\Controller\AbstractController;
use App\DTO\CardanoWalletAddressDto;
use App\Form\CardanoMnemonicType;
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

        $mnemonicForm = $this->createForm(CardanoMnemonicType::class, null, [
            'action' => $this->generateUrl('cardano_wallet_mnemonic'),
        ]);
        $addressForm  = $this->createForm(CardanoWalletAddressType::class, $dto);

        $addressForm->handleRequest($request);

        if ($addressForm->isSubmitted() && $addressForm->isValid()) {
            $userManager->attachWallet($this->getUserOrThrow(), $dto);

            $this->redirectToRefererOrHome($request);
        }

        return $this->render(
            'user/profile/tips.html.twig',
            [

                'mnemonicForm' => $mnemonicForm->createView(),
                'addressForm'  => $addressForm->createView(),
                'transactions' => [],
            ],
            new Response(null, $addressForm->isSubmitted() && !$addressForm->isValid() ? 422 : 200)
        );
    }
}
