<?php

namespace App\DTO\LMM;

use Symfony\Component\Validator\Constraints as Assert;

class SettingLmmDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $name;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $model;

    #[Assert\NotNull]
    #[Assert\Type('int')]
    #[Assert\Range(min: 0, max: 150)]
    public ?int $temperature;

    #[Assert\NotNull]
    #[Assert\Type('int')]
    #[Assert\Range(min: 0, max: 150)]
    public ?int $maxToken;

    public function __construct($name, $model, $temperature, $maxToken)
    {
        $this->name = $name;
        $this->model = $model;
        $this->temperature = $temperature;
        $this->maxToken = $maxToken;
    }
}
