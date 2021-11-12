<?php declare(strict_types = 1);

namespace App\Controller\User\Profile;

use App\Controller\AbstractController;
use App\Form\UserSettingsType;
use App\Service\UserSettingsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserTipController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     */
    public function __invoke(UserSettingsManager $manager, Request $request): Response
    {
        return $this->render(
            'user/profile/tips.html.twig',
            [
                'transactions' => [],
            ],
//            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }
}
