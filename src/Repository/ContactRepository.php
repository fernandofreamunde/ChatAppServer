<?php

namespace App\Repository;

use App\Entity\Contact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Contact|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contact|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contact[]    findAll()
 * @method Contact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Contact::class);
    }

     /**
      * @return Contact[] Returns an array of Contact objects
      */
    public function findInvitesByContact($contactId)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.contact = :contact')
            ->setParameter('contact', $contactId)
            ->andWhere('c.status = :status')
            ->setParameter('status', 'invited')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

     /**
      * @return Contact[] Returns an array of Contact objects
      */
    public function findContactInvites($contactId)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.owner = :owner')
            ->orWhere('c.contact = :owner')
            ->setParameter('owner', $contactId)
            ->andWhere('c.status = :status_invited')
            ->setParameter('status_invited', 'invited')
            ->orWhere('c.status = :status_rejected')
            ->setParameter('status_rejected', 'rejected')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Contact
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
