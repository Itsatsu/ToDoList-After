<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{

    /**
     * Permet d'afficher la liste des utilisateurs
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route('/admin/user', name: 'app_user_index')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('user/index.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * Permet de supprimer un utilisateur
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @return Response
     */
    #[Route('/admin/user/{id}', name: 'app_user_delete', methods:['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($user->getRoles() === ['ROLE_ADMIN'] || $user === $this->getUser() ){
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer cet utilisateur.');
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', "L'utilisateur a été supprimé avec succès.");
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }
        $this->addFlash('danger', 'Vous ne pouvez pas supprimer cet utilisateur.');
        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Formulaire pour éditer un utilisateur
     * @param User $user
     *
     */
    #[Route('/admin/user/{id}/edit', name: 'app_user_edit')]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager,): Response
    {
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', "L'utilisateur a été modifié avec succès.");
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form
        ]);

    }


    /**
     * Permet de modifier le rôle d'un utilisateur
     * @param User $user
     *
     */
    #[Route('/admin/user/{id}/role', name: 'app_user_role', methods:['POST'])]
    public function role(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($user === $this->getUser() ){
            $this->addFlash('danger', 'Vous ne pouvez pas modifier le rôle de cet utilisateur.');
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }
        if ($this->isCsrfTokenValid('role' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $user->setRoles($user->getRoles() === ['ROLE_USER'] ? ['ROLE_ADMIN'] : ['ROLE_USER']);
            $entityManager->flush();
            $this->addFlash('success', "Le rôle de l'utilisateur a été modifié avec succès.");
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

    }

}
