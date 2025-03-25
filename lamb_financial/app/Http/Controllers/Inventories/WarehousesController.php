<?php

namespace App\Http\Controllers\Inventories;

use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Inventories\WarehousesData;
use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\Inventories\InventoriesData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

use PDO;
use Excel;

class WarehousesController extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function listWarehouses()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            //$data = WarehousesData::listWarehouses($id_entidad,$id_depto);
            try {
                $data = WarehousesController::recursiveWarehouses("A", $id_entidad, $id_depto);
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
    public function recursiveWarehouses($id_parent, $id_entidad, $id_depto)
    {
        $parent = [];
        $data = WarehousesData::listWarehouses($id_parent, $id_entidad, $id_depto);
        foreach ($data as $key => $value) {
            $row = $this->recursiveWarehouses($value->id_almacen, $id_entidad, $id_depto);
            $parent[] = [
                'id_almacen' => $value->id_almacen,
                'id_parent' => $value->id_parent,
                'id_entidad' => $value->id_entidad,
                'id_depto' => $value->id_depto,
                'nombre' => $value->nombre,
                'estado' => $value->estado,
                'children' => $row
            ];
        }
        return $parent;
    }
    public function showWarehouses($id_almacen)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::showWarehouses($id_almacen);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data[0];
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
    public function listWarehousesOrigins()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad, $id_user);
                foreach ($warehouse as $key => $item) {
                    $id_almacen = $item->id_almacen;
                }
                if (count($warehouse) > 0) {
                    if (count($warehouse) == 1) {
                        $data = WarehousesData::showWarehouses($id_almacen);
                        if ($data) {
                            $jResponse['success'] = true;
                            $jResponse['message'] = 'OK';
                            $jResponse['data'] = $data[0];
                            $code = "200";
                        } else {
                            $jResponse['success'] = true;
                            $jResponse['message'] = 'The item does not exist';
                            $jResponse['data'] = [];
                            $code = "202";
                        }
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = 'There is more than one warehouse assigning for the user: ' . $id_user;
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The user does not have an assigned Warehouse: ' . $id_user;
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
    public function listWarehousesDestinations()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_almacen = 0;
            try {

                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad, $id_user);
                foreach ($warehouse as $key => $item) {
                    $id_almacen = $item->id_almacen;
                }
                $data = WarehousesData::listWarehousesDestinationsByEntidad($id_entidad, $id_almacen);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";

                // ESTO FUE ACTUALIZADO POR LA UPN. 
                // $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad,$id_user);
                // foreach ($warehouse as $key => $item){
                //     $id_almacen = $item->id_almacen;
                // }
                // if(count($warehouse) > 0){
                //     if(count($warehouse) == 1){
                //         $data = WarehousesData::listWarehousesDestinations($id_almacen);
                //         if ($data) {          
                //             $jResponse['success'] = true;
                //             $jResponse['message'] = 'OK';
                //             $jResponse['data'] = $data;
                //             $code = "200";
                //         }else{
                //             $jResponse['success'] = true;
                //             $jResponse['message'] = 'The item does not exist';
                //             $jResponse['data'] = [];
                //             $code = "202";
                //         }
                // }else{
                //         $jResponse['success'] = false;
                //         $jResponse['message'] = 'There is more than one warehouse assigning for the user: '.$id_user;
                //         $jResponse['data'] = [];
                //         $code = "202";
                // }
                // }else{
                //     $jResponse['success'] = false;
                //     $jResponse['message'] = 'The user does not have an assigned Warehouse: '.$id_user;
                //     $jResponse['data'] = [];
                //     $code = "202";
                // }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addWarehouses()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            // $params = json_decode(file_get_contents("php://input"));
            // $id_parent = $params->id_parent;
            // $nombre = $params->nombre;
            // $estado = $params->estado;
            // $id_sedearea = $params->id_sedearea;

            $id_parent = Input::get("id_parent");
            $nombre = Input::get("nombre");
            $estado = Input::get("estado");
            $id_sedearea = Input::get("id_sedearea");
            $id_existencia = Input::get("id_existencia");

            try {
                $data = WarehousesData::addWarehouses($id_parent, $id_entidad, $id_depto, $nombre, $estado, $id_sedearea, $id_existencia);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateWarehouses($id_almacen)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            // $params = json_decode(file_get_contents("php://input"));
            // $id_parent = $params->id_parent;
            // $nombre = $params->nombre;
            // $estado = $params->estado;
            // $id_sedearea = $params->id_sedearea;
            $id_parent = Input::get("id_parent");
            $nombre = Input::get("nombre");
            $estado = Input::get("estado");
            $id_sedearea = Input::get("id_sedearea");
            $id_existencia = Input::get("id_existencia");
            try {
                $data = WarehousesData::updateWarehouses($id_almacen, $id_parent, $nombre, $estado, $id_sedearea, $id_existencia);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function deleteWarehouses($id_almacen)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::deleteWarehouses($id_almacen);
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
    public function listMyWarehousesUsers()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::listMyWarehousesUsers($id_user, $id_entidad, $id_depto);
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

    public function listWarehousesUsers($id_almacen)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::listWarehousesUsers($id_almacen);
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
    public function showWarehousesUsers($id_almacen, $id_persona)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::showWarehousesUsers($id_almacen, $id_persona);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data[0];
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
    public function addWarehousesUsers($id_almacen)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $id_persona = $params->id_persona;
            $estado = $params->estado;
            try {
                $data = WarehousesData::addWarehousesUsers($id_almacen, $id_persona, $estado);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateWarehousesUsers($id_almacen, $id_persona)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $estado = $params->estado;
            try {
                $data = WarehousesData::updateWarehousesUsers($id_almacen, $id_persona, $estado);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateWarehousesUsersAssign($id_almacen)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $asignado = $params->asignado;
            try {

                $user = WarehousesData::showWarehousesUsers($id_almacen, $id_user);
                if (count($user) == 0) {
                    WarehousesData::addWarehousesUsers($id_almacen, $id_user, '1');
                }

                $data = WarehousesData::updateWarehousesUsersAssign($id_entidad, $id_almacen, $id_user, $asignado);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function deleteWarehousesUsers($id_almacen, $id_persona)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::deleteWarehousesUsers($id_almacen, $id_persona);
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
    public function listMeasurementUnits()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::listMeasurementUnits();
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
    public function showMeasurementUnits($id_unidadmedida)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::showMeasurementUnits($id_unidadmedida);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data[0];
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
    public function addMeasurementUnits()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $datax = Input::all();
            $validador = Validator::make($datax, $this->rulesUnits());
            if ($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = null;
                $code = "202";
                goto end;
            }
            try {
                $data = WarehousesData::addMeasurementUnits(
                    $datax['nombre'],
                    $datax['codigo_sunat'],
                    $datax['es_decimal'],
                    $datax['estado']
                );

                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }
    private function rulesUnits()
    {
        return [
            'nombre' => 'required|max:3',
            'codigo_sunat' => 'required|max:3',
            'es_decimal' => 'required',
            'estado' => 'required',
        ];
    }
    public function updateMeasurementUnits($id_unidadmedida)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $datax = Input::all();
            $validador = Validator::make($datax, $this->rulesUnits());
            if ($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = null;
                $code = "202";
                goto end;
            }
            try {
                $data = WarehousesData::updateMeasurementUnits(
                    $id_unidadmedida,
                    $datax['nombre'],
                    $datax['codigo_sunat'],
                    $datax['es_decimal'],
                    $datax['estado']
                );

                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }
    public function deleteMeasurementUnits($id_unidadmedida)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::deleteMeasurementUnits($id_unidadmedida);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listArticles()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::listArticles("A");
                //$data = WarehousesController::recursiveArticles("A");
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
    public function listArticlesChildren($id_articulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesController::recursiveArticles($id_articulo);
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
    public function recursiveArticles($id_parent)
    {
        $parent = [];
        $data = WarehousesData::listArticles($id_parent);
        foreach ($data as $key => $value) {
            $row = $this->recursiveArticles($value->id_articulo);
            $parent[] = [
                'id_articulo' => $value->id_articulo,
                'id_parent' => $value->id_parent,
                'id_unidadmedida' => $value->id_unidadmedida,
                'nombre_unidadmedida' => $value->nombre_unidadmedida,
                'id_marca' => $value->id_marca,
                'nombre_marca' => $value->nombre_marca,
                'id_codcubso' => $value->id_codcubso,
                'id_clase' => $value->id_clase,
                'nombre_clase' => $value->nombre_clase,
                'nombre' => $value->nombre,
                'estado' => $value->estado,
                'codigo' => $value->codigo,
                'square' => $value->square,

                'text' => $value->nombre,
                // 'value' => $value->id_articulo,
                'value' => $value,
                'checked' => false,

                'children' => $row
            ];
        }
        return $parent;
    }
    public function showArticles($id_articulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::showArticles($id_articulo);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data[0];
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
    public function addArticles(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));

            $id_parent = $request->id_parent;
            $id_unidadmedida = $request->id_unidadmedida;
            $id_marca = $request->id_marca;
            $id_codcubso = null;
            if (isset($request->id_codcubso)) {
                //$id_codcubso = $request->id_codcubso; 
            }
            $id_clase = $request->id_clase;
            $nombre = $request->nombre;
            $codigo = $request->codigo;
            $estado = $request->estado;

            $id_almacen = $request->id_almacen;
            $id_articulo = $request->id_articulo;

            $codigo_barra = $request->codigo_barra;
            $id_tipoigv = $request->id_tipoigv;
            $stock_minimo = $request->stock_minimo;
            $es_servicio = $request->es_servicio;
            $ubicacion = $request->ubicacion;
            $id_ctacte = $request->id_ctacte;
            $codigo_cw = $request->codigo_cw;
            $file = $request->file('file');
            //$file = $params->file('file');
            try {
                if ($id_articulo == "") {
                    //Adjunta Imagen Articulo
                    $objFile = $this->uploadImage($file, $codigo, '');
                    if ($objFile["success"]) {
                        $url = $objFile["data"];
                    } else {
                        $url = "";
                    }
                    //REIGSTRA DATOS EN EL CATALOGO GENERAL DE ARTICULOS
                    $data = WarehousesData::addArticles($id_parent, $id_unidadmedida, $id_marca, $id_codcubso, $id_clase, $nombre, $codigo, $estado, $url);
                    foreach ($data as $item) {
                        $id_articulo = $item->id_articulo;
                    }
                } else {
                    /*$data = AccountingData::showArticuloEdit($id_articulo);
                    foreach ($data as $item){
                        $edit = $item->edita;               
                    } 
                    if($edit == "s"){
                        WarehousesData::updateArticles($id_articulo,$id_parent,$id_unidadmedida,$id_marca,$id_codcubso,$id_clase,$nombre,$codigo,$estado);
                    }*/
                }

                //SI REGISTRA EN EL CATALOGO, ASIGNAR A LOS ALMACENES AUTOAMTICAMENTE SI SON DE CLASE ITEMS (6)
                if ($id_clase == 6) {
                    //REGISTRA CODIGO DE BARRA
                    WarehousesData::addArticlesCodes($id_articulo, $codigo_barra);

                    $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                    foreach ($data_anho as $item) {
                        $id_anho = $item->id_anho;
                    }

                    $data_warehouses = WarehousesController::showWarehousesParent($id_almacen);
                    foreach ($data_warehouses as $row) {
                        $data_articulo = WarehousesData::showWarehousesArticles($row->id_almacen, $id_articulo, $id_anho, '');
                        if (count($data_articulo) == 0) {
                            $data = WarehousesData::addWarehousesArticles($row->id_almacen, $id_articulo, $id_anho, $id_tipoigv, $codigo_cw, $stock_minimo, $es_servicio, $estado, $ubicacion, $id_ctacte);
                        }
                    }
                }
                //FIN ASIGNACION
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addArticlesUpload(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            try {

                \Excel::load($request->excel, function ($reader) use ($request) {
                    $excel = $reader->get();
                    $reader->each(function ($row) use ($request) {
                        $nombre = $row->nombre;
                        $id_unidadmedida = $row->unidadmedida;
                        $id_tipoigv = $row->tipoigv;
                        $codigo_barra = $row->codigobarra;
                        $id_ctacte = $row->id_ctacte;

                        $data = WarehousesData::showArticlesName($nombre);
                        if (count($data) == 0) {
                            $data = WarehousesData::addArticles($request->id_parent, $id_unidadmedida, null, null, $request->id_clase, $nombre, NULL, '1', '');
                            foreach ($data as $item) {
                                $id_articulo = $item->id_articulo;
                            }
                            if ($request->id_clase == 6) {
                                //REGISTRA CODIGO DE BARRA
                                WarehousesData::addArticlesCodes($id_articulo, $codigo_barra);
                                $entidad = WarehousesData::showWarehousesEntidad($request->id_almacen);
                                foreach ($entidad as $item) {
                                    $id_entidad = $item->id_entidad;
                                }
                                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                                foreach ($data_anho as $item) {
                                    $id_anho = $item->id_anho;
                                }
                                $data_warehouses = WarehousesController::showWarehousesParent($request->id_almacen);
                                foreach ($data_warehouses as $item) {
                                    $data = WarehousesData::addWarehousesArticles($item->id_almacen, $id_articulo, $id_anho, $id_tipoigv, '', 0, 'N', '1', '', $id_ctacte);
                                }
                            }
                        }
                    });
                });
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function addArticlesImports(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            try {
                //dd($request->query('id_parent'));

                \Excel::load($request->excel, function ($reader) use ($request) {
                    $excel = $reader->get();
                    $reader->each(function ($row) use ($request) {
                        $codigo = $row->codigo;
                        $nombre = $row->nombre;
                        $articulo = WarehousesData::showArticlesCodigo($codigo);
                        if (count($articulo) == 0) {
                            WarehousesData::addArticles($request->id_parent, null, null, null, $request->id_clase, $nombre, $codigo, '1', '');
                        }
                    });
                });
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateArticles($id_articulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $id_parent = $params->id_parent;
            $id_unidadmedida = $params->id_unidadmedida;
            $id_marca = $params->id_marca;
            $id_codcubso = $params->id_codcubso;
            $id_clase = $params->id_clase;
            $nombre = $params->nombre;
            $estado = $params->estado;
            try {
                $data = WarehousesData::updateArticles(
                    $id_articulo,
                    $id_parent,
                    $id_unidadmedida,
                    $id_marca,
                    $id_codcubso,
                    $id_clase,
                    $nombre,
                    $estado
                );
                // $data = WarehousesData::updateArticles($id_articulo,$id_unidadmedida,$id_marca,
                // $id_codcubso,$id_clase, $nombre,null,$estado, null);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function deleteArticles($id_articulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::deleteArticles($id_articulo);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listClass()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::listClass();
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
    public function listClassArticles(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_articulo = $request->query('id_articulo');
                $data = WarehousesData::listClassArticles($id_articulo);
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
    public function listArticlesCodes($id_articulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::listArticlesCodes($id_articulo);
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
    public function addArticlesCodes($id_articulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $data = Input::all();
            $validador = Validator::make($data, ['codigo' => 'required|max:50']);
            if ($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }
            try {
                $data = WarehousesData::addArticlesCodes($id_articulo, $data['codigo']);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }
    public function deleteArticlesCodes($id_articulo, $id_codarticulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::deleteArticlesCodes($id_codarticulo);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listWarehousesArticlesAll(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_almacen = $request->query('id_almacen');
                $dato = $request->query('dato');
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                }
                $string_to_array = explode(' ', $dato);
                $i = 1;
                $or = "";
                $and = "";
                foreach ($string_to_array as $value) {
                    if ($i == 1) {
                        $and = $value;
                    } else {
                        $or = $or . " " . "OR UPPER(NOMBRE) LIKE UPPER('%" . $value . "%')";
                    }
                    $i++;
                }
                $data = WarehousesData::listWarehousesArticlesAll($id_almacen, $id_anho, $and, $or);
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

    public function listArticlesSearch()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_articulo = $this->request->query('id_articulo');
        $param = $this->request->query('nombre_articulo');
        if ($valida == 'SI' and $id_articulo and $param) {
            $jResponse = [];
            try {
                $data = WarehousesData::listArticlesSearch($id_articulo, $param);
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
    public function listWarehousesArticles($id_almacen)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                }
                $data = WarehousesController::recursiveWarehousesArticles($id_almacen, $id_anho, "A");
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
    public function recursiveWarehousesArticles($id_almacen, $id_anho, $id_parent)
    {
        $parent = [];
        $data = WarehousesData::listWarehousesArticles($id_almacen, $id_anho, $id_parent);
        foreach ($data as $key => $value) {
            $row = $this->recursiveWarehousesArticles($id_almacen, $id_anho, $value->id_articulo);
            $parent[] = [
                'id_articulo' => $value->id_articulo,
                'id_parent' => $value->id_parent,
                'id_unidadmedida' => $value->id_unidadmedida,
                'nombre_unidadmedida' => $value->nombre_unidadmedida,
                'id_clase' => $value->id_clase,
                'nombre_clase' => $value->nombre_clase,
                'nombre' => $value->nombre,
                'estado' => $value->estado,
                'children' => $row
            ];
        }
        return $parent;
    }
    public function showWarehousesArticles($id_almacen, $id_articulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                }
                $url = secure_url(''); //Prod
                //$url = url('');//Dev
                $data = WarehousesData::showWarehousesArticles($id_almacen, $id_articulo, $id_anho, $url);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data[0];
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
    public function showWarehousesParent($id_almacen)
    {
        $data = "";
        try {
            $data = WarehousesData::showWarehousesParent($id_almacen);
        } catch (Exception $e) {
            $jResponse['success'] = false;
            $jResponse['message'] = "ORA-" . $e->getCode();
            $jResponse['data'] = [];
            $code = "400";
        }
        return $data;
    }
    public function listWarehousesArticlesClass($id_almacen)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                }
                $data = WarehousesData::listWarehousesArticlesClass($id_almacen, $id_anho);
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
    public function listWarehousesArticlesItems(Request $request, $id_almacen)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_parent = $request->query('id_parent');
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                }
                $data = WarehousesData::listWarehousesArticlesItems($id_parent, $id_almacen, $id_anho);
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

    public function listWarehousesAlmacenCategorias(Request $request, $id_almacen)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::listWarehousesAlmacenCategorias($id_almacen);
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

    public function getWarehousesAlmacenCategorias(Request $request, $id_almacen, $id_rcategoria)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::getWarehousesAlmacenCategorias($id_almacen, $id_rcategoria);
                if (count($data) === 1) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data[0];
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

    public function deleteWarehousesAlmacenCategorias(Request $request, $id_almacen, $id_rcategoria)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::deleteWarehousesAlmacenCategorias($id_almacen, $id_rcategoria);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function addWarehousesAlmacenCategorias(Request $request, $id_almacen)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];

            $data = Input::all();
            $validador = Validator::make($data,  [
                'id_rcategoria' => 'required',
                'id_almacen' => 'required',
                'id_anho' => 'required',
                'id_modulo' => 'required',
                'change_depto' => 'required',
                'new_depto' => '',
                'estado' => 'required',
            ]);
            if ($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }

            try {
                $rubroCat = WarehousesData::getAlmacenRubroCategoriaByParams($data['id_rcategoria'], $id_almacen);

                if (count($rubroCat) === 1) {
                    // Editar
                    $data = WarehousesData::updateAlmacenRubroCategoria($data['id_rcategoria'], $id_almacen, $data);
                } else {
                    // Nuevo                    
                    $data = WarehousesData::addAlmacenRubroCategoria($id_entidad, $id_depto, $data['id_rcategoria'], $id_almacen, $data);
                }

                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }

    public function addWarehousesArticles($id_almacen)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $id_entidad = $jResponse["id_entidad"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $id_articulo = $params->id_articulo;
            $id_tipoigv = $params->id_tipoigv;
            $codigo = $params->codigo;
            $stock_minimo = $params->stock_minimo;
            $estado = $params->estado;
            $es_servicio = $params->es_servicio;
            $id_ctacte = $params->id_ctacte;

            $id_anho = null;
            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
            foreach ($data_anho as $item) {
                $id_anho = $item->id_anho;
            }
            if (is_null($id_anho)) {
                $jResponse['success'] = false;
                $jResponse['message'] = 'No existe un año activo.';
                $jResponse['data'] = null;
                $code = "202";
                goto end;
            }

            try {

                $data_warehouses = WarehousesController::showWarehousesParent($id_almacen);
                foreach ($data_warehouses as $item) {
                    $data = WarehousesData::addWarehousesArticles($item->id_almacen, $id_articulo, $id_anho, $id_tipoigv, $codigo, $stock_minimo, $es_servicio, $estado, '', $id_ctacte);
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }
    public function updateWarehousesArticles(Request $request, $id_almacen, $id_articulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $id_entidad = $jResponse["id_entidad"];
        $valida = $jResponse["valida"];
        // dd('holllll');
        if ($valida == 'SI') {
            $jResponse = [];
            //$params = json_decode(file_get_contents("php://input"));
            $id_unidadmedida = $request->id_unidadmedida;
            $id_marca = $request->id_marca;
            $id_codcubso = null;
            if (isset($request->id_codcubso)) {
                //$id_codcubso = $params->id_codcubso; 
            }
            $id_clase = $request->id_clase;
            $nombre = $request->nombre;
            $codigo = $request->codigo;
            $codigo_cw = $request->codigo_cw;
            $ubicacion = $request->ubicacion;
            $estado = $request->estado;
            $id_tipoigv = $request->id_tipoigv;
            $stock_minimo = $request->stock_minimo;
            $es_servicio = $request->es_servicio;
            $file = $request->file('file');
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                }

                $data = WarehousesData::showArticuloEdit($id_articulo);
                foreach ($data as $item) {
                    $edit = $item->edita;
                }
                $datos = WarehousesData::showArticlesWarehouses($id_almacen, $id_articulo, $id_anho);
                foreach ($datos as $item) {
                    if ($id_tipoigv == $item->id_tipoigv) {
                        $edit = "S";
                    }
                }
                $edit = "S";
                if ($edit == "S") {
                    $articulo = WarehousesData::showArticles($id_articulo);
                    foreach ($articulo as $item) {
                        $cod = $item->codigo;
                        $image_old = $item->imagen_url;
                    }
                    //Adjunta Imagen Articulo

                    $url = "";
                    if ($file) {
                        $objFile = $this->uploadImage($file, $cod, $image_old);
                        if ($objFile["success"]) {
                            $url = $objFile["data"];
                        }
                    }
                    WarehousesData::updateArticles($id_articulo, $id_unidadmedida, $id_marca, $id_codcubso, $id_clase, $nombre, $codigo, $estado, $url);
                    $dir = secure_url(''); //Prod
                    //$dir = url('');//Dev
                    $data_warehouses = WarehousesController::showWarehousesParent($id_almacen);
                    foreach ($data_warehouses as $item) {
                        $data = WarehousesData::updateWarehousesArticles($id_almacen, $id_articulo, $id_anho, $id_tipoigv, $codigo_cw, $stock_minimo, $es_servicio, $estado, $dir, $ubicacion);
                    }
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No se Puede Editar los Items";
                    $jResponse['data'] = $data;
                    $code = "200";
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
    public function deleteWarehousesArticles($id_almacen, $id_articulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::showArticuloEdit($id_articulo);
                foreach ($data as $item) {
                    $edit = $item->edita;
                }
                if ($edit == "S") {
                    /*$data_warehouses = WarehousesController::showWarehousesParent($id_almacen);
                    foreach ($data_warehouses as $item){
                        $data = WarehousesData::deleteWarehousesArticlesAll($id_articulo);
                    }*/
                    $data = WarehousesData::deleteWarehousesArticlesAll($id_articulo);
                    WarehousesData::deleteArticlesCodesP($id_articulo);
                    WarehousesData::deleteArticles($id_articulo);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was deleted successfully";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = "No se Puede Eliminar, Ya exixten Registros";
                    $jResponse['data'] = $data;
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listWarehousesRecipes($id_almacen)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::listWarehousesRecipes($id_almacen);
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
    public function showWarehousesRecipes($id_almacen, $id_receta)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::showWarehousesRecipes($id_receta);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data[0];
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
    public function addWarehousesRecipes($id_almacen)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $nombre = $params->nombre;
            $rendimiento = $params->rendimiento;
            $estado = $params->estado;
            $id_recetatipo = $params->id_recetatipo;
            try {
                $data = WarehousesData::addWarehousesRecipes($id_almacen, $nombre, $rendimiento, $estado, $id_recetatipo);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateWarehousesRecipes($id_almacen, $id_receta)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $nombre = $params->nombre;
            $rendimiento = $params->rendimiento;
            $estado = $params->estado;
            $id_recetatipo = $params->id_recetatipo;

            try {
                $data = WarehousesData::updateWarehousesRecipes($id_receta, $id_almacen, $nombre, $rendimiento, $estado, $id_recetatipo);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function deleteWarehousesRecipes($id_almacen, $id_receta)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::deleteWarehousesRecipes($id_receta);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listRecipesArticles($id_receta)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::listRecipesArticles($id_receta);
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
    public function showRecipesArticles($id_receta, $id_articulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::showRecipesArticles($id_receta, $id_articulo);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data[0];
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
    public function addRecipesArticles($id_receta)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $id_articulo = $params->id_articulo;
            $cantidad = $params->cantidad;
            $estado = $params->estado;
            $precio_unitario_ref = $params->precio_unitario_ref;
            try {
                $data = WarehousesData::addRecipesArticles($id_receta, $id_articulo, $cantidad, $estado, $precio_unitario_ref);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateRecipesArticles($id_receta, $id_articulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $cantidad = $params->cantidad;
            $estado = $params->estado;
            $precio_unitario_ref = $params->precio_unitario_ref;
            try {
                $data = WarehousesData::updateRecipesArticles($id_receta, $id_articulo, $cantidad, $estado, $precio_unitario_ref);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function deleteRecipesArticles($id_receta, $id_articulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::deleteRecipesArticles($id_receta, $id_articulo);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listTypeOperations()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::listTypeOperations();
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
    public function listWarehousesDocuments($id_almacen)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::listWarehousesDocuments($id_almacen);
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
    public function showWarehousesDocuments($id_almacen, $id_doc)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::showWarehousesDocuments($id_almacen, $id_doc);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data[0];
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
    public function addWarehousesDocuments($id_almacen)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $id_documento = $params->id_documento;
            $id_tipooperacion = $params->id_tipooperacion;
            $estado = $params->estado;
            try {
                $data = WarehousesData::addWarehousesDocuments($id_almacen, $id_documento, $id_tipooperacion, $estado);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function updateWarehousesDocuments($id_almacen, $id_doc)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $id_documento = $params->id_documento;
            $id_tipooperacion = $params->id_tipooperacion;
            $estado = $params->estado;
            try {
                $data = WarehousesData::updateWarehousesDocuments($id_almacen, $id_doc, $id_documento, $id_tipooperacion, $estado);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function deleteWarehousesDocuments($id_almacen, $id_doc)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::deleteWarehousesDocuments($id_doc);
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
    public function listWarehousesArticlesFind(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            $dato = $request->query('dato');
            try {




                $id_anho   = $request->query('id_anho');
                if (empty($id_anho)) {
                    $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                    foreach ($data_anho as $item) {
                        $id_anho = $item->id_anho;
                    }
                }

                $id_almacen = $request->id_almacen ?? null;

                if (empty($id_almacen)) {
                    $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad, $id_user);
                    foreach ($warehouse as $key => $item) {
                        $id_almacen = $item->id_almacen;
                    }
                } else {
                    $warehouse = [["id_almacen" => $id_almacen]];
                }
                /*if($id_almacen = 33){
                    $id_anho = 2021;// solo temporal logistica
                }*/
                if (count($warehouse) > 0) {
                    if (count($warehouse) == 1) {
                        $url = secure_url(''); // Prod.
                        //$url = url(''); // Dev.
                        $data = WarehousesData::listWarehousesArticlesFind($id_almacen, $id_anho, $dato, $url);
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
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = 'There is more than one warehouse assigning for the user: ' . $id_user;
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The user does not have an assigned Warehouse: ' . $id_user;
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
    public function listArticlesClassParent()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::listArticlesClassParent();
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
    public function listArticlesClassChild($id_almacen, $id_articulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                foreach ($data_anho as $item) {
                    $id_anho = $item->id_anho;
                }
                $id_clase = 0;
                $data = WarehousesData::listArticlesClassChild($id_articulo);
                foreach ($data as $item) {
                    $id_clase = $item->id_clase;
                }
                if ($id_clase == 6) {
                    $data = WarehousesData::listArticlesWarehouses($id_almacen, $id_articulo, $id_anho);
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
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listMyWarehousesYears($id_anho)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::listMyWarehousesYears($id_anho, $id_entidad, $id_depto);
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
    public function listMyWarehousesStock(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_almacen = $request->query('id_almacen');
                $id_anho = $request->query('id_anho');
                $data = WarehousesData::listMyWarehousesStock($id_almacen, $id_anho);
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
    public function addStockWarehouses()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $params = json_decode(file_get_contents("php://input"));
            $id_almacen = $params->id_almacen;
            $id_anho = $params->id_anho;
            try {
                $data = WarehousesData::addStockWarehouses($id_almacen, $id_anho);
                $jResponse['success'] = true;
                $jResponse['message'] = count($data) . " Records have been inserted successfully";
                $jResponse['data'] = $data[0];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function listWarehousesEntity(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $id_entidad = $request->query('id_entidad');
                $id_depto = $request->query('id_depto');
                $data = WarehousesData::listWarehouses('A', $id_entidad, $id_depto);
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
    private function rulesArticulo()
    {
        return [
            'id_parent' => '',
            'id_clase' => 'required',
            'nombre' => 'required',
            'codigo' => 'required|max:50',
            'estado' => 'required',
        ];
    }

    public function addArticlesStructures()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_parent = Input::get('id_parent');
            $id_clase = Input::get('id_clase');
            $nombre = Input::get('nombre');
            $codigo = Input::get('codigo');
            $estado = Input::get('estado');

            $data = Input::all();
            $validador = Validator::make($data,  $this->rulesArticulo());
            if ($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }

            try {
                //REIGSTRA DATOS EN EL CATALOGO GENERAL DE ARTICULOS
                if ($id_clase !== '6') {
                    $dataaName = WarehousesData::showArticlesName($nombre);
                    $dataaCode = WarehousesData::showArticlesCodigo($codigo);
                    if (count($dataaName) !== 0) {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Ya existe un articulo con el mismo nombre.";
                        $jResponse['data'] = null;
                        $code = "202";
                        goto end;
                    }
                    if (count($dataaCode) !== 0) {
                        $jResponse['success'] = false;
                        $jResponse['message'] = "Ya existe un articulo con el mismo código.";
                        $jResponse['data'] = null;
                        $code = "202";
                        goto end;
                    }
                    $url = secure_url(''); //Prod
                    //$url = url('');//Dev
                    $data = WarehousesData::addArticles($id_parent, null, null, null, $id_clase, $nombre, $codigo, $estado, $url);
                }
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = $data;
                $code = "201";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = null;
                $code = "202";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }
    public function updateArticlesStructures($id_articulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            $id_parent = Input::get('id_parent');
            $id_clase = Input::get('id_clase');
            $nombre = Input::get('nombre');
            $codigo = '';
            $estado = Input::get('estado');

            $data = Input::all();
            $validador = Validator::make($data,  $this->rulesArticulo());
            if ($validador->fails()) {
                $jResponse['success'] = false;
                $jResponse['message'] = $validador->errors()->first();
                $jResponse['data'] = NULL;
                $code = "202";
                goto end;
            }

            try {
                //REIGSTRA DATOS EN EL CATALOGO GENERAL DE ARTICULOS
                if ($id_clase != 6) {
                    $data = WarehousesData::updateArticles($id_articulo, null, null, null, $id_clase, $nombre, $codigo, $estado, '');
                }
                //FIN ASIGNACION
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was updated successfully";
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }
    public function deleteArticlesStructures($id_articulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::deleteArticlesAndChilds($id_articulo);
                if ($data > 0) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = $data . "items was deleted successfully";
                    $jResponse['data'] = $data;
                    $code = "200";
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'Can not be eliminated';
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getCode();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse, $code);
    }
    public function showWarehousesUsersAssign()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::showWarehousesUsersAssign($id_entidad, $id_user);
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data[0];
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
    public function listMyWarehousesRecipes(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $dato = $request->query('dato');
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad, $id_user);
                foreach ($warehouse as $key => $item) {
                    $id_almacen = $item->id_almacen;
                }
                if (count($warehouse) > 0) {
                    if (count($warehouse) == 1) {
                        $data = WarehousesData::listMyWarehousesRecipes($id_almacen, $dato);
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
                    } else {
                        $jResponse['success'] = false;
                        $jResponse['message'] = 'There is more than one warehouse assigning for the user: ' . $id_user;
                        $jResponse['data'] = [];
                        $code = "202";
                    }
                } else {
                    $jResponse['success'] = false;
                    $jResponse['message'] = 'The user does not have an assigned Warehouse: ' . $id_user;
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
    public function uploadImage($file, $codigo, $image_old)
    {
        try {
            if ($file != null) {
                $fileName = $file->getClientOriginalName();
                $formato = strtoupper($file->getClientOriginalExtension());
                $size = ($file->getSize() / 1024);
                if ($size <= 768) {
                    if (is_file($image_old)) {
                        unlink($image_old);
                    }
                    $foto = $codigo . "." . $formato;
                    $path = 'inventories_files/articulos';
                    $url = $path . "/" . $foto;
                    $file->move($path, $foto);
                    return ["success" => true, "message" => "OK", "data" => $url, "path" => $path, "formato" => $formato, "size" => $size];
                } else {
                    return ["success" => false, "message" => "Error: The image exceeds the allowed size (768 KB)"];
                }
            } else {
                return ["success" => false, "message" => "Error: The image was not attached"];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
    public function listArticlesTree($id_articulo)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::listArticlesTree($id_articulo);
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
    public function addArticlesUploadALL(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            try {

                $jResponse = [];
                \Excel::load($request->excel, function ($reader) use ($request) {
                    $excel = $reader->get();
                    $reader->each(function ($row) use ($request) {
                        $id_almacen = $row->almacen; //id_almacen
                        $codigo_cw = $row->codigo_cw; //codigo del sistema anterior
                        $codigo_unspsc = $row->codigo_unspsc; // codificacion universal
                        $nombre = $row->nombre;
                        $id_unidadmedida = $row->unidadmedida;
                        $id_tipoigv = $row->tipoigv;
                        $codigo_barra = $row->codigobarra; // codigo de barra registrado en el contaweb
                        $costo = $row->costo;
                        $descuento = $row->dscto;
                        $precio = $row->pv;
                        $id_ctacte = $row->cta_cte;
                        $data = WarehousesData::showArticlesName($nombre);
                        if (count($data) == 0) {
                            $parent = WarehousesData::showArticlesCodigo($codigo_unspsc);
                            foreach ($parent as $item) {
                                $id_parent = $item->id_articulo;
                            }
                            $data = WarehousesData::addArticles($id_parent, $id_unidadmedida, null, null, 6, $nombre, NULL, '1', '');
                            foreach ($data as $item) {
                                $id_articulo = $item->id_articulo;
                            }
                            WarehousesData::addArticlesCodes($id_articulo, $codigo_barra);
                            $entidad = WarehousesData::showWarehousesEntidad($id_almacen);
                            foreach ($entidad as $item) {
                                $id_entidad = $item->id_entidad;
                            }
                            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                            foreach ($data_anho as $item) {
                                $id_anho = $item->id_anho;
                            }
                            $data_warehouses = WarehousesController::showWarehousesParent($id_almacen);
                            foreach ($data_warehouses as $item) {
                                WarehousesData::addWarehousesArticles($item->id_almacen, $id_articulo, $id_anho, $id_tipoigv, $codigo_cw, 0, 'N', '1', '', $id_ctacte);
                            }
                            if ($costo + $descuento + $precio > 0) {
                                WarehousesData::addPriceSalesTemp($id_almacen, $id_articulo, $id_anho, $costo, $descuento, $precio, '1');
                            }
                        } else {
                            foreach ($data as $item) {
                                $id_articulo = $item->id_articulo;
                            }
                            $entidad = WarehousesData::showWarehousesEntidad($id_almacen);
                            foreach ($entidad as $item) {
                                $id_entidad = $item->id_entidad;
                            }
                            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                            foreach ($data_anho as $item) {
                                $id_anho = $item->id_anho;
                            }

                            $data_articulo = WarehousesData::showWarehousesArticles($id_almacen, $id_articulo, $id_anho, '');
                            if (count($data_articulo) == 0) {
                                $data_warehouses = WarehousesController::showWarehousesParent($id_almacen);
                                foreach ($data_warehouses as $item) {
                                    WarehousesData::addWarehousesArticles($item->id_almacen, $id_articulo, $id_anho, $id_tipoigv, $codigo_cw, 0, 'N', '1', '', $id_ctacte);
                                }
                                if ($costo + $descuento + $precio > 0) {
                                    WarehousesData::addPriceSalesTemp($id_almacen, $id_articulo, $id_anho, $costo, $descuento, $precio, '1');
                                }
                            }
                        }
                    });
                });
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage() . ", File: " . $e->getFile() . ", Linea:" . $e->getLine();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }

    public function addArticlesUploadALLUPN(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if ($valida == 'SI') {
            try {

                // $id_almacen = $request->id_almacen;
                $jResponse = [];
                \Excel::load($request->excel, function ($reader) use ($request) {
                    $excel = $reader->get();
                    $reader->each(function ($row) use ($request) {
                        $id_almacen = $request->id_almacen; //id_almacen
                        $codigo_cw = $row->codigo_cw; //codigo del sistema anterior
                        $codigo_unspsc = $row->codigo_unspsc; // codificacion universal
                        $nombre = $row->nombre;
                        $id_unidadmedida = $row->unidadmedida;
                        $id_tipoigv = $row->tipoigv;
                        $codigo_barra = $row->codigobarra; // codigo de barra registrado en el contaweb
                        $costo = $row->costo;
                        $descuento = $row->dscto;
                        $precio = $row->pv;
                        $id_ctacte = $row->cta_cte;
                        $data = WarehousesData::showArticlesName($nombre);
                        if (count($data) == 0) {
                            // throw new Exception($nombre);
                            $parent = WarehousesData::showArticlesCodigo($codigo_unspsc);
                            foreach ($parent as $item) {
                                $id_parent = $item->id_articulo;
                            }
                            $data = WarehousesData::addArticles($id_parent, $id_unidadmedida, null, null, 6, $nombre, NULL, '1', '');
                            foreach ($data as $item) {
                                $id_articulo = $item->id_articulo;
                            }
                            WarehousesData::addArticlesCodes($id_articulo, $codigo_barra);
                            $entidad = WarehousesData::showWarehousesEntidad($id_almacen);
                            foreach ($entidad as $item) {
                                $id_entidad = $item->id_entidad;
                            }
                            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                            foreach ($data_anho as $item) {
                                $id_anho = $item->id_anho;
                            }
                            $data_warehouses = WarehousesController::showWarehousesParent($id_almacen);
                            foreach ($data_warehouses as $item) {
                                WarehousesData::addWarehousesArticles($item->id_almacen, $id_articulo, $id_anho, $id_tipoigv, $codigo_cw, 0, 'N', '1', '', $id_ctacte);
                            }
                            if ($costo + $descuento + $precio > 0) {
                                WarehousesData::addPriceSalesTemp($id_almacen, $id_articulo, $id_anho, $costo, $descuento, $precio, '1');
                            }
                        } else {
                            foreach ($data as $item) {
                                $id_articulo = $item->id_articulo;
                            }
                            $entidad = WarehousesData::showWarehousesEntidad($id_almacen);
                            foreach ($entidad as $item) {
                                $id_entidad = $item->id_entidad;
                            }
                            $data_anho = AccountingData::showPeriodoActivo($id_entidad);
                            foreach ($data_anho as $item) {
                                $id_anho = $item->id_anho;
                            }

                            $data_articulo = WarehousesData::showWarehousesArticles($id_almacen, $id_articulo, $id_anho, '');
                            if (count($data_articulo) == 0) {
                                $data_warehouses = WarehousesController::showWarehousesParent($id_almacen);
                                foreach ($data_warehouses as $item) {
                                    WarehousesData::addWarehousesArticles($item->id_almacen, $id_articulo, $id_anho, $id_tipoigv, $codigo_cw, 0, 'N', '1', '', $id_ctacte);
                                }
                                if ($costo + $descuento + $precio > 0) {
                                    WarehousesData::addPriceSalesTemp($id_almacen, $id_articulo, $id_anho, $costo, $descuento, $precio, '1');
                                }
                            } else {
                                // Vamos a actualizar la cuenta cte.
                                WarehousesData::updateCtacteWarehousesArticles($id_almacen, $id_articulo, $id_anho, $id_ctacte);
                            }
                        }
                    });
                });
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was inserted successfully";
                $jResponse['data'] = [];
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-" . $e->getMessage() . ", File: " . $e->getFile() . ", Linea:" . $e->getLine();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        end:
        return response()->json($jResponse, $code);
    }

    public function listWarehouseByExist()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::listWarehouseByExist($id_entidad, $id_depto);
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

    public function searchWarehouse(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::searchWarehouse($id_entidad, $id_depto, $request->key);
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

    public function addWarehouseProduct(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $rpta = WarehousesData::addWarehouseProduct($request);
                if ($rpta['success']) {
                    DB::commit();
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was insert successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    DB::rollback();
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }

    public function getWarehouseProduct($id_receta)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];
            try {
                $data = WarehousesData::getWarehouseProduct($id_receta);
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

    public function updateWarehouseProduct(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if ($valida == 'SI') {
            $jResponse = [];
            DB::beginTransaction();
            try {
                $rpta = WarehousesData::updateWarehouseProduct($request);
                if ($rpta['success']) {
                    DB::commit();
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $jResponse['data'] = [];
                    $code = "200";
                } else {
                    DB::rollback();
                    $jResponse['success'] = false;
                    $jResponse['message'] = $rpta['message'];
                    $jResponse['data'] = [];
                    $code = "202";
                }
            } catch (Exception $e) {
                DB::rollback();
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "400";
            }
        }
        return response()->json($jResponse, $code);
    }
}
