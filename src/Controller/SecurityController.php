<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\AuthOperationType;
use App\Enum\AuthStatus;
use App\Form\RegistrationFormType;
use App\Repository\AuthLogRepository;
use App\Repository\UserRepository;
use App\Service\AuthLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_logs');
        }

        return $this->redirectToRoute('app_login');
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_logs');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method should not be reached.');
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        AuthLogService $authLogService,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_logs');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($userRepository->findOneBy(['email' => $user->getEmail()]) !== null) {
                $message = 'Пользователь с таким email уже существует.';
                $authLogService->log(
                    AuthOperationType::Register,
                    AuthStatus::Error,
                    null,
                    $request,
                    $message,
                );
                $this->addFlash('danger', $message);

                return $this->render('security/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }

            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            $authLogService->log(
                AuthOperationType::Register,
                AuthStatus::Success,
                $user,
                $request,
            );

            $this->addFlash('success', 'Регистрация успешна. Теперь вы можете войти.');

            return $this->redirectToRoute('app_login');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }
            $authLogService->log(
                AuthOperationType::Register,
                AuthStatus::Error,
                null,
                $request,
                implode(' ', $errors),
            );
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/logs', name: 'app_logs')]
    public function logs(Request $request, AuthLogRepository $authLogRepository): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', 20)));

        $filters = [
            'operation_type' => $request->query->get('operation_type'),
            'status' => $request->query->get('status'),
            'user_id' => $request->query->get('user_id'),
            'ip' => $request->query->get('ip'),
            'user_agent' => $request->query->get('user_agent'),
            'created_at_from' => $request->query->get('created_at_from'),
            'created_at_to' => $request->query->get('created_at_to'),
        ];

        $result = $authLogRepository->findPaginated($page, $limit, $filters);
        $totalPages = max(1, (int) ceil($result['total'] / $limit));

        return $this->render('auth_log/index.html.twig', [
            'logs' => $result['items'],
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $result['total'],
                'pages' => $totalPages,
            ],
            'filters' => $filters,
        ]);
    }
}
