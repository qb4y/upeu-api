<?php
namespace App\Http\Data\Calls;
use App\Http\Data\Calls\Element;

class Record extends Element {

    protected $valid = array('action', 'method', 'timeout','finishOnKey',
                             'maxLength', 'bothLegs', 'playBeep',
                             'fileFormat', 'filePath', 'fileName', 'redirect');

    /**
    * Record Constructor
    *
    * Instatiates a new Record object with optional attributes.
    * Possible attributes are:
    *   "action" =>  absolute url, 
    *   "method" => 'GET'|'POST', (default: POST)
    *   "timeout" => positive integer, (default: 5)
    *   "finishOnKey"   => any digit, #, * (default: 1234567890*#)
    *   "maxLength" => integer >= 1, (default: 3600, 1hr)
    *   "playBeep" => true|false, (default: true)
    *
    * @param array $attr Optional attributes
    * @return Record
    */
    function __construct($attr = array()) {
        parent::__construct($attr);
    }
}
?>