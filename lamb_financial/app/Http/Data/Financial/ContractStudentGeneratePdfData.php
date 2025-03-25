<?php


namespace App\Http\Data\Financial;

use App\Helpers\Helpers;
use App\Http\Data\Academic\AcademicAlumnoContratoData;
use App\Http\Data\FinancesStudent\StudentData;
use App\Http\Data\Financial\PaymentStudentInfoData;
use Exception;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Http;

class ContractStudentGeneratePdfData
{
    public static function generarArchivoContratoAlumno($id_alumno_contrato, $jResponse)
    {
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        // $id_depto = $jResponse["id_depto"];
        $username = $jResponse["email"];

        $forSession = self::datoContrato($id_alumno_contrato);

        $id_depto = $forSession->id_depto ?? $jResponse["id_depto"];

        $sedeParams = [
            array('id_depto' => '1', 'depto' => 'UNIVERSIDAD SEDE', 'address' => 'Villa Unión s/n. , Ñaña, altura del Km. 19 de la carretera Central, distrito de Lurigancho-Chosica, provincia y departamento de Lima', 'place' => 'Villa Unión,', 'resp' => 'Mtra. Mirtha Jeanette Torres Núñez', 'respDocument' => '10600855', 'court' => 'Corte Superior de Justicia de Lima', 'apo' => 'APODERADA'),

            array('id_depto' => '5', 'depto' => 'CAMPUS JULIACA', 'address' => 'Carretera Salida a Arequipa Km. 6 Chullunquiani, Autopista Heroes de la Guerra del Pacifico, provincia San Roman y departamento de Puno', 'place' => 'Chullunquiani,', 'resp' => 'Mtra. Mirtha Jeanette Torres Núñez', 'respDocument' => '10600855', 'court' => 'Corte Superior de Justicia de San Martin', 'apo' => 'APODERADA'),
            array('id_depto' => '6', 'depto' => 'CAMPUS TARAPOTO', 'address' => 'Urb. Santa Lucía, Jr. Los Mártires 340, Tarapoto, provincia San Martin y departamento de San Martin', 'place' => 'Villa Unión,', 'resp' => 'Mtra. Mirtha Jeanette Torres Núñez', 'respDocument' => '10600855', 'court' => 'Corte Superior de Justicia de Puno', 'apo' => 'APODERADA'),
            array('id_depto' => '8', 'depto' => 'ISTAT', 'address' => 'Villa Chullunquiani-Illapuso, altura Km. 6 de la carretera Arequipa, distrito de Juliaca, provincia de San Román, departamento de Puno', 'place' => 'Chullunquiani,', 'resp' => 'Mtra. Mirtha Jeanette Torres Núñez', 'respDocument' => '10600855', 'court' => 'Juliaca', 'apo' => 'APODERADA'),
        ];
        $sedeParam = null;
        foreach ($sedeParams as $struct) {
            if ($struct['id_depto'] == $id_depto) {
                $sedeParam = $struct;
                break;
            }
        }
        if ($valida == 'SI') {
            $response = [];
            try {
                $params = array();
                $photoStudent = '';
                $params['id_contrato'] = $id_alumno_contrato;
                $meses = array("0", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                $ordinal = array("ra", "da", "ra", "ta", "ta", "ta", "ma", "va", "na", "ma");
                $nDate = $sedeParam['place'] . date("d") . ' de ' . $meses[intval(date('n'))] . ' del ' . date('Y') . '.';
                $contractAlumn = collect(StudentData::contratoAlumn($id_alumno_contrato))->first();
                $plains = StudentData::planPagoSemestreDetail($contractAlumn);
                $plain = collect(StudentData::planPago($contractAlumn->id_planpago_semestre))->first();
                $info = collect(PaymentStudentInfoData::getAlumnContractDetail($id_alumno_contrato));
                $photoStudent = Helpers::fotoUser($info['foto'], 'minio-academic');
                $infoPayment = PaymentStudentInfoData::getInforEnrrollment($id_alumno_contrato);
                $enrrollmentDiscountText = PaymentStudentInfoData::getEnrrollmentDiscountText($id_alumno_contrato);
                $debits = collect(StudentData::pagosDC($id_alumno_contrato, 'D'));
                $credits = collect(StudentData::pagosDC($id_alumno_contrato, 'C'))->groupBy(function ($item) {
                    return $item->tipo == 'X' ? 'cobranza suspendida' : 'descuentos';
                });
                $courses = collect(self::enrollmentCoursesSelectedList($id_alumno_contrato));
                $coursesCreditDiscount = self::getCoursesCreditDiscount($id_alumno_contrato);
                $courses = collect($courses['data']);
                $tcredito = $courses->map(function ($carry) {
                    return intval($carry['credito']);
                })->toArray();
                $tcredito = array_sum($tcredito);

                $datosSession = Session::get('datosPrint', []);
                $datosSession['depto'] = $sedeParam['depto'];
                $datosSession['matriculador'] = $forSession->email ?? null;
                Session::put('datosPrint', $datosSession);

                $params_dato = $datosSession;

                $modeContract = self::getModeContract($id_alumno_contrato);

                // CAMBIA EL ESTADO DE HORARIO PARA YA NO SER PINTADO TODOS LOS CURSOS RETIRADOS
                // PARA EL CONTRATO ALUMNO ORIGINAL Y CLONADO
                $courses = collect($courses)->transform(function ($item, $key) use ($modeContract) {
                    if ($modeContract == 'V') {
                        if ($item['codigo_estado_mov_current'] == 'R') {
                            $item['horario']['cruce'] = 'S';
                        }
                    } else {
                        if ($item['codigo_estado_movimiento'] == 'R') {
                            $item['horario']['cruce'] = 'S';
                        }
                    }
                    return $item;
                });
                $creditoInVariation = null;
                $doVariationExist = self::doVariation($id_alumno_contrato);

                if ($modeContract == 'V') {
                    $creditoInVariation = self::getCreditoInVariation($id_alumno_contrato);
                }

                $fotoTest = self::convertImageToBase64($photoStudent);

                //fecha
                $now = date('d/m/Y H:i:s');

                $course_AS = self::enrollmentCoursesSelectedListAS($courses);
                // Filtrar cursos L-31803
                $coursesL31803 = self::filterL31803Courses($courses);
                // dd($coursesL31803);
                // var_dump($courses);
                $content = [
                    'info' => $info,
                    'debits' => $debits,
                    'credits' => $credits,
                    'contract' => $contractAlumn,
                    'horary' => [],
                    //                    'horary' => $horary,
                    'courses' => $courses,
                    'plains' => $plains,
                    'plain' => $plain,
                    'tcredito' => $tcredito,
                    'nowDate' => $nDate,
                    'ordinal' => $ordinal,
                    //'photo' => $photoStudent,
                    'photo' => $fotoTest,
                    'username' => $username,
                    'doVariation' => $doVariationExist,
                    'tcreditoInVariation' => $creditoInVariation,
                    'modeContract' => $modeContract,
                    'coursesCreditDiscount' => $coursesCreditDiscount,
                    'enrrollmentDiscountText' => $enrrollmentDiscountText,
                    'infoPayment' => $infoPayment,
                    'sedeParam' => $sedeParam,
                    'coursesCreditDiscountTotalCp' => self::getSummaryProp($coursesCreditDiscount, 'cp'),
                    'coursesCreditDiscountTotal' => self::getSummaryProp($coursesCreditDiscount, 'subtotal'),
                    'fecha_actual' => $now,
                    'course_AS' => count($course_AS)>0 ? '1' : '0',
                    'coursesL31803' => $coursesL31803
                ];

                //depto 8 -->
                //$pathTemplate = "pdf.finances-student.contract.test";
                $pathTemplate = "pdf.finances-student.contract." . ($contractAlumn->contrato_plantilla ? $contractAlumn->contrato_plantilla : "contract-v2");
                if ($id_depto == '8') {
                    $pathTemplate = "pdf.finances-student.contract." . ($contractAlumn->contrato_plantilla ? $contractAlumn->contrato_plantilla : "contract");
                }
                $data = ["datos_session" => $params_dato, "id_alumno_contrato" => $id_alumno_contrato, "content" => $content, "pathTemplate" => $pathTemplate, "id_user" => $id_user];
                $getvalue = AcademicAlumnoContratoData::validate($data);

                $doc = $getvalue['data'];

                $response = [
                    'success' => true,
                    'message' => "OK",
                    'data' => $doc,
                    'datos' => $content,
                    'type' => $getvalue['type'],
                    'valor_prueba' => "prueba para verifica si se actualizan cambios",
                    'foto' => $info['foto'],
                    'picture' => $photoStudent
                ];
            } catch (Exception $e) {
                $response = [
                    'success' => false,
                    'message' => $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine(),
                    'data' => '',
                    'datos' => '',
                    'type' => '',
                    'valor_prueba' => ""
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => $jResponse["message"],
                'data' => '',
                'datos' => '',
                'type' => '',
                'valor_prueba' => ""
            ];
        }
        return $response;
    }

    public static function convertImageToBase64($imageUrl)
    {
        $imageContent = file_get_contents($imageUrl);

        $imageBase64 = base64_encode($imageContent);

        return "data:image/jpeg;base64," . $imageBase64;
    }

    public static function getSummaryProp($lista, $prop)
    {
        /* return array_reduce($lista, function($carry, $item, $prop) {
             return $carry + $item->$prop;
         });*/
        $sum = 0;
        foreach ($lista as $key => $value) {
            if (isset($value->$prop))
                $sum += $value->$prop;
        }
        return $sum;
    }

    private static function getCreditoInVariation($id_alumno_contrato)
    {
        return DB::table('DAVID.ACAD_ALUMNO_CONTRATO A')
            ->select('A.CREDITOSVAR')
            ->where('A.ID_ALUMNO_CONTRATO', '=', $id_alumno_contrato)
            ->pluck('creditosvar')
            ->first();
    }

    private static function getModeContract($id_alumno_contrato)
    {
        return DB::table('DAVID.ACAD_ALUMNO_CONTRATO A')
            ->select('D.CODIGO')
            ->join('DAVID.ACAD_MATRICULA_DETALLE C', 'A.ID_MATRICULA_DETALLE', '=', 'C.ID_MATRICULA_DETALLE')
            ->join('DAVID.MODO_CONTRATO D', 'C.ID_MODO_CONTRATO', '=', 'D.ID_MODO_CONTRATO')
            ->where('A.ID_ALUMNO_CONTRATO', '=', $id_alumno_contrato)
            ->pluck('codigo')
            ->first();
    }

    private static function doVariation($id_alumno_contrato)
    {
        return DB::table('DAVID.ACAD_ALUMNO_CONTRATO A')
            ->select('D.CODIGO')
            ->join('DAVID.ACAD_ALUMNO_CONTRATO_CURSO B', 'A.ID_ALUMNO_CONTRATO', '=', 'B.ID_ALUMNO_CONTRATO')
            ->join('DAVID.ACAD_MATRICULA_DETALLE C', 'A.ID_MATRICULA_DETALLE', '=', 'C.ID_MATRICULA_DETALLE')
            ->join('DAVID.MODO_CONTRATO D', 'C.ID_MODO_CONTRATO', '=', 'D.ID_MODO_CONTRATO')
            ->where('A.ID_ALUMNO_CONTRATO_ASOCIADO', '=', $id_alumno_contrato)
            ->where('D.CODIGO', '=', 'V')
            ->whereNotNull('B.ID_TIPO_MOVIMIENTO_VAR')
            ->exists();
    }

    public  static function enrollmentCoursesSelectedListAS($courses){
        $data = [];
        foreach ($courses as &$value) {
            if($value['course_as'] == '1'){
               $data[] = $value; 
            }
        }
        return $data;
    }

    public static function filterL31803Courses($courses)
    {
        return $courses->filter(function($course) {
            return (isset($course['codigo_curso_modo']) && $course['codigo_curso_modo'] === 'L-31803') 
                   || (isset($course['codigo_curso_ui']) && $course['codigo_curso_ui'] === 'UI01');
        });
    }

    public static function enrollmentCoursesSelectedList($id_contrato_alumno) //Lista de Cursos Seleccionados por el estudiante en la matrícula
    {
        $response = array();
        try {
            $qry = "select aac.id_persona, aac.id_plan_programa, asm.semestre, asm.id_semestre
        from david.acad_alumno_contrato aac
        inner join david.acad_semestre_programa asp on asp.id_semestre_programa=aac.id_semestre_programa 
                    and aac.id_alumno_contrato=$id_contrato_alumno
        inner join david.acad_semestre asm on asm.id_semestre=asp.id_semestre";

            $oQuery = \DB::select($qry);

            //dd($oQuery);
            if (!array_key_exists(0, $oQuery)) {
                $jResponse['success'] = false;
                $jResponse['message'] = 'El contrato no es válido';
                $jResponse['data'] = [];
                return \Response::json($jResponse, 500);
            }

            $id_plan_programa = $oQuery[0]->id_plan_programa;
            $id_persona = $oQuery[0]->id_persona;
            $semestre = $oQuery[0]->semestre;
            $id_semestre = $oQuery[0]->id_semestre;


            $query82 = "select 'S'
                    from david.Solicitud_Mat_Alum sma 
                    inner join david.tipo_solicitud_matricula tsm on Tsm.Id_Tipo_Solicitud_Matricula=Sma.Id_Tipo_Solicitud_Matricula
                    where tsm.codigo='CC' and sma.estado='3' and sma.id_semestre=$id_semestre and sma.id_persona=$id_persona";

            $carta = \DB::select($query82);

            if (!$carta) {
                $queryAR = "SELECT AP.ID_AREA
                FROM DAVID.ACAD_PLAN_PROGRAMA APP 
                INNER JOIN DAVID.ACAD_PLAN AP ON AP.ID_PLAN=APP.ID_PLAN 
                                            AND APP.ID_PLAN_PROGRAMA=$id_plan_programa";
                $dataArea = \DB::select($queryAR);

                $id_area = $dataArea[0]->id_area;

                $queryPA = "select aca.nombre_curso,count(*) 
                        from david.vw_acad_curso_alumno aca 
                        inner join david.acad_plan_programa app on app.id_plan_programa=aca.id_plan_programa
                                                    and aca.id_persona=$id_persona and aca.id_tipo_condicion=2
                        inner join david.acad_plan ap on ap.id_plan=app.id_plan 
                        where ap.id_area=$id_area
                        and nvl(david.ft_curso_aprobado($id_persona,aca.id_curso_detalle),'n')='n'
                        group by aca.nombre_curso
                        having count(*)>=3";


                $ProbAcad = \DB::select($queryPA);
                $numP = count($ProbAcad);
            } else {
                $queryPA = "select 'S' from dual";
                $ProbAcad = \DB::select($queryPA);
                $numP = 0;
            }

            $StudentCouses = array();

            $query = "select distinct acc.id_carga_curso, acc.id_curso_modo, acm.codigo codigo_curso_modo, ac.codigo codigo_curso_ui ,
                                    Aca.Id_Curso_Alumno,
                                    ac.nombre                                              nombre_curso,
                                    david.ft_profesor_teoria(aca.Id_Carga_Curso)                 nombre_docente,
                                    acd.credito,
                                    acd.ht,
                                    acd.hp,
                                    apc.ciclo,
                                    acd.hnp,
                                    nvl(acc.grupo, 'Único')                                grupo,
                                    aca.id_curso_detalle,
                                    accdt.id_modulo,
                                    accdt.Id_Tipo_Formato_Modulo                           id_formato_modulo,
                                    accdt.id_modulo_detalle,
                                    accdt.horario,
                                    accdt.fecha_inicio,
                                    accdt.fecha_fin,
                                    accdt.cruce,
                                    accdt.nombre,
                                    p.id_persona,
                                    aca.estado,
                                    trim(P.Paterno || ' ' || P.Materno || ' ' || P.Nombre) nombre_docente_practica,
                                    accd.horario                                           horario_practica,
                                    Th.Nombre                                              nombre_practica,
                                    Th.Codigo                                              codigo_practica,
                                    amd.id_modulo_detalle                                  id_modulo_detalle_p,
                                    Amd.Fecha_Inicio                                       fecha_inicio_p,
                                    Amd.Fecha_Inicio                                       fecha_fin_p,
                                    am.id_modulo                                           id_modulo_p,
                                    am.Id_Tipo_Formato_Modulo                              id_formato_modulo_p,
                                    Amd.ciclo                                              ciclo_p,
                                    Am.nombre                                              nombre_p,
                                    Accd.Id_Carga_Curso_Docente,
                                    Vape.Nombre_Escuela,
                                    aca.estado as ESTADO_CURSO,
                                        (select TM.CODIGO
                                            from DAVID.ACAD_ALUMNO_CONTRATO CA
                                                INNER JOIN DAVID.ACAD_ALUMNO_CONTRATO_CURSO CC ON CA.ID_ALUMNO_CONTRATO = CC.ID_ALUMNO_CONTRATO
                                                INNER JOIN DAVID.TIPO_MOVIMIENTO_VAR TM ON CC.ID_TIPO_MOVIMIENTO_VAR = TM.ID_TIPO_MOVIMIENTO_VAR
                                            WHERE CA.ID_ALUMNO_CONTRATO_ASOCIADO = $id_contrato_alumno AND CC.ID_CURSO_ALUMNO = aca.ID_CURSO_ALUMNO) AS CODIGO_ESTADO_MOVIMIENTO,
                                        (select TM.NOMBRE
                                            from DAVID.ACAD_ALUMNO_CONTRATO_CURSO CC
                                                INNER JOIN DAVID.TIPO_MOVIMIENTO_VAR TM ON CC.ID_TIPO_MOVIMIENTO_VAR = TM.ID_TIPO_MOVIMIENTO_VAR
                                            WHERE CC.ID_ALUMNO_CONTRATO = $id_contrato_alumno AND CC.ID_CURSO_ALUMNO = aca.ID_CURSO_ALUMNO) AS ESTADO_MOVIMIENTO_CURRENT,
                                        (select TM.CODIGO
                                    from DAVID.ACAD_ALUMNO_CONTRATO_CURSO CC
                                        INNER JOIN DAVID.TIPO_MOVIMIENTO_VAR TM ON CC.ID_TIPO_MOVIMIENTO_VAR = TM.ID_TIPO_MOVIMIENTO_VAR
                                    WHERE CC.ID_ALUMNO_CONTRATO = $id_contrato_alumno AND CC.ID_CURSO_ALUMNO = aca.ID_CURSO_ALUMNO) AS CODIGO_ESTADO_MOV_CURRENT,
                                    CASE 
								        WHEN tma.codigo = 'AS' THEN '1'
								        ELSE '0'
								    END AS course_as,
								    CASE 
								        WHEN tma.codigo = 'AS' THEN ac.nombre || ' (*)'
								        ELSE ac.nombre
								    END AS nombre_curso_2 -- modo aprendizaje
                    from david.ACAD_ALUMNO_CONTRATO_CURSO aacc
                             INNER JOIN david.acad_curso_alumno aca ON aacc.ID_CURSO_ALUMNO = aca.ID_CURSO_ALUMNO
                             inner join david.acad_carga_curso acc on acc.id_carga_curso = aca.id_carga_curso
                             LEFT JOIN david.TIPO_MODO_APRENDIZAJE tma ON tma.id_modo_aprendizaje = acc.id_modo_aprendizaje -- modo aprendizaje
                             left join david.Acad_Carga_Curso_Docente accd on Accd.Id_Carga_Curso_Docente = Aca.Id_Horario_Practica
                             left join david.Acad_Modulo_Detalle amd on Amd.Id_Modulo_Detalle = accd.Id_Modulo_Detalle
                             left join david.Acad_Modulo am on Am.Id_Modulo = Amd.Id_Modulo
                             inner join david.acad_curso_detalle acd on acd.id_curso_detalle = aca.id_curso_detalle
                             inner join david.acad_curso ac on ac.id_curso = acd.id_curso
                             left join david.Acad_Plan_Programa app on App.Id_Plan_Programa = Aca.Id_Plan_Programa
                             left join david.Acad_Plan_Curso apc on Apc.Id_Plan = App.Id_Plan and Apc.Id_Curso_Detalle = Aca.Id_Curso_Detalle
                             left join david.tipo_horario th on th.Id_Tipo_Horario = Accd.Id_Tipo_Horario
                             left join moises.persona p on p.id_persona = Accd.Id_persona
                             inner join david.acad_semestre_programa asp on Asp.Id_Semestre_Programa = Acc.Id_Semestre_Programa
                             inner join david.acad_semestre asm on asm.id_semestre = asp.id_semestre
                             inner join david.vw_acad_programa_estudio vape on Vape.Id_Programa_Estudio = Asp.Id_Programa_Estudio
                             left join david.acad_curso_modo acm on acc.id_curso_modo = acm.id_curso_modo
                             left join (select amd.id_modulo_detalle,
                                               Amd.Fecha_Inicio,
                                               Amd.Fecha_fin,
                                               horario,
                                               amd.ciclo,
                                               Accd.Id_Carga_Curso,
                                               am.nombre,
                                               am.id_modulo,
                                               Tfm.Id_Tipo_Formato_Modulo,
                                               accd.cruce
                                        from david.Acad_Carga_Curso_Docente accd
                    
                                                 left join david.Acad_Modulo_Detalle amd on Amd.Id_Modulo_Detalle = accd.Id_Modulo_Detalle
                                                 left join david.Acad_Modulo am on Am.Id_Modulo = Amd.Id_Modulo
                                                 left join david.Tipo_Formato_Modulo tfm on Tfm.Id_Tipo_Formato_Modulo = Am.Id_Tipo_Formato_Modulo
                                        where accd.id_tipo_docente = 1) accdt
                                       on accdt.Id_Carga_Curso = aca.Id_Carga_Curso
                    WHERE aacc.id_alumno_contrato = $id_contrato_alumno";

            /*$query = "select distinct nvl(Tmv.Nombre,'Regular') tipo_movimiento,
                    acc.id_carga_curso,Aca.Id_Curso_Alumno,ac.nombre nombre_curso,DAVID.ft_profesor_teoria(aca.Id_Carga_Curso) nombre_docente,
                    acd.credito, acd.ht, acd.hp,apc.ciclo, acd.hnp,nvl(acc.grupo,'Único') grupo,aca.id_curso_detalle,
                    accdt.id_modulo,accdt.Id_Tipo_Formato_Modulo id_formato_modulo,
                    accdt.horario,
                    accdt.fecha_inicio,
                    accdt.fecha_fin,accdt.cruce,
                    accdt.nombre,p.id_persona,aca.estado,
                    trim(P.Paterno||' '||P.Materno||' '||P.Nombre) nombre_docente_practica,accd.horario horario_practica,
                    Th.Nombre nombre_practica, Th.Codigo codigo_practica,Amd.Fecha_Inicio fecha_inicio_p, Amd.Fecha_Inicio fecha_fin_p,
                    am.id_modulo id_modulo_p,am.Id_Tipo_Formato_Modulo id_formato_modulo_p,
                    Amd.ciclo ciclo_p,Am.nombre nombre_p, Accd.Id_Carga_Curso_Docente, Vape.Nombre_Escuela, aca.estado as ESTADO_CURSO,
                    (select TM.CODIGO
                    from DAVID.ACAD_ALUMNO_CONTRATO CA
                        INNER JOIN DAVID.ACAD_ALUMNO_CONTRATO_CURSO CC ON CA.ID_ALUMNO_CONTRATO = CC.ID_ALUMNO_CONTRATO
                        INNER JOIN DAVID.TIPO_MOVIMIENTO_VAR TM ON CC.ID_TIPO_MOVIMIENTO_VAR = TM.ID_TIPO_MOVIMIENTO_VAR
                    WHERE CA.ID_ALUMNO_CONTRATO_ASOCIADO = :id_contrato_alumno_p AND CC.ID_CURSO_ALUMNO = aca.ID_CURSO_ALUMNO) AS CODIGO_ESTADO_MOVIMIENTO,
                                    (select TM.NOMBRE
                    from DAVID.ACAD_ALUMNO_CONTRATO_CURSO CC
                        INNER JOIN DAVID.TIPO_MOVIMIENTO_VAR TM ON CC.ID_TIPO_MOVIMIENTO_VAR = TM.ID_TIPO_MOVIMIENTO_VAR
                    WHERE CC.ID_ALUMNO_CONTRATO = :id_contrato_alumno_p AND CC.ID_CURSO_ALUMNO = aca.ID_CURSO_ALUMNO) AS ESTADO_MOVIMIENTO_CURRENT,
                                        (select TM.CODIGO
                    from DAVID.ACAD_ALUMNO_CONTRATO_CURSO CC
                        INNER JOIN DAVID.TIPO_MOVIMIENTO_VAR TM ON CC.ID_TIPO_MOVIMIENTO_VAR = TM.ID_TIPO_MOVIMIENTO_VAR
                    WHERE CC.ID_ALUMNO_CONTRATO = :id_contrato_alumno_p AND CC.ID_CURSO_ALUMNO = aca.ID_CURSO_ALUMNO) AS CODIGO_ESTADO_MOV_CURRENT
                    from DAVID.Acad_Alumno_Contrato_Curso aacc
                    inner join DAVID.acad_curso_alumno aca on Aca.Id_Curso_Alumno=Aacc.Id_Curso_Alumno
                    inner join DAVID.acad_carga_curso acc on acc.id_carga_curso=aca.id_carga_curso
                    left join DAVID.Acad_Carga_Curso_Docente accd on Accd.Id_Carga_Curso_Docente=Aca.Id_Horario_Practica
                            left join DAVID.Acad_Modulo_Detalle amd on Amd.Id_Modulo_Detalle=accd.Id_Modulo_Detalle
                            left join DAVID.Acad_Modulo am on Am.Id_Modulo=Amd.Id_Modulo
                    inner join DAVID.acad_curso_detalle acd on acd.id_curso_detalle=aca.id_curso_detalle
                    inner join DAVID.acad_curso ac on ac.id_curso=acd.id_curso
                    left join DAVID.Acad_Plan_Programa app on App.Id_Plan_Programa=Aca.Id_Plan_Programa
                    left join DAVID.Acad_Plan_Curso apc on Apc.Id_Plan=App.Id_Plan and Apc.Id_Curso_Detalle=Aca.Id_Curso_Detalle
                    left join DAVID.tipo_horario th on th.Id_Tipo_Horario=Accd.Id_Tipo_Horario
                    left join moises.persona p on p.id_persona=Accd.Id_persona
                    inner join DAVID.acad_semestre_programa asp on Asp.Id_Semestre_Programa=Acc.Id_Semestre_Programa
                    inner join DAVID.acad_semestre asm on asm.id_semestre=asp.id_semestre
                    inner join DAVID.vw_acad_programa_estudio vape on Vape.Id_Programa_Estudio=Asp.Id_Programa_Estudio
                    left join (select Amd.Fecha_Inicio, Amd.Fecha_fin,
                                horario, amd.ciclo, Accd.Id_Carga_Curso, am.nombre,am.id_modulo,Tfm.Id_Tipo_Formato_Modulo,accd.cruce
                                from DAVID.Acad_Carga_Curso_Docente accd inner join DAVID.Tipo_Horario th on Accd.Id_Tipo_Horario=Th.Id_Tipo_Horario and Th.Codigo like 'T%'
                                left join DAVID.Acad_Modulo_Detalle amd on Amd.Id_Modulo_Detalle=accd.Id_Modulo_Detalle
                                left join DAVID.Acad_Modulo am on Am.Id_Modulo=Amd.Id_Modulo
                                left join DAVID.Tipo_Formato_Modulo tfm on Tfm.Id_Tipo_Formato_Modulo=Am.Id_Tipo_Formato_Modulo) accdt
                                on  accdt.Id_Carga_Curso=aca.Id_Carga_Curso
                    left join DAVID.Tipo_Movimiento_Var tmv on Tmv.Id_Tipo_Movimiento_Var=Aacc.Id_Tipo_Movimiento_Var
                    where Id_Alumno_Contrato=:id_contrato_alumno_p order by NVL(CODIGO_ESTADO_MOVIMIENTO , CODIGO_ESTADO_MOV_CURRENT) asc nulls first";

            $Couses = \DB::select($query, ['id_contrato_alumno_p' => $id_contrato_alumno]);*/
            $Couses = \DB::select($query);

            $numCourses = count($Couses);

            $practicas = array();

            $bloqueo_cursos = "N";

            for ($i = 0; $i < $numCourses; $i++) {
                $id_carga_curso = $Couses[$i]->id_carga_curso;
                $id_curso_detalle = $Couses[$i]->id_curso_detalle;
                $nombre_curso_e = $Couses[$i]->nombre_curso;


                //Curso bloqueado
                for ($pa = 0; $pa < $numP; $pa++) {
                    $cur_equiv = $ProbAcad[$pa]->nombre_curso;

                    //echo($cur_equiv.'--------'.$id_curso_detalle.chr(33));

                    $pos = strpos($nombre_curso_e, $cur_equiv);

                    if ($nombre_curso_e == $cur_equiv) {
                        $bloqueo_cursos = "S";

                        $pa = $numP;
                    }
                }

                $reg = 'S';
                $jResponse = array();

                if ($Couses[$i]->estado == '0') {
                    $query0 = "select cupo
                            from david.acad_carga_curso
                            where id_carga_curso=$id_carga_curso";

                    $cupo = \DB::select($query0);

                    if ($cupo[0]->cupo == '0') {
                        $reg = 'S';
                    } else {
                        $query1 = "select count(id_carga_curso) as cupos_t
                                from david.acad_curso_alumno acu
                                where id_carga_curso = $id_carga_curso
                                and estado in ('1','M')";

                        $cupos_t = \DB::select($query1);

                        $num_cupos = $cupo[0]->cupo - $cupos_t[0]->cupos_t;

                        if ($num_cupos >= 1) {
                            $reg = 'S';
                        } else {
                            $reg = 'N';
                        }
                    }
                }

                if ($reg == 'N') {
                    StudentCourses::find($Couses[$i]->id_curso_alumno)->delete();
                } else {
                    $horario = array(
                        'id_modulo' => $Couses[$i]->id_modulo,
                        'id_formato_modulo' => $Couses[$i]->id_formato_modulo,
                        'tipo' => 'T',
                        'horario' => $Couses[$i]->horario,
                        'fecha_inicio' => $Couses[$i]->fecha_inicio,
                        'fecha_fin' => $Couses[$i]->fecha_fin,
                        'ciclo' => $Couses[$i]->ciclo,
                        'nombre_modulo' => $Couses[$i]->nombre,
                        'cruce' => $Couses[$i]->cruce
                    );


                    $practicas[$i] = array();


                    if ($Couses[$i]->codigo_practica) {
                        $horario_practica = array(
                            'id_modulo' => $Couses[$i]->id_modulo_p,
                            'id_formato_modulo' => $Couses[$i]->id_formato_modulo_p,
                            'tipo' => $Couses[$i]->codigo_practica,
                            'horario' => $Couses[$i]->horario_practica,
                            'fecha_inicio' => $Couses[$i]->fecha_inicio_p,
                            'fecha_fin' => $Couses[$i]->fecha_fin_p,
                            'ciclo' => $Couses[$i]->ciclo_p,
                            'nombre_modulo' => $Couses[$i]->nombre_p
                        );

                        $practicas[$i] = array(
                            'id_carga_curso_docente' => $Couses[$i]->id_carga_curso_docente,
                            'nombre' => $Couses[$i]->nombre_practica,
                            'codigo' => $Couses[$i]->codigo_practica,
                            'docente' => $Couses[$i]->nombre_docente_practica,
                            'ciclo' => $Couses[$i]->ciclo_p,
                            'cupo' => null,
                            'cupo_disponible' => null,
                            'horario' => $horario_practica
                        );
                    }

                    if ($practicas[$i]) {
                        $practicass[$i] = array($practicas[$i]);
                    } else {
                        $practicass[$i] = array();
                    }


                    if ($bloqueo_cursos == "S") {
                        $detail[$i] = array(
                            'type' => "2",
                            'value' => "Desaprobado 3 veces el curso de " . $Couses[$i]->nombre_curso
                        );

                        $details[$i] = array();

                        if ($detail[$i]) {
                            $details[$i] = array($detail[$i]);
                        }

                        $status = array(
                            'status' => false,
                            'message' => 'Curso Bloqueado',
                            'detail' => $details[$i]
                        );
                    } else {
                        $details[$i] = array();

                        $status = array(
                            'status' => true,
                            'message' => 'Ok',
                            'detail' => $details[$i]
                        );
                    }

                    $StudentCouses[$i] = array(
                        'id_curso_alumno' => $Couses[$i]->id_curso_alumno,
                        'nombre_curso' => $Couses[$i]->nombre_curso,
                        'nombre_curso_2' => $Couses[$i]->nombre_curso_2,
                        'id_curso_modo' => $Couses[$i]->id_curso_modo,
                        'codigo_curso_modo' => $Couses[$i]->codigo_curso_modo,
                        'codigo_curso_ui' => $Couses[$i]->codigo_curso_ui,
                        'nombre_docente' => $Couses[$i]->nombre_docente,
                        'credito' => $Couses[$i]->credito,
                        'ht' => $Couses[$i]->ht,
                        'hp' => $Couses[$i]->hp,
                        'hnp' => $Couses[$i]->hnp,
                        'horario' => $horario,
                        'ciclo' => $Couses[$i]->ciclo,
                        'grupo' => $Couses[$i]->grupo,
                        'practicas' => $practicass[$i],
                        'estado' => $status,
                        'nombre_escuela' => $Couses[$i]->nombre_escuela,
                        'estado_curso' => $Couses[$i]->estado_curso,
                        'codigo_estado_movimiento' => $Couses[$i]->codigo_estado_movimiento,
                        'estado_movimiento_current' => $Couses[$i]->estado_movimiento_current,
                        'codigo_estado_mov_current' => $Couses[$i]->codigo_estado_mov_current,
                        'course_as' => $Couses[$i]->course_as
                    );
                }
            }

            if ($StudentCouses) {
                $response['success'] = true;
                $response['message'] = 'OK';
                $response['data'] = $StudentCouses;
            } else {
                $response['success'] = false;
                $response['message'] = 'Sin resultados';
                $response['data'] = [];
            }
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
            $response['data'] = [];
        }
        return $response;
    }

    public static function getCoursesCreditDiscount($idContractStudent)
    {
        $respo = array();
        $valoriation = collect(DB::select("select c.unitario
                from eliseo.vw_mat_criterio a,
                     mat_criterio_semestre b,
                     mat_alumno_contrato_det c
                where a.id_criterio = b.id_criterio
                  and b.id_criterio_semestre = c.id_criterio_semestre
                  and c.id_alumno_contrato = $idContractStudent
                  and a.codigo in ('DSCTOCPRE', 'DSCTOCPBE', 'DSCTOCPCA')"))
            ->pluck('unitario')
            ->first();
        $valoriation = $valoriation ? intval($valoriation) : null;
        $data = DB::table('eliseo.VW_MAT_CURSOS_PLAN_ALUMNO')
            ->select('ACAD_CURSO.NOMBRE', 'VW_MAT_CURSOS_PLAN_ALUMNO.CP')
            ->join("david.ACAD_CURSO_DETALLE", 'ACAD_CURSO_DETALLE.ID_CURSO_DETALLE', '=', 'VW_MAT_CURSOS_PLAN_ALUMNO.ID_CURSO_DETALLE')
            ->join("david.ACAD_CURSO", 'ACAD_CURSO_DETALLE.ID_CURSO', '=', 'ACAD_CURSO.ID_CURSO')
            ->where('VW_MAT_CURSOS_PLAN_ALUMNO.ID_ALUMNO_CONTRATO', $idContractStudent)
            ->where('VW_MAT_CURSOS_PLAN_ALUMNO.DESCUENTO_CREDITO', 'S') // sdsdfd sdfsd sfdgsg
            ->get();
        foreach ($data as $item) {
            $item->valorizacion = $valoriation;
            if ($valoriation) {
                $item->subtotal = $valoriation * intval($item->cp);
            } else {
                $item->subtotal = null;
            }
            array_push($respo, $item);
        }
        return $respo;
    }
    public static function datoContrato($id_alumno_contrato)
    {
        $results = DB::table('eliseo.venta as a')
        ->join('eliseo.venta_detalle as b', 'a.id_venta', '=', 'b.id_venta')
        ->join('david.acad_alumno_contrato as aac', 'aac.id_alumno_contrato', '=', 'b.id_alumno_contrato')
        ->join('eliseo.users as c', 'aac.id_usuario_reg', '=', 'c.id')
        ->join('eliseo.conta_entidad as e', 'e.id_entidad', '=', 'a.id_entidad')
        ->join('eliseo.conta_empresa as f', 'f.id_empresa', '=', 'e.id_empresa')
        ->join('eliseo.conta_entidad_depto as h', function($join) {
            $join->on('a.id_depto', '=', 'h.id_depto')
                 ->on('h.id_entidad', '=', 'a.id_entidad');
        })
        ->select('aac.id_usuario_reg', 'c.email', 'a.id_entidad', 'e.nombre', 'f.razon_social', 'a.id_depto', 'h.nombre as depto')
        ->where('b.id_alumno_contrato', $id_alumno_contrato)
        ->distinct()
        ->first();

        return $results;
    }
}
