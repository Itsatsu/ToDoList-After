<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/task')]
class TaskController extends AbstractController
{
    /**
     * Pour les utilisateurs normaux : Affiche la liste ces tâches
     * Pour les administrateurs : Affiche la liste de toutes les tâches, y compris celles sans utilisateur et
     * les attribue à l'utilisateur anonyme
     *
     * @param TaskRepository $taskRepository
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/', name: 'app_task_index', methods: ['GET'])]
    public function index(TaskRepository $taskRepository, UserRepository $userRepository, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        $tasks = $taskRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);
        if ($this->isGranted('ROLE_ADMIN')) {

            $noUserTasks = $taskRepository->findBy(['user' => null], ['createdAt' => 'DESC']);
            $anonymUser = $userRepository->findOneBy(['email' => 'anonyme@email.fr']);
            if ($noUserTasks) {

                foreach ($noUserTasks as $task) {
                    $task->setUser($anonymUser);
                    $manager->persist($task);
                    $manager->flush();
                }

            }
            $tasks = array_merge($tasks, $taskRepository->findBy(['user' => $anonymUser], ['createdAt' => 'DESC']));

        }
        return $this->render('task/index.html.twig', [
            'tasks' => $tasks
        ]);
    }

    /**
     * Crée une nouvelle tâche
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/ajouter', name: 'app_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUser($this->getUser());
            $entityManager->persist($task);
            $entityManager->flush();
            $this->addFlash('success', 'La tâche a été ajoutée avec succès.');
            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    /**
     * Affiche la tâche à modifier
     *
     * @param Request $request
     * @param Task $task
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
    #[IsGranted('TASK_MODIFY', subject: 'task')]
    public function edit(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'La tâche a été modifiée avec succès.');
            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    /**
     * Supprime une tâche
     *
     * @param Request $request
     * @param Task $task
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}', name: 'app_task_delete', methods: ['POST'])]
    #[IsGranted('TASK_MODIFY', subject: 'task')]
    public function delete(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $task->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($task);
            $entityManager->flush();
            $this->addFlash('success', 'La tâche a été supprimée avec succès.');
            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }
        $this->addFlash('danger', 'Vous ne pouvez pas supprimer une tâche qui ne vous appartient pas.');
        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Valide une tâche
     *
     * @param Request $request
     * @param Task $task
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}/validate/', name: 'app_task_validate', methods: ['POST'])]
    #[IsGranted('TASK_MODIFY', subject: 'task')]
    public function validate(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('validate' . $task->getId(), $request->getPayload()->getString('_token'))) {
            $task->setDoneAt(new \DateTime());
            $task->setDone(true);
            $entityManager->flush();
            $this->addFlash('success', 'La tâche a été marquée comme faite avec succès.');
        }

        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }

}
