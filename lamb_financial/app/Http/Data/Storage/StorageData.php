<?php
namespace App\Http\Data\Storage;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StorageData extends Controller
{
    //public  $storage;

    public function __construct()
    {
        //$this->torage = Storage::disk('minio-lamb');
    }

    public static function store(Request $request)
    {
        // print_r($request->file('file'));
        $message = 'ok';
        $data = null;

        $directory = $request->get('directory');
        $keep_file_name = $request->get('keep_file_name', false);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            if (is_array($file)) {
                $data = [];
                foreach ($file as $archive) {
                    array_push($data, self::saveFile($archive, $directory, $keep_file_name));
                }
                $message = 'Carga de archivos con éxito';
            } else {
                // print_r($file);
                $data = self::saveFile($file, $directory, $keep_file_name);
                $message = 'Carga de archivo con éxito';
            }
        }
        $ret = ['success' => true, 'message' => $message, 'data' => $data];
        return $ret;
    }

    // Local


    public static function destroyFile($fileName)
    {
        self::deleteFile($fileName);
        return [
            'message' => 'Se eliminó correctamente el archivo',
            'success' => true,
            'data' => null
        ];
    }

/*
    public  function saveFile($file, $directory = 'anonymous', $keep_file_name = false)
    {
        if ($keep_file_name) {
            return $this->storage->putFileAs($directory, $file, $file->getClientOriginalName());
        } else {
            return $this->storage->putFile($directory, $file);
        }
    }
    */
    public static function saveFile($file, $folder, $filename='')
    {
        if ($filename != null && $filename != "" && $filename != "null") {
            $status = Storage::disk('minio-lamb')->exists($folder.'/'.$filename);
            if ($status) {
                Storage::disk('minio-lamb')->delete($folder.'/'.$filename);
            }
        } else {
            $ext = $file->getClientOriginalExtension();
            $filename = uniqid() . '.' . $ext;
        }
        Storage::disk('minio-lamb')->putFileAs($folder, $file, $filename);
        return $filename;
    }

    public static function saveFilePdf($file, $folder, $filename='')
    {
        if ($filename != null && $filename != "" && $filename != "null") {
            $status = Storage::disk('minio-lamb')->exists($folder.'/'.$filename);
            if ($status) {
                Storage::disk('minio-lamb')->delete($folder.'/'.$filename);
            }
        } else {

            $filename = uniqid() . '.pdf';
        }
        Storage::disk('minio-lamb')->put($folder.'/'.$filename, $file);
        return $filename;
    }

    public static function deleteFile($fileName)
    {
        self::checkFileExists($fileName);
        Storage::disk('minio-lamb')->delete($fileName);
    }

    public static function checkFileExists($fileName)
    {
        $status = Storage::disk('minio-lamb')->exists($fileName);
        if (!$status) {
            $ret =['nerror'=>1, 'message'=>'Archivo no encontrado en el servidor de almacenamiento: '.$fileName];
            return $ret;
        }
        $ret =['nerror'=>0, 'message'=>'ok'];
        return $ret;
    }

    public static function getUrlFile($fileName)
    {
        $ret = self::checkFileExists($fileName);
        if($ret['nerror']==0){ 
            $data = Storage::disk('minio-lamb')->temporaryUrl($fileName, Carbon::now()->addHour(5));
            $ret =['nerror'=>0, 'message'=>'ok','data'=>$data];
        }
        return $ret;
    }

    public static function getUrlByName($fileName)
    {
        $ret = self::checkFileExists($fileName);
        if($ret['nerror']==0){ 
            $data = Storage::disk('minio-lamb')->temporaryUrl($fileName, Carbon::now()->addHour(15));
            $ret =['nerror'=>0, 'message'=>'ok','data'=>$data];
        }
        return $ret;

    }

    public static function responseFile($fileName)
    {
        $ret = self::checkFileExists($fileName);
        if($ret['nerror']==0){ 
            $data = Storage::disk('minio-lamb')->get($fileName);
            $ret =['nerror'=>0, 'message'=>'ok','data'=>$data];
        }
        return $ret;
    }


    public static function getFileShell($filename, $token) {
        
        $response=[];
        $response['success']=true;
        $response['message']="Items";

        
/*         $auth = $_SESSION['auth'];
        $session_token = $auth->token; */
        $url = env('API_LAMB_FINANCIAL_SHELL').'api/storage?fileName='.$filename; 

        $headers = array(
            'Authorization:'.$token.''
        );
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => $headers,
        ));

        $result = curl_exec($curl);
        if ($result === FALSE) {
            $response['success']=false;
            $response['message']="No se encontro el archivo";
        } else {
            $response = json_decode($result);
        }
        curl_close($curl);

        return $response;
    }

    public static function saveFileShell($file, $directory,$token) {
        $response=[];
        $response['success']=false;
        $response['message']="Se guardo el archivo";
        // $query = "SELECT * FROM ELISEO.USER_SESSION where TOKEN = $token";
        
        $resultQuery = DB::table('ELISEO.USERS_SESSION')
        ->select(
            DB::raw("REGEXP_REPLACE(TOKEN_OAUTH, '^[a-zA-Z]+ ', '') TOKEN_OAUTH")
        )->where('TOKEN','=', $token)->first();
        if (!empty($resultQuery)) {
            $tokenOAUTH = $resultQuery->token_oauth;

            $pathFile = $file->getPathname();
            $nameFile = $file->getClientOriginalName();
            // print($file->getClientMimeType());
            $extension = $file->getClientMimeType();
            //print($pathFile);
            //print(' ');
            //print($nameFile);
            $file_server_path = realpath($nameFile);
            $url = env('API_LAMB_FINANCIAL_SHELL').'api/storage'; 

/*             print(dirname(__FILE__));
            print('\n');
            print(basename(__FILE__)); */

            
            // print($pathFile);
            $post_data = array('directory' => $directory,
            'file'=> curl_file_create(dirname(__FILE__).'/DNI ULICES.pdf', $extension, 'file'));
    
            $headers = array(
                // 'Content-Type: application/x-www-form-urlencoded',
                'Authorization:'.$tokenOAUTH.''
            );
           



            $aPost = array(
                'directory' => $directory,
            );
            $stateUpload = true; 
            if ((version_compare(PHP_VERSION, '5.5') >= 0)) {
                $aPost['file'] = new \CURLFile($pathFile);
            } else {
            $stateUpload = false; 
                $aPost['file'] = "@ DNI ULICES.pdf";
            }
            

    
    
            $curl = curl_init();
    
            curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_BUFFERSIZE=>128,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SAFE_UPLOAD=> $stateUpload,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $aPost,
            CURLOPT_HTTPHEADER => $headers,
            ));
    
            $result = curl_exec($curl);
            if ($result === FALSE) {
                $response['success']=false;
                $response['message']="No se guardo el archivo";
            } else {
                $response = json_decode($result);
            }
    
            curl_close($curl);




        }else {
            $response['success']=false;
            $response['message']="Token Oauth no existe";
        }
        


        return $response;
    }


    public static function deleteFileShell($fileName, $token) {
        $response=[];
        $response['success']=true;
        $response['message']="Se elimino el archivo correctamente";
        // $query = "SELECT * FROM ELISEO.USER_SESSION where TOKEN = $token";
        
        $resultQuery = DB::table('ELISEO.USERS_SESSION')
        ->select(
            DB::raw("REGEXP_REPLACE(TOKEN_OAUTH, '^[a-zA-Z]+ ', '') TOKEN_OAUTH")
        )->where('TOKEN','=', $token)->first();
        
        if (!empty($resultQuery)) {
            $tokenOAUTH = $resultQuery->token_oauth;
            $url = env('API_LAMB_FINANCIAL_SHELL').'api/storage'; 
            $post_data = array('fileName'=> $fileName);
    
            $headers = array(
                'Authorization:'.$tokenOAUTH.''
            );
            $curl = curl_init();
    
            curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_POST=> true,
            CURLOPT_POSTFIELDS => http_build_query($post_data),
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER=>true,
            ));
    
            $result = curl_exec($curl);
            if ($result === FALSE) {
                $response['success']=false;
                $response['message']="No se elimino el archivo";
            } else {
                $response = json_decode($result);
            }
    
            curl_close($curl);
        }else {
            $response['success']=false;
            $response['message']="Token Oauth no existe";
        }
        


        return $response;
    }
}