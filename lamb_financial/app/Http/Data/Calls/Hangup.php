<?php
namespace App\Http\Data\Calls;
use App\Http\Data\Calls\Element;

class Hangup extends Element {

    protected $valid = array('reason', 'schedule');

    /**
    * Hangup Constructor
    *
    * Instatiates a new Hangup object object with optional attributes.
    * Possible attributes are:
    *   "reason" => 'rejected'|'busy'
    *   "schedule" => '25'
    *
    * @return Hangup
    */
    function __construct($attr = array()) {
        parent::__construct(NULL, $attr);
    }

}
?>