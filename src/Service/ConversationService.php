<?php


namespace App\Service;


use App\Entity\Conversation;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        Request $request,
        ContainerInterface $container,
        UserRepository $userRepository
    )
    {
        $this->security = $security;
        $this->conversationRepository = $conversationRepository;
        $this->request = $request;
        $this->container = $container;
        $this->userRepository = $userRepository;
    }

    public function getConversationWithContact(User $participant)
    {
        $conversation = $this->conversationRepository->findByWithParticipants($this->security->getUser()->getId(), $participant->getId());

        return $this->serialize($conversation, 'conversation');
    }

    public function createConversation()
    {
        $participant = $this->userRepository->findOneBy(['email' => $this->request->request->get('contact')['email']]);

        $conversation = $this->getConversationEntity();
        $conversation->addParticipant($this->security->getUser());
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

    private function save($entity)
    {
        $em = $this->container->get('doctrine')->getManager();

        $em->persist($entity);
        $em->flush();
    }

}