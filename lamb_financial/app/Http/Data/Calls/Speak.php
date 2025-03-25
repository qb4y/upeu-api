<?php
namespace App\Http\Data\Calls;
use App\Http\Data\Calls\Element;

class Speak extends Element {

    protected $valid = array('voice','language','loop', 'engine', 'method', 'type');
    /**
    * Speak Constructor
    *
    * Instatiates a new Speak object with text and optional attributes.
    * Possible attributes are:
    *   "voice" => 'man'|'woman',
    *   "language" => 'en'|'es'|'fr'|'de',
    *   "loop"  => integer >= 0
    *
    * @param string $text
    * @param array $attr Optional attributes
    * @return Speak
    */
    function __construct($text='', $attr = array()) {
        parent::__construct($text, $attr);
    }
}
?>