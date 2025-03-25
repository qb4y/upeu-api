<?php
namespace App\Http\Data\Calls;
use App\Http\Data\Calls\Element;

class PreAnswer extends Element {
    protected $valid = array();

    protected $nesting = array('Speak', 'Play', 'Wait', 'GetDigits', 'GetSpeech', 'Redirect', 'SIPTransfer');

     function __construct($attr = array()){
        parent::__construct($attr);
     }
}
?>