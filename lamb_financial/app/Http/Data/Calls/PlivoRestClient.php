<?php
namespace App\Http\Data\Calls;
use App\Http\Data\Calls\PlivoException;
use App\Http\Data\Calls\PlivoRestResponse;

class PlivoRestClient {

    protected $Endpoint;
    protected $AccountSid;
    protected $AuthToken;
    protected $ApiVersion;

    /*
     * __construct
     *   $username : Plivo Sid
     *   $password : Plivo AuthToken
     *   $endpoint : The Plivo REST URL
     *   $ApiVersion : The API version
     */
    public function __construct($endpoint, $accountSid, $authToken, $ApiVersion = 'v0.1') {
        $this->AccountSid = $accountSid;
        $this->AuthToken = $authToken;
        $this->Endpoint = $endpoint;
        $this->ApiVersion = $ApiVersion;
    }

    /*
     * sendRequst
     *   Sends a REST Request to the Plivo REST API
     *   $path : the URL (relative to the endpoint URL, after the /v1)
     *   $method : the HTTP method to use, defaults to GET
     *   $vars : for POST or PUT, a key/value associative array of data to
     * send, for GET will be appended to the URL as query params
     */
    public function request($path, $method = "GET", $vars = array()) {
        
        $fp = null;
        $tmpfile = "";
        $encoded = "";
        foreach($vars AS $key=>$value)
            $encoded .= "$key=".urlencode($value)."&";
        $encoded = substr($encoded, 0, -1);

        // construct full url
        $url = "{$this->Endpoint}/$path";
        // dd($url);
        // if GET and vars, append them
        if($method == "GET")
            $url .= (FALSE === strpos($path, '?')?"?":"&").$encoded;

        // initialize a new curl object
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        switch(strtoupper($method)) {
            case "GET":
                curl_setopt($curl, CURLOPT_HTTPGET, TRUE);
                break;
            case "POST":
                curl_setopt($curl, CURLOPT_POST, TRUE);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $encoded);
                break;
            case "PUT":
                // curl_setopt($curl, CURLOPT_PUT, TRUE);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $encoded);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                file_put_contents($tmpfile = tempnam("/tmp", "put_"),
                    $encoded);
                curl_setopt($curl, CURLOPT_INFILE, $fp = fopen($tmpfile,
                    'r'));
                curl_setopt($curl, CURLOPT_INFILESIZE,
                    filesize($tmpfile));
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
                throw(new PlivoException("Unknown method $method"));
                break;
        }

        // send credentials
        curl_setopt($curl, CURLOPT_USERPWD,
            $pwd = "{$this->AccountSid}:{$this->AuthToken}");

        // do the request. If FALSE, then an exception occurred
        if(FALSE === ($result = curl_exec($curl)))
            throw(new PlivoException(
                "Curl failed with error " . curl_error($curl)));

        // get result code
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // unlink tmpfiles
        if($fp)
            fclose($fp);
        if(strlen($tmpfile))
            unlink($tmpfile);

        return new PlivoRestResponse($url, $result, $responseCode);
    }

    // REST Reload Plivo Config Helper
    public function reload_config($vars = array()) {
        $path = "$this->ApiVersion/ReloadConfig/";
        $method = "POST";
        return $this->request($path, $method, $vars);
    }

    // REST Reload Plivo Cache Config Helper
    public function reload_cache_config($vars = array()) {
        $path = "$this->ApiVersion/ReloadCacheConfig/";
        $method = "POST";
        return $this->request($path, $method, $vars);
    }

    // REST Call Helper
    public function call($vars = array()) {
        $path = "$this->ApiVersion/Call/";
        $method = "POST";
        return $this->request($path, $method, $vars);
    }

    // REST Bulk Call Helper
    public function bulk_call($vars = array()) {
        $path = "$this->ApiVersion/BulkCall/";
        $method = "POST";
        return $this->request($path, $method, $vars);
    }

    // REST Group Call Helper
    public function group_call($vars = array()) {
        $path = "$this->ApiVersion/GroupCall/";
        $method = "POST";
        return $this->request($path, $method, $vars);
    }

    // REST Transfer Live Call Helper
    public function transfer_call($vars = array()) {
        $path = "$this->ApiVersion/TransferCall/";
        $method = "POST";
        return $this->request($path, $method, $vars);
    }

    // REST Hangup All Live Calls Helper
    public function hangup_all_calls() {
        $path = "$this->ApiVersion/HangupAllCalls/";
        $method = "POST";
        return $this->request($path, $method);
    }

    // REST Hangup Live Call Helper
    public function hangup_call($vars = array()) {
        $path = "$this->ApiVersion/HangupCall/";
        $method = "POST";
        return $this->request($path, $method, $vars);
    }

    // REST Schedule Hangup Helper
    public function schedule_hangup($vars = array()) {
        $path = "$this->ApiVersion/ScheduleHangup/";
        $method = "POST";
        return $this->request($path, $method, $vars);
    }

    // REST Cancel a Scheduled Hangup Helper
    public function cancel_scheduled_hangup($vars = array()) {
        $path = "$this->ApiVersion/CancelScheduledHangup/";
        $method = "POST";
        return $this->request($path, $method, $vars);
    }

        // REST RecordStart helper
	public function record_start($vars = array()) {
           $path = "$this->ApiVersion/RecordStart/";
           $method = "POST";
           return $this->request($path, $method, $vars);
	}

        // REST RecordStop
	public function record_stop($vars = array()) {
           $path = "$this->ApiVersion/RecordStop/";
           $method = "POST";
           return $this->request($path, $method, $vars);
	}

        // REST Play something on a Call Helper
        public function play($vars = array()) {
            $path = "$this->ApiVersion/Play/";
            $method = "POST";
            return $this->request($path, $method, $vars);
        }

        // REST PlayStop something on a Call Helper
        public function play_stop($vars = array()) {
            $path = "$this->ApiVersion/PlayStop/";
            $method = "POST";
            return $this->request($path, $method, $vars);
        }

        // REST Schedule Play Helper
        public function schedule_play($vars = array()) {
            $path = "$this->ApiVersion/SchedulePlay/";
            $method = "POST";
            return $this->request($path, $method, $vars);
        }

        // REST Cancel a Scheduled Play Helper
        public function cancel_scheduled_play($vars = array()) {
            $path = "$this->ApiVersion/CancelScheduledPlay/";
            $method = "POST";
            return $this->request($path, $method, $vars);
        }

        // REST Add soundtouch audio effects to a Call Helper
        public function sound_touch($vars = array()) {
            $path = "$this->ApiVersion/SoundTouch/";
            $method = "POST";
            return $this->request($path, $method, $vars);
        }

        // REST Remove soundtouch audio effects on a Call Helper
        public function sound_touch_stop($vars = array()) {
            $path = "$this->ApiVersion/SoundTouchStop/";
            $method = "POST";
            return $this->request($path, $method, $vars);
        }

        // REST Send digits to a Call Helper
        public function send_digits($vars = array()) {
            $path = "$this->ApiVersion/SendDigits/";
            $method = "POST";
            return $this->request($path, $method, $vars);
        }

        // REST Conference Mute helper
    	public function conference_mute($vars = array()) {
           $path = "$this->ApiVersion/ConferenceMute/";
           $method = "POST";
           return $this->request($path, $method, $vars);
	}

        // REST Conference Unmute helper
	public function conference_unmute($vars = array()) {
	   $path = "$this->ApiVersion/ConferenceUnmute/";
           $method = "POST";
           return $this->request($path, $method, $vars);
	}

        // REST Conference Kick helper
	public function conference_kick($vars = array()) {
           $path = "$this->ApiVersion/ConferenceKick/";
           $method = "POST";
           return $this->request($path, $method, $vars);
	}

        // REST Conference Hangup helper
	public function conference_hangup($vars = array()) {
           $path = "$this->ApiVersion/ConferenceHangup/";
           $method = "POST";
           return $this->request($path, $method, $vars);
	}

        // REST Conference Deaf helper
	public function conference_deaf($vars = array()) {
           $path = "$this->ApiVersion/ConferenceDeaf/";
           $method = "POST";
           return $this->request($path, $method, $vars);
	}

        // REST Conference Undeaf helper
	public function conference_undeaf($vars = array()) {
           $path = "$this->ApiVersion/ConferenceUndeaf/";
           $method = "POST";
           return $this->request($path, $method, $vars);
	}

        // REST Conference RecordStart helper
	public function conference_record_start($vars = array()) {
           $path = "$this->ApiVersion/ConferenceRecordStart/";
           $method = "POST";
           return $this->request($path, $method, $vars);
	}

        // REST Conference RecordStop
	public function conference_record_stop($vars = array()) {
           $path = "$this->ApiVersion/ConferenceRecordStop/";
           $method = "POST";
           return $this->request($path, $method, $vars);
	}

        // REST Conference Play helper
	public function conference_play($vars = array()) {
           $path = "$this->ApiVersion/ConferencePlay/";
           $method = "POST";
           return $this->request($path, $method, $vars);
	}

        // REST Conference Speak helper
	public function conference_speak($vars = array()) {
           $path = "$this->ApiVersion/ConferenceSpeak/";
           $method = "POST";
           return $this->request($path, $method, $vars);
	}

        // REST Conference List Helper
	public function conference_list($vars = array()) {
           $path = "$this->ApiVersion/ConferenceList/";
           $method = "POST";
           return $this->request($path, $method, $vars);
	}

        // REST Conference List Members Helper
	public function conference_list_members($vars = array()) {
           $path = "$this->ApiVersion/ConferenceListMembers/";
           $method = "POST";
           return $this->request($path, $method, $vars);
	}
}
?>