<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/api")]
class GroupController extends AbstractController
{
    #[Route("/group/index")]
    public function index():Response{

        return $this->json($this->getUser()->getGroups(),200,[],["groups"=>"forIndex"]);
    }

    #[Route("/group/create")]
    public function createGroup(SerializerInterface $serializer, Request $request, EntityManagerInterface $manager):Response{

        $group = $serializer->deserialize($request->getContent(),Group::class,"json");

        if (!$this->getUser()){
            return $this->json("vous devez être connecté pour créer un groupe",200);
        }

        $group->addMember($this->getUser());
        $manager->persist($group);
        $manager->flush();

        return $this->json("groupe créer avec succès sous le nom ".$group->getName(),200);
    }

    #[Route("/group/connect/{id}")]
    public function connectIntoGroup(Group $group, EntityManagerInterface $manager):Response{


        if (!$this->getUser()){
            return $this->json("Vous devez être connecté pour pour connecter à un groupe",200);
        }
        foreach ($this->getUser()->getGroups() as $entity){
            if ($group->getId() === $entity->getId()){
                $this->getUser()->setCurrentGroup($group);
                $manager->flush();

                return $this->json("Vous êtes bien connecté au groupe ".$group->getName(),200,[],["groups"=>"forIndex"]);
            }
        }


        return $this->json("Désolé mais vous ne faites pas partie de ce groupe",200);
    }

    #[Route("/group/getCurrent")]
    public function getCurrentGroup(){

        if ($this->getUser()->getCurrentGroup() == null){
            return $this->json("Vous n'êtes actuellement pas dans un groupe");
        }

        return $this->json($this->getUser()->getCurrentGroup(),200,[],["groups"=>"forIndex"]);
    }
}
