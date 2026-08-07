<?php

namespace App\Repository;

use App\Entity\AuthLog;
use App\Enum\AuthOperationType;
use App\Enum\AuthStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AuthLog>
 */
class AuthLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthLog::class);
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return array{items: list<AuthLog>, total: int}
     */
    public function findPaginated(int $page, int $limit, array $filters = []): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.user', 'u')
            ->addSelect('u')
            ->orderBy('l.createdAt', 'DESC');

        if (isset($filters['operation_type']) && $filters['operation_type'] !== '') {
            $operation = AuthOperationType::tryFrom((string) $filters['operation_type']);
            if ($operation !== null) {
                $qb->andWhere('l.operationType = :operationType')
                    ->setParameter('operationType', $operation);
            }
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $status = AuthStatus::tryFrom((string) $filters['status']);
            if ($status !== null) {
                $qb->andWhere('l.status = :status')
                    ->setParameter('status', $status);
            }
        }

        if (isset($filters['user_id']) && $filters['user_id'] !== '') {
            $qb->andWhere('u.id = :userId')
                ->setParameter('userId', (int) $filters['user_id']);
        }

        if (isset($filters['ip']) && $filters['ip'] !== '') {
            $qb->andWhere('l.ip LIKE :ip')
                ->setParameter('ip', '%'.$filters['ip'].'%');
        }

        if (isset($filters['user_agent']) && $filters['user_agent'] !== '') {
            $qb->andWhere('l.userAgent LIKE :userAgent')
                ->setParameter('userAgent', '%'.$filters['user_agent'].'%');
        }

        if (isset($filters['created_at_from']) && $filters['created_at_from'] !== '') {
            $from = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $filters['created_at_from'].' 00:00:00')
                ?: \DateTimeImmutable::createFromFormat('Y-m-d', (string) $filters['created_at_from']);
            if ($from !== false) {
                $qb->andWhere('l.createdAt >= :createdFrom')
                    ->setParameter('createdFrom', $from);
            }
        }

        if (isset($filters['created_at_to']) && $filters['created_at_to'] !== '') {
            $to = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $filters['created_at_to'].' 23:59:59')
                ?: \DateTimeImmutable::createFromFormat('Y-m-d', (string) $filters['created_at_to']);
            if ($to !== false) {
                $qb->andWhere('l.createdAt <= :createdTo')
                    ->setParameter('createdTo', $to);
            }
        }

        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(DISTINCT l.id)')
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();

        $items = $qb
            ->setFirstResult(max(0, ($page - 1) * $limit))
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return ['items' => $items, 'total' => $total];
    }
}
