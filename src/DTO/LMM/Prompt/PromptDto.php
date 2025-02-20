<?php

namespace App\DTO\LMM\Prompt;

use Symfony\Component\Validator\Constraints as Assert;

class PromptDto
{
    //#[Assert\NotBlank]
    #[Assert\Type('int')]
    public ?int $conversation_id;
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $system_field;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $role;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $content;

    #[Assert\NotNull]
    #[Assert\Type('string')]
    public string $model;

    #[Assert\Type('float')]
    public float $temperature;

    #[Assert\Type('numeric')]
    public float $max_token;

    public function __construct($conversation_id, $system_field, $role, $content, $model, $temperature, $max_token)
    {
        $this->conversation_id = $conversation_id;
        $this->system_field = $system_field;
        $this->role = $role;
        $this->content = $content;
        $this->model = $model;
        $this->temperature = $temperature;
        $this->max_token = $max_token;
    }


}
