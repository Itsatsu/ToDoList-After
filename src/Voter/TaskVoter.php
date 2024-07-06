<?php

namespace App\Voter;

use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TaskVoter extends Voter
{

    public const MODIFY = 'TASK_MODIFY';

    private $security;

    private $anonymeUser;


    public function __construct(Security $security, UserRepository $userRepository)
    {
        $this->security = $security;
        $this->anonymeUser = $userRepository->findOneBy(['email' => 'anonyme@email.fr']);
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute == self::MODIFY
            && $subject instanceof \App\Entity\Task;

    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Check conditions and return true to grant permission.
        // L'auteur peut modifier uniquement ses propres tâches.
        if ($attribute == self::MODIFY) {
            if ($user === $subject->getUser()) {
                return true;
            }
            // Une tâche sans auteur ou raccroché à l'utilisateur anonyme peut être modifiée uniquement par un/e admin.
            if (($subject->getUser()=== null || $subject->getUser() ==$this->anonymeUser )&& $this->security->isGranted('ROLE_ADMIN') === true) {
                return true;
            }
        }

        return false;

    }


}
