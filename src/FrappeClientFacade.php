<?php
namespace Rombituon\FrappeClient;

use Illuminate\Support\Facades\Facade;
use Rombituon\FrappeClient\FrappeClient;

class FrappeClientFacade extends Facade {
    protected static function getFacadeAccessor() 
    { 
        return FrappeClient::class; 
    }
}
?>