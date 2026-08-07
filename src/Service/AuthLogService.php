<?php

namespace App\Service;

use App\Entity\AuthLog;
use App\Entity\User;
use App\Enum\AuthOperationType;
use App\Enum\AuthStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class AuthLogService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
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

        $this->entityManager->persist($authLog);
        $this->entityManager->flush();
    }
}
