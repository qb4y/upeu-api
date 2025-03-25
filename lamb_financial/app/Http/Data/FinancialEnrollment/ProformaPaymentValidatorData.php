<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 03/03/20
 * Time: 05:18 PM
 */

namespace App\Http\Data\FinancialEnrollment;


use App\Http\Data\Financial\PaymentStudentInfoData;
use Illuminate\Support\Facades\DB;

class ProformaPaymentValidatorData
{

    protected static function getEntDepStudent($id_alumno_contrato){
        return DB::table('DAVID.ACAD_ALUMNO_CONTRATO as a')
            ->select(
                'd.ID_ENTIDAD',
                DB::raw('SUBSTR(d.ID_DEPTO,1,1) as ID_DEPTO'),
                'a.ID_PERSONA'
            )
            ->join('DAVID.ACAD_PLAN_PROGRAMA b', 'a.ID_PLAN_PROGRAMA', '=', 'b.ID_PLAN_PROGRAMA')
            ->join('DAVID.ACAD_PROGRAMA_ESTUDIO c', 'b.ID_PROGRAMA_ESTUDIO', '=', 'c.ID_PROGRAMA_ESTUDIO')
            ->join('ELISEO.ORG_SEDE_AREA d', 'c.ID_SEDEAREA', '=', 'd.ID_SEDEAREA')
            ->where('a.ID_ALUMNO_CONTRATO','=', $id_alumno_contrato)
            ->first();

    }

    // obtiene la accion de rol asignado al usuario
    public static function getActionModuleUser($id_persona, $id_entidad, $url){
        $data = array('valid'=> false, 'value' => null, 'msg'=> null);
        $query = "SELECT a.NOMBRE ,a.CLAVE FROM LAMB_ACCION a left join LAMB_MODULO b ON (a.ID_MODULO = b.ID_MODULO)
        where b.URL = '$url' and exists(select * from LAMB_ROL_MODULO_ACCION e left join LAMB_USUARIO_ROL u on (e.ID_ROL = u.ID_ROL)
        where u.ID_PERSONA=$id_persona and u.ID_ENTIDAD=$id_entidad AND e.ID_MODULO=a.ID_MODULO AND e.ID_ACCION= a.ID_ACCION)";
        $collection = collect(DB::select($query));
        if ($collection->count() > 1) {
            $data['valid'] = false;
            $data['msg'] = 'No puede tener mas de un rol especifico en configuracion de matricula financiera';
        } else if ( $collection->count() == 1){
            $data['valid'] = true;
            $data['value'] = $collection->pluck('clave')->first();
        } else {
            $data['valid'] = false;
            $data['msg'] = 'No tiene permiso para realizar una accion en matricula financiera';
        }
        return $data;

    }

    protected static function getImporteRol($codeRol, $idEntidad, $idDepto) {
        $importe = 0;

        /*switch ($codeRol) {
            case "JEFE_FINANZAS":
                return 5000;
                break;
            case "TESORERO":
                return 1500;
                break;
            case "FINANZAS_ALUMNO":
                return 700;
                break;
            case "DENOMINACIONAL":
                return 3000;
                break;
            default:
                return 0;
                break;
        }*/

        $data = DB::table('LAMB_ROL_ENTIDAD_DEPTO a')
            ->select('a.IMPORTE')
            ->where([
                ['a.ID_ENTIDAD','=', $idEntidad],
                ['a.ID_DEPTO','=', $idDepto],
                ['a.CODIGO_ROL','=', $codeRol],
                ['a.ESTADO','=', 1],
            ])
            ->first();

        if (!empty($data)) {
            $importe = $data->importe;
        } else {
            abort(500, 'No se encontro importe configurado para el rol: '.$codeRol);
        }
        return $importe;
    }

    protected static function isBeca18($id_persona) {
        return DB::table('DAVID.ACAD_ALUMNO_BECA a')
            ->where([
                ['a.ID_PERSONA','=', $id_persona],
                ['a.ESTADO','=', 1],
            ])
            ->whereNotNull('a.ID_TIPO_BECA_ESTATAL')
            ->exists();
    }



