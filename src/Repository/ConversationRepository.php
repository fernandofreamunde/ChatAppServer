<?php

namespace App\Repository;

use App\Entity\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Conversation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conversation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conversation[]    findAll()
 * @method Conversation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

     /**
      * @return Conversation[] Returns an array of Conversation objects
      */
    public function findByWithParticipants($participant1, $participant2)
    {
        // works for now, since we want a conversation with just 2 participants
        // if group chat is eventually introduced by adding more users to a conversation,
        // this will need to be re done.
        // PS I really do not like the raw query, but I dont know doctrine that well as of now.
        $conn = $this->getEntityManager()
            ->getConnection();
        $sql = 'SELECT conversation_id as id, count(conversation_id) as user_count 
                FROM conversation_user
                WHERE user_id IN (:participant1,:participant2)
                GROUP BY conversation_id
                ORDER BY user_count DESC
                LIMIT 1';
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':participant1' => $participant1,
            ':participant2' => $participant2,
        ]);

        return $this->find($stmt->fetch()['id']);
    }

    public function findConversationsByUserId($id)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.participants', 'p')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

    /*
    public function findOneBySomeField($value): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
