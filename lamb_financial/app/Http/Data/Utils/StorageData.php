<?php

namespace App\Http\Data\Utils;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class StorageData
{
    public static function saveFileMinio($directory, $file, $use_name) /// Se usa en varios lugares
    {
        // $request->validate([
        //     'directory' => 'required',
        //     'filename_persists' => 'boolean',
        //     // (is_array($request->file('file')) ? 'file.*' : 'file') => 'required|mimes:jpg,png,jpeg,pdf,xlsx,xls,xlsm,docx,txt,csv,xml,svg'
        // ]);
        // $directory = $request->directory;
        // $file = $request->file('file');

        // $use_name = false; // si es true se guarda en el minio con el nombre original y si es false se guarda con un nombre encriptado
        $result = '';
        $storage = Storage::disk('minio-lamb');

        if (!empty($file)) {
            
            if (!empty($use_name)) {
                $result = $storage->putFileAs($directory, $file, $file->getClientOriginalName());
            } else {
                $result = $storage->putFile($directory, $file);
            }

            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Archivo registrado en la nube',
                    'data' => $result
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No  se pudo registrar archivo',
                    'data' => $result
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'No existe el archivo',
                'data' => $result
            ];
        }
        return $response;
    }
    public static function deleteFileMinio($directoryFileName)
    {
        $storage = Storage::disk('minio-lamb');
        
        $status = $storage->exists($directoryFileName);

        if ($status) {
            $deleteFile = $storage->delete($directoryFileName);

            if ($deleteFile) {
                $response = [
                    'success' => true,
                    'message' => 'Archivo eliminado en la nube',
                    'data' => $deleteFile
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No  se pudo eliminar archivo',
                    'data' => $deleteFile
                ];
            }
        } else {
            $response = [
                'success' => true,
                'message' => 'No  existe el archivo',
                'data' => $status
            ];
        }
        return $response;
    }
    public static function viewFileMinio($directoryFileName)
    {
        $storage = Storage::disk('minio-lamb');

        $status = $storage->exists($directoryFileName);

        if ($status) {
            $file = $storage->temporaryUrl($directoryFileName, Carbon::now()->addHour(10));
            if ($file) {
                $response = [
                    'success' => true,
                    'message' => 'Completado',
                    'data' => $file
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No  se pudo obtener archivo',
                    'data' => $file
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'No  existe el archivo',
                'data' => $status
            ];
        }
        return $response;
    }
    public static function downloadFileMinio($directoryFileName)
    {
         $storage = Storage::disk('minio-lamb');

        $status = $storage->exists($directoryFileName);

        if ($status) {
            $file = $storage->download($directoryFileName);
            if ($file) {
                $response = [
                    'success' => true,
                    'message' => 'Completado',
                    'data' => $file
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No  se pudo obtener archivo',
                    'data' => $file
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'No  existe el archivo',
                'data' => $status
            ];
        }
        return $response;
    }
    public static function saveFileMinioPdfLocal($directory, $file) /// Se usa en varios lugares, se usa cuando generas el pdf en el back y guardas en el minio
    {
        $result = '';
        $storage = Storage::disk('minio-lamb');
        if (!empty($file)) {
            $result = $storage->put($directory, $file);
            // dd($result);
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Archivo registrado en la nube',
                    'data' => $result
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No  se pudo registrar archivo',
                    'data' => $result
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'No existe el archivo',
                'data' => $result
            ];
        }
        return $response;
    }

}
