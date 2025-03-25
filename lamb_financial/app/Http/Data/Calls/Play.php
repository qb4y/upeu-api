<?php
namespace App\Http\Data\Calls;
use App\Http\Data\Calls\Element;

class Play extends Element {

    protected $valid = array('loop');

    /**
    * Play Constructor
    *
    * Instatiates a new Play object with a URL and optional attributes.
    * Possible attributes are:
    *   "loop" =>  integer >= 0
    *
    * @param string $url The URL of an audio file that Plivo will retrieve and play to the caller.
    * @param array $attr Optional attributes
    * @return Play
    */
    function __construct($url='', $attr = array()) {
        parent::__construct($url, $attr);
    }
} 
?>