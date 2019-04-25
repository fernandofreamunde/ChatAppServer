<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Service\MessageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
    /**
     * @Route("/conversation/{id}/message", name="new_message", methods={"POST"})
     * @param Conversation $conversation
     * @param MessageService $messageService
     * @return JsonResponse
     */
    public function new(Conversation $conversation, MessageService $messageService)
    {
        $message = $messageService->createMessageToConversation($conversation);

        return new JsonResponse($message, 201, [], true);
    }

    /**
     * @Route("/conversation/{id}/messages", name="get_conversation_messages", methods={"GET"})
     * @param Conversation $conversation
     * @param MessageService $messageService
     * @return JsonResponse
     */
    public function show(Conversation $conversation, MessageService $messageService)
    {
        $messages = $messageService->serialize($conversation->getMessages());

        return new JsonResponse($messages, 200, [], true);
    }
}
