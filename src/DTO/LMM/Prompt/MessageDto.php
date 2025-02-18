<?php

namespace App\DTO\LMM\Prompt;

use Symfony\Component\Validator\Constraints as Assert;

class MessageDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $role;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $content;

    public function __construct(string $role, string $content)
    {
        $this->role = $role;
        $this->content = $content;
    }


}