    protected static function getRolesUser($user) {
        return DB::table('ELISEO.LAMB_USUARIO_ROL a')
            ->join('ELISEO.LAMB_ROL b', 'a.ID_ROL','=','b.ID_ROL')
            ->select('b.NOMBRE')
            ->where([
                ['a.ID_PERSONA','=',$user['id_user']],
                ['a.ID_ENTIDAD','=',$user['id_entidad']],
                ['b.ESTADO','=','1']
            ])
            ->pluck('nombre');
    }

    protected static function chunkDebt($request, $importe) {
        $data = array('valid'=> false, 'msg'=> null);
        $sub = PaymentStudentInfoData::calculationDetail($request->id_alumno_contrato, $request->id_anio);
        if ($sub && $sub['studentBalance']) {

            $balanceTotal = floatval($sub['studentBalance']->total);

            if ($sub['studentBalance']->signo == '1') { // mi deuda

                if ($balanceTotal < floatval($importe)) {
                    $data['valid'] = true;
                    $data['msg'] = 'Puede generar el contrato, deuda del estudiante '.$balanceTotal.' es menor a '.$importe.' ';
                } else {
                    $data['valid'] = false;
                    $data['msg'] = 'No puede generar el contrato, deuda del estudiante '.$balanceTotal.' debe ser menor a '.$importe.' ';
                }

            } else {
                $data['valid'] = true;
                $data['msg'] = 'Puede generar el contrato';
            }

        } else {
            $data['valid'] = false;
            $data['msg'] = 'Accion no disponible error en bakend (saldo disponible)';
        }
        return $data;

    }

    protected static function chunkCajero($request) {
        // validar importe deposito
        $data = array('valid'=> false, 'msg'=> null);
        $sub = PaymentStudentInfoData::calculationDetail($request->id_alumno_contrato, $request->id_anio);
        if ($sub && $sub['contractStudent'] && $sub['studentBalance']) {
            $totalDep = 0;
            if ($sub['contractStudent']->id_planpago == '1' && $sub['contractStudent']->mensual == '0') {
                $totalDep = floatval($sub['contractStudent']->contado);
            } elseif ($sub['contractStudent']->id_planpago == '2' && $sub['contractStudent']->mensual != '0') {
                $totalDep = floatval($sub['contractStudent']->matricula1cuota);
            }

            $balanceTotal = floatval($sub['studentBalance']->total);
            $importeDeposito = 0;

            $caso = '';

            if ($sub['studentBalance']->signo == '-1' && $balanceTotal >= $totalDep) {
                $importeDeposito = 0;
                $caso = 'Saldo a favor puede generar contrato, no es necesario depositar';
            }elseif ($sub['studentBalance']->signo == '-1' && $balanceTotal < $totalDep) {
                $caso = 'Saldo insuficiente necesita depositar'.($totalDep - $balanceTotal);
                $importeDeposito = ($totalDep - $balanceTotal);
            } elseif ($sub['studentBalance']->signo == '1') {
                $caso = 'Alumno con deuda de '.$balanceTotal.' total de deposito '.($totalDep + $balanceTotal);
                $importeDeposito = ($totalDep + $balanceTotal);
            } elseif ($sub['studentBalance']->signo == '0') {
                $caso = 'Saldo 0,  total de deposito '.($totalDep + $balanceTotal);
                $importeDeposito = ($totalDep + $balanceTotal);
            }

            if ($request->importe_deposito >= $importeDeposito) {
                $data['valid'] = true;
                $data['msg'] = 'Puede generar el contrato';
            } else {
                $data['valid'] = false;
                $data['msg'] = $caso;
            }
        } else {
            $data['valid'] = false;
            $data['msg'] = 'Accion no disponible error en bakend (detalle de calculo)';
        }
        return $data;
    }

