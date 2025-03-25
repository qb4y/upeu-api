<?php

namespace App\Http\Controllers\Setup\Person;

use App\Http\Controllers\Controller;
use App\Http\Data\SetupData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use App\Http\Data\Setup\Registry\RegistryData;
use App\Http\Data\Setup\Registry\EditorData;
use Exception;

class RegistryController extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getPersona(Request $request)
    {
        $key = $request->query('key');
        $init = $request->query('init');
        $fin = $request->query('fin');
        $jResponse = [];
        $data = RegistryData::getPersona($key, $init, $fin);
        $jResponse['rpta'] = 'ok';
        $code = "200";
        $jResponse['data'] = $data;
        return response()->json($jResponse, $code);
    }

    public function getInfoPersonas(Request $request)
    {
        try {
            $jResponse = [];
            $per_page = $request->per_page;
            $data = RegistryData::getInfoPersonas($request, $per_page);
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = "success";
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = false;
                $jResponse['message'] = "No Data Found";
                $jResponse['data'] = [];
                $code = "202";
            }
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $code = "202";
        }
        return response()->json($jResponse, $code);
    }

    public function getComprobateReg(Request $request)
    {
        $key = $request->query('n_doc');
        $jResponse = RegistryData::getComprobateReg($key);
        return response()->json($jResponse);
    }

    public function getTipoDocumento()
    {
        $jResponse = [];
        $data = DB::table('moises.tipo_documento')
            ->select('id_tipodocumento', 'nombre', 'siglas')
            ->where('siglas', '=', 'DNI')
            ->orWhere('siglas', '=', 'CarEx')
            ->orWhere('siglas', '=', 'Pass')
            ->distinct()->get();
        $jResponse['rpta'] = 'ok';
        $code = "200";
        $jResponse['data'] = $data;
        return response()->json($jResponse, $code);
    }

    public function getEstadoCivil()
    {
        $jResponse = [];
        $data = DB::table('moises.tipo_estado_civil')
            ->select('id_tipoestadocivil', 'nombre')
            ->distinct()->get();
        $jResponse['rpta'] = 'ok';
        $code = "200";
        $jResponse['data'] = $data;
        return response()->json($jResponse, $code);
    }

    public function getTipoPais()
    {
        $jResponse = [];
        $data = DB::table('moises.tipo_pais')
            ->select('id_tipopais', 'nombre', 'iso_a3')
            ->orderBy('nombre', 'asc')
            ->distinct()->get();
        $jResponse['rpta'] = 'ok';
        $code = "200";
        $jResponse['data'] = $data;
        return response()->json($jResponse, $code);
    }

    public function registrarPersona(Request $request)
    {
        $jResponse = [];
        try {
            $response = RegistryData::registrarPersona($request);
            if ($response['success']) {
                $code = "200";
            } else {
                $code = "202";
            }
            $jResponse = $response;
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $code = "202";
        }
        return response()->json($jResponse, $code);
    }

    public function registrarAlumno(Request $request)
    {
        $jResponse = [];
        try {
            $response = RegistryData::registrarAlumno($request, $request->id_persona);
            if ($response['pass']) {
                $code = "200";
            } else {
                $code = "202";
            }
            $jResponse = $response;
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $code = "202";
        }
        return response()->json($jResponse, $code);
    }

    public function registrarDocente(Request $request)
    {
        $jResponse = [];
        try {
            $response = RegistryData::registrarDocente($request, $request->id_persona);
            if ($response['pass']) {
                $code = "200";
            } else {
                $code = "202";
            }
            $jResponse = $response;
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $code = "202";
        }
        return response()->json($jResponse, $code);
    }

    public function resetPassword(Request $request)
    {
        $jResponse = [];
        try {
            $response = RegistryData::resetPassword($request);
            if ($response['success']) {
                $code = "200";
            } else {
                $code = "202";
            }
            $jResponse = $response;
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $code = "202";
        }
        return response()->json($jResponse, $code);
    }


}
