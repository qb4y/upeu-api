<?php
namespace App\Http\Data\Calls;
use App\Http\Data\Calls\Element;

class Conference extends Element {

    protected $valid = array('muted','beep','startConferenceOnEnter',
        'endConferenceOnExit','waitSound','enterSound', 'exitSound',
        'timeLimit', 'hangupOnStar', 'maxMembers', 'recordFilePath',
        'recordFileFormat', 'recordFileName', 'action', 'method',
    'callbackUrl', 'callbackMethod', 'digitsMatch', 
    'floorEvent', 'stayAlone');

    /**
    * Conference Constructor
    *
    * Instatiates a new Conference object with room and optional attributes.
    * Possible attributes are:
    *   waitSound: sound to play while alone in conference
    *       (default no sound)
    *   muted: enter conference muted
    *       (default false)
    *   startConferenceOnEnter: the conference start when this member joins
    *       (default true)
    *   endConferenceOnExit: close conference after this member leaves
    *       (default false)
    *   maxMembers: max members in conference
    *       (0 for max : 200)
    *   enterSound: if "", disabled
    *       if beep:1, play one beep when a member enters
    *       if beep:2 play two beeps when a member enters
    *       (default "")
    *   exitSound: if "", disabled
    *       if beep:1, play one beep when a member exits
    *       if beep:2 play two beeps when a member exits
    *       (default "")
    *   timeLimit: max time before closing conference
    *       (default 14400 seconds)
    *   hangupOnStar: exit conference when member press '*'
    *       (default false)
    *   action: redirect to this URL after leaving conference
    *   method: submit to 'action' url using GET or POST
    *   callbackUrl: url to request when call enters/leaves conference
            or has pressed digits matching (digitsMatch)
    *   callbackMethod: submit to 'callbackUrl' url using GET or POST
    *   digitsMatch: a list of matching digits to send with callbackUrl
            Can be a list of digits patterns separated by comma.

    *
    * @param string $room Conference room to join
    * @param array $attr Optional attributes
    * @return Conference
    */
     function __construct($room = '', $attr = array()){
        parent::__construct($room, $attr);
     }

}
?>