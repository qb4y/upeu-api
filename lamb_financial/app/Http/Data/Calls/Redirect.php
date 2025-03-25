<?php
namespace App\Http\Data\Calls;
use App\Http\Data\Calls\Element;

class Redirect extends Element {

    protected $valid = array('method');

    /**
    * Redirect Constructor
    *
    * Instatiates a new Redirect object with text and optional attributes.
    * Possible attributes are:
    *   "method" => 'GET'|'POST', (default: POST)
    *
    * @param string $url An absolute or relative URL for a different RESTXML document.
    * @param array $attr Optional attributes
    * @return Redirect
    */
    function __construct($url='', $attr = array()) {
        parent::__construct($url, $attr);
    }

}
?>