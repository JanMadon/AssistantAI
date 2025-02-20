<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('conversation')]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('conversation')]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups('conversation')]
    private ?string $system_field = null;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'conversation', fetch: 'EAGER')]
    #[Groups('conversation')]
    #[MaxDepth(1)]
    private ?Collection $messages = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('conversation')]
    private ?string $model_id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('conversation')]
    private ?string $temperature = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('conversation')]
    private ?string $max_token = null;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSystemField(): ?string
    {
        return $this->system_field;
    }

    public function setSystemField(?string $description): static
    {
        $this->system_field = $description;

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setConversation($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getConversation() === $this) {
                $message->setConversation(null);
            }
        }

        return $this;
    }

    public function getMessagesCount(): int
    {
        return $this->messages->count();
    }
    public function getModelId(): ?string
    {
        return $this->model_id;
    }
    public function setModelId(?string $model_id): static
    {
        $this->model_id = $model_id;
        return $this;
    }
    public function getTemperature(): ?string
    {
        return $this->temperature;
    }
    public function setTemperature(?string $temperature): static
    {
        $this->temperature = $temperature;
        return $this;
    }
    public function getMaxToken(): ?string
    {
        return $this->max_token;
    }
    public function setMaxToken(?string $max_token): static
    {
        $this->max_token = $max_token;
        return $this;
    }
}
