<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        // Ensure we have a clean database
        $container = static::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();
        $this->em = $em;

        $this->userRepository = $container->get(UserRepository::class);

        foreach ($this->userRepository->findAll() as $user) {
            $this->em->remove($user);
        }

        $this->em->flush();
    }

    public function testResetPasswordController(): void
    {
        // Create a test user
        $user = (new User())
            ->setEmail('me@example.com')

            ->setPassword('a-test-password-that-will-be-changed-later')
        ;
        $user->setFirstname('John');
        $user->setLastname('Doe');
        $user->setVerified(1);
        $this->em->persist($user);
        $this->em->flush();

        // Test Request reset password page
        $this->client->request('GET', '/reinitialisation-mot-de-passe');

        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('Todo | Demande de réinitialisation de mot de passe');

        // Submit the reset password form and test email message is queued / sent
        $this->client->submitForm('Demander la réinitialisation du mot de passe', [
            'reset_password_request_form[email]' => 'me@example.com',
        ]);

        // Ensure the reset password email was sent
        // Use either assertQueuedEmailCount() || assertEmailCount() depending on your mailer setup
        self::assertQueuedEmailCount(1);


        self::assertCount(1, $messages = $this->getMailerMessages());

        self::assertEmailAddressContains($messages[0], 'from', 'ne-pas-repondre@todoco.fr');
        self::assertEmailAddressContains($messages[0], 'to', 'me@example.com');

        self::assertResponseRedirects('/');

        // Test check email landing page shows correct "expires at" time
        $crawler = $this->client->followRedirect();

        self::assertPageTitleContains('Todo | Accueil');
        self::assertStringContainsString('Un email vous a été envoyé avec un lien pour réinitialiser votre mot de passe', $crawler->html());

        // Test the link sent in the email is valid
        $templatedEmail = $messages[0];
        $messageBody = $templatedEmail->getHtmlBody();
        preg_match('#<a href="(http://localhost/reinitialisation-mot-de-passe/reinitialisation/[^"]+)#', $messageBody, $resetLink);
        $this->client->request('GET', $resetLink[1]);
        $this->client->followRedirect();
        $this->client->submitForm('Réinitialiser le mot de passe', [
            'change_password_form[plainPassword][first]' => '@zefienBbeuk26864',
            'change_password_form[plainPassword][second]' => '@zefienBbeuk26864',
        ]);

        self::assertResponseRedirects('/connexion');

        $user = $this->userRepository->findOneBy(['email' => 'me@example.com']);

        self::assertInstanceOf(User::class, $user);

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($passwordHasher->isPasswordValid($user, '@zefienBbeuk26864'));
    }
}
