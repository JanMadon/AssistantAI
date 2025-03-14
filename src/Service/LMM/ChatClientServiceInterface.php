<?php

namespace App\Service\LMM;

use App\DTO\LMM\Prompt\PromptDto;
use App\DTO\LMM\Prompt\ResponseLmmDto;
use App\Entity\Conversation;

interface ChatClientServiceInterface
{
    public function prompt(Conversation $conversation): ResponseLmmDto;
    public function promptVisionModelWithUrlImage(promptDto $promptDto): ResponseLmmDto;
    public function makeTranscription(string $filePath): ?string;
    public function createSpeech(string $text, string $savePath): ?string;
}