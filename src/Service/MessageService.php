<?php

namespace App\Service;

use App\Entity\Conversation;
use App\Entity\Message;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @var ContainerInterface
     */
    private $container;

    /**
     * MessageService constructor.
     * @param Security $security
     * @param Request $request
     * @param ContainerInterface $container
     */
    public function __construct(Security $security, Request $request, ContainerInterface $container)
    {
        $this->security = $security;
        $this->request = $request;
        $this->container = $container;
    }

    /**
     * @param Conversation $conversation
     * @return Message
     */
    public function createMessageToConversation(Conversation $conversation)
    {
        $message = $this->getMessageEntity();
        $message->setAuthor($this->security->getUser());
        $message->setConversation($conversation);
        $message->setContent($this->request->request->get('message'));

        $this->save($message);
        return $this->serialize($message, 'message');
    }

    public function serialize($data, $title = 'messages')
    {
        //had to use this because of a circular reference,
        //most of the code is the $this->json() that is normally used in controllers
        return $this->container->get('serializer')->serialize([ $title => $data], 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ], [
            'groups' => ['list']
        ]));
    }

    /**
     * @return Message
     */
    private function getMessageEntity()
    {
        return new Message();
    }

    private function save($entity)
    {
        $em = $this->container->get('doctrine')->getManager();

        $em->persist($entity);
        $em->flush();
    }
}
