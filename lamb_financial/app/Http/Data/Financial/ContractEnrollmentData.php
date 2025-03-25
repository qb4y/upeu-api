<?php


namespace App\Http\Data\Financial;

use App\Helpers\Helpers;
use App\Http\Data\Academic\AcademicAlumnoContratoData;
use App\Http\Data\FinancesStudent\StudentData;
use App\Models\StudentCourses;
use Exception;
use Illuminate\Support\Facades\DB;
use PDO;
use Session;

class ContractEnrollmentData
{

    public static function generateContract($params, $response)
    {
        $nerror = 0;
        $msgerror = "";
        $id_venta = "";
        $id_contrato_alumno = $params['id_alumno_contrato'];

        for ($x = 1; $x <= 200; $x++) {
            $msgerror .= "0";
        }

        $entDep = self::getEntDepStudent($id_contrato_alumno);
        //        dd($entDep);

        if ($entDep) {

            $id_entidad = $entDep->id_entidad;
            $id_depto = $entDep->id_depto;
            $id_persona = $entDep->id_persona;
            $id_anho = intval(date('Y'));
            $id_mes = intval(date('m'));
            $es_virtual = 'S'; // S=es portal del alumno
            $id_tipo_venta = 1; // 1=ventas academicas

            //            dd($id_anho, $id_mes);

            DB::beginTransaction();

            try {
                //                dd($id_contrato_alumno, $id_entidad,$id_depto, $id_anho, $id_mes, $id_persona, $es_virtual ,$id_tipo_venta);
                //SP_GENERAR_VENTA_MATRICULA
                $stmt = DB::getPdo()->prepare("BEGIN PKG_FINANCES_STUDENTS.SP_GENERAR_VENTA_MATRICULA(
                                        :P_ID_ALUMNO_CONTRATO,
                                        :P_ID_ENTIDAD,
                                        :P_ID_DEPTO,
                                        :P_ID_ANHO,
                                        :P_ID_MES,
                                        :P_ID_PERSONA,
                                        :P_ES_VIRTUAL,
                                        :P_ID_TIPOVENTA,
                                        :P_ID_VENTA,
                                        :P_ERROR,
                                        :P_MSGERROR
                                     ); END;");
                $stmt->bindParam(':P_ID_ALUMNO_CONTRATO', $id_contrato_alumno, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
                $stmt->bindParam(':P_ES_VIRTUAL', $es_virtual, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_TIPOVENTA', $id_tipo_venta, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
                $stmt->execute();

                if ($nerror == 0) {
                    //DB::rollBack();
                    //                 /*   dd($id_venta);
                    /* $params['id_venta'] = $id_venta;
                    $params['es_virtual'] = $es_virtual;
                    //dd($nerror, $params);
                    $spec = self::generateSpecialDescto($params);
                    if($spec['nerror'] == 0) {
                        DB::commit();
                    } else {
                        DB::rollBack();
                    }*/

                    //generar archivo
                    // $genrateContract = ContractStudentGeneratePdfData::generarArchivoContratoAlumno($id_contrato_alumno, $response);
                    // $msgerror = $genrateContract['message'];
                    DB::commit();
                } else {
                    $nerror = 1;
                    $msgerror = $msgerror;
                    DB::rollBack();
                }
            } catch (\PDOException $e) {
                $nerror = 1;
                $msgerror = $e->getMessage();
                DB::rollBack();
            } catch (Exception $e) {
                $nerror = 1;
                $msgerror = $e->getMessage();
                DB::rollBack();
            }
        } else {
            $nerror = 1;
            $msgerror = 'No existe entidad o departamento';
        }

        $return = [
            'nerror' => $nerror,
            'msgerror' => $msgerror,
            'id_venta' => $id_venta
        ];

        return $return;
    }



    protected static function getEntDepStudent($id_alumno_contrato)
    {
        return DB::table('DAVID.ACAD_ALUMNO_CONTRATO as a')
            ->select(
                'd.ID_ENTIDAD',
                DB::raw("decode(d.id_sede,1,'1',2,'5',3,'6',4,'8') as ID_DEPTO"),
                'a.ID_PERSONA'
            )
            ->join('DAVID.ACAD_PLAN_PROGRAMA b', 'a.ID_PLAN_PROGRAMA', '=', 'b.ID_PLAN_PROGRAMA')
            ->join('DAVID.ACAD_PROGRAMA_ESTUDIO c', 'b.ID_PROGRAMA_ESTUDIO', '=', 'c.ID_PROGRAMA_ESTUDIO')
            ->join('ELISEO.ORG_SEDE_AREA d', 'c.ID_SEDEAREA', '=', 'd.ID_SEDEAREA')
            ->where('a.ID_ALUMNO_CONTRATO', '=', $id_alumno_contrato)
            ->first();
    }

    protected static function generateSpecialDescto($params)
    {
        $importe = 0;
        $nerror = 0;
        $msgerror = "";
        $id_nota = "";
        for ($x = 1; $x <= 200; $x++) {
            $msgerror .= "0";
        }

        $contract = DB::table("DAVID.ACAD_ALUMNO_CONTRATO")
            ->where("ACAD_ALUMNO_CONTRATO.ID_ALUMNO_CONTRATO", $params['id_alumno_contrato'])
            ->get()
            ->first();
        $id_cliente = $contract->id_persona;
        $id_persona = $params['id_user']; // id matriculador
        $es_virtual = $params['es_virtual']; // s si es virtual
        $id_venta = $params['id_venta']; // obtener id_venta
        $importe = $contract->imp_dscto; // Obtener importe de descuento
        $glosa = 'NOTA DE CRDITO A MAT';

        if ($id_venta and $importe > 0) {
            DB::beginTransaction();

            try {

                $stmt = DB::getPdo()->prepare("BEGIN PKG_FINANCES_STUDENTS.SP_NOTA_MAT(
                        :P_ID_CLIENTE,
                        :P_ID_PERSONA,
                        :P_ID_VENTA,
                        :P_GLOSA,
                        :P_IMPORTE,
                        :P_ES_VIRTUAL,
                        :P_ERROR,
                        :P_MSN,
                        :P_ID_NOTA)
                        ;END;");
                $stmt->bindParam(':P_ID_CLIENTE', $id_cliente, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_STR);
                $stmt->bindParam(':P_GLOSA', $glosa, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
                $stmt->bindParam(':P_ES_VIRTUAL', $es_virtual, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN', $msgerror, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_NOTA', $id_nota, PDO::PARAM_INT);
                $stmt->execute();

                if ($nerror == 0) {
                    //                    dd($nerror);
                    //DB::rollBack();
                    //dd('SUCEESS',$nerror);
                    DB::commit();
                } else {
                    $nerror = 1;
                    $msgerror = $msgerror;
                    DB::rollBack();
                }
            } catch (\PDOException $e) {
                $nerror = 1;
                $msgerror = $e->getMessage();
                DB::rollBack();
            } catch (Exception $e) {
                $nerror = 1;
                $msgerror = $e->getMessage();
                DB::rollBack();
            }
        }

        $return = [
            'nerror' => $nerror,
            'msgerror' => $msgerror,
            'id_nota' => $id_nota
        ];
        return $return;
    }

}
/*:P_ID_ALUMNO_CONTRATO IN NUMBER,
:P_ID_ENTIDAD NUMBER,
:P_ID_DEPTO VARCHAR2,
:P_ID_ANHO NUMBER,
:P_ID_MES NUMBER,
:P_ID_PERSONA NUMBER,
:P_ES_VIRTUAL VARCHAR2,
:P_ID_TIPOVENTA number,
:P_ID_VENTA OUT NUMBER,
:P_ERROR OUT number,
:P_MSGERROR out varchar2,*/
