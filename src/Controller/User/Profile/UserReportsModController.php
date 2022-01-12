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

class UserReportsModController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     */
    public function __invoke(): Response
    {
        return new Response();
    }
}
