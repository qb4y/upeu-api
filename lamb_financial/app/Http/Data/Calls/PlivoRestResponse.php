<?php
namespace App\Http\Data\Calls;

class PlivoRestResponse {

    public $ResponseJson;
    public $Response;
    public $HttpStatus;
    public $Url;
    public $QueryString;
    public $IsError;
    public $ErrorMessage;

    public function __construct($url, $text, $status) {
        preg_match('/([^?]+)\??(.*)/', $url, $matches);
        $this->Url = $matches[1];
        $this->QueryString = $matches[2];
        $this->ResponseJson = $text;
        $this->HttpStatus = $status;
        if($this->HttpStatus != 204)
            $this->Response = @json_decode($text);

        if($this->IsError = ($status >= 400)) {
          if($status == 401) {
            $this->ErrorMessage = "Authentication required";
          } else {
            $this->ErrorMessage =
                (string)$this->Response->Message;
          }
        }
    }

}
?>