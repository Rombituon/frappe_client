<?php


namespace Rombituon\FrappeClient\Exceptions;


class FrappeException extends \Exception
{
    public function __construct(
        $message
        , $code = 0
    ) {
        parent::__construct(
            $message
            ,$code
            ,$previous = null
        );
    }
    public function __toString() {
        return __CLASS__ .': ['.$this->code.']: '.$this->message.PHP_EOL;
    }
}