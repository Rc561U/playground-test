<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Enum\AuthOperationType;
use App\Enum\AuthStatus;
use App\Service\AuthLogService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class AuthenticationLogSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AuthLogService $authLogService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LoginFailureEvent::class => 'onLoginFailure',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }

        $this->authLogService->log(
            AuthOperationType::Login,
            AuthStatus::Success,
            $user,
            $event->getRequest(),
        );
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $passport = $event->getPassport();
        $user = null;
        if ($passport !== null) {
            $badgeUser = $passport->getUser();
            if ($badgeUser instanceof User) {
                $user = $badgeUser;
            }
        }

        $exception = $event->getException();
        $this->authLogService->log(
            AuthOperationType::Login,
            AuthStatus::Error,
            $user,
            $event->getRequest(),
            $exception->getMessage(),
        );
    }
}
