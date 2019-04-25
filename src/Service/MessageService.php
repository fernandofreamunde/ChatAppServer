<?php

namespace App\Service;

use App\Entity\Conversation;
use App\Entity\Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class MessageService
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var Request
     */
    private $request;

    /**
     * MessageService constructor.
     * @param Security $security
     * @param Request $request
     */
    public function __construct(Security $security, Request $request)
    {
        $this->security = $security;
        $this->request = $request;
    }

    /**
     * @param Conversation $conversation
     * @return Message
     */
    public function addMessageToConversation(Conversation $conversation)
    {
        $message = $this->getMessageEntity();
        $message->setAuthor($this->security->getUser());
        $message->setConversation($conversation);
        $message->setContent($this->request->request->get('message'));

        $em = $this->getDoctrine()->getManager();

        $em->persist($message);
        $em->flush();

        return $message;
    }

    /**
     * @return Message
     */
    private function getMessageEntity()
    {
        return new Message();
    }
}
