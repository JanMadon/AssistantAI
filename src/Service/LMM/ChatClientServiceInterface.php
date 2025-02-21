<?php

namespace App\Service\LMM;

use App\DTO\LMM\Prompt\ResponseLmmDto;
use App\Entity\Conversation;

interface ChatClientServiceInterface
{
    public function prompt(Conversation $conversation): ResponseLmmDto;
}