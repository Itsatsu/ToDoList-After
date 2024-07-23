<?php

namespace App\Tests\Controller;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends WebTestCase
{

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $userRepository = $em->getRepository(User::class);

        // Remove any existing users from the test database
        foreach ($userRepository->findAll() as $user) {
            $em->remove($user);
        }

        $em->flush();

        // Create a User fixture
        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = $container->get('security.user_password_hasher');

        $user = (new User())->setEmail('email@example.com');
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));
        $user->setFirstname('John');
        $user->setLastname('Doe');
        $user->setRoles(["ROLE_ADMIN"]);
        $user->setVerified(true);
        $em->persist($user);
        $em->flush();

        for ($i = 0; $i < 2; $i++) {
            $normalUser = (new User())->setEmail('normal@email.fr'. $i);
            $normalUser->setPassword($passwordHasher->hashPassword($user, 'password'));
            $normalUser->setFirstname('John');
            $normalUser->setLastname('Doe');
            $normalUser->setVerified(true);
            $em->persist($normalUser);
            $em->flush();
        }
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/connexion');
        self::assertResponseIsSuccessful();
        $this->client->submitForm('Se connecter', [
            '_username' => 'email@example.com',
            '_password' => 'password',
        ]);
        self::assertResponseRedirects('/');

        $this->client->followRedirect();
        $this->client->request('GET', '/admin/user');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Liste des utilisateurs');

        $this->client->submitForm('Supprimer', [
        ]);

        self::assertResponseRedirects('/admin/user');
        $this->client->followRedirect();
        self::assertSelectorTextContains(
            '.alert-success',
            "L'utilisateur a été supprimé avec succès.");
        $this->client->submitForm('Changer le rôle');
        self::assertResponseRedirects('/admin/user');
        $this->client->followRedirect();
        self::assertSelectorTextContains(
            '.alert-success',
            "Le rôle de l'utilisateur a été modifié avec succès.");


    }
}
