<?php	
 namespace App\Http\Controllers\Setup\Provider;
     class sunatTemp {
        function __construct()
		{
		}
        function searchRuc($ruc){
        
            $ruta = "https://incared.com/api/apirest";

            // $ruc = (isset($_GET['ruc'])) ? $_GET['ruc'] : '20168999926';

            $data = array(
                "action"                         => "getnumero",
                "numero"                         => $ruc,
            );
                
            $data_json = json_encode($data);
            //Invocamos el servicio de RUC 
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $ruta);
            curl_setopt(
                $ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                )
            );
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $respuesta  = curl_exec($ch);
            curl_close($ch);
            //print_r($respuesta);
            //exit();
            // dd($respuesta);
            $resp = json_decode($respuesta);
            return  $resp;

        }
     }

?>