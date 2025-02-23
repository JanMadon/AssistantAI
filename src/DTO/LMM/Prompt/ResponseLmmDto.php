<?php

namespace App\DTO\LMM\Prompt;

use App\Entity\Conversation;
use Symfony\Component\Validator\Constraints as Assert;

class ResponseLmmDto
{
    #[Assert\Type(Conversation::class)]
    public ?Conversation $forConversation = null;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $id;

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
    #[Assert\Type('numeric')]
    public int $prompt_tokens;
    #[Assert\Type('numeric')]
    public int $completion_tokens;
    #[Assert\Type('numeric')]
    public int $total_tokens;
    public ?string $use_function = null;
    public array $function_arguments = [];

    public function __construct(?Conversation $forConversation, string $id, string $role, string $content, string $model, int $prompt_tokens, int $completion_tokens, int $total_tokens)
    {
        $this->forConversation = $forConversation;
        $this->id = $id;
        $this->role = $role;
        $this->content = $content;
        $this->model = $model;
        $this->prompt_tokens = $prompt_tokens;
        $this->completion_tokens = $completion_tokens;
        $this->total_tokens = $total_tokens;
    }

}
