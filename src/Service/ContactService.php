<?php


namespace App\Service;


use App\Entity\Contact;
use App\Repository\ContactRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class ContactService
{
    const CONTACT_STATUS_INVITED = 'invited';
    const CONTACT_STATUS_ACCEPTED = 'accepted';
    const CONTACT_STATUS_REJECTED = 'rejected';

    /**
     * @var Security
     */
    private $security;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var ContactRepository
     */
    private $contactRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        Security $security,
        Request $request,
        ContainerInterface $container,
        ContactRepository $contactRepository,
        UserRepository $userRepository
    )
    {
        $this->security = $security;
        $this->request = $request;
        $this->container = $container;
        $this->contactRepository = $contactRepository;
        $this->userRepository = $userRepository;
    }

    public function getContactInvites()
    {
        return $this->contactRepository->findInvitesByContact($this->security->getUser()->getId());
    }

    public function getContacts()
    {
        return $this->contactRepository->findBy(['owner' => $this->security->getUser()]);
    }

    public function createContact()
    {
        $email = $this->request->request->get('email');
        if ($email === null) {
            return [
                'error' => 'Bad Request',
                'code' => 400,
            ];
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if ($user === null) {
            return [
                'error' => 'User not found',
                'code' => 404,
            ];
        }

        $contact = $this->getContactEntity();
        $contact->setContact($user);
        $contact->setStatus(self::CONTACT_STATUS_INVITED);

        try {
            $this->save($contact);
        } catch (\Exception $exception) {
            if ($exception instanceof UniqueConstraintViolationException) {
                return [
                    'error' => 'Contact Already Exists',
                    'code' => 422,
                ];
            }
            return [
                'error' => 'Unprocessable Entity',
                'code' => 422,
            ];
        }

        return $contact;
    }

    public function acceptContactInvite(Contact $contact)
    {
        $newContact = $this->getContactEntity();
        $newContact->setContact($contact->getOwner());
        $newContact->setStatus(self::CONTACT_STATUS_ACCEPTED);

        $contact->setStatus(self::CONTACT_STATUS_ACCEPTED);

        try {
            $this->save($newContact);
        } catch (\Exception $exception) {
            if ($exception instanceof UniqueConstraintViolationException) {
                return [
                    'error' => 'Contact Already Exists',
                    'code' => 422,
                ];
            }
            return [
                'error' => 'Unprocessable Entity',
                'code' => 422,
            ];
        }

        return $newContact;
    }

    public function rejectContactInvite(Contact $contact)
    {
        $contact->setStatus(self::CONTACT_STATUS_REJECTED);
        $this->update($contact);
        return $contact;
    }

    public function deleteContact(Contact $contact)
    {
        if ($this->security->getUser()->getId() !== $contact->getOwner()->getId()) {
            return [
                'error' => 'Unauthorized',
                'code' => 401,
            ];
        }

        try {
            $manager = $this->container->get('doctrine')->getManager();
            $manager->remove($contact);
            $manager->flush();

        } catch (\Exception $exception) {
            return [
                'error' => 'Unprocessable Entity',
                'code' => 422,
            ];
        }
    }

    private function save($entity)
    {
        $em = $this->container->get('doctrine')->getManager();

        $em->persist($entity);
        $em->flush();
    }

    public function update($entity)
    {
        $em = $this->container->get('doctrine')->getManager();

        $em->merge($entity);
        $em->flush();
    }

    private function getContactEntity()
    {
        return (new Contact())->setOwner($this->security->getUser());
    }
}
