<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use  App\Http\Data\HumanTalent\PaymentsData;
class SendBoleta extends Mailable
{
    use Queueable, SerializesModels;
 
    /**
     * The order instance.
     *
     * @var Order
     */
    private $fileName;
    private $name;
    private $asunto;
    private $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $fileName,$name,$asunto)
    {
        $this->fileName = $fileName;
        $this->name = $name;
        $this->asunto = $asunto;
        $this->data = $data;
    }
 
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $ret = PaymentsData::getUrlByName($this->fileName);

        $url  = $ret['data'];

        $getFile = file_get_contents($url);

        $doc  = base64_encode($getFile);

         return $this->view('emails.boleta',$this->data)
                    ->attachData($doc,$this->name,
                        [
                        'mime' => 'application/pdf',
                        ])
                    ->subject($this->asunto);

    }
}
?>