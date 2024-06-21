<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use function Symfony\Component\Translation\t;

final class CheckVerifiedUserListener
{
    #[AsEventListener(event: AuthenticationSuccessEvent::class)]
    public function onCheckPassportEvent(AuthenticationSuccessEvent $event): void
    {
        $passport = $event->getAuthenticationToken();
        $user = $passport->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('Unexpected user type');
        }
        if (!$user->isVerified()) {
            throw new CustomUserMessageAuthenticationException(t('CheckVerifiedUserListener.not_verified_user'));
        }
    }
}
