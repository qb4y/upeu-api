<?php
namespace App\Http\Data\Calls;
use App\Http\Data\Calls\Element;

class Number extends Element {

    protected $valid = array('sendDigits', 'sendOnPreanswer', 'gateways', 'gatewayCodecs',
                            'gatewayTimeouts', 'gatewayRetries', 'extraDialString');

     /**
    * Number Constructor
    *
    * Instatiates a new Number object with optional attributes.
    * Possible attributes are:
    *   "sendDigits"    => any digits
    *
    * @param string $number Number you wish to dial
    * @param array $attr Optional attributes
    * @return Number
    */
     function __construct($number = '', $attr = array()){
        parent::__construct($number, $attr);
     }

}
?>