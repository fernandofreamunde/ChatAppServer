<?php

namespace App\Service;

use App\Entity\Conversation;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class ConversationService
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var ConversationRepository
     */
    private $conversationRepository;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * ConversationService constructor.
     * @param Security $security
     * @param ConversationRepository $conversationRepository
     * @param Request $request
     * @param ContainerInterface $container
     */
    public function __construct(
        Security $security,
        ConversationRepository $conversationRepository,
        RequestStack $requestStack,
        ContainerInterface $container,
        UserRepository $userRepository
    )
    {
        $this->security = $security;
        $this->conversationRepository = $conversationRepository;
        $this->request = $requestStack->getCurrentRequest();
        $this->container = $container;
        $this->userRepository = $userRepository;
    }

    /**
     * @param User $participant
     * @return bool|float|int|string
     */
    public function getConversations()
    {
        $conversation = $this->conversationRepository->findConversationsByUserId($this->getUser()->getId());

        return $this->serialize($conversation);
    }

    /**
     * @return array|bool|float|int|string
     */
    public function createConversation()
    {
        $participant = $this->userRepository->findOneBy(['email' => $this->request->request->get('contact')['email']]);

        $conversation = $this->getConversationEntity();
        $conversation->addParticipant($this->getUser());
        $conversation->addParticipant($participant);

        try {
            $this->save($conversation);

        } catch (\Exception $exception) {
            return [
                'error' => 'Unprocessable Entity',
                'code' => 422
            ];
        }

        return $this->serialize($conversation);
    }

    /**
     * @param $data
     * @param string $title
     * @return bool|float|int|string
     */
    public function serialize($data, $title = 'conversations')
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
     * @return Conversation
     */
    private function getConversationEntity()
    {
        return new Conversation();
    }

    /**
     * @param $entity
     */
    private function save($entity)
    {
        $em = $this->container->get('doctrine')->getManager();

        $em->persist($entity);
        $em->flush();
    }

    /**
     * @return \Symfony\Component\Security\Core\User\UserInterface|null
     */
    public function getUser(): User
    {
        return $this->security->getUser();
    }

}