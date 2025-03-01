<?php

namespace App\Service\Assistant;

use App\DTO\LMM\Prompt\PromptDto;
use App\DTO\LMM\Prompt\ResponseLmmDto;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Service\LMM\ChatClientServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class ChatService
{

    private promptDto $promptDto;
    public function __construct(
        private readonly EntityManagerInterface     $entityManager,
        private readonly ConversationRepository     $conversationRepository,
        private readonly MessageRepository          $messageRepository,
        private readonly ChatClientServiceInterface $chatClientService,
        private readonly KernelInterface $kernel
    ) {}

    public function chat(PromptDto $promptDto): ResponseLmmDto
    {
        $this->promptDto = $promptDto;

        if($promptDto->conversation_id === null){
            $conversation = $this->conversationRepository->CreateAndSaveNewConversation($promptDto);
        }else {
            $conversation = $this->conversationRepository->getConversationById($promptDto->conversation_id);
        }

        $this->messageRepository->createNewMessage($conversation, $promptDto->role, $promptDto->content);
        $this->entityManager->flush();

        if($promptDto->is_function_calling){
            $res = $this->useFunctionCalling();
            $res->forConversation = $conversation;
            return $res;
        }

        $gptRes = $this->chatClientService->prompt($conversation);
        $this->messageRepository->createNewMessage($conversation, $gptRes->role, $gptRes->content);

        return $gptRes;
    }

    private function useFunctionCalling(): ResponseLmmDto
    {
        $system = $this->kernel->getProjectDir().'/src/Data/OpenAi/functionCalling/prompt.txt';
        $functions = $this->kernel->getProjectDir().'/src/Data/OpenAi/functionCalling/functions.json';
        $this->promptDto->system_field = file_get_contents($system);
        $this->promptDto->functions = json_decode(file_get_contents($functions), true);

        $gptRes = $this->chatClientService->functionCalling($this->promptDto);
        if(is_null($gptRes->use_function)){
            $gptRes->content = 'Assistant did not return any function to call';
            return $gptRes;
        }

        if(!in_array($gptRes->use_function, array_map(fn($fn) => $fn['name'], $this->promptDto->functions))){
            $gptRes->content = 'Assistant did not return known function to call';
            return $gptRes;
        }

        $selectedFunction = array_values(
            array_filter($this->promptDto->functions, fn($fn) => $fn['name'] === $gptRes->use_function)
        )[0];

        $arguments = [];
        foreach ($selectedFunction['parameters']['properties'] as $argumentName => $parameter) {
            $arguments[$argumentName] = $gptRes->function_arguments[$argumentName];
        }

        $gptRes->content = $this->callFunction($selectedFunction['name'], $arguments);
        return $gptRes;
    }

    private function callFunction(string $name, array $arguments)
    {
        if(method_exists($this, $name)){
            return $this->$name($arguments);
        }
        throw new \Exception('Function not found: ' . $name);
    }

    /* Methods defined in src/Data/OpenAi/functionCalling/functions.json **/
    private function image_to_text(array $arg): string
    {
        $this->promptDto->function_arguments = $arg;
        $res = $this->chatClientService->promptVisionModelWithUrlImage($this->promptDto);

        return $res->content;
    }

    private function transcription(array $arg) : string
    {
        $filePath = $this->kernel->getProjectDir().'/public/uploads/' . $arg['file_name'];
        $res = $this->chatClientService->makeTranscription($filePath);

        return $res ?? 'error returning transcription';
    }

    private function text_to_speech(array $arg) : string
    {
        $savePath = $this->kernel->getProjectDir().'/public/storage/audio/tts/speech.mp3';
        $text = $arg['text'];

        $res = $this->chatClientService->createSpeech($text, $savePath);


        return 'text_to_speech';
    }


}