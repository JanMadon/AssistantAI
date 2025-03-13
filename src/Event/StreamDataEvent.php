<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class StreamDataEvent extends Event
{
    public function __construct(
        private readonly ?string $content
    ) {}

    public function getContent(): ?string
    {
        return $this->content;
    }

}