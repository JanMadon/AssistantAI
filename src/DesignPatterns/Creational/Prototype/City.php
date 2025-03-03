<?php
declare(strict_types=1);

namespace App\DesignPatterns\Creational\Prototype;

final class City
{
    private string $id;

    public function __construct()
    {
        $this->id = uniqid();
    }
}