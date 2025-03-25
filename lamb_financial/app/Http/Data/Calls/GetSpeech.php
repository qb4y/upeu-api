<?php
namespace App\Http\Data\Calls;
use App\Http\Data\Calls\Element;

class GetSpeech extends Element {

    protected $valid = array('action','method','timeout', 'engine', 'grammar', 
                             'playBeep', 'grammarPath');

    protected $nesting = array('Speak', 'Play', 'Wait');
    /**
    * GetSpeech Constructor
    * @param array $attr Optional attributes
    * @return GetSpeech
    */
    function __construct($attr = array()){
        parent::__construct(NULL, $attr);
    }

}
?>