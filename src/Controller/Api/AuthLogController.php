<?php

namespace App\Controller\Api;

use App\Repository\AuthLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/logs')]
class AuthLogController extends AbstractController
{
    #[Route('', name: 'api_logs', methods: ['GET'])]
    public function index(Request $request, AuthLogRepository $authLogRepository): JsonResponse
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

        $items = array_map(static function ($log) {
            return [
                'id' => $log->getId(),
                'operation_type' => $log->getOperationType()?->value,
                'status' => $log->getStatus()?->value,
                'user_id' => $log->getUser()?->getId(),
                'ip' => $log->getIp(),
                'user_agent' => $log->getUserAgent(),
                'error_message' => $log->getErrorMessage(),
                'created_at' => $log->getCreatedAt()?->format('Y-m-d H:i:s'),
            ];
        }, $result['items']);

        return $this->json([
            'data' => $items,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $result['total'],
                'pages' => (int) ceil($result['total'] / $limit),
            ],
            'filters' => array_filter($filters, static fn ($value) => $value !== null && $value !== ''),
        ]);
    }
}
