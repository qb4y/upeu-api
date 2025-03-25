<?php
namespace App\Http\Data\Calls;
use App\Http\Data\Calls\Element;

class Wait extends Element {

    protected $valid = array('length');

    /**
    * Wait Constructor
    *
    * Instatiates a new Wait object with text and optional attributes.
    * Possible attributes are:
    *   "length" => integer > 0, (default: 1)
    *
    * @param array $attr Optional attributes
    * @return Wait
    */
    function __construct($attr = array()) {
        parent::__construct(NULL, $attr);
    }

}
?>