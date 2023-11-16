<?php

declare(strict_types=1);

namespace App\Controller\User\Profile;

use App\Controller\AbstractController;
use App\DTO\UserDto;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Form\UserTwoFactorType;
use App\Kbin\User\UserEdit;
use App\Service\TwoFactorManager;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class User2FAController extends AbstractController
{
    public const TOTP_SESSION_KEY = 'totp_user_secret';
    public const BACKUP_SESSION_KEY = 'totp_backup_codes';

    public function __construct(
        private readonly UserEdit $userEdit,
        private readonly UserFactory $userFactory,
        private readonly TwoFactorManager $twoFactorManager,
        private readonly TotpAuthenticatorInterface $totpAuthenticator,
        private readonly TranslatorInterface $translator,
        private readonly Security $security,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    public function enable(Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        $user = $this->getUserOrThrow();
        if ($user->isTotpAuthenticationEnabled()) {
            throw new SuspiciousOperationException('User accessed 2fa enable path with existing 2fa in place');
        }

        $totpSecret = $request->getSession()->get(self::TOTP_SESSION_KEY, null);
        if (null === $totpSecret || 'GET' === $request->getMethod()) {
            $totpSecret = $this->totpAuthenticator->generateSecret();
            $request->getSession()->set(self::TOTP_SESSION_KEY, $totpSecret);
        }

        $backupCodes = $request->getSession()->get(self::BACKUP_SESSION_KEY, null);
        if (null === $backupCodes || 'GET' === $request->getMethod()) {
            $backupCodes = $this->twoFactorManager->createBackupCodes($user);
            $request->getSession()->set(self::BACKUP_SESSION_KEY, $backupCodes);
        }

        $dto = $this->userFactory->createDto($user);
        $dto->totpSecret = $totpSecret;

        // QR code generation needs a user with a code. Add one for that then immediately remove
        // to stop side effects when persisting.
        $user->setTotpSecret($totpSecret);
        $qrCodeContent = $this->totpAuthenticator->getQRContent($user);
        $user->setTotpSecret(null);

        $form = $this->handleForm($this->createForm(UserTwoFactorType::class, $dto), $dto, $request);
        if (!$form instanceof FormInterface) {
            return $form;
        }

        return $this->render(
            'user/settings/2fa.html.twig',
            [
                'form' => $form->createView(),
                'two_fa_url' => $qrCodeContent,
                'codes' => $backupCodes,
            ],
            new Response(
                null,
                $form->isSubmitted() && !$form->isValid() ? 422 : 200
            )
        );
    }

    #[IsGranted('ROLE_USER')]
    public function disable(Request $request): Response
    {
        $this->validateCsrf('user_2fa_remove', $request->request->get('token'));

        $user = $this->getUserOrThrow();
        if (!$user->isTotpAuthenticationEnabled()) {
            throw new SuspiciousOperationException('User accessed 2fa disable path without existing 2fa in place');
        }

        $this->twoFactorManager->remove2FA($user);

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_USER')]
    public function qRCode(Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        $totpSecret = $request->getSession()->get(self::TOTP_SESSION_KEY, null);
        if (null === $totpSecret) {
            throw new AccessDeniedException('/settings/2fa/qrcode.png');
        }
        $this->getUserOrThrow()->setTotpSecret($totpSecret);

        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($this->totpAuthenticator->getQRContent($this->getUserOrThrow()))
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(250)
            ->margin(0)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->build();

        return new Response($result->getString(), 200, ['Content-Type' => 'image/png']);
    }

    #[IsGranted('ROLE_ADMIN')]
    public function remove(User $user, Request $request): Response
    {
        $this->validateCsrf('user_2fa_remove', $request->request->get('token'));

        $this->twoFactorManager->remove2FA($user);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'has2FA' => false,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_USER')]
    public function backup(): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        $user = $this->getUserOrThrow();
        if (!$user->isTotpAuthenticationEnabled()) {
            throw new SuspiciousOperationException('User accessed 2fa backup path without existing 2fa');
        }

        return $this->render(
            'user/settings/2fa_backup.html.twig',
            [
                'codes' => $this->twoFactorManager->createBackupCodes($user),
            ]
        );
    }

    private function handleForm(
        FormInterface $form,
        UserDto $dto,
        Request $request
    ): FormInterface|Response {
        $form->handleRequest($request);

        if ($form->isSubmitted()
            && $form->has('totpCode')
            && !$this->setupHasValidCode($dto->totpSecret, $form->get('totpCode')->getData())) {
            $form->get('totpCode')->addError(new FormError($this->translator->trans('2fa.code_invalid')));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            ($this->userEdit)($this->getUser(), $dto);

            if ($dto->totpSecret) {
                $this->security->logout(false);

                $this->addFlash('success', 'account_settings_changed');

                return $this->redirectToRoute('app_login');
            }

            return $this->redirectToRoute('user_settings_profile');
        }

        return $form;
    }

    private function setupHasValidCode(string $totpSecret, string $submittedCode): bool
    {
        $user = $this->getUser();
        $user->setTotpSecret($totpSecret);

        $isValid = false;
        if ($this->totpAuthenticator->checkCode($user, $submittedCode)) {
            $isValid = true;
        }

        // the totpAuthenticator checkCode method requires the secret to be present in the user, but we
        // don't want it there right now, so we remove it after we check.
        $user->setTotpSecret(null);

        return $isValid;
    }
}
