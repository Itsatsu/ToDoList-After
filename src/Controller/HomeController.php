<?php

namespace App\Controller;

use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{

    /**
     * Route pour la page d'accueil
     * si l'utilisateur est connecté : affiche les 10 dernières tâches non faites et les 10 dernières tâches faites
     * Sinon : affiche la page d'accueil sans tâches
     * @param TaskRepository $taskRepository
     * @return Response
     *
     */
    #[Route('/', name: 'app_home')]
    public function index(TaskRepository $taskRepository): Response
    {
        if ($this->getUser()) {
            $tasksNoDone = count($taskRepository->findBy(['isDone' => false , 'user'=> $this->getUser()], ['createdAt' => 'DESC'], 10));
            $tasksDone = $taskRepository->findBy(['isDone' => true , 'user'=> $this->getUser() ], ['createdAt' => 'DESC'], 10);
        }
        return $this->render('home/index.html.twig', [
            'tasks_no_done' => $tasksNoDone??null,
            'tasks_done' => $tasksDone??null,
        ]);
    }
}
