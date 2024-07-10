<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TaskControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;

    private UserRepository $userRepository;

    private string $path = '/task/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Task::class);
        $this->userRepository = $this->manager->getRepository(User::class);
        $container = static::getContainer();
        // Create a User fixture
        /** @var UserPasswordHasherInterface $passwordHasher */
        $existingUser = $this->userRepository->findOneBy(['email' => 'bmail@example.com']);

        if (!$existingUser) {
            $passwordHasher = $container->get('security.user_password_hasher');
            $em = $container->get('doctrine.orm.entity_manager');
            $user = (new User())->setEmail('bmail@example.com');
            $user->setPassword($passwordHasher->hashPassword($user, 'password'));
            $user->setFirstname('John');
            $user->setLastname('Doe');
            $user->setVerified(true);
            $em->persist($user);
            $em->flush();
        }
        $this->client->loginUser($this->userRepository->findAll()[0]);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);

        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Todo | Mes taches');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%sajouter', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Ajouter', [
            'task[title]' => 'Testing',
            'task[content]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testEdit(): void
    {
        $fixture = new Task();
        $fixture->setTitle('Value');
        $fixture->setContent('Value');
        $fixture->setDone(false);
        $date = new \DateTime();
        $fixture->setCreatedAt($date);
        $fixture->setUser($this->userRepository->findAll()[0]);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Enregistrer', [
            'task[title]' => 'Something New',
            'task[content]' => 'Something New',
        ]);

        self::assertResponseRedirects('/task/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitle());
        self::assertSame('Something New', $fixture[0]->getContent());
        self::assertSame($this->userRepository->findAll()[0], $fixture[0]->getUser());
    }

    public function testRemove(): void
    {
        $fixture = new Task();
        $fixture->setTitle('vValue');
        $fixture->setContent('vValue');
        $fixture->setCreatedAt(new \DateTime());
        $fixture->setDone(false);
        $fixture->setUser($this->userRepository->findAll()[0]);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Todo | Mes taches');
        $this->client->submitForm('Supprimer');

        self::assertResponseRedirects('/task/');
        self::assertSame(0, $this->repository->count([]));
    }

    public function testValidate(): void
    {
        $fixture = new Task();
        $fixture->setTitle('bValue');
        $fixture->setContent('bValue');
        $fixture->setCreatedAt(new \DateTime());
        $fixture->setDone(false);
        $fixture->setUser($this->userRepository->findAll()[0]);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Todo | Mes taches');
        $this->client->submitForm('Valider');

        self::assertResponseRedirects('/task/');
        $this->assertTrue($this->repository->findAll()[0]->isDone());

    }
}
