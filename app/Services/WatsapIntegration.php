<?php

namespace App\Services;
use UltraMsg\WhatsAppApi;
class WatsapIntegration
{
    public $watsap;
    public $object;
    public function __construct($object){
         $this->object = $object;
    }
    public function Process(){

    $ultramsg_token="8gdjvtvz4u8n5oor"; // Ultramsg.com token
    $instance_id="instance82297"; // Ultramsg.com instance id
    $watsap = new WhatsAppApi($ultramsg_token,$instance_id);

    $to="+966 0505725290";
    $body = "Name: " . $this->object['name'] .
    "\nDate: " . $this->object['date'] .
    "\nTime: " . $this->object['time'] .
    "\nArea: " . $this->object['area'] .
    "\nCity: " . $this->object['city'] .
    "\nMessage: " . $this->object['message'];
    $api=$watsap->sendChatMessage($to,$body);
    // print_r($api);

}
}
