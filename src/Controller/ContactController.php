<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Service\ContactService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="get_contact", methods={"GET"})
     */
    public function index(ContactService $contactService)
    {
        $contacts = $contactService->getContacts();

        return $this->json([
            'contacts' => $contacts
        ], 200, [], [
            'groups' => ['list'],
        ]);
    }

    /**
     * @Route("/contact", name="set_contact_invite", methods={"POST"})
     */
    public function new(ContactService $contactService)
    {
        $contact = $contactService->createContact();

        if (is_array($contact)) {
            return $this->json([
                'error' => $contact['error'],
            ], $contact['code']);
        }

        return $this->json([
            'contact' => $contact
        ], 201, [], [
            'groups' => ['list'],
        ]);
    }

    /**
     * @Route("/contact/invites", name="get_contact_invites", methods={"GET"})
     * @param ContactService $contactService
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function invites(ContactService $contactService)
    {
        return $this->json([
            'contacts' => $contactService->getContactInvites()
        ], 200, [], [
            'groups' => ['list'],
        ]);
    }

    /**
     * @Route("/contact/{id}", name="set_contact", methods={"PUT"})
     */
    public function update(Contact $contact, Request $request, ContactService $contactService)
    {
        if ($request->request->get('status') === 'accepted') {
            $newContact = $contactService->acceptContactInvite($contact);
        }

        if ($request->request->get('status') === 'rejected') {
            $contact = $contactService->rejectContactInvite($contact);
        }

        if (is_array($contact)) {
            return $this->json([
                'error' => $contact['error'],
            ], $contact['code']);
        }

        return $this->json([
            'contact' => $contact->getStatus() !== 'accepted' ? $contact : $newContact
        ], 200, [], [
            'groups' => ['list'],
        ]);
    }

    /**
     * @Route("/contact/{id}", name="delete_contact", methods={"DELETE"})
     */
    public function delete(Contact $contact, ContactService $contactService)
    {
        $result = $contactService->deleteContact($contact);

        if (is_array($result)) {
            return $this->json([
                'error' => $result['error'],
            ], $result['code']);
        }

        return $this->json([], 200, [], []);
    }
}
