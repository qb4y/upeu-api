<?php


namespace App\Http\Controllers\Storage;


use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Psr7\MimeType;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
{
    public $storage;
    public $storageAcademic;

    public function __construct()
    {
        $this->storage = Storage::disk('minio-lamb');
        $this->storageAcademic = Storage::disk('minio-academic');
    }

    public function store(Request $request)
    {
        // print_r($request->file('file'));
        $message = 'ok';
        $data = null;
        $this->validate($request, [
            'directory' => 'required',
            'filename_persists' => 'boolean',
            (is_array($request->file('file')) ? 'file.*' : 'file') => 'required|mimes:jpg,png,jpeg,pdf,xlsx,xls,xlsm,docx,txt,csv,xml,svg'
        ]);
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

        return response()->json(['success' => true, 'message' => $message, 'data' => $data], 200);
    }

    public function index(Request $request)
    {
        $this->validate($request, ['fileName' => 'required']);
        $fileName = $request->get('fileName');
        $download = $request->get('download');
        try {
            if ($download) {
                $content = self::responseFile($fileName);
                return response($content, 200)
                    ->header('Content-Type', MimeType::fromFilename($fileName));
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Url de archivo generado correctamente',
                    'data' => self::getUrlFile($fileName),
                    'env' => env('LAMB_ACADEMIC-MINIO_ENDPOINT')
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => env('LAMB_ACADEMIC-MINIO_ENDPOINT'),
                'env' => env('LAMB_ACADEMIC-MINIO_ENDPOINT'),
            ], 400);
        }
       
    }
    public function indexAcademic(Request $request)
    {
        $this->validate($request, ['fileName' => 'required']);
        $fileName = $request->get('fileName');
        $download = $request->get('download');
        try {
            if ($download) {
                $content = self::responseFileAcademic($fileName);
                return response($content, 200)
                    ->header('Content-Type', MimeType::fromFilename($fileName));
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Url de archivo generado correctamente',
                    'data' => self::getUrlFileAcademic($fileName),
                    'env' => env('LAMB_ACADEMIC-MINIO_ENDPOINT')
                ], 200);
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => env('LAMB_ACADEMIC-MINIO_ENDPOINT'),
                'env' => env('LAMB_ACADEMIC-MINIO_ENDPOINT'),
            ], 400);
        }
    }
    public function destroy(Request $request)
    {
        $this->validate($request, ['fileName' => 'required']);
        $fileName = $request->get('fileName');
        self::deleteFile($fileName);
        return response()->json([
            'message' => 'Se eliminó correctamente el archivo',
            'success' => true,
            'data' => null
        ], 200);
    }

    // Local

    public function postFile($file, $directory)
    {
        $message = 'ok';
        $data = null;
        if ($file) {
            $data = self::saveFile($file, $directory);
        }
        return ['success' => true, 'message' => $message, 'data' => $data];
    }

    public function destroyFile($fileName)
    {
        self::deleteFile($fileName);
        return [
            'message' => 'Se eliminó correctamente el archivo',
            'success' => true,
            'data' => null
        ];
    }


    function saveFile($file, $directory = 'anonymous', $keep_file_name = false)
    {
        if ($keep_file_name) {
            return $this->storage->putFileAs($directory, $file, $file->getClientOriginalName());
        } else {
            return $this->storage->putFile($directory, $file);
        }
    }

    function deleteFile($fileName)
    {
        self::checkFileExists($fileName);
        $this->storage->delete($fileName);
    }

    function checkFileExists($fileName)
    {   
        $status = $this->storage->exists($fileName);
        if (!$status) {
            abort(500, 'Archivo no encontrado en el servidor de almacenamiento');
        }
    }
    function checkFileExistsAcademic($fileName)
    {   
        $status = $this->storageAcademic->exists($fileName);
        if (!$status) {
            abort(500, 'Archivo no encontrado en el servidor de almacenamiento');
        }
    }
    function getUrlFile($fileName)
    {

        self::checkFileExists($fileName);
        return $this->storage->temporaryUrl($fileName, Carbon::now()->addHour(5));
    }
    function getUrlFileAcademic($fileName)
    {

        self::checkFileExistsAcademic($fileName);
        return $this->storageAcademic->temporaryUrl($fileName, Carbon::now()->addHour(5));
    }
    function getUrlByName($fileName)
    {
        self::checkFileExists($fileName);
        return $this->storage->temporaryUrl($fileName, Carbon::now()->addHour(15));
    }

    function responseFile($fileName)
    {
        self::checkFileExists($fileName);
        return $this->storage->get($fileName);
    }
    function responseFileAcademic($fileName)
    {
        self::checkFileExists($fileName);
        return $this->storageAcademic->get($fileName);
    }
}
