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
        $message =$messageService->addMessageToConversation($conversation);

        //had to use this because of a circular reference,
        //most of the code is the $this->>json() that is normally used in this app
        $json = $this->container->get('serializer')->serialize([ 'message' => $message], 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ], [
            'groups' => ['list']
        ]));
        return new JsonResponse($json, 201, [], true);
    }

    /**
     * @Route("/conversation/{id}/messages", name="get_conversation_messages", methods={"GET"})
     * @param Conversation $conversation
     * @return JsonResponse
     */
    public function show(Conversation $conversation)
    {
        //had to use this because of a circular reference,
        //most of the code is the $this->>json() that is normally used in this app
        $json = $this->container->get('serializer')->serialize([ 'messages' => $conversation->getMessages()], 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ], [
            'groups' => ['list']
        ]));

        return new JsonResponse($json, 200, [], true);
    }
}
