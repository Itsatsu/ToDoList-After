<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création de l'utilisateur admin
        $admin = new User();
        $admin->setEmail('admin@email.fr');
        $admin->setFirstname('Admin');
        $admin->setLastname('User');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'passpass'));
        $admin->setVerified(true);

        $manager->persist($admin);
        $this->addReference('admin-user', $admin);
        // Création de l'utilisateur normal
        $user = new User();
        $user->setEmail('user@email.fr');
        $user->setFirstname('Normal');
        $user->setLastname('User');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'passpass'));
        $user->setVerified(true);
        $manager->persist($user);
        $this->addReference('normal-user', $user);
        $anonyme = new User();
        $anonyme->setEmail('anonyme@email.fr');
        $anonyme->setFirstname('Anonyme');
        $anonyme->setLastname('User');
        $anonyme->setPassword($this->passwordHasher->hashPassword($user, 'passpass'));
        $anonyme->setVerified(false);
        $manager->persist($anonyme);

        // Enregistrement des utilisateurs en base de données
        $manager->flush();
    }
}
