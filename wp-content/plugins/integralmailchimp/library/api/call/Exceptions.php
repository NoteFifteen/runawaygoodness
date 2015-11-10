<?php

namespace IMC\Library\API\Call;

class MailChimp_Error extends \Exception {


    public $type;


    public function __construct($type, $message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->type = $type;


    }


}

