<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use function Symfony\Component\Translation\t;

class RegistrationController extends AbstractController
{


    #Injection de dépendance de la classe EmailVerifier
    public function __construct(private readonly EmailVerifier $emailVerifier)
    {


    } //end__construct()


    # Route pour l'inscription
    #[Route('/rejoindre', name: 'app_register')]
    public function register(
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface      $entityManager,
        TranslatorInterface         $translator
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('ne-pas-repondre@todoco.fr', 'ToDo&Co'))
                    ->to($user->getEmail())
                    ->subject($translator->trans(t('mail.register.subject')))
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // do anything else you need here, like send an email
            $this->addFlash('success', $translator->trans(t('flash.success.registered')));
            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }


    # Route pour la vérification de l'email
    #[Route('/verification/email', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request             $request,
        TranslatorInterface $translator,
        UserRepository      $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id || $userRepository->find($id) === null) {
            $this->addFlash('error', t('flash.error.not_found'));
            return $this->redirectToRoute('app_register');

        }
        $user = $userRepository->find($id);

        // Validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', t('flash.success.email_verified'));

        return $this->redirectToRoute('app_home');
    }
}
