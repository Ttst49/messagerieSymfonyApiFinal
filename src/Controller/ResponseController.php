<?php

namespace App\Controller;

use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/api")]
class ResponseController extends AbstractController
{
    #[Route('/response/create/{id}', name: 'app_response_index')]
    public function create(MessageRepository $repository ,Message $message, EntityManagerInterface $manager, Request $request, SerializerInterface $serializer): Response
    {
        $response = $serializer->deserialize($request->getContent(),\App\Entity\Response::class,"json");


        if (!$this->getUser()){
            return $this->json("Veuillez vous connecter pour répondre à ce message",200);
        }
        $response->setAuthor($this->getUser());

        $response->setMessage($repository->findOneBySomeField($message->getId()));
        $manager->persist($response);
        $manager->flush();

        return $this->json($message,200,[],["groups"=>"forCreation"]);

    }

    #[Route("/response/delete/{id}",name: "app_response_delete")]
    public function delete(\App\Entity\Response $response,EntityManagerInterface $manager):Response{

        $manager->remove($response);
        $manager->flush();

        return $this->json("Réponse supprimée avec succès",200);
    }
}
