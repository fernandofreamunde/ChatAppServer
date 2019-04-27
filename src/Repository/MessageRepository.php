<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function findByConversationPaginated($conversationId, $page = 1, $itemsPerPage = 5)
    {
        $offset = ($page - 1) * $itemsPerPage;
        return $this->createQueryBuilder('m')
            ->andWhere('m.conversation = :id')
            ->setParameter('id', $conversationId)
            ->orderBy('m.id', 'DESC')
            ->setMaxResults($itemsPerPage)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getPageCount($conversationId, $itemsPerPage = 5)
    {
        $itemCount = $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->andWhere('m.conversation = :id')
            ->setParameter('id', $conversationId)
            ->getQuery()
            ->getSingleScalarResult();

        return ceil($itemCount / $itemsPerPage);
    }

    public function findByConversationSinceDate($conversationId, $date)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.conversation = :id')
            ->setParameter('id', $conversationId)
            ->andWhere('m.createdAt > :date')
            ->setParameter('date', $date)
            ->getQuery()->getSQL()
            ->getResult()
        ;
    }

    public function findByConversationSinceId($conversationId, $id)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.conversation = :id')
            ->setParameter('id', $conversationId)
            ->andWhere('m.id > :since')
            ->setParameter('since', $id)
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Message
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
