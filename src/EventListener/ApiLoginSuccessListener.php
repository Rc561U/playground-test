<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener(event: LoginSuccessEvent::class, method: 'onLoginSuccess', priority: -10)]
class ApiLoginSuccessListener
{
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $request = $event->getRequest();
        if ($request->getPathInfo() !== '/api/login') {
            return;
        }

        $user = $event->getUser();
        $event->setResponse(new JsonResponse([
            'message' => 'Авторизация успешна.',
            'user' => [
                'id' => method_exists($user, 'getId') ? $user->getId() : null,
                'email' => $user->getUserIdentifier(),
                'roles' => $user->getRoles(),
            ],
        ]));
    }
}
