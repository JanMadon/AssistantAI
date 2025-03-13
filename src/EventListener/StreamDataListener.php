<?php

namespace App\EventListener;

use App\Controller\AssistantController;
use App\Event\StreamDataEvent;

class StreamDataListener
{
    public function __construct(
        private AssistantController $assistantController
    )
    {}
    public function __invoke(StreamDataEvent $event)
    {
        $content = $event->getContent();
        // Obsługa odbieranego zdarzenia (np. zapis do loga)
        if ($content !== null) {
            $this->assistantController->assistantStreamResponse($event);
            // Przykład
           // dump("Przesłane dane: {$content}");
        }
    }
}