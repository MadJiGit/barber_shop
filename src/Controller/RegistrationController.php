<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Service\DateTimeHelper;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        EmailService $emailService
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $plainRepeatPassword = $form->get('plainRepeatPassword')->getData();

            if ($plainPassword !== $plainRepeatPassword) {
                $this->addFlash('error', 'Паролите не съвпадат.');
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }

            // Encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Set user as inactive until email is verified
            $user->setIsActive(false);

            // Generate verification token
            $verificationToken = bin2hex(random_bytes(32));
            $user->setConfirmationToken($verificationToken);

            // Set token expiration to 24 hours from now
            $expiresAt = DateTimeHelper::now()->modify('+24 hours');
            $user->setTokenExpiresAt($expiresAt);

            $entityManager->persist($user);
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
     * @throws \Exception
     */
    #[Route('/verify-email/{token}', name: 'app_verify_email')]
    public function verifyEmail(
        string $token,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        EmailService $emailService
    ): Response
    {
        // Find user by confirmation token
        $user = $userRepository->findOneBy(['confirmation_token' => $token]);

        if (!$user) {
            $this->addFlash('error', 'Невалиден или изтекъл линк за потвърждение.');
            return $this->redirectToRoute('app_login');
        }

        // Check if token has expired
        $now = DateTimeHelper::now();
        if ($user->getTokenExpiresAt() < $now) {
            $this->addFlash('error', 'Линкът за потвърждение е изтекъл. Моля, регистрирайте се отново.');
            return $this->redirectToRoute('app_register');
        }

        // Activate user
        $user->setIsActive(true);
        $user->setConfirmationToken(null);
        $user->setTokenExpiresAt(null);

        $entityManager->flush();

        // Send welcome email
        $emailService->sendWelcomeEmail($user);

        $this->addFlash('success', 'Вашият акаунт е потвърден успешно! Можете да влезете.');
        return $this->redirectToRoute('app_login');
    }
}
