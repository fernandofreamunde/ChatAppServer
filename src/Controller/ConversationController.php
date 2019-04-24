<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ConversationController extends AbstractController
{
    /**
     * @Route("/conversation", name="get_conversations", methods={"GET"})
     */
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ConversationController.php',
        ]);
    }

    /**
     * @Route("/conversation/{email}", name="get_conversation_byuser", methods={"GET"})
     */
    public function getConversation(User $participant, ConversationRepository $conversationRepository, Security $security)
    {
        $conversation = $conversationRepository->findByWithParticipants($security->getUser()->getId(), $participant->getId());

        //had to use this because of a circular reference,
        //most of the code is the $this->>json() that is normally used in this app
        $json = $this->container->get('serializer')->serialize([ 'conversation' => $conversation], 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ], [
            'groups' => ['list']
        ]));

        return new JsonResponse($json, 200, [], true);
    }

    /**
     * @Route("/conversation", name="post_conversations", methods={"POST"})
     */
    public function new(Request $request, Security $security, UserRepository $userRepository)
    {
        $participant = $userRepository->findOneBy(['email' => $request->request->get('contact')['email']]);

        $conversation = new Conversation();
        $conversation->addParticipant($security->getUser());
        $conversation->addParticipant($participant);

        try {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($conversation);
            $manager->flush();

        } catch (\Exception $exception) {
            return $this->json([
                'error' => 'Unprocessable Entity',
            ], 422);
        }

        return $this->json([
            'conversation' => $conversation,
            ], 201, [], [
            'groups' => ['list'],
        ]);
    }
}