    public static function validator($request, $user) {

        $preData = array('enable' => false, 'enableDeposit' => false, 'rol' => null);
        $data = array('valid'=> false, 'data' => null, 'msg'=> null);

        $student = self::getEntDepStudent($request->id_alumno_contrato);
        if ($student) {
            // $id_entidad = $student->id_entidad;
            // $id_depto = $student->id_depto;
            $id_persona = $student->id_persona;

            $rol = self::getActionModuleUser($user['id_user'], $user['id_entidad'], 'enrollment/financial-enrollment');

            if ($rol['valid']) {
                $data['valid'] = $rol['valid'];
                $preData['rol'] = $rol['value'];
                switch ($rol['value']) {
                    case "GERENTE":
                        $importe = self::getImporteRol("GERENTE", $user['id_entidad'], $user['id_depto']);
                        $chunkDebt = self::chunkDebt($request, $importe);

                        $valid = $chunkDebt['valid'];
                        $data['msg'] = $chunkDebt['msg'];

                        $data['valid'] = $valid;
                        $preData['enable'] = $valid;
                        $preData['enableDeposit'] = false;
                        $data['data'] = $preData;
                        break;
                    case "JEFE_FINANZAS":
                        $isBeca = self::isBeca18($id_persona);
                        $importe = self::getImporteRol("JEFE_FINANZAS", $user['id_entidad'], $user['id_depto']);
                        $chunkDebt = self::chunkDebt($request, $importe);
                        $valid = $isBeca || $chunkDebt['valid'];
                        $data['msg'] = $chunkDebt['msg'];
                        if ($isBeca) {
                            $data['msg'] = $data['msg'].' (alumno beca 18)';
                        }
                        $data['valid'] = $valid;
                        $preData['enable'] = $valid;
                        $preData['enableDeposit'] = false;
                        $data['data'] = $preData;
                        break;
                    case "TESORERO":
                        $isBeca = self::isBeca18($id_persona);
                        $importe = self::getImporteRol("TESORERO", $user['id_entidad'], $user['id_depto']);
                        $chunkDebt = self::chunkDebt($request, $importe);
                        $valid = $isBeca || $chunkDebt['valid'];
                        $data['msg'] = $chunkDebt['msg'];
                        if ($isBeca) {
                            $data['msg'] = $data['msg'].' (alumno beca 18)';
                        }
                        $data['valid'] = $valid;
                        $preData['enable'] = $valid;
                        $preData['enableDeposit'] = false;
                        $data['data'] = $preData;
                        break;
                    case "CAJERO":
                        $chunkCajero = self::chunkCajero($request);
                        $data['valid'] = $chunkCajero['valid'];
                        $data['msg'] = $chunkCajero['msg'];
                        $preData['enable'] = $chunkCajero['valid'];
                        $preData['enableDeposit'] = true;
                        $data['data'] = $preData;
                        break;
                    case "FINANZAS_ALUMNO":
                        $isBeca = self::isBeca18($id_persona);
                        $importe = self::getImporteRol("FINANZAS_ALUMNO", $user['id_entidad'], $user['id_depto']);
                        $chunkDebt = self::chunkDebt($request, $importe);
                        $valid = $isBeca || $chunkDebt['valid'];
                        $data['msg'] = $chunkDebt['msg'];
                        if ($isBeca) {
                            $data['msg'] = $data['msg'].' (alumno beca 18)';
                        }
                        $data['valid'] = $valid;
                        $preData['enable'] = $valid;
                        $preData['enableDeposit'] = false;
                        $data['data'] = $preData;
                        break;
                    case "DENOMINACIONAL":
                        $isBeca = self::isBeca18($id_persona);
                        $importe = self::getImporteRol("DENOMINACIONAL", $user['id_entidad'], $user['id_depto']);
                        $chunkDebt = self::chunkDebt($request, $importe);
                        $valid = $isBeca || $chunkDebt['valid'];
                        $data['msg'] = $chunkDebt['msg'];
                        if ($isBeca) {
                            $data['msg'] = $data['msg'].' (alumno beca 18)';
                        }
                        $data['valid'] = $valid;
                        $preData['enable'] = $valid;
                        $preData['enableDeposit'] = false;
                        $data['data'] = $preData;
                        break;
                    default:
                        $data['valid'] = false;
                        $data['msg'] = 'Error en configuracion de rol';
                        break;
                }
            } else {
                $data['valid'] = $rol['valid'];
                $data['msg'] = $rol['msg'];
            }

        } else {
            $data['valid'] = false;
            $data['msg'] = 'Accion no disponible error al encontrar datos del alumno';
        }

        return $data;
    }

}