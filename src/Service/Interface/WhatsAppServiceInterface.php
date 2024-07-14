<?php 

namespace App\Service\Interface;

interface WhatsAppServiceInterface{
    public function getSession();
    public function StartSession();
    public function StopSession();
}