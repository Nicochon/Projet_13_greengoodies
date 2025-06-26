<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;

final class JWTLoginListener
{
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();

        if ($user instanceof UserInterface && method_exists($user, 'isApiEnabled') && !$user->isApiEnabled()) {
            $event->setData([
                'error' => 'Accès à l’API désactivé pour cet utilisateur.'
            ]);
        }
    }
}
