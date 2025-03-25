<?php
namespace App\Http\Data\Calls;
use App\Http\Data\Calls\Element;

class SIPTransfer extends Element {

    protected $valid = array();

    /**
    * SIPTransfer Constructor
    *
    * Instatiates a new SIPTransfer object with text and optional attributes.
    * @param string $url An absolute or relative URL for a different RESTXML document.
    * @return SIPTransfer
    */
    function __construct($url='', $attr = array()) {
        parent::__construct($url, $attr);
    }

}
?>