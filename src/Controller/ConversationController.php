<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\ConversationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ConversationController extends AbstractController
{
    /**
     * @Route("/conversation/{email}", name="get_conversation_byuser", methods={"GET"})
     * @param User $user
     * @param ConversationService $conversationService
     * @return JsonResponse
     */
    public function getConversation(User $user, ConversationService $conversationService)
    {
        $conversation = $conversationService->getConversationWithContact($user);
        return new JsonResponse($conversation, 200, [], true);
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
