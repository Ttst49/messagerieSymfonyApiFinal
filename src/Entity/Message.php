<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups("forCreation")]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups("forCreation")]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups("forCreation")]
    private ?User $author = null;

    #[ORM\Column]
    #[Groups("forCreation")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'message', targetEntity: Response::class)]
    #[Groups("forCreation")]
    private Collection $responses;

    #[ORM\ManyToOne(inversedBy: 'message')]
    private ?Group $ofGroup = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    private ?Group $associatedTo = null;

    public function __construct()
    {
        $this->responses = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->tests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, Response>
     */
    public function getResponses(): Collection
    {
        return $this->responses;
    }

    public function addResponse(Response $response): static
    {
        if (!$this->responses->contains($response)) {
            $this->responses->add($response);
            $response->setMessage($this);
        }

        return $this;
    }

    public function removeResponse(Response $response): static
    {
        if ($this->responses->removeElement($response)) {
            // set the owning side to null (unless already changed)
            if ($response->getMessage() === $this) {
                $response->setMessage(null);
            }
        }

        return $this;
    }

    public function getOfGroup(): ?Group
    {
        return $this->ofGroup;
    }

    public function setOfGroup(?Group $ofGroup): static
    {
        $this->ofGroup = $ofGroup;

        return $this;
    }


}
