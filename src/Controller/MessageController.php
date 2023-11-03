<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Repository\ResponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/api")]
class MessageController extends AbstractController
{
    #[Route("/message/index/{id}", name: "app_message_indexgroup", methods: "GET")]
    #[Route('/message/index', name: 'app_message_index', methods: "GET")]
    public function index(MessageRepository $messageRepository,Request $request ,ResponseRepository $responseRepository,Group $group): Response
    {
        if ($request->get("_route")=== "app_message_indexgroup"){
            $all = [$messageRepository->findByIndex($group), $responseRepository->findAll()];
        }else{
            $all = [$messageRepository->findBy(["ofGroup"=>null]),$responseRepository->findAll()];
        }


        return $this->json($all, 200,[],["groups"=>"forCreation"]);
    }


    #[Route('/message/create/{id}',name: "app_message_createmessageforgroup")]
    #[Route("/message/create",name: "app_message_createmessage")]
    public function createMessage(SerializerInterface $serializer, Request $request, EntityManagerInterface $manager, Group $group):Response{


        $message = $serializer->deserialize($request->getContent(),Message::class,"json");

        if (!$this->getUser()){
            return $this->json("Veuillez vous connecter pour envoyer un message",200);
        }

        $message->setAuthor($this->getUser());

        if ($request->get("_route")=== "app_message_createmessageforgroup"){
            $message->setOfGroup($group);
        }


        $manager->persist($message);
        $manager->flush();

        return $this->json($message,200,[],["groups"=>"forCreation"]);
    }


    #[Route("/message/delete/{id}")]
    public function deleteMessage(Message $message, EntityManagerInterface $manager):Response{


        if ($message->getAuthor() != $this->getUser()){
            return $this->json("Vous n'êtes pas l'auteur de ce message à priori", 200);
        }
        foreach ($message->getResponses() as $response){
            $manager->remove($response);
        }

        $manager->remove($message);
        $manager->flush();

        return $this->json("Message supprimé", 200);
    }
}
