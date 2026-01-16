<?php

namespace App\Controller;

use App\Form\ForgotPasswordFormType;
use App\Form\ResetPasswordFormType;
use App\Repository\UserRepository;
use App\Service\DateTimeHelper;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(
        Request $request,
        UserRepository $userRepository,
        EmailService $emailService,
        EntityManagerInterface $entityManager
    ): Response
    {
        $form = $this->createForm(ForgotPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $email]);

            // Always show success message (security best practice - don't reveal if email exists)
            if ($user) {
                // Generate reset token
                $resetToken = bin2hex(random_bytes(32));
                $user->setConfirmationToken($resetToken);

                // Set token expiration to 1 hour from now
                $expiresAt = DateTimeHelper::now()->modify('+1 hour');
                $user->setTokenExpiresAt($expiresAt);

                $entityManager->flush();

                // Send reset email
                $emailService->sendPasswordResetEmail($user, $resetToken);
            }

            $this->addFlash('success', $this->translator->trans('security.success.reset_email_sent', [], 'flash_messages'));
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgot_password.html.twig', [
            'forgotPasswordForm' => $form,
        ]);
    }

    #[Route(path: '/reset-password/{token}', name: 'app_reset_password')]
    public function resetPassword(
        string $token,
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response
    {
        // Find user by reset token
        $user = $userRepository->findOneBy(['confirmation_token' => $token]);

        if (!$user) {
            $this->addFlash('error', $this->translator->trans('security.error.invalid_reset_link', [], 'flash_messages'));
            return $this->redirectToRoute('app_forgot_password');
        }

        // Check if token has expired
        $now = DateTimeHelper::now();
        if ($user->getTokenExpiresAt() < $now) {
            $this->addFlash('error', $this->translator->trans('security.error.reset_link_expired', [], 'flash_messages'));
            return $this->redirectToRoute('app_forgot_password');
        }

        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $plainRepeatPassword = $form->get('plainRepeatPassword')->getData();

            if ($plainPassword !== $plainRepeatPassword) {
                $this->addFlash('error', $this->translator->trans('security.error.passwords_do_not_match', [], 'flash_messages'));
                return $this->render('security/reset_password.html.twig', [
                    'resetPasswordForm' => $form,
                ]);
            }

            // Update password
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $user->setConfirmationToken(null);
            $user->setTokenExpiresAt(null);

            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('security.success.password_changed', [], 'flash_messages'));
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'resetPasswordForm' => $form,
        ]);
    }

    /**
     * Confirm password change from email link
     */
    #[Route(path: '/confirm-password-change/{token}', name: 'app_confirm_password_change')]
    public function confirmPasswordChange(
        string $token,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        EmailService $emailService,
        Security $security
    ): Response
    {
        // Find user by confirmation token
        $user = $userRepository->findOneBy(['confirmation_token' => $token]);

        if (!$user) {
            $this->addFlash('error', $this->translator->trans('security.error.invalid_verification_link', [], 'flash_messages'));
            return $this->redirectToRoute('app_login');
        }

        // Check if token has expired
        $now = DateTimeHelper::now();
        if ($user->getTokenExpiresAt() < $now) {
            $this->addFlash('error', $this->translator->trans('security.error.verification_link_expired', [], 'flash_messages'));

            // Clear expired token
            $user->setConfirmationToken(null);
            $user->setTokenExpiresAt(null);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        // Get hashed password from session
        $newPasswordHashed = $request->getSession()->get('pending_password_change_' . $user->getId());

        if (!$newPasswordHashed) {
            $this->addFlash('error', $this->translator->trans('security.error.session_expired', [], 'flash_messages'));
            return $this->redirectToRoute('app_login');
        }

        // Update password
        $user->setPassword($newPasswordHashed);
        $user->setConfirmationToken(null);
        $user->setTokenExpiresAt(null);

        $entityManager->flush();

        // Remove from session
        $request->getSession()->remove('pending_password_change_' . $user->getId());

        // Send notification email
        $emailService->sendPasswordChangedNotification($user);

        // Logout user for security (only if logged in)
        if ($security->getUser()) {
            $security->logout(false);
        }

        $this->addFlash('success', $this->translator->trans('security.success.password_changed_login', [], 'flash_messages'));
        return $this->redirectToRoute('app_login');
    }
}
