<?php

namespace App\DTO\LMM\Prompt;

use Symfony\Component\Validator\Constraints as Assert;

class PromptDto
{
    //#[Assert\NotBlank]
    #[Assert\Type('int')]
    public ?int $id;
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $system_field;

    /** @var array|MessageDto[]  */
    #[Assert\NotNull]
    #[Assert\Type('array')]
    public array $conversation;

    #[Assert\NotNull]
    #[Assert\Type('string')]
    public string $model;

    #[Assert\Type('float')]
    public float $temperature; // todo its stdclass make dto or vo

    public function __construct($id, $system_field, $conversation, $model, $temperature)
    {
        $this->id = $id;
        $this->system_field = $system_field;
        $this->model = $model;
        $this->temperature = $temperature;
        $this->conversation = array_map(function($message) {
            return new MessageDto($message->role, $message->content);
        }, $conversation);;
    }


}
