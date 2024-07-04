<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        // Ensure we have a clean database
        $container = static::getContainer();

        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();
        $this->userRepository = $container->get(UserRepository::class);

        foreach ($this->userRepository->findAll() as $user) {
            $em->remove($user);
        }

        $em->flush();
    }

    public function testRegister(): void
    {
        // Register a new user
        $this->client->request('GET', '/inscription');
        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('Todo | Inscription');

        $this->client->submitForm('Créer mon compte', [
            'registration_form[email]' => 'meme@example.com',
            'registration_form[firstname]' => 'John',
            'registration_form[lastname]' => 'Doe',
            'registration_form[plainPassword]' => 'password',
        ]);

        self::assertCount(1, $this->userRepository->findAll());
        self::assertFalse(($user = $this->userRepository->findAll()[0])->isVerified());

        // Ensure the verification email was sent
        // Use either assertQueuedEmailCount() || assertEmailCount() depending on your mailer setup
        self::assertQueuedEmailCount(1);
        //self::assertEmailCount(1);

        self::assertCount(1, $messages = $this->getMailerMessages());
        self::assertEmailAddressContains($messages[0], 'from', 'ne-pas-repondre@todoco.fr');
        self::assertEmailAddressContains($messages[0], 'to', 'meme@example.com');
        self::assertEmailTextBodyContains($messages[0], 'Confirmez votre adresse email');

        // Login the new user
        $this->client->followRedirect();
        $this->client->loginUser($user);

        // Get the verification link from the email
        /** @var TemplatedEmail $templatedEmail */
        $templatedEmail = $messages[0];
        $messageBody = $templatedEmail->getHtmlBody();
        self::assertIsString($messageBody);
        preg_match('#<a href="(http://localhost/verification/email[^"]+)">#', $messageBody, $resetLink);
        self::assertNotNull($resetLink);
        //self::assertTrue(static::getContainer()->get(UserRepository::class)->findAll()[0]->isVerified());
    }
}
