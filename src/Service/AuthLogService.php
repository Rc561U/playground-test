<?php

namespace App\Service;

use App\Entity\AuthLog;
use App\Entity\User;
use App\Enum\AuthOperationType;
use App\Enum\AuthStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

class AuthLogService implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
        ];
    }

    public function log(
        AuthOperationType $operationType,
        AuthStatus $status,
        ?User $user,
        Request $request,
        ?string $errorMessage = null,
    ): void {
        $authLog = (new AuthLog())
            ->setOperationType($operationType)
            ->setStatus($status)
            ->setUser($user)
            ->setIp($request->getClientIp())
            ->setUserAgent($request->headers->get('User-Agent'))
            ->setErrorMessage($errorMessage);

        $this->container->get(EntityManagerInterface::class)->persist($authLog);
        $this->container->get(EntityManagerInterface::class)->flush();
    }
}
