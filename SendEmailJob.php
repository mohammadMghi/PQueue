<?php
require 'JobInterface.php';

class SendEmailJob implements JobInterface {
    public $to;
    public $message;

    public function __construct($to, $message) {
        $this->to = $to;
        $this->message = $message;
    }

    public function handle() {
        mail($this->to, "Subject", $this->message);
    } 
}
