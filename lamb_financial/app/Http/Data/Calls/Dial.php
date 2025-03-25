<?php
namespace App\Http\Data\Calls;
use App\Http\Data\Calls\Element;

class Dial extends Element {

    protected $valid = array('action','method','timeout','hangupOnStar',
'timeLimit', 'callerId', 'callerName', 'confirmSound', 'dialMusic', 'confirmKey', 'redirect',
'callbackUrl', 'callbackMethod', 'digitsMatch');

    protected $nesting = array('Number');

    /**
    * Dial Constructor
    *
    * Instatiates a new Dial object with a number and optional attributes.
    * Possible attributes are:
    *   "action" =>  absolute url
    *   "method" => 'GET'|'POST', (default: POST)
    *   "timeout" => positive integer, (default: 30)
    *   "hangupOnStar"  => true|false, (default: false)
    *   "timeLimit" => integer >= 0, (default: 14400, 4hrs)
    *   "callerId" => valid phone #, (default: Caller's callerid)
*   "redirect" => true|false, if 'false', don't redirect to 'action', only request url 
    *   and continue to next element. (default 'true')
    *
    * @param string|Number|Conference $number The number or conference you wish to call
    * @param array $attr Optional attributes
    * @return Dial
    */
    function __construct($number='', $attr = array()) {
        parent::__construct($number, $attr);
    }

}
?>