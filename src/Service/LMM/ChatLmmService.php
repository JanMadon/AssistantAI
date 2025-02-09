<?php

namespace App\Service\LMM;

use Doctrine\Common\Collections\Collection;

interface ChatLmmService
{
    public function getChatModels(): array;
}