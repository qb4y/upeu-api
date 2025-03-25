<?php


namespace App\Http\Data\FinancesStudent;


use Illuminate\Support\Facades\DB;
use PDO;

class SalesData
{
    public static function addtem($data)
    {
//        $submit = DB::select("EXEC ReturnIdExample ?,?", array($paramOne, $paramTwo));
    }

    public static function executeProcedure()
    {


    }

    public static function add($params)
    {
        $id = "";
        $code = 1;
        $msg = implode('', array_fill(0, 200, '0'));
        $id_entidad = $params->id_entidad;
        $id_depto = $params->id_depto;
        $id_anho = $params->id_anho;
        $id_mes = $params->id_mes;
        $id_persona = $params->id_persona;
        $id_cliente = $params->id_cliente;
        $id_sucursal = $params->id_sucursal; // $params->id_sucursal
        $id_cliente_legal = $params->id_cliente_legal;
        $id_comprobante = $params->id_comprobante;
        $id_moneda = $params->id_moneda;
        $agrupado = 'S'; // $params->agrupado;
        $glosa = $params->glosa;
        $id_tipoventa = $params->id_tipoventa;
        DB::beginTransaction();

        try {

            $stmt = DB::getPdo()->prepare("begin PKG_SALES.SP_CREAR_VENTA_FA(:P_ID_ENTIDAD,
                            :P_ID_DEPTO,
                            :P_ID_ANHO,
                            :P_ID_MES,
                            :P_ID_PERSONA,
                            :P_ID_CLIENTE,
                            :P_ID_SUCURSAL,
                            :P_ID_CLIENTE_LEGAL,
                            :P_ID_COMPROBANTE,
                            :P_ID_MONEDA,
                            :P_AGRUPADO,
                            :P_GLOSA,
                            :P_ID_TIPOVENTA,
                            :P_ID_VENTA,
                            :P_ERROR,
                            :P_MSN); end;");
            $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_CLIENTE', $id_cliente, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_SUCURSAL', $id_sucursal, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_CLIENTE_LEGAL', $id_cliente_legal, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_COMPROBANTE', $id_comprobante, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
            $stmt->bindParam(':P_AGRUPADO', $agrupado, PDO::PARAM_STR);
            $stmt->bindParam(':P_GLOSA', $glosa, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_TIPOVENTA', $id_tipoventa, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_VENTA', $id, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $code, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSN', $msg, PDO::PARAM_STR);
            $stmt->execute();


            if ($code == 0) {
                DB::commit();
            } else {
                DB::rollBack();
            }

        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            DB::rollBack();
        } catch (Exception $e) {
            $msg = $e->getMessage();
            DB::rollBack();
        }
        $return = [
            'nerror' => $code,
            'msgerror' => $msg,
            'id' => $id
        ];

        return $return;

    }

    public static function saveUpdateDireccion($request) {
        $id_persona       = $request->keyas;
        $direccion        = $request->direccion;
        $tipo_direccion   = $request->tipo_direccion;
        $count = DB::table('moises.persona_direccion')->where('id_persona', $id_persona)->count();
        if ($count==0) {
            $save = DB::table('moises.persona_direccion')->insert(
            [
              'id_persona'      => $id_persona,
              'direccion'       => $direccion,
              'id_tipodireccion'  => $tipo_direccion,
            ]);
            if($save){
                    $response=[
                        'success'=> true,
                        'message'=>'Se inserto satisfactoriamente',
                    ];
            }else{
                    $response=[
                        'success'=> false,
                        'message'=>'No se puede insertar',
                    ];
            }
        } else {
            $update = DB::table('moises.persona_direccion')->where('id_persona', $id_persona)->update(
                [
                  'direccion'       => $direccion,
                ]);
                if($update){
                        $response=[
                            'success'=> true,
                            'message'=>'Se modifico satisfactoriamente',
                        ];
                }else{
                        $response=[
                            'success'=> false,
                            'message'=>'No se puede modifico',
                        ];
                }
        }

        return $response;
    }
}