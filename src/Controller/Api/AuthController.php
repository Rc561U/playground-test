<?php

namespace App\Controller\Api;

use App\Dto\RegisterRequestDto;
use App\Entity\User;
use App\Enum\AuthOperationType;
use App\Enum\AuthStatus;
use App\Repository\UserRepository;
use App\Service\AuthLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class AuthController extends AbstractController
{
    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        #[MapRequestPayload] RegisterRequestDto $dto,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        AuthLogService $authLogService,
    ): JsonResponse {
        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            $message = implode(' ', $messages);
            $authLogService->log(
                AuthOperationType::Register,
                AuthStatus::Error,
                null,
                $request,
                $message,
            );

            return $this->json(['error' => $message], Response::HTTP_BAD_REQUEST);
        }

        if ($userRepository->findOneBy(['email' => $dto->email]) !== null) {
            $message = 'Пользователь с таким email уже существует.';
            $authLogService->log(
                AuthOperationType::Register,
                AuthStatus::Error,
                null,
                $request,
                $message,
            );

            return $this->json(['error' => $message], Response::HTTP_CONFLICT);
        }

        $user = (new User())
            ->setEmail((string) $dto->email)
            ->setPassword($passwordHasher->hashPassword(new User(), (string) $dto->password));

        $entityManager->persist($user);
        $entityManager->flush();

        $authLogService->log(
            AuthOperationType::Register,
            AuthStatus::Success,
            $user,
            $request,
        );

        return $this->json([
            'message' => 'Регистрация успешна.',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
            ],
        ], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Обрабатывается json_login authenticator в security.yaml
        throw new \LogicException('This method should not be reached.');
    }
}
