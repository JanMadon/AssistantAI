<?php

namespace App\Service\LMM;

use Doctrine\Common\Collections\Collection;

interface ChatClinetInterface
{
    public function getChatModels(): array;
}