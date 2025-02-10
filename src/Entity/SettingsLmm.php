<?php

namespace App\Entity;

use App\Repository\SettingsLmmRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingsLmmRepository::class)]
class SettingsLmm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $temperature = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $max_token = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $model_id = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getModelId(): ?string
    {
        return $this->model_id;
    }

    public function setModelId(?string $model_id): static
    {
        $this->model_id = $model_id;

        return $this;
    }
}
