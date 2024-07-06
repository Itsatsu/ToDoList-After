<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Références aux utilisateurs créés dans UserFixtures
        $admin = $this->getReference('admin-user');
        $user = $this->getReference('normal-user');

        // Création des tâches pour l'admin
        $this->createTasks($manager, $admin, 2, false);
        $this->createTasks($manager, $admin, 2, true);

        // Création des tâches pour l'utilisateur normal
        $this->createTasks($manager, $user, 2, false);
        $this->createTasks($manager, $user, 2, true);

        // Création des tâches sans utilisateur
        $this->createUnassignedTasks($manager, 5);

        // Enregistrement des entités en base de données
        $manager->flush();
    }

    private function createTasks(ObjectManager $manager, User $user, int $count, bool $isCompleted): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $task = new Task();
            $task->setTitle(($isCompleted ? "tâche fini " : "tâche pas fini") . " n° $i pour " . $user->getEmail());
            $task->setContent("Description de la tâche n° $i pour " . $user->getEmail());
            $task->setDone($isCompleted);
            if ($isCompleted) {
                $task->setDoneAt(new \DateTime());
            }
            $task->setUser($user);

            $manager->persist($task);
        }
    }

    private function createUnassignedTasks(ObjectManager $manager, int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $task = new Task();
            $task->setTitle("tache anonyme $i");
            $task->setContent("contenu de la tache $i");
            $task->setUser(null);

            $manager->persist($task);
        }
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
