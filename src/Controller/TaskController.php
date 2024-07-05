<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/task')]
class TaskController extends AbstractController
{
    #[Route('/', name: 'app_task_index', methods: ['GET'])]
    public function index(TaskRepository $taskRepository): Response
    {
        $user = $this->getUser();
        return $this->render('task/index.html.twig', [
            'tasks' => $taskRepository->findBy(['user'=> $user], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/ajouter', name: 'app_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUser($this->getUser());
            $task->setCreatedAt(new \DateTime());
            $task->setDone(false);
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

    #[Route('/{id}', name: 'app_task_show', methods: ['GET'])]
    public function show(Task $task): Response
    {
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
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

    #[Route('/{id}', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($task);
            $entityManager->flush();
            $this->addFlash('success', 'La tâche a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/validate/', name: 'app_task_validate', methods: ['POST'])]
    public function validate(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('validate'.$task->getId(), $request->getPayload()->getString('_token'))) {
            $task->setDoneAt(new \DateTime());
            $task->setDone(true);
            $entityManager->flush();
            $this->addFlash('success', 'La tâche a été marquée comme faite avec succès.');
        }

        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }

}
