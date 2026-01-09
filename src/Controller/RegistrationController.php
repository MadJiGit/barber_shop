<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Service\DateTimeHelper;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    /**
     * @throws RandomException
     */
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        EmailService $emailService,
        UserRepository $userRepository,
    ): Response {
        // Check if this is a guest upgrade BEFORE creating the form
        $user = new User();
        $isGuestUpgrade = false;

        if ($request->isMethod('POST')) {
            $email = $request->request->all('registration_form')['email'] ?? null;
            if ($email) {
                $existingUser = $userRepository->findOneBy(['email' => $email]);

                // If a guest user exists (no password, inactive) - use it for the form
                if ($existingUser && !$existingUser->getPassword() && !$existingUser->getIsActive()) {
                    $user = $existingUser;
                    $isGuestUpgrade = true;
                }
            }
        }

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $plainRepeatPassword = $form->get('plainRepeatPassword')->getData();

            if ($plainPassword !== $plainRepeatPassword) {
                $this->addFlash('error', $this->translator->trans('registration.error.passwords_do_not_match', [], 'flash_messages'));

                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }

            // Encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // All registrations require email verification (guest or new)
            $user->setIsActive(false);

            // Generate verification token
            $verificationToken = bin2hex(random_bytes(32));
            $user->setConfirmationToken($verificationToken);

            // Set token expiration to 24 hours from now
            $expiresAt = DateTimeHelper::now()->modify('+24 hours');
            $user->setTokenExpiresAt($expiresAt);

            // Only persist if it's a new user, not a guest upgrade
            if (!$isGuestUpgrade) {
                $entityManager->persist($user);
            }

            $entityManager->flush();

            // Send verification email
            $emailService->sendVerificationEmail($user, $verificationToken);

            // Redirect to confirmation page
            return $this->render('registration/check_email.html.twig', [
                'email' => $user->getEmail(),
            ]);
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    /**
     * @throws \Exception|TransportExceptionInterface
     */
    #[Route('/verify-email/{token}', name: 'app_verify_email')]
    public function verifyEmail(
        string $token,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        EmailService $emailService,
    ): Response {
        // Find a user by confirmation token
        $user = $userRepository->findOneBy(['confirmation_token' => $token]);

        if (!$user) {
            $this->addFlash('error', $this->translator->trans('registration.error.invalid_verification_link', [], 'flash_messages'));
            return $this->redirectToRoute('app_login');
        }

        // Check if token has expired
        $now = DateTimeHelper::now();
        if ($user->getTokenExpiresAt() < $now) {
            $this->addFlash('error', $this->translator->trans('registration.error.verification_link_expired', [], 'flash_messages'));
            return $this->redirectToRoute('app_register');
        }

        // Activate user
        $user->setIsActive(true);
        $user->setConfirmationToken(null);
        $user->setTokenExpiresAt(null);

        $entityManager->flush();

        // Send welcome email
        $emailService->sendWelcomeEmail($user);

        $this->addFlash('success', $this->translator->trans('registration.success.account_verified', [], 'flash_messages'));

        return $this->redirectToRoute('app_login');
    }
}
