<?php


namespace App\Http\Controllers\Report\FinancesStudent;

use Academic\Eloquent\Entities\StudentCourses;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Data\Academic\AcademicAlumnoContratoData;
use App\Http\Data\FinancesStudent\StudentData;
use App\Http\Data\Financial\ContractStudentGeneratePdfData;
use App\Http\Data\Financial\PaymentStudentInfoData;
use App\Http\Data\GlobalMethods;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DOMPDF;
use Session;
use App\TraitResponse\ApiResponse;
class StudentContractController extends Controller
{
    use ApiResponse;
    
    private $request;
    private $_tempCrossing = [];
    private $tempo_arra = [];
    private $types = [
        1 => 'R',
        2 => 'B',
        3 => 'M',
    ];

    public function __construct(Request $request)
    {
        $this->request = $request;
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


    public function studentContracts($id_alumno_contrato)
    {
        $response = GlobalMethods::authorizationLamb($this->request);

        $username = $response["email"];

        if ($response["valida"] == 'SI') {
            $meses = array("0", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
            $ordinal = array("ra", "da", "ra", "ta", "ta", "ta", "ma", "va", "na", "ma");
            $nDate = 'Villa Unión, ' . date("d") . ' de ' . $meses[intval(date('n'))] . ' del ' . date('Y') . '.';
            $contractAlumn = collect(StudentData::contratoAlumn($id_alumno_contrato))->first();
            $plains = StudentData::planPagoSemestreDetail($contractAlumn);
            $plain = collect(StudentData::planPago($contractAlumn->id_planpago_semestre))->first();
            $info = collect(PaymentStudentInfoData::getAlumnContractDetail($id_alumno_contrato));
            $debits = collect(StudentData::pagosDC($id_alumno_contrato, 'D'));
            $credits = collect(StudentData::pagosDC($id_alumno_contrato, 'C'));
//            dd($info, $plains, $contractAlumn, $debits, $credits);
            $courses = collect($this->enrollmentCoursesSelectedList($id_alumno_contrato));
            if ($courses['success'] == true) {
                $courses = collect($courses['data']);
//                dd($courses);
                $tcredito = $courses->map(function ($carry) {
                    return intval($carry['credito']);
                })->toArray();
                $tcredito = array_sum($tcredito);
                $horary = collect($this->ScheduleListProgram($id_alumno_contrato))->map(function ($item) {
                    $item['header'] = self::getKeys($item['codigo']);
                    return $item;
                });
                $horary = $horary->toArray();


                self::build($courses, $horary);

                $horary = collect($horary)->groupBy('nombre');
                $horary = $horary->map(function ($modulos) {
                    $modulos = collect($modulos)->groupBy(function ($item) {
                        return date('d-m-Y', strtotime($item["fecha_inicio"])) . " - " . date('d-m-Y', strtotime($item["fecha_fin"]));
                    });
                    $modulos = $modulos->map(function ($mo, $key2) {
                        $mo = $mo->map(function ($itemMo) {
                            $itemMo['header'] = $itemMo['header'];
                            $itemMo['horario'] = collect($itemMo['horario'])->groupBy('turno')->toArray();
                            return $itemMo;
                        });
                        return $mo;
                    });

                    return $modulos;

                })->toArray();

                $content = [
                    'info' => $info,
                    'debits' => $debits,
                    'credits' => $credits,
                    'contract' => $contractAlumn,
                    'horary' => $horary,
                    'courses' => $courses,
                    'plains' => $plains,
                    'plain' => $plain,
                    'tcredito' => $tcredito,
                    'nowDate' => $nDate,
                    'ordinal' => $ordinal,
                    'username' => $username// OBLIGATORIO
                ];
                if ($plain and $contractAlumn) {
                    $pdf = DOMPDF::loadView('pdf.finances-student.contract', $content)->setPaper('a4', 'portrait'); //landscape
                    $doc = base64_encode($pdf->stream('print.pdf'));
                    $html = view('pdf.finances-student.contract', $content)->render();

                    // FIN DE CAMBIOS ENTRANTS
                    $response["code"] = 200;
                    $response['success'] = true;
                    $response['message'] = 'OK';
                    $response['data'] = $doc;
                    $response['content'] = $content;
                } else {
                    $response["code"] = '500';
                    $response['success'] = false;
                    $response['message'] = $plain ? 'error al procesar información' : 'Plan de alumno no identificado';
                    $response['data'] = null;
                }
            } else {
                $response["code"] = 500;
                $response['success'] = false;
                $response['message'] = $courses['message'];
                $response['data'] = null;
            }

        }
        return response()->json($response, $response["code"]);
    }

    public function studentContract($id_alumno_contrato, Request $request)
    {
//        $id_alumno_contrato = 297469;
        // $storageAcademic = Storage::disk('minio-academic');
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            // var_dump(value: "Load data");
            //validation minio
            $adjunto_validate = AcademicAlumnoContratoData::getArchivoAdjunto($id_alumno_contrato);
            if(isset($adjunto_validate)){
                // $jResponse = [];
                $code = 200;
                if(\Storage::cloud()->exists($adjunto_validate)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Ok";
                    $fileContent = \Storage::cloud()->get($adjunto_validate);
                    $doc = base64_encode($fileContent);
                    $jResponse['type'] = "read minio";
                    $jResponse['data'] = $doc;
                }else{
                    $generateContractStudent = ContractStudentGeneratePdfData::generarArchivoContratoAlumno($id_alumno_contrato, $jResponse);
                    if ($generateContractStudent['success']) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = $generateContractStudent['message'];
                        $jResponse['type'] = "read minio";
                        $jResponse['data'] = $generateContractStudent['data'];
                    } else {
                        $code = 404;
                        $jResponse['success'] = false;
                        $jResponse['message'] = "No se encontro el adjunto en minio";
                        $jResponse['data']=[];
                    }
                }
                return response()->json($jResponse, $code);
            }else{
                $generateContractStudent = ContractStudentGeneratePdfData::generarArchivoContratoAlumno($id_alumno_contrato, $jResponse);
                
                return response()->json($generateContractStudent);
            }
        } else {
            $mensaje = $jResponse["message"];
        }


        $pdf = DOMPDF::loadView('pdf.error', [
            'mensaje' => $mensaje
        ])->setPaper('a4', 'portrait');


        $doc = base64_encode($pdf->stream('print.pdf'));
        $jResponse = [
            'success' => true,
            'message' => "OK",
            'data' => $doc
        ];
        return response()->json($jResponse);

    }
    public function getSummaryProp($lista, $prop)
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

