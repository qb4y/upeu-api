<?php

/**
 * Created by PhpStorm.
 * User: raul
 * Date: 4/3/19
 * Time: 6:30 PM
 */

namespace App\Http\Controllers\Setup\Organization;

use App\Http\Data\GlobalMethods;
use App\Http\Data\Setup\Organization\NivelGestionData;
use App\Http\Data\Setup\Organization\SedeData;
use App\Http\Data\Setup\OrganizationData;
use App\Http\Data\Setup\PersonData;
use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use PDO;
use PhpParser\Node\Expr\Cast\Object_;

class OrganizationController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getInfoOrganization(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = OrganizationData::getInfoOrganization($request);
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

    public function listOrganization()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                //                $data = OrganizationController::recursiveOrganization(1,0);
                //                dd('hola');
                $idArea = OrganizationData::getAreaParentEntidad($id_entidad);
                //                dd($idArea->id_area);

                if ($idArea) {
                    $data = OrganizationController::recursiveOrganization($idArea->id_area, 1, $id_entidad);
                } else {
                    $data = [];
                }
                $jResponse['success'] = true;
                if (count($data) > 0) {
                    $jResponse['message'] = "Succes";
                    $jResponse['data'] = $data;
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
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function recursiveOrganization($id_area, $x, $id_entidad)
    {
        $parent = [];
        if ($x == 1) {
            $data = OrganizationData::listOrganization($id_area, $id_entidad);
            //            dd($x, $data);
        } else {
            $data = OrganizationData::listChildrenOrganization($id_area, $id_entidad);
        }
        $x++;
        foreach ($data as $key => $value) {
            $row = $this->recursiveOrganization($value->id_area, $x, $id_entidad);

            $parent[] = [
                'id_area' => $value->id_area,
                'id_parent' => $value->id_parent,
                'estado' => $value->estado,
                'nombre' => $value->nombre,
                'orden' => $value->orden,
                'tipo_area' => $value->tipo_area,
                'tipo_area_codigo' => $value->tipo_area_codigo,
                'num_child' => (int)$value->num_child,
                'num_dep' => (int)$value->num_dep,
                'num_respon' => (int)$value->num_respon,
                'children' => $row
            ];
            //            dd($value);
        }
        return $parent;
    }

    public function createOrganization()
    {
        //        dd('hola');
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        if ($valida == 'SI') {

            //            dd(Input::get("data"));
            $jResponse = [];
            $nombre = Input::get("nombre");
            $estado = Input::get("estado");
            $id_parent = Input::get("id_parent");
            $id_tipoarea = Input::get("id_tipoarea");
            $nivelhijo = Input::get("nivelhijo");
            $gth = Input::get("gth");
            $codigo = Input::get("codigo");

            try {
                //                dd('hola43');
                $idOrganisacion = OrganizationData::getMax('ORG_AREA', 'ID_AREA');
                $data = ['id_entidad' => $id_entidad, 
                    'id_area' => $idOrganisacion, 
                    'id_tipoarea' => $id_tipoarea, 
                    'id_parent' => $id_parent, 
                    'nombre' => $nombre, 
                    'estado' => $estado, 
                    'nivelhijo' => $nivelhijo, 
                    'gth' => $gth,
                    'codigo' => $codigo
                ];
                $result = OrganizationData::addOrganization($data);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = False;
                    $jResponse['message'] = 'Area is not asigned';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                //                $jResponse['params'] = $params;
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function showOrganization($id_org)
    {
        //        dd($id_org);
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = OrganizationData::getOrganization($id_org);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item is exist";
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function updateOrganization($id_org)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $nombre = Input::get("nombre");
            $estado = Input::get("estado");
            $id_parent = Input::get("id_parent");
            $id_tipoarea = Input::get("id_tipoarea");
            $nivelhijo = Input::get("nivelhijo");
            $gth = Input::get("gth");
            $codigo = Input::get("codigo");
            try {
                //                $data = ['id_area'=>$id_org,'id_tipoarea'=>$id_tipoarea,'id_parent'=>$id_parent,'nombre'=>$nombre,'estado'=>$estado];
                $data = OrganizationData::updateOrganization($id_org, $id_parent, $id_tipoarea, $estado, $nombre, $nivelhijo, $gth,$codigo);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item is exist";
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function deleteOrganization($id_org)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = OrganizationData::deleteOrganizations($id_org);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function listTypeOrganization()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $actions = OrganizationData::listTypeArea();
            if ($actions) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $actions;
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

    public function listSede()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $actions = SedeData::listSede();
            if ($actions) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $actions;
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

    public function listNivelGestion()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $actions = NivelGestionData::listNivelGestion();
            if ($actions) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $actions;
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

    public function searchDepartment(Request $request)
    {

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $txtSearch = $request->query('text_search');
        //        dd($txtSearch);
        if ($valida == 'SI') {
            $jResponse = [];
            if ($txtSearch) {
                // dd($id_entidad);
                $data = OrganizationData::findDepartmentByName($txtSearch, $id_entidad);
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

    public function searchPeople(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $txtSearch = $request->query('text_search');
        if ($valida == 'SI') {
            $jResponse = [];
            //            dd($txtSearch);
            if ($txtSearch) {
                $data = OrganizationData::findPeople($txtSearch, $id_entidad);
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
    /*public function recursiveOrganization($id_modulo,$x){
        $parent = [];
        if($x == 1){
            $data = ModuloData::showModules($id_modulo);
        }else{
            $data = ModuloData::listModulesChildrens($id_modulo);
        }
        $x++;
        foreach ($data as $key => $value){
            $row = $this->recursiveModules($value->id_modulo,$x);
            $parent[] = ['id_modulo' => $value->id_modulo,
                'id_padre' => $value->id_padre,
                'nivel' => $value->nivel,
                'nombre' => $value->nombre,
                'url' => $value->url,
                'imagen' => $value->imagen,
                'orden' => $value->orden,
                'estado' => $value->estado,
                'es_activo' => $value->es_activo,
                'children'=>$row];
        }
        return $parent;
    }*/
    /***
     * AreasPedidos
     */
    public function listAreasOrders(Request $request)
    {

        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $dato = $request->query('dato');
            try {
                $data = OrganizationData::listAreasOrders($id_entidad, $id_depto);
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
    public function listAreasOrdersTo(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        // $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $searchs = $request->query('search');
            try {
                
                $data = OrganizationData::listAreasOrdersTo($id_entidad, $id_depto, $searchs);
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

    public function CreateOrUpdateAreaOrder()

    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_sedearea = Input::get("id_sedearea");
            $activo = Input::get("activo");
            $id_formulario = Input::get("id_formulario");
            try {
                // validate if sedearea have any almacen
                $inventory = OrganizationData::getInventorySedeArea($id_sedearea);
                $data = ['id_sedearea' => $id_sedearea, 'fecha' => date('Y-m-d H:i:s')];
                if ($id_formulario) {
                    $data['id_formulario'] = $id_formulario;
                }
                if ($activo === '0' or $activo === '1') {
                    $data['activo'] = $activo;
                    if ($activo === '0') {
                        $data['id_formulario'] = '';
                    }
                }
                //                dd($data);
                if ($activo === '0') {
                    $result = OrganizationData::deleteAreaOrder($id_sedearea);
                    // if (!$result) {
                    //     $jResponse['success'] = False;
                    //     $jResponse['message'] = 'La operación no tuvo éxito.';
                    //     $jResponse['data'] = [];
                    //     $code = "202";
                    //     goto end;
                    // } 
                } else {

                    // $id_entidad = (int) $id_entidad;
                    if ($inventory->isEmpty() && ($id_entidad === '7124' || $id_entidad === '9415')) {
                        $jResponse['success'] = False;
                        $jResponse['message'] = 'Alto! El área no tiene un almacén de inventarios.';
                        $jResponse['data'] = [];
                        $code = "202";
                        goto end;
                    }
                    $result = OrganizationData::addOrUpdateAreaOrder($data);
                    if (!$result) {
                        $jResponse['success'] = False;
                        $jResponse['message'] = 'La operación no tuvo éxito.';
                        $jResponse['data'] = [];
                        $code = "202";
                        // goto end;
                    }
                }
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $result;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }

    public function listAreas(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $dato = $request->query('dato');
            try {
                $data = OrganizationData::listAreas($id_entidad);
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

    public function misAreas(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $dato = $request->query('dato');
            try {


                $data = OrganizationData::getArea($id_entidad, $id_user);

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
    public function listAreaSedeArea(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];

        $id_departamento = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        $id_persona = $request->query('id_persona');

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                //                $idArea = OrganizationController::getAreaParentSedeArea($id_entidad);
                /*if ($idArea) {
                    $data = OrganizationController::recursiveDeptoSedeArea($idArea->id_area, 1, $id_entidad, $id_persona, $id_departamento);
                } else {
                    $data = [];
                }*/
                $data = OrganizationData::listSedeAreaPersona($id_entidad, $id_persona);
                $jResponse['success'] = true;
                //                dd($data);
                if (count($data) > 0) {
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = $data;
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
                $code = "500";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }
    public function recursiveDeptoSedeArea($id_area, $x, $id_entidad, $idPersona, $id_departamento)
    { //sdfsd.sdf.d .s sfg .sdf sdfg .s fgs d.s dfgsdfg .
        $parent = [];
        if ($x === 1) {
            $data = OrganizationData::listAreaSedeArea($id_area, $id_entidad, '1', $idPersona, $id_departamento);
        } else {
            $data = OrganizationData::listAreaSedeArea($id_area, $id_entidad, null, $idPersona, $id_departamento);
        }
        $x++;
        $item_checked = false;
        foreach ($data as $key => $value) {
            $row = $this->recursiveDeptoSedeArea($value->id_area, $x, $id_entidad, $idPersona, $id_departamento);
            if ($value->selected and $value->selected == '1') {
                $item_checked = true;
            } else {
                $item_checked = false;
            }
            $parent[] = [
                'id_area' => $value->id_area,
                'id_parent' => $value->id_parent,
                'estado' => $value->estado,
                'nombre' => $value->nombre,
                'text' => $value->area_dep,
                'orden' => $value->orden,
                'value' => $value->id_sedearea,
                'departamento' => $value->departamento,
                'checked' => $item_checked,
                'items' => $x,
                'children' => $row
            ];
        }
        //        dd($parent);
        return $parent;
    }

    public function addAreaSedeArea()
    {
        //        dd('hola');
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_depto = $jResponse["id_depto"];
        $id_entidad = $jResponse["id_entidad"];


        if ($valida == 'SI') {
            $jResponse = [];
            $id_person = Input::get("id_persona");
            $id_areas = Input::get("id_areas");

            $where_data = array(
                'id_persona' => $id_person,
                'id_depto' => $id_depto,
                'id_entidad' => $id_entidad
            );

            //            dd($id_person, ' ..> ', $id_areas, ' -> ', $where_data);

            $data = array();
            for ($i = 0; $i < count($id_areas); ++$i) {
                $x = array(
                    //                    'ID_PAUTORIZA'=>OrganizationData::getMax('PEDIDO_AUTORIZA_AREA', 'ID_PAUTORIZA'),
                    //                    'id_pautoriza'=>$i,
                    'id_persona' => $id_person,
                    'id_sedearea' => $id_areas[$i],
                    'id_depto' => $id_depto,
                    'id_entidad' => $id_entidad,
                    'estado' => '1'
                );
                $data[$i] = $x;
            }
            //            dd('dataEND', $data);
            /*$b = array_map(function($element){
                $x = (object) array('id_sedearea'=>$element, 'id_persona'=>'16655');
                return $x;
            }, $a);
//            dd('entryyy');*/

            try {
                //                $idOrganisacion = OrganizationData::getMax('ORG_AREA', 'ID_AREA');
                //                $data = ['id_entidad' => $id_entidad, 'id_area' => $idOrganisacion, 'id_tipoarea' => $id_tipoarea, 'id_parent' => $id_parent, 'nombre' => $nombre, 'estado' => $estado];
                //                dd('heeeeree', $data);
                $result = OrganizationData::addAuthorizeAreas($data, $where_data);
                if ($result) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = False;
                    $jResponse['message'] = 'Area is not asigned';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                //                $jResponse['params'] = $params;
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }
    
    public function copyOrganization(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_depto = $jResponse["id_depto"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];


        if ($valida == 'SI') {
            $jResponse = [];

            try {
                $result = OrganizationData::copyOrganization($request, $id_user);
                if ($result['success']) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "200";
                } else {
                    $jResponse['success'] = False;
                    $jResponse['message'] = $result['message'];
                    $jResponse['data'] = $result['data'];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function cube($n)
    {
        return ($n * $n * $n);
    }
}
