<?php
namespace App\Http\Data\Utils;
use Exception;
use MailchimpTransactional;

class SendEmail
{

    public static function send($data)
    {
        $obj = (object) $data;
        $mailchimp = new MailchimpTransactional\ApiClient();
        $mailchimp->setApiKey(env('MAILCHIMP_KEY'));

        if($obj->attachments) {
            $message = [
                "from_email" => $obj->from_email,
                "from_name" => $obj->from_name,
                "to" => [[
                    "email" => $obj->correo,
                    "type" => "to"
                ]],
                "html" => $obj->html,
                "subject" => $obj->asunto,
                "attachments" => $obj->attachments
            ];
        }else{
            $message = [
                "from_email" => $obj->from_email,
                "from_name" => $obj->from_name,
                "to" => [[
                    "email" => $obj->correo,
                    "type" => "to"
                ]],
                "html" => $obj->html,
                "subject" => $obj->asunto,
            ];
        }
        if ($message) {
            $respuesta = $mailchimp->messages->send(
                [
                    "message" => $message
                ]
            );
        } else {
            $respuesta = "";
        }


        if (is_array($respuesta)  == false) {
            if (strpos($respuesta, 'HTTP/1.1 500')) {
                $response = array(
                    'success' => false,
                    'message' => 'Operación incorrectas',
                    'data' => $respuesta,

                );
                return  $response;
            }
        } else {
            $response = array(
                'success' => true,
                'message' => 'Operación correcta, documentos enviado con éxito',
                'data' => $respuesta,
            );
            return  $response;
        }


    }
    public static function sendGroupMail($data)
    {
        $obj = (object) $data;
        $mailchimp = new MailchimpTransactional\ApiClient();
        $mailchimp->setApiKey(env('MAILCHIMP_KEY'));
        $correos= [];
        //dd($obj);
        foreach($obj->data as $r){
            $dat = [
                "email" => $r,
                "type" => "to"
            ];
            $correos[]=$dat;
        }
        if (count($correos)>0) {
            $message = [
                "from_email" => $obj->from_email,
                "from_name" => $obj->from_name,
                "to" => $correos,
                "html" => $obj->html,
                "subject" => $obj->asunto 
            ];
            $respuesta = $mailchimp->messages->send(
                [
                    "message" => $message
                ]
            );

        } else {
            $respuesta = "";
        }

        dd($respuesta);
        if (is_array($respuesta)  == false) {
            if (strpos($respuesta, 'HTTP/1.1 500')) {
                $response = array(
                    'success' => false,
                    'message' => 'Operación incorrectas',
                    'data' => $respuesta,

                );
                return  $response;
            }
        } else {
            $response = array(
                'success' => true,
                'message' => 'Operación correcta, documentos enviado con éxito',
                'data' => $respuesta,
            );
            return  $response;
        }



    }


}