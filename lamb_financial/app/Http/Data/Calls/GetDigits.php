<?php
namespace App\Http\Data\Calls;
use App\Http\Data\Calls\Element;

class GetDigits extends Element {

    protected $valid = array('action','method','timeout','finishOnKey',
        'numDigits', 'retries', 'invalidDigitsSound', 'validDigits', 'playBeep');

    protected $nesting = array('Speak', 'Play', 'Wait');
    /**
    * GetDigits Constructor
    *
    * Instatiates a new GetDigits object with optional attributes.
    * Possible attributes are:
    *   "action" =>  absolute url 
    *   "method" => 'GET'|'POST', (default: POST)
    *   "timeout" => positive integer, (default: 5)
    *   "finishOnKey"   => any digit, #, *, (default: #)
    *   "numDigits" => integer >= 1 (default: unlimited)
    *
    * @param array $attr Optional attributes
    * @return GetDigits
    */
    function __construct($attr = array()){
        parent::__construct(NULL, $attr);
    }

}
?>