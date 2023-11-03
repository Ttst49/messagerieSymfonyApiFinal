<?php

namespace App\Controller;

use App\Entity\Request;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/api")]
class RequestController extends AbstractController
{
    #[Route("/request/getRequest")]
    public function getRequest():Response{
        if (!$this->getUser()){
            return $this->json("Connectez-vous pour voir vos invitations",200);
        }

        return $this->json($this->getUser()->getRequests(),200,[],["groups"=>"forIndex"]);
    }

    #[Route('/request/{id}', name: 'app_request_sendtouser')]
    public function sendToUser(User $user, EntityManagerInterface $manager): Response
    {
        if (!$this->getUser()){
            return $this->json("Vous devez vous connecter pour envoyer une invitation à un groupe",200);
        }
        if (!$this->getUser()->getCurrentGroup()){
            return $this->json("Vous devez vous connecter à un groupe pour pouvoir envoyer une invitation à un groupe",200);
        }
        $group = $this->getUser()->getCurrentGroup();
        $request = new Request();
        $request->setToGroups($group);
        $request->setToUser($user);
        foreach ($user->getRequests() as $request){
            if ($request->getToGroups()->getId() === $group->getId()){
                return $this->json("Cet utilisateur a déjà été invité dans ce groupe",200);
            }
        }
        $manager->persist($request);
        $manager->flush();

        return $this->json("L'invitation a bien été envoyé à l'utilisateur ".$request->getToUser()->getUsername()." pour rejoindre le groupe ".$request->getToGroups()->getName(),200);
    }


    #[Route("/request/accept/{id}")]
    public function acceptRequest(Request $request, EntityManagerInterface $manager):Response{

        $group = $request->getToGroups();
        $group->addMember($request->getToUser());
        $manager->remove($request);
        $manager->flush();

        return $this->json("Vous avez accepté l'invitation au groupe ".$request->getToGroups()->getName(),200);
    }

    #[Route('/request/deny/{id}')]
public function denyRequest(Request $request, EntityManagerInterface $manager):Response{

        $manager->remove($request);
        $manager->flush();

        return $this->json("Vous avez supprimé l'invitation au groupe ".$request->getToGroups()->getName(),200);
    }

}
