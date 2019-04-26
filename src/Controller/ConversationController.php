<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\User;
use App\Service\ConversationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ConversationController extends AbstractController
{
    /**
     * @Route("/conversation", name="get_conversations", methods={"GET"})
     * @param User $user
     * @param ConversationService $conversationService
     * @return JsonResponse
     */
    public function index(ConversationService $conversationService)
    {
        return new JsonResponse($conversationService->getConversations(), 200, [], true);
    }

    /**
     * @Route("/conversation/{id}", name="get_conversation_byuser", methods={"GET"})
     * @param User $user
     * @param ConversationService $conversationService
     * @return JsonResponse
     */
    public function show(Conversation $conversation, ConversationService $conversationService)
    {
        return new JsonResponse($conversationService->serialize($conversation, 'conversation'), 200, [], true);
    }

    /**
     * @Route("/conversation", name="post_conversations", methods={"POST"})
     * @param ConversationService $conversationService
     * @return JsonResponse
     */
    public function new(ConversationService $conversationService)
    {
        $conversation = $conversationService->createConversation();

        if (is_array($conversation)) {
            return $this->json([
                'error' => $conversation['error'],
            ], $conversation['code']);
        }

        return new JsonResponse($conversation, 201, [], true);
    }
}
