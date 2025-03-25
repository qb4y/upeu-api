<?php
namespace App\Http\Data\Calls;
use App\Http\Data\Calls\PlivoException;
use App\Http\Data\Calls\Speak;//****hay me quede
use App\Http\Data\Calls\Play;;
use App\Http\Data\Calls\Dial;
use App\Http\Data\Calls\Number;
use App\Http\Data\Calls\GetDigits;
use App\Http\Data\Calls\GetSpeech;
use App\Http\Data\Calls\Record;
use App\Http\Data\Calls\Hangup;
use App\Http\Data\Calls\Redirect;
use App\Http\Data\Calls\SIPTransfer;
use App\Http\Data\Calls\Wait;
use App\Http\Data\Calls\Conference;
use App\Http\Data\Calls\PreAnswer;

class Element {
    private $tag;
    private $body;
    private $attr;
    private $children;

    /*
     * __construct
     *   $body : Element contents
     *   $body : Element attributes
     */
    function __construct($body=NULL, $attr = array()) {
        if (is_array($body)) {
            $attr = $body;
            $body = NULL;
        }
        $this->tag = get_class($this);
        $this->body = $body;
        $this->attr = array();
        $this->children = array();
        self::addAttributes($attr);
    }

    /*
     * addAttributes
     *     $attr  : A key/value array of attributes to be added
     *     $valid : A key/value array containging the accepted attributes
     *     for this element
     *     Throws an exception if an invlaid attribute is found
     */
    private function addAttributes($attr) {
        foreach ($attr as $key => $value) {
            if(in_array($key, $this->valid))
                $this->attr[$key] = $value;
            else
                throw new PlivoException($key . ', ' . $value .
                   " is not a supported attribute pair");
        }
    }

    /*
     * append
     *     Nests other element elements inside self.
     */
    function append($element) {
        if (!isset($this->nesting) or is_null($this->nesting))
            throw new PlivoException($this->tag ." doesn't support nesting");
        else if(!is_object($element))
            throw new PlivoException($element->tag . " is not an object");
        else if(!in_array(get_class($element), $this->nesting))
            throw new PlivoException($element->tag . " is not an allowed element here");
        else {
            $this->children[] = $element;
            return $element;
        }
    }

    /*
     * set
     *     $attr  : An attribute to be added
     *    $valid : The attrbute value for this element
     *     No error checking here
     */
    function set($key, $value){
        $this->attr[$key] = $value;
    }

    /* Convenience Methods */
    function addSpeak($body=NULL, $attr = array()){
        return self::append(new Speak($body, $attr));
    }

    function addPlay($body=NULL, $attr = array()){
        return self::append(new Play($body, $attr));
    }

    function addDial($body=NULL, $attr = array()){
        return self::append(new Dial($body, $attr));
    }

    function addNumber($body=NULL, $attr = array()){
        return self::append(new Number($body, $attr));
    }

    function addGetDigits($attr = array()){
        return self::append(new GetDigits($attr));
    }

    function addGetSpeech($attr = array()){
        return self::append(new GetSpeech($attr));
    }

    function addRecord($attr = array()){
        return self::append(new Record($attr));
    }

    function addHangup($attr = array()){
        return self::append(new Hangup($attr));
    }

    function addRedirect($body=NULL, $attr = array()){
        return self::append(new Redirect($body, $attr));
    }

    function addSIPTransfer($body=NULL, $attr = array()){
        return self::append(new SIPTransfer($body, $attr));
    }

    function addWait($attr = array()){
        return self::append(new Wait($attr));
    }

    function addConference($body=NULL, $attr = array()){
        return self::append(new Conference($body, $attr));
    }

    function addPreAnswer($attr = array()){
        return self::append(new PreAnswer(NULL, $attr));
    }

    /*
     * write
     * Output the XML for this element and all it's children
     *    $parent: This element's parent element
     *    $writeself : If FALSE, Element will not output itself,
     *    only its children
     */
    protected function write($parent, $writeself=TRUE){
        if($writeself) {
            $elem = $parent->addChild($this->tag, htmlspecialchars($this->body));
            foreach($this->attr as $key => $value)
                $elem->addAttribute($key, $value);
            foreach($this->children as $child)
                $child->write($elem);
        } else {
            foreach($this->children as $child)
                $child->write($parent);
        }

    }

}
?>