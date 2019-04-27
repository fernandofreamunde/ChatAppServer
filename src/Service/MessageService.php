<?php

namespace App\Service;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\MessageRepository;
use DateTime;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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
     * @var MessageRepository
     */
    private $messageRepository;

    /**
     * MessageService constructor.
     * @param Security $security
     * @param RequestStack $requestStack
     * @param ContainerInterface $container
     * @param MessageRepository $messageRepository
     */
    public function __construct(Security $security, RequestStack $requestStack, ContainerInterface $container, MessageRepository $messageRepository)
    {
        $this->security = $security;
        $this->request = $requestStack->getCurrentRequest();
        $this->container = $container;
        $this->messageRepository = $messageRepository;
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
        $message->setCreatedAt(new DateTime());
        $message->setUpdatedAt(new DateTime());

        $this->save($message);
        return $this->serialize($message, 'message');
    }

    public function getMessages(Conversation $conversation)
    {
        if ($this->request->query->get('page') !== null && is_numeric($this->request->query->get('page'))) {
            $page = $this->request->query->get('page');
            return $this->serialize($this->messageRepository->findByConversationPaginated($conversation->getId(), $page));
        }

        if ($this->request->query->get('since') !== null) {
            //$date = DateTime::createFromFormat( 'Y-m-d H:i:s',$this->request->query->get('since'));

            return $this->serialize($this->messageRepository->findByConversationSinceId($conversation->getId(), $this->request->query->get('since')));
        }

        return $this->serialize($this->messageRepository->findByConversationPaginated($conversation->getId()));
    }

    public function getMessagesPages(Conversation $conversation)
    {
        return $this->serialize($this->messageRepository->getPageCount($conversation->getId()), 'pages');
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
