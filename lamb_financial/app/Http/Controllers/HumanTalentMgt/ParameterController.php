<?php

namespace App\Http\Controllers\HumanTalentMgt;

use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Budget\ConfiguracionData;
use App\Http\Data\HumanTalentMgt\ParameterData;
use Illuminate\Http\Request;
use App\Http\Data\GlobalMethods;
use App\Http\Data\HumanTalentMgt\RequestData;
use Session;
use Carbon\Carbon;

class ParameterController extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getYearParameter()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::getYearParameter();
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listMyEntities()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::listMyEntities($id_user, $id_entidad);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function SearchGlobalGroupScale(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $searchs = $request->query('search');
            if ($searchs) {
                $data = ParameterData::SearchGlobalGroupScale($searchs);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function Level(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            $data = ParameterData::Level();

            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function SearchGlobalPositions(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ParameterData::SearchGlobalPositions($request);

            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function Grade(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            $data = ParameterData::Grade();
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function SearchListDepartaments(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        // $id_entidad = $jResponse["id_entidad"];
        $id_entidad = $request->query('id_entidad');
        $id_depto = $request->query('id_depto');
        $department = $request->query('department');
        $id_persona = $jResponse["id_user"];
        $date = Carbon::now();
        $id_anho = $date->format('Y');
        $gth = 'S';
        if ($request->has('gth')) {
            $gth = $request->query('gth');
        }
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ParameterData::SearchListDepartaments($id_entidad, $id_depto, $department, $id_persona, $id_anho, $gth);

            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function SubGrad(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            $data = ParameterData::SubGrad();
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listDeptoEntidad(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_entidad = $request->query('id_entidad');
            if ($id_entidad) {
                $data = ParameterData::listDeptoEntidad($id_entidad, $id_depto);
            } else {
                $data = null;
            }

            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getYearSalaryScale(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_entidad = $request->query('id_entidad');
            if ($id_entidad) {
                $data = ParameterData::getYearSalaryScale($id_entidad);
            } else {
                $data = null;
            }

            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function SearchCargoProfilePositions(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];

            // if ($id_entidad && $id_depto && $name) {
            $data = ParameterData::SearchCargoProfilePositions($request);
            // } else {
            //    $data = null;
            // }

            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getYearStaffPositions()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::getYearStaffPositions();
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getLevelIntelligence()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $intelegence = ParameterData::getLevelIntelligence();
                $lenguages = ParameterData::getTypeLenguages();
                $ofimatica = ParameterData::getOfimatica();
                $jResponse['success'] = true;
                if (count($intelegence) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['intelegence' => $intelegence, 'lenguages' => $lenguages, 'ofimatica' => $ofimatica];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listFormations()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $situacionEduc = ParameterData::getSituationEducation();
                $tipoESPDIP = ParameterData::getTypeEspdip();
                if (!empty($situacionEduc)) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = [
                        'situacionEduc' => $situacionEduc,
                        'tipoESPDIP' => $tipoESPDIP,
                    ];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function searchProfesionOcupation(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $searchs = $request->query('search');
            if ($searchs) {
                $data = ParameterData::searchProfesionOcupation($searchs);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getCarrera()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::getCarrera();
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getRequirements()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::getRequirements();
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getPeriodoHolidays(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ParameterData::getPeriodoHolidays($request);
            if (count($data) > 0) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }


    // get fundamental data 
    public function getDataP1()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $sexo           = ParameterData::getSexo();
                $typeDocument   = ParameterData::getTypeDocument();
                $civilStatus    = ParameterData::getCivilStatus();
                $sangre         = ParameterData::getSangre();
                $direccion      = ParameterData::getTypeDirecction();
                $zone           = ParameterData::getZone();
                $via            = ParameterData::getVia();
                $depto          = ParameterData::getDepto();
                $seccion        = ParameterData::getSeccion();
                $religion       = ParameterData::getTypeReligion();
                $autoridad      = ParameterData::getTypeAutoridad();
                if (!empty($sexo)) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = [
                        'sexo'          => $sexo,
                        'typeDocument'  => $typeDocument,
                        'civilStatus'   => $civilStatus,
                        'sangre'        => $sangre,
                        'direccion'     => $direccion,
                        'zone'          => $zone,
                        'via'           => $via,
                        'depto'         => $depto,
                        'seccion'       => $seccion,
                        'religion'      => $religion,
                        'autoridad'     => $autoridad
                    ];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    // buscador de pais
    public function SearchPais(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $searchs = $request->query('search');
            if ($searchs) {
                $data = ParameterData::SearchPais($searchs);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    // traer provincia de select anidado
    public function getProv(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_depto = $request->query('id_depto');
            // dd($id_depto);
            if ($id_depto) {
                $data = ParameterData::getProv($id_depto);
            } else {
                $data = null;
            }

            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    // traer provincia de select anidado
    public function getDistri(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_distrito = $request->query('id_distrito');
            $id_depto = $request->query('id_depto');

            // dd($id_depto);
            if ($id_distrito) {
                $data = ParameterData::getDistri($id_depto, $id_distrito);
            } else {
                $data = null;
            }

            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listCompetences(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            $tipo = $request->query('tipo');
            $agregados = $request->query('agregados');
            try {
                $data = ParameterData::listCompetences($tipo, $agregados);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getTypeLevelCompetences()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $typeLevelComp = ParameterData::getTypeLevelCompetences();
                $jResponse['success'] = true;
                if (count($typeLevelComp) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['typeLevelComp' => $typeLevelComp];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listGrupoEscala(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_entidad = $request->query('id_entidad');
            $id_depto = $request->query('id_depto');
            $id_anho = $request->query('id_anho');
            $id_nivel = $request->query('id_nivel');
            try {
                $data = ParameterData::listGrupoEscala($id_entidad, $id_depto, $id_anho, $id_nivel);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    // get fundamental data P2
    public function getDataP2()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $tipo_instituciones  = ParameterData::getTypeInstitucion();
                $situacion           = ParameterData::getSituacion();
                $regimen_institucion = ParameterData::getTypeRegimen();
                $languages           = ParameterData::getTypeLenguages();
                //$religion            = ParameterData::getTypeReligion();
                //$autoridad           = ParameterData::getTypeAutoridad();
                //$ctaBanco            = ParameterData::getCtaBanco();
                /*
                'religion'             => $religion,
                'autoridad'            => $autoridad,
                'ctaBanco'             => $ctaBanco,
                */
                if (!empty($tipo_instituciones)) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = [
                        'tipo_instituciones'   => $tipo_instituciones,
                        'regimen_institucion'  => $regimen_institucion,
                        'situacion'            => $situacion,
                        'languages'            => $languages
                    ];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    // buscador de institucion

    public function SearchInstitucion(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $regimen = $request->query('regimen');
            $tipo_ins = $request->query('tipo_ins');
            $name = $request->query('name');
            $data = ParameterData::SearchInstitucion($name, $regimen, $tipo_ins);
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    /// buscar carrera
    public function SearchCarrera(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $searchs = $request->query('search');
            if ($searchs) {
                $data = ParameterData::SearchCarrera($searchs);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listCompetencias()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::listCompetencias();
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listCompetenciasLb(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $per_page = $request->per_page;
                $nombre = $request->nombre;
                // dd('SSS')
                $data = ParameterData::listCompetenciasLb($nombre, $per_page);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getGroupCompetences(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            $id_tipo = $request->tipo;
            try {
                $data = ParameterData::getGroupCompetences($id_tipo);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    /// buscar Persona
    public function SearchPersona(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $searchs = $request->query('search');
            $sexo    = $request->query('sexo');
            if ($searchs) {
                $data = ParameterData::SearchPersona($searchs, $sexo);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function SearchPersonaNoWorker(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $searchs = $request->query('search');
            $sexo    = $request->query('sexo');
            if ($searchs) {
                $data = ParameterData::SearchPersonaNoWorker($searchs, $sexo);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    // get fundamental data P3
    public function getDataP3()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $vinculo        = ParameterData::getVinculoPersona();
                $docSusVf       = ParameterData::getDocSusVf();
                $motivoBaja     = ParameterData::getMotivoBaja();
                $discapacidad   = ParameterData::getDiscapacidad();
                if (!empty($vinculo)) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = [
                        'vinculo'           => $vinculo,
                        'docSusVf'          => $docSusVf,
                        'motivoBaja'        => $motivoBaja,
                        'discapacidad'      => $discapacidad,
                    ];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    /// buscar comisiones
    public function searchComitions(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $search = $request->query('search');
            if ($search) {
                $data = ParameterData::searchComitions($search);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    /// buscar procesos
    public function searchProcess(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $search = $request->query('search');
            if ($search) {
                $data = ParameterData::searchProcess($search);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function salaryScaleLevel($id_perfil_puesto)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {

                $data = ParameterData::salaryScaleLevel($id_perfil_puesto);
                //    dd('sss', $data);
                if (!empty($data)) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['object' => $data];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getEstadoPeriodoVac()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::getEstadoPeriodoVac();
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    // get fundamental data P4
    public function getDataP4()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $periodoRemu    = ParameterData::getPeriodoRemuneracion();
                $ctaBanco       = ParameterData::getCtaBanco();
                $discapacidad   = ParameterData::getDiscapacidad();
                $tipoComision   = ParameterData::getTypeComision();
                if (!empty($ctaBanco)) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = [
                        'tipoComision'  => $tipoComision,
                        'periodoRemu'   => $periodoRemu,
                        'ctaBanco'      => $ctaBanco,
                        'discapacidad'  => $discapacidad,
                    ];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getDataRegPerson()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $sexo           = ParameterData::getSexo();
                $typeDocument   = ParameterData::getTypeDocument();
                $pais           = ConfiguracionData::listTipoPais();
                $situaciones    = ParameterData::getSituationEducation();
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
                $jResponse['data'] = [
                    'sexos'       => $sexo,
                    'tipo_docs'   => $typeDocument,
                    'paises'      => $pais,
                    'situaciones' => $situaciones
                ];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getTipoSuspension()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::getTipoSuspension();
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getControlsPersons()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::getControlsPersons();
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function SearchTrabajador(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        // $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $searchs = $request->query('search');
            $id_sedearea = $request->query('id_sedearea');
            $id_entidad = $request->query('id_entidad');
            if ($id_sedearea and $searchs and $id_entidad) {
                $data = ParameterData::SearchTrabajador($id_sedearea,  $id_entidad, $searchs);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getTypeConcept(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        // $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];


            $data = ParameterData::getTypeConcept();


            if (count($data) > 0) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getTypeSunatConcept(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        // $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_tipo_concepto = $request->query('id_tipo_concepto');


            $data = ParameterData::getTypeSunatConcept($id_tipo_concepto);

            if (count($data) > 0) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getTypeApsConcept(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        // $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];


            $data = ParameterData::getTypeApsConcept();

            if (count($data) > 0) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getEstadoLicaPer()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::getEstadoLicaPer();
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function SearchGlobalPositionsFiltrado(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_sedearea = $request->query('id_sedearea');
            $searchs = $request->query('search');
            if ($searchs) {
                $data = ParameterData::SearchGlobalPositionsFiltrado($id_sedearea, $searchs);
            } else {
                $data = null;
            }

            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getTypePayroll(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_entidad = $request->query('id_entidad');
            $id_grupo_planilla = $request->query('id_grupo_planilla');
            $data = ParameterData::getTypePayroll($id_entidad, $id_grupo_planilla);


            if (count($data) > 0) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getGroupPayroll(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];

            $data = ParameterData::getGroupPayroll();


            if (count($data) > 0) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function SearchTrabajadorOverTime(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        // $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $searchs = $request->query('search');
            $id_sedearea = $request->query('id_sedearea');
            $id_entidad = $request->query('id_entidad');
            if ($id_sedearea and $searchs and $id_entidad) {
                $data = ParameterData::SearchTrabajadorOverTime($id_sedearea,  $id_entidad, $searchs);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
            // }
        }
        return response()->json($jResponse, $code);
    }
    public function getEstadoOvertime()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::getEstadoOvertime();
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getMonths()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::getMonths();
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getMonthlyPaymentType(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_entidad = $request->id_entidad;
                $data = ParameterData::getMonthlyPaymentType($id_entidad);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getYearAssignMonth(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_entidad = $request->id_entidad;
                $data = ParameterData::getYearAssignMonth($id_entidad);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getMileageType(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_entidad = $request->id_entidad;
                $data = ParameterData::getMileageType($id_entidad);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getDays(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_tipo_horario = $request->id_tipo_horario;
                $data = ParameterData::getDays($id_tipo_horario);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getTypeShedule(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_entidad = $request->id_entidad;
                $id_depto = $request->id_depto;
                if ($id_entidad and $id_depto) {
                    $data = ParameterData::getTypeShedule($id_entidad, $id_depto);
                } else {
                    $data = null;
                }
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getYearAssistance(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            $id_entidad = $request->id_entidad;
            try {
                $data = ParameterData::getYearAssistance($id_entidad);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function searchConceptPlla(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $search = $request->query('search');
            if ($search) {
                $data = ParameterData::searchConceptPlla($search);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The search is succesfull';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getEstadoPlanilla()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::getEstadoPlanilla();
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getPlanillaEntidad(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            $id_entidad = $request->id_entidad;
            try {
                $data = ParameterData::getPlanillaEntidad($id_entidad);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getTypePeriodBS(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            $tipo = $request->tipo;
            try {
                $data = ParameterData::getTypePeriodBS($tipo);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function searchTrabajadorHolidays(Request $request)
    {
        // dd();
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        // $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $datos = $request->query('datos');

                if ($datos) {
                    $data = ParameterData::searchTrabajadorHolidays($datos);
                } else {
                    $data = null;
                }
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listMyPeriodosAsig(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_persona = $request->id_persona;
            try {
                $data = ParameterData::listMyPeriodosAsig($id_persona);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getPeriodosSinAsig(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_entidad = $request->id_entidad;
            $id_persona = $request->id_persona;
            try {
                $data = ParameterData::getPeriodosSinAsig($id_entidad, $id_persona);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function searchsTrabajadorAprobe(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::searchsTrabajadorAprobe($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function trabajadoresPuesto(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::trabajadoresPuesto($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getFirmaTrabajador(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_persona = $request->id_persona;
                $object = ParameterData::getFirmaTrabajador($id_persona);

                if ($object['id_persona'] and $object['nombre_firma'] and  $object['urls_dw']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $object;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Usted no registro su firma, porfavor comuniquese con su responsable";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getRequestPersonalData()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $mot_req = ParameterData::getMotivoRequerimiento();
                $cod_adm = ParameterData::getCodigoAdmision();
                $mod_sel = ParameterData::getModalidadSeleccion();
                $jor_lab = ParameterData::getTipoTiempoTrabajo();
                $tip_sex = ParameterData::getSexo();
                $con_lab = ParameterData::getCondicionLaboral();
                $est_req = ParameterData::getEstadoReq();
                $org_sed = ParameterData::getSedes();
                $sit_edu = ParameterData::getSituationEducation();
                $tip_hor = ParameterData::getAllTypeShedule();
                $jResponse['success'] = true;
                //if (count($data) > 0) {
                $jResponse['message'] = "Success";
                $jResponse['data'] = [
                    'mot_req' => $mot_req,
                    'cod_adm' => $cod_adm,
                    'mod_sel' => $mod_sel,
                    'jor_lab' => $jor_lab,
                    'tip_sex' => $tip_sex,
                    'est_req' => $est_req,
                    'con_lab' => $con_lab,
                    'org_sed' => $org_sed,
                    'sit_edu' => $sit_edu,
                    'tip_hor' => $tip_hor
                ];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getPerfilPuestobyArea(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::getPerfilPuestobyArea($request->id_sedearea);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getAreaRequest(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $searchs = $request->query('search');
            $id_entidad    = $request->query('id_entidad');
            $id_depto    = $request->query('id_depto');
            $id_sede    = $request->query('id_sede');
            if ($searchs) {
                $data = ParameterData::SearchListDepartaments($id_entidad, $id_depto, $searchs);
            } else {
                $data = null;
            }
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getCCosto(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ParameterData::getCCosto($request);
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getNivelModalidad(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ParameterData::getNivelModalidad($request);
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getFistScaleSalatyTeacher()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $sit_edu = ParameterData::getSituationEducation();
                $exp_pro = ParameterData::getExpProf();
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
                $jResponse['data'] = [
                    'sit_edu' => $sit_edu,
                    'exp_pro' => $exp_pro
                ];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getNivModInfo(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $niv_ens = ParameterData::getNivelEnsenanza($request);
                $mod = ParameterData::getModalidad($request);
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
                $jResponse['data'] = [
                    'niv_ens' => $niv_ens,
                    'mod' => $mod
                ];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getInfoPersonaCBH(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ParameterData::getInfoPersonaCBH($request);
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getInfoCostoHour(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ParameterData::getExpProf($request);
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getTipoContrato()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ParameterData::getTipoContrato();
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getAreaParents(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = ParameterData::getAreaParents($request);
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function searchOcupacion(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $key = $request->key;
            $data = ParameterData::searchOcupacion($key);
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getDataWorkerActive()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $jor_lab = ParameterData::getTipoTiempoTrabajo();
                $con_lab = ParameterData::getCondicionLaboral();
                $tip_reg = ParameterData::getTipoRegimenLaboral();
                $tip_ctr = ParameterData::getTipoSCTRPension();
                $sit_tra = ParameterData::getSituacionTrabajador();
                $tip_pag = ParameterData::getTipoPago();
                $tip_cat = ParameterData::getTipoCategoriaOcupa();
                $tip_ses = ParameterData::getSituacionEspecial();
                $tip_dtr = ParameterData::getTipoDobleTrib();
                $tip_con = ParameterData::getTipoContrato();
                $per_rem = ParameterData::getPerRemuneracion();
                $tip_sta = ParameterData::getTipoStatus();
                $gru_pla = ParameterData::getGrupoPlanilla();
                $jResponse['success'] = true;
                $jResponse['message'] = "Success";
                $jResponse['data'] = [
                    'jor_lab' => $jor_lab,
                    'con_lab' => $con_lab,
                    'tip_reg' => $tip_reg,
                    'tip_ctr' => $tip_ctr,
                    'sit_tra' => $sit_tra,
                    'tip_pag' => $tip_pag,
                    'tip_cat' => $tip_cat,
                    'tip_ses' => $tip_ses,
                    'tip_dtr' => $tip_dtr,
                    'tip_con' => $tip_con,
                    'per_rem' => $per_rem,
                    'tip_sta' => $tip_sta,
                    'gru_pla' => $gru_pla
                ];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getScaleSalary(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_entidad = $request->id_entidad;
            $id_depto = $request->id_depto;
            $id_perfil_puesto = $request->id_perfil_puesto;
            $data = RequestData::getScaleSalary($id_perfil_puesto, $id_entidad, $id_depto);
            $fmr = RequestData::getFMR($id_entidad);
            if ($data) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = [
                    'scale' => $data,
                    'fmr' => $fmr
                ];
                $code = "200";
            } else {
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getTipoEstadoVacTrab()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = ParameterData::getTipoEstadoVacTrab();
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getEstadoSolicitud()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = ParameterData::getEstadoSolicitud();
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getPeriodoAprobe(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data = ParameterData::getPeriodoAprobe($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listMyEntityAccess(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::listMyEntityAccess($id_user, $id_entidad, $request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listMyDeptoAccess(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::listMyDeptoAccess($request, $id_depto);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function searchMyListAreasAccess(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_persona = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {

                $data = ParameterData::searchMyListAreasAccess($request, $id_persona);

                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function getAccesoNivel(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $object = ParameterData::getAccesoNivel($request, $id_user);
                if ($object) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $object;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = '';
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = '';
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listArbolProfilePosition(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::listArbolProfilePosition($request);
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                } else {
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function searchPersonaAccess(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::searchPersonaAccess($request, $id_user);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function moduleFather(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::moduleFather($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function tipoNivelVista()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::tipoNivelVista();
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function tipoNivelArea()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::tipoNivelArea();
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function entidadDeprtamentoAreaPersona(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::entidadDeprtamentoAreaPersona($request);
                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function searchAreas(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = ParameterData::searchAreas($request);

                if (count($data) > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
}
