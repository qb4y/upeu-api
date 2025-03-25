<?php
/**
 * Created by PhpStorm.
 * User: alexander.llacho
 * Date: 25/05/2017
 * Time: 4:12 PM
 */

namespace App\Http\Controllers\Email;

use App\Http\Controllers\Controller;
use App\Http\Data\TestData;
use App\LambUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use PDF;
use DOMPDF;
use PHPMailer\PHPMailer\PHPMailer;
 
class EmailController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    

    public function test()
    {


$mail = new PHPMailer(true);

// notice the \ you have to use root namespace here
try {
$mail->isSMTP(); // tell to use smtp
$mail->CharSet = 'UTF-8';// set charset to utf8
$mail->Host = 'smtp.upn.org.pe';
$mail->SMTPAuth = true;
$mail->SMTPSecure = false;   
$mail->Port = 587; // most likely something different for you. This is the mailtrap.io port i use for testing. 
$mail->Username = 'visitacion.upn@adventistas.org.pe';                 // SMTP username
$mail->Password = 'visitacion2017';                           // SMTP password

$mail->setFrom('visitacion.upn@adventistas.org.pe', 'UPN');
$mail->Subject = "examle";
$mail->Body = "This is a test new test";
$mail->addAddress('carlos.saavedra@adventistas.org.pe', 'Carlos Saavedra');  
$mail->AltBody = 'Visitacion UPN';

//$mail->addAttachment(‘/home/kundan/Desktop/abc.doc’, ‘abc.doc’); // Optional name
/*$mail->SMTPOptions= array(
‘ssl’ => array(
‘verify_peer’ => false,
‘verify_peer_name’ => false,
‘allow_self_signed’ => true
)
);
*/
//dd($mail);
$mail->send();
} catch (phpmailerException $e) {
dd($e);
} catch (Exception $e) {
dd($e);
}
dd('success');

/*


    		$resp = "";
            $message = utf8_encode("Hubo un problema, por favor comuníquese con su Pastor.");
            
            $mail = new PHPMailer;
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.upn.org.pe';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'visitacion.upn@adventistas.org.pe';                 // SMTP username
            $mail->Password = 'visitacion2017';                           // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                                    // TCP port to connect to
            $mail->setFrom('visitacion.upn@adventistas.org.pe', 'UPN');
            $mail->addAddress('carlos.saavedra@adventistas.org.pe', 'Carlos Saavedra');     // Add a recipient
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Acceso: Plan de Visitación UPN';
            $mail->Body    = 'Hola <b>'.$data_user[0]["name"].' '.$data_user[0]["lastname"] .' </b> su contraseña de acceso es: <b>'.$clave. '</b> '.
                    '<br/><br/> Atentamente. <br/> Visitación UPN';
            $mail->AltBody = 'Visitación UPN';

            if(!$mail->send()) {
                $resp = utf8_encode("Disculpa, No se envió el correo, si vuelve a ocurrir escribenos a visitacion@adventistas.org.pe, gracias");
            } else {
                $resp = utf8_encode("Se ha enviado satisfactoriamente el correo con las indicaciones de acceso :)");
            }


*/


        $jResponse = [
            'success' => false,
            'message' => 'no register'
        ];

        $lstData = TestData::atest();
        dd($lstData);
        return response()->json($jResponse);
    }

}