    public static function enrollmentHttpClient($endPoint, $queryParams, $response)
    {
        $recover = null;
        $enrollmentDomain = 'https://api-lamb-academic.upeu.edu.pe/enrollment';
        $token = $response['token'];
        $url = $enrollmentDomain . $endPoint . self::getQueryParams($queryParams);
        $client = new Client();

        $res = $client->request('GET', $url, [
            'headers' => ['Authorization' => $token],
            'http_errors' => false
        ]);
        $json = $res->getBody()->getContents();
//        $json = $res->getStatusCode();
        $incomen = json_decode($json);
        $success = false;
        $success = $incomen and property_exists($incomen, 'success') ? $incomen->success : false;
        if ($success) {
            $recover = $incomen->data;
            // validate types recover -> is array or  is instance
        }
        return $recover;
    }

    public static function getQueryParams($qp)
    {
        $resp = '?';
        $lKeys = array_keys($qp);
        foreach ($lKeys as $i => $q) {
            $resp .= $q . '=' . $qp[$q] . (count($lKeys) - 1 == $i ? '' : '&');
        }
        return $resp;
    }

    public static function getStudentPhoto($data)
    {
        $url = 'https://encrypted-tbn0.gstatic.com/images?q=tbn%3AANd9GcQoFcFekAfe9SAUG0qNAkqWA6yhQcg-cv_wsUU5WbC-RLxpuYkJ';
        if ($data and isset($data->items->alumno->foto)) {
            $aliveUrl = self::isAliveHttpStatus($data->items->alumno->foto);
            if ($aliveUrl) {
                $url = $data->items->alumno->foto;
            }
        }
        $context = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
            "http" => array(
                'ignore_errors' => true
            )
        );
        $image = file_get_contents($url, false, stream_context_create($context));
        $base = 'data:image/jpg;base64,' . base64_encode($image);
        return $base;
    }

    public static function isAliveHttpStatus($url)
    {
        self::changeContext(false);
        $headers = get_headers($url);
        self::changeContext(true);
        $code = substr($headers[0], 9, 3);
        return $code == '200';
    }

    public static function changeContext($value)
    {
        stream_context_set_default([
            'ssl' => [
                'verify_peer' => $value,
                'verify_peer_name' => $value,
            ],
        ]);
    }

    private function geNameDays($code)
    {
        if (in_array($code, array('R', 'B'))) {
            return array('DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA');
        } elseif ($code == 'M') {
            return array('B1', 'B2', 'B3', 'B4', 'V1', 'V2', 'V3');
        }
    }

    public function updateData($courses, &$horary)
    {
        $this->_tempCrossing = [];
        foreach ($courses as $index => &$course) {
            $schedule = str_split(substr($course['horario']['horario'], 0, strrpos($course['horario']['horario'], '1') + 1));
//            foreach ($horary as &$obj)
            for ($i = 0; $i < count($horary); $i++) {
                $obj = &$horary[$i];
                $type_id = $obj['id_tipo_formato_modulo'];
                $type_course_id = intval($course['horario']['id_formato_modulo']);
                if ($type_id == $type_course_id) {
                    $obj['bas'] = 23;
                    $this->draw($schedule, $obj['horas'], $index, $course, $type_course_id);
                    break; // funcionaraaa??
                }
            }
        }
    }

    public static function getPattern($horario)
    {
        return str_split(substr($horario, 0, strrpos($horario, '1') + 1));
    }


    public static function build($cursos, &$horary)
    {


        foreach ($cursos as $index => $course) {

            $indexCourse = $index + 1;

            // verifica que el curso tenga horario
            if (array_key_exists('horario', $course) && $course['horario']) {

                // verifica que el curso tiene un horario que definido y puede llegar a tener cruce ()
                if ($course['horario']['cruce'] === 'N') {

                    $moduloIdCourse = [$course['horario']['id_modulo']];

                    // Obtenemos el patron del horario
                    $schedules = [array(
                        'type' => $course['horario']['tipo'],
                        'pattern' => self::getPattern($course['horario']['horario']))
                    ];

                    // agregamos el patron de horario si en caso tenemos practicas
                    if (array_key_exists('practicas', $course) && count($course['practicas']) == 1) {
                        array_push($moduloIdCourse, $course['practicas'][0]['horario']['id_modulo']);
                        array_push($schedules, array(
                            'type' => $course['practicas'][0]['horario']['tipo'],
                            'pattern' => self::getPattern($course['practicas'][0]['horario']['horario'])));
                    }

                    // verificar si horario curso esta dentro del los periodos (buscar interseccion)

                    for ($i = 0; $i < count($horary); $i++) {

                        if (in_array($horary[$i]['id_modulo'], $moduloIdCourse)) {
                            // empezamos a pintar las horas
                            self::drawCtrl($schedules, $horary[$i], $course, $indexCourse);
                        } else {
                            // console.log('Las fechas no coinciden');
                        }
                    }
                }


            }
        }

    }

    public static function drawCtrl($schedules, &$modulo, $course, $courseIndex)
    {
//        dd($modulo);


        foreach ($schedules as $schedule) {
            $index = 0;

            array_reduce($schedule['pattern'], function ($res, $value) use (&$index, &$modulo, $courseIndex, $course, $schedule) {
                $i = intval(floor($index / 7));
                if (!array_key_exists($i, $res)) {
                    $res[$i] = [];
                }

                if (intval($value) == 1) {
                    $keyDay = $modulo['header'][count($res[$i])];
                    if (isset($modulo['horario'][$i]) && !array_key_exists($keyDay, $modulo['horario'][$i])) { // cuando no esta pintado el curso
                        $obj = array();
                        $obj['status'] = true;
                        $obj['id'] = $courseIndex;
                        $obj['type'] = $schedule['type'];
                        $obj['curso'] = $course;
                        $modulo['horario'][$i][$keyDay] = $obj;
                    }
                }
                array_push($res[$i], $value);
                $index++;
                return $res;
            }, []);
        }
    }

    function draw($target, &$data, $course_id, $currentCourse, $type_id)
    {
        $data[0]['saludo'] = 'holaaa';
        $index = 0;

        array_reduce($target, function ($res, $value) use (&$data, &$index, $course_id, $currentCourse, $type_id) {
            $chunkIndex = intval(floor($index / 7));
            if (!array_key_exists($chunkIndex, $res)) {
                $res[$chunkIndex] = [];
            }
            $indexDay = count($res[$chunkIndex]);
            $keyDay = $this->getKeysIndex($type_id, $indexDay);
            array_push($res[$chunkIndex], $value);
            $isItemOne = intval($value) == 1;
            if (intval($value) == 1) {
                if (!array_key_exists($keyDay, $data[$chunkIndex])) {
                    $data[$chunkIndex][$keyDay] = array(
                        'state' => $isItemOne,
                        'statePattern' => $value,
                        'courseNumber' => $course_id,
                        'courseCurrent' => $currentCourse,
                        'courseCrossing' => [],
                        'dayName' => $keyDay,
                        'timeName' => null,
                        'isOver' => array_key_exists('over', $currentCourse) == true
                    );
                }
            }
            $index++;

            return $res;
        }, []);
    }

    public
    function getKeysIndex($id, $index)
    {
        return $this->getKeys($id)[$index];
    }

    public
    function getKeys($id)
    {
        if (in_array($id, ['R', 'M'])) {
            return ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
        } elseif ($id == 'B') {
            return ['B1', 'B2', 'B3', 'B4', 'V1', 'V2', 'V3'];
        }
    }

    public function getCoursesCreditDiscount($idContractStudent)
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
            ->where('VW_MAT_CURSOS_PLAN_ALUMNO.DESCUENTO_CREDITO', 'S')// sdsdfd sdfsd sfdgsg
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

    public
    function enrollmentCoursesSelectedList($id_contrato_alumno)//Lista de Cursos Seleccionados por el estudiante en la matrícula
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

            $query = "select distinct acc.id_carga_curso,
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
                                    WHERE CC.ID_ALUMNO_CONTRATO = $id_contrato_alumno AND CC.ID_CURSO_ALUMNO = aca.ID_CURSO_ALUMNO) AS CODIGO_ESTADO_MOV_CURRENT
                    from david.ACAD_ALUMNO_CONTRATO_CURSO aacc
                             INNER JOIN david.acad_curso_alumno aca ON aacc.ID_CURSO_ALUMNO = aca.ID_CURSO_ALUMNO
                             inner join david.acad_carga_curso acc on acc.id_carga_curso = aca.id_carga_curso
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
                        'codigo_estado_mov_current' => $Couses[$i]->codigo_estado_mov_current
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


    public function ScheduleListProgram($id_contrato_alumno) //Lista horas por cada tipo de horario
    {

        $qry = "select aac.id_persona, aac.id_plan_programa, asp.id_programa_estudio, asp.id_semestre_programa, asp.id_semestre
                from david.acad_alumno_contrato aac
                inner join david.acad_semestre_programa asp on asp.id_semestre_programa=aac.id_semestre_programa 
                            and aac.id_alumno_contrato=$id_contrato_alumno";

        $oQuery = \DB::select($qry);

        $id_plan_programa = $oQuery[0]->id_plan_programa;
        $id_persona = $oQuery[0]->id_persona;
        $id_programa_estudio = $oQuery[0]->id_programa_estudio;
        $id_semestre_programa = $oQuery[0]->id_semestre_programa;
        $id_semestre = $oQuery[0]->id_semestre;

        $isp = $id_semestre_programa;


        $queryCS = "select Distinct asp.id_semestre_programa
                    from david.acad_curso_alumno aca 
                    inner join david.acad_carga_curso acc on acc.id_carga_curso=aca.id_carga_curso
                    inner join david.acad_semestre_programa asp on Asp.Id_Semestre_Programa=Acc.Id_Semestre_Programa
                    inner join david.acad_semestre asm on asm.id_semestre=asp.id_semestre
                    where aca.id_persona=$id_persona
                    and aca.id_plan_programa=$id_plan_programa
                    and asp.id_semestre=$id_semestre";
        $CurSelec = \DB::select($queryCS);
        $numDS = count($CurSelec);

        if ($CurSelec) {
            for ($cs = 0; $cs < $numDS; $cs++) {
                if ($isp != $CurSelec[$cs]->id_semestre_programa) {
                    $id_semestre_programa = $id_semestre_programa . "," . $CurSelec[$cs]->id_semestre_programa;
                }
            }
        }


        $query = "select distinct am.id_modulo,am.id_tipo_formato_modulo,substr(am.codigo,0,1) codigo,(am.nombre || ' - ' || ah.nombre) as nombre,
                amd.fecha_inicio,amd.fecha_fin,ah.id_horario,
                tt.nombre turno,ahd.id_tipo_turno,ahd.periodo,ahd.hora_inicio,ahd.hora_fin 
                from david.acad_semestre_programa asp 
                inner join david.acad_modulo_detalle amd on amd.id_semestre_programa=asp.id_semestre_programa 
                                                and asp.id_semestre_programa in($id_semestre_programa)
                inner join david.acad_modulo am on am.id_modulo=amd.id_modulo
                inner join david.acad_horario ah on ah.id_horario=asp.id_horario
                inner join david.acad_horario_detalle ahd on ahd.id_horario=ah.id_horario
                inner join david.tipo_turno tt on Tt.Id_Tipo_Turno=Ahd.Id_Tipo_Turno
                order by amd.fecha_inicio||'-'||amd.fecha_fin,
                am.id_modulo,ahd.id_tipo_turno,ahd.periodo";

        $oQuery = \DB::select($query);

        $numHours = count($oQuery);

        $id_modulo = "";
        $id_tipo_formato_modulo = "";
        $nombre = "";
        $codigo = "";
        $id_horario = "";
        $fecha_inicio = "";
        $fecha_fin = "";

        $k = 0;

        for ($i = 0; $i < $numHours; $i++) {
            if ($id_modulo . '-' . $id_tipo_formato_modulo . '-' . $codigo . '-' . $nombre . '-' . $fecha_inicio . '-' . $fecha_fin . '-' . $id_horario !=
                $oQuery[$i]->id_modulo . '-' . $oQuery[$i]->id_tipo_formato_modulo . '-' . $oQuery[$i]->codigo . '-' . $oQuery[$i]->nombre . '-' . $oQuery[$i]->fecha_inicio . '-' . $oQuery[$i]->fecha_fin . '-' . $oQuery[$i]->id_horario) {

                //echo('*'.$id_modulo.'-'.$id_tipo_formato_modulo.'-'.$nombre.'-'.$fecha_inicio.'-'.$fecha_fin.'-'.$id_horario.'.....'.$oQuery[$i]->id_modulo.'-'.$oQuery[$i]->id_tipo_formato_modulo.'-'.$oQuery[$i]->nombre.'-'.$oQuery[$i]->fecha_inicio.'-'.$oQuery[$i]->fecha_fin.'-'.$oQuery[$i]->id_horario.'<br>');

                $modules[$k] = array(
                    "id_modulo" => $oQuery[$i]->id_modulo,
                    "id_tipo_formato_modulo" => $oQuery[$i]->id_tipo_formato_modulo,
                    "codigo" => $oQuery[$i]->codigo,
                    "nombre" => $oQuery[$i]->nombre,
                    "fecha_inicio" => $oQuery[$i]->fecha_inicio,
                    "fecha_fin" => $oQuery[$i]->fecha_fin
                );

                $k++;

                $id_modulo = $oQuery[$i]->id_modulo;
                $id_tipo_formato_modulo = $oQuery[$i]->id_tipo_formato_modulo;
                $codigo = $oQuery[$i]->codigo;
                $nombre = $oQuery[$i]->nombre;
                $id_horario = $oQuery[$i]->id_horario;
                $fecha_inicio = $oQuery[$i]->fecha_inicio;
                $fecha_fin = $oQuery[$i]->fecha_fin;

                $y = 0;
            }

            if ($id_modulo . '-' . $id_tipo_formato_modulo . '-' . $codigo . '-' . $nombre . '-' . $fecha_inicio . '-' . $fecha_fin . '-' . $id_horario ==
                $oQuery[$i]->id_modulo . '-' . $oQuery[$i]->id_tipo_formato_modulo . '-' . $oQuery[$i]->codigo . '-' . $oQuery[$i]->nombre . '-' . $oQuery[$i]->fecha_inicio . '-' . $oQuery[$i]->fecha_fin . '-' . $oQuery[$i]->id_horario) {
                $horario[$k - 1][$y] = array(
                    "periodo" => $oQuery[$i]->periodo,
                    "turno" => $oQuery[$i]->turno,
                    "hora_inicio" => $oQuery[$i]->hora_inicio,
                    "hora_fin" => $oQuery[$i]->hora_fin
                );

                $y++;
            }


            $$id_modulo = $oQuery[$i]->id_modulo;
            $id_tipo_formato_modulo = $oQuery[$i]->id_tipo_formato_modulo;
            $codigo = $oQuery[$i]->codigo;
            $nombre = $oQuery[$i]->nombre;
            $id_horario = $oQuery[$i]->id_horario;
            $fecha_inicio = $oQuery[$i]->fecha_inicio;
            $fecha_fin = $oQuery[$i]->fecha_fin;
        }

        for ($r = 0; $r < $k; $r++) {
            $modules[$r]['horario'] = $horario[$r];
        }


        return $modules;
    }
    public function studentFoto(Request $request)
    {
        $directorio = $request->directorio;
        $bucket = $request->bucket;
        $foto = Helpers::fotoUser($directorio, $bucket);
        return response()->json($foto);
    }
}
