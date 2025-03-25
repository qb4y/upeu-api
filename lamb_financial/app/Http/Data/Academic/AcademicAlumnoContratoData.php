<?php

/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 13/01/20
 * Time: 02:53 PM
 */

namespace App\Http\Data\Academic;

// use App\Models\File;
use Carbon\Carbon;

use DOMPDF;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class AcademicAlumnoContratoData
{

    static function validate($request){
        $response = array();
        $response['success'] = true;
        $response['message'] = "ok";

        $id_alumno_contrato = $request['id_alumno_contrato'];
        $content = $request['content'];
        $pathTemplate = $request['pathTemplate'];
        $datos_session = $request['datos_session'];
        $id_user = $request['id_user'];

        if (!isset($id_alumno_contrato)) {
            $response['success'] = false;
            $response['message'] = "No se encontro id_alumno_contrato";
            $jResponse['data']=[];
            return $response;
        }


        $pdf = DOMPDF::loadView($pathTemplate, $content)->setPaper('a4', 'portrait');
        $value_pdf = $pdf->download()->getOriginalContent();
        $name = 'lamb-academic/contratos/' . date('mdYHis') . uniqid() . '.pdf';
        $path2 = \Storage::cloud()->put($name, $value_pdf);
        $data_update_alumno = self::update_alumno_contrato($id_alumno_contrato, $name,$id_user);
        $data_insert_or_update_alumno = self::insertOrUpdateAdjunto($id_alumno_contrato, $name, $id_user); // insertar o actualizar el registro adjunto
        $doc = base64_encode($pdf->stream()->getOriginalContent());
        $jResponse['data'] = $doc;
        $jResponse['type'] = "create";
        $jResponse['value'] = $data_update_alumno['success'];
        return $jResponse;
    }

    static function validateTest($request){
        $response = array();
        $response['success'] = true;
        $response['message'] = "ok";

        $id_alumno_contrato = $request['id_alumno_contrato'];
        $content = $request['content'];
        $pathTemplate = $request['pathTemplate'];
        $datos_session = $request['datos_session'];
        $id_user = $request['id_user'];

        if (!isset($id_alumno_contrato)) {
            $response['success'] = false;
            $response['message'] = "No se encontro id_alumno_contrato";
            $jResponse['data']=[];
            return $response;
        }


        $pdf = DOMPDF::loadView($pathTemplate, $content)->setPaper('a4', 'portrait');
        /*$value_pdf = $pdf->download()->getOriginalContent();
        $name = 'lamb-academic/contratos/' . date('mdYHis') . uniqid() . '.pdf';
        $path2 = \Storage::cloud()->put($name, $value_pdf);
        $data_update_alumno = self::update_alumno_contrato($id_alumno_contrato, $name,$id_user);
        $data_insert_or_update_alumno = self::insertOrUpdateAdjunto($id_alumno_contrato, $name, $id_user); // insertar o actualizar el registro adjunto
        */$doc = base64_encode($pdf->stream()->getOriginalContent());
        $jResponse['data'] = $doc;
        $jResponse['type'] = "create";
        $jResponse['value'] = true;
        return $jResponse;
    }

    static function getArchivoAdjunto($id_alumno_contrato)
    {
        $adjunto = DB::table("david.acad_alumno_contrato_adjunto")->where("ID_ALUMNO_CONTRATO", $id_alumno_contrato)->value("adjunto");

        return $adjunto;
    }

    static function insertOrUpdateAdjunto($id_alumno_contrato, $path, $id_user){
        $response = array();
        $response['success'] = true;
        $response['message'] = "ok";
        //$auth = $_SESSION['auth'];
        try {
            DB::beginTransaction();

            $exist = DB::table("david.acad_alumno_contrato_adjunto")
            ->where("id_alumno_contrato", $id_alumno_contrato)
            ->first();

            if($exist){
                $response['data'] = DB::table("david.acad_alumno_contrato_adjunto")
                ->where("id_alumno_contrato", $id_alumno_contrato)
                ->update([
                    "adjunto" => $path,
                    "id_usuario_act" => $id_user,
                    "fecha_actualizacion" => Carbon::now(),
                ]);
            }else{
                $response['data'] = DB::table("david.acad_alumno_contrato_adjunto")->insert([
                    "id_alumno_contrato_adjunto" => null,
                    "id_alumno_contrato" => $id_alumno_contrato,
                    "adjunto" => $path,
                    "estado" => '1',
                    "id_usuario_reg" => $id_user,
                    "fecha_registro" => Carbon::now(),
                    "id_usuario_act" => null,
                    "fecha_actualizacion" => null
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
            $response['data'] = '';
            DB::rollBack();
        }
        return $response;
    }


    static function getarchivo($request)
    {
        $response = array();
        $archivo = DB::table("david.acad_alumno_contrato")->where("ID_ALUMNO_CONTRATO", $request)->value("ARCHIVO");

        return $archivo;
    }

    static function update_alumno_contrato($id_alumno_contrato, $ruta,$id_user)
    {
        $response = array();
        $response['success'] = true;
        $response['message'] = "ok";
        try {
            //code...
            DB::beginTransaction();
            $response['data'] = DB::table("david.acad_alumno_contrato")->where("ID_ALUMNO_CONTRATO", $id_alumno_contrato)->update([
                "ARCHIVO" => $ruta 
            ]);
            DB::commit();
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
            $response['data'] = '';
            DB::rollBack();
        }
        return $response;
    }


    static function df($url)
    {
        $ch = curl_init($url);
        $dir = '/tmp/';
        // the base name of file
        $url_format_link = strtok($url, '?');
        $file_name = basename($url_format_link);
        // Save file into file location
        $save_file_loc = $dir . $file_name;
        // $tmpName = tempnam(sys_get_temp_dir(), $file_name);
        $fp = fopen($save_file_loc, 'w');
        // Open file
        // It set an option for a cURL transfer
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // Perform a cURL session
        curl_exec($ch);
        // Closes a cURL session and frees all resources
        curl_close($ch);
        // Close file
        fclose($fp);
        return $save_file_loc;
    }
}
