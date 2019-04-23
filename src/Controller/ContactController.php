<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="get_contact", methods={"GET"})
     */
    public function index(Security $security, ContactRepository $contactRepository)
    {
        $contacts = $contactRepository->findBy(['owner' => $security->getUser()]);

        return $this->json([
            'contacts' => $contacts
        ], 200, [], [
            'groups' => ['list'],
        ]);
    }

    /**
     * @Route("/contact", name="set_contact_invite", methods={"POST"})
     */
    public function new(Request $request, Security $security, UserRepository $userRepository)
    {
        if ($request->request->get('email') === null) {
            return $this->json([
                'error' => 'Bad Request',
            ], 400);
        }

        $user = $userRepository->findOneBy(['email' => $request->request->get('email')]);

        if ($user === null) {
            return $this->json([
                'error' => 'User not found',
            ], 404);
        }

        $contact = new Contact();
        $contact->setOwner($security->getUser());
        $contact->setContact($user);
        // Set Value Object
        $contact->setStatus('invited');

        try {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($contact);
            $manager->flush();

        } catch (\Exception $exception) {
            if ($exception instanceof UniqueConstraintViolationException) {
                return $this->json([
                'error' => 'Contact Already Exists',
            ], 422);
            }
            return $this->json([
                'error' => 'Unprocessable Entity',
            ], 422);
        }

        return $this->json([
            'contact' => $contact
        ], 201, [], [
            'groups' => ['list'],
        ]);
    }

    /**
     * @Route("/contact/invites", name="get_contact_invites", methods={"GET"})
     */
    public function invites(Security $security, ContactRepository $contactRepository)
    {
        $contacts = $contactRepository->findInvitesByContact($security->getUser()->getId());

        return $this->json([
            'contacts' => $contacts
        ], 200, [], [
            'groups' => ['list'],
        ]);
    }

    /**
     * @Route("/contact/{id}", name="set_contact", methods={"PUT"})
     */
    public function update(Security $security, Contact $contact, Request $request)
    {
        $newContact = null;
        if ($request->request->get('status') === 'accepted') {
            // probably there is a better solution for the contacts thing...
            $newContact = new Contact();
            $newContact->setOwner($security->getUser());
            $newContact->setContact($contact->getOwner());
            // Set Value Object
            $newContact->setStatus($request->request->get('status'));
        }

        $contact->setStatus($request->request->get('status'));

        try {
            $manager = $this->getDoctrine()->getManager();
            $manager->merge($contact);
            if ($newContact instanceof Contact) {
                $manager->persist($newContact);
            }
            $manager->flush();

        } catch (\Exception $exception) {
            if ($exception instanceof UniqueConstraintViolationException) {
                return $this->json([
                    'error' => 'Contact Already Exists',
                ], 422);
            }
            return $this->json([
                'error' => 'Unprocessable Entity',
            ], 422);
        }

        return $this->json([
            'contact' => $contact->getStatus() !== 'accepted' ? $contact :$newContact
        ], 200, [], [
            'groups' => ['list'],
        ]);
    }
    /**
     * @Route("/contact/{id}", name="delete_contact", methods={"DELETE"})
     */
    public function delete(Security $security, Contact $contact)
    {
        // if user not owner do not delete send unauthorized
        if ($security->getUser()->getId() !== $contact->getOwner()->getId()) {
            return $this->json([
                'error' => 'Unauthorized',
            ], 401);
        }

        try {
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($contact);
            $manager->flush();

        } catch (\Exception $exception) {
            return $this->json([
                'error' => 'Unprocessable Entity',
            ], 422);
        }

        return $this->json([], 200, [], []);
    }
}
