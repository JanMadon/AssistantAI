<?php

namespace App\DTO\LMM;

use Symfony\Component\Validator\Constraints as Assert;

class TemplateLmmDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $name;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Type('content')]
    public string $content;
    public function __construct($name, $content)
    {
        $this->name = $name;
        $this->content = $content;
    }
}
