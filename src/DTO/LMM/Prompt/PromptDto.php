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

    #[Assert\Type('bool')]
    public bool $is_function_calling = false;

    public array $functions;

    public array $function_arguments;

    public ?string $file_name = null;

    public bool $stream = false;

    public function __construct(
        $conversation_id,
        $system_field,
        $role,
        $content,
        $model,
        $temperature,
        $max_token,
        $function_calling = false,
        $file_name = null,
        $stream = false
    )
    {
        $this->conversation_id = $conversation_id;
        $this->system_field = $system_field;
        $this->role = strtolower($role);
        $this->content = $content;
        $this->model = $model;
        $this->temperature = $temperature;
        $this->max_token = $max_token;
        $this->is_function_calling = $function_calling;
        $this->functions = [];
        $this->file_name = $file_name;
        $this->stream = $stream;
    }


}
