<?php

namespace App\Http\Data\HumanTalentMgt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\HumanTalentMgt\ComunData;
use Exception;
use PDO;

class EmployeeData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }



    /// ============ delete
    // eliminar direccion
    public static function deleteDireccion($id_direccion)
    {
        $query = DB::table('moises.persona_direccion')->where('id_direccion', $id_direccion)->delete();
        return $query;
    }

    // eliminar superior
    public static function deleteSuperior($id_item)
    {
        $query = DB::table('moises.vinculo_familiar_superior')->where('id_item', $id_item)->delete();
        return $query;
    }

    // eliminar cuentas bancarias
    public static function deleteBank($id_pbancaria, $request)
    {
        //$query = DB::table('moises.persona_cuenta_bancaria')->where('id_pbancaria', $id_pbancaria)->delete();
        $query = DB::table('moises.persona_cuenta_bancaria')
            ->where('id_pbancaria', $id_pbancaria)
            ->update(["activo" => $request->activo]);
        return $query;
    }

    // eliminar  parentesco
    public static function deleteParentesco($id_vinculo_familiar)
    {
        $count = DB::table('moises.vinculo_familiar_superior')
            ->where('id_vinculo_familiar', $id_vinculo_familiar)
            ->count();
        if ($count == 0) {
            $query = DB::table('moises.vinculo_familiar')->where('id_vinculo_familiar', $id_vinculo_familiar)->delete();
            if ($query) {
                $response = [
                    'success' => true,
                    'message' => '',
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Se elimino correctamente',
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'El item contiene registros menores, debe eliminar esos registros antes',
            ];
        }
        return $response;
    }
    // =========================================

    //// ============= update
    // editar direccion
    public static function updateDireccion($id_direccion, $request)
    {
        $comentario = $request->comentario;
        //$depto = $request->depto;
        $direccion = $request->direccion;
        $es_activo = $request->es_activo;
        $eslegal = $request->eslegal;
        $id_persona = $request->id_persona;
        $id_tipodireccion = $request->id_tipodireccion;
        //$id_tiposeccion = $request->id_tiposeccion;
        $id_tipovia = $request->id_tipovia;
        $id_tipozona = $request->id_tipozona;
        $id_ubigeo = $request->id_ubigeo;
        $map_latitud = $request->map_latitud;
        $map_longitud = $request->map_longitud;
        //$provincia = $request->provincia;
        $referencia = $request->referencia;
        //$tiposeccion = $request->tiposeccion;
        $tipovia = $request->tipovia;
        $result = false;
        $tipozona = $request->tipozona;

        $num_via = $request->num_via;
        $departamento = $request->departamento;
        $interior = $request->interior;
        $manzana = $request->manzana;
        $lote = $request->lote;
        $kilometro = $request->kilometro;
        $blok = $request->blok;
        $etapa = $request->etapa;

        if ($es_activo == "1" && $id_persona) {
            $result = DB::table('moises.persona_direccion')
                ->where('id_direccion', $id_direccion)
                ->update(
                    [
                        'id_persona' =>  $id_persona,
                        'id_tipodireccion' =>  $id_tipodireccion,
                        'id_tipovia' => $id_tipovia,
                        'id_tipozona' =>  $id_tipozona,
                        //'id_tiposeccion' => $id_tiposeccion,
                        'direccion' =>  $direccion,
                        'map_latitud' => $map_latitud,
                        'map_longitud' => $map_longitud,
                        'comentario' => $comentario,
                        'es_activo' => $es_activo,
                        'id_ubigeo' => $id_ubigeo,
                        'tipovia' => $tipovia,
                        'tipozona' => $tipozona,
                        //'tiposeccion' => $tiposeccion,
                        'referencia' => $referencia,
                        'eslegal' => $eslegal,
                        'num_via' => $num_via,
                        'referencia' => $referencia,
                        'departamento' => $departamento,
                        'interior' => $interior,
                        'manzana' => $manzana,
                        'lote' => $lote,
                        'kilometro' => $kilometro,
                        'blok' => $blok,
                        'etapa' => $etapa
                    ]
                );
        } else {
            $result = DB::table('moises.persona_direccion')
                ->where('id_direccion', $id_direccion)
                ->update(
                    [
                        'es_activo' => $es_activo
                    ]
                );
        }

        if ($result) {
            $response = [
                'success' => true,
                'message' => '',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede modificar',
            ];
        }
        return $response;
    }



    // Update para aspecto academico y social
    public static function updateAcademicoSocial($dni, $request)
    {
        $id_persona = '';

        $dni             = $request->dni;
        $id_situacion_educativo = $request->id_situacion_educativo;
        // persona_natural_trabajador
        $educacion_peru         = $request->educacion_peru;
        $id_regimen_institucion = $request->id_regimen_institucion;
        $id_tipo_institucion    = $request->id_tipo_institucion;
        $id_institucion         = $request->id_institucion;
        $id_carrera_profesional = $request->id_carrera_profesional;
        $anho_egreso            = $request->anho_egreso;
        $cod_universitario      = $request->cod_universitario;
        $padre_registrado       = $request->padre_registrado;
        $id_padre               = $request->id_padre;
        $padre_datos            = $request->padre_datos;
        $padre_tlf              = $request->padre_tlf;
        $madre_registrado       = $request->madre_registrado;
        $id_madre               = $request->id_madre;
        $madre_datos            = $request->madre_datos;
        $madre_tlf              = $request->madre_tlf;

        $res = DB::table('moises.persona_natural')
            ->where('num_documento', $dni)
            ->select('id_persona')
            ->get();
        foreach ($res as $datos) {
            $id_persona = $datos->id_persona;
        }

        // para persona trabajador
        $result = DB::table('moises.persona_natural_trabajador')
            ->where('id_persona', $id_persona)
            ->update(
                [
                    'educacion_peru' =>  $educacion_peru,
                    'id_regimen_institucion' => $id_regimen_institucion,
                    'id_tipo_institucion' =>  $id_tipo_institucion,
                    'id_instituicion' => $id_institucion,
                    'id_carrera_profesional' =>  $id_carrera_profesional,
                    'anho_egreso' => $anho_egreso,
                    'cod_universitario' => $cod_universitario,
                    'padre_registrado' => $padre_registrado,
                    'id_padre' => $id_padre,
                    'padre_datos' => $padre_datos,
                    'padre_tlf' => $padre_tlf,
                    'madre_registrado' => $madre_registrado,
                    'id_madre' => $id_madre,
                    'madre_datos' => $madre_datos,
                    'madre_tlf' => $madre_tlf,
                ]
            );


        $result2 = DB::table('moises.persona_natural')
            ->where('id_persona', $id_persona)
            ->update(
                [
                    'id_situacion_educativo' =>  $id_situacion_educativo,

                ]
            );
        if ($result && $result2) {
            $response = [
                'success' => true,
                'message' => '',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede modificar',
            ];
        }
        return $response;
    }


    // ActuaLIZAR nivel superior
    public static function updateSuperiro($request)
    {
        $id_vinculo_familiar = $request->id_vinculo_familiar;

        $id_item = $request->id_item;
        $desde = $request->desde;
        $hasta = $request->hasta;
        $hijo_nivel_superior_url = $request->url;
        $file2 = $request->file('hijo_nivel_superior_url');
        if (!empty($file2)) {
            $ext = $file2->getClientOriginalExtension();
            $destino = 'gth'; //por ahora
            $uid = hash("md2", (string) microtime());
            $archivo     = $uid . '.' . strtolower($ext);
            $documento_url = $archivo;
            $file2->move($destino, $archivo);
            if (!empty($documento_url)) {

                $result = DB::table('moises.vinculo_familiar_superior')
                    ->where('id_item', $id_item)
                    ->update(
                        [
                            'id_vinculo_familiar' =>  $id_vinculo_familiar,
                            'desde' =>  $desde,
                            'hasta' => $hasta,
                            'hijo_nivel_superior_url' => $documento_url,
                        ]
                    );
            } else {
                $nerror = 1;
            }
        } else {
            $result = DB::table('moises.vinculo_familiar_superior')
                ->where('id_item', $id_item)
                ->update(
                    [
                        'id_vinculo_familiar' =>  $id_vinculo_familiar,
                        'desde' =>  $desde,
                        'hasta' => $hasta,
                        'hijo_nivel_superior_url' => $hijo_nivel_superior_url,
                    ]
                );
        }
        if ($result) {
            $response = [
                'success' => true,
                'message' => '',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede modificar',
            ];
        }
        return $response;
    }

    public static function updateLabor($request)
    {
        //$dni = $request->dni;
        $id_persona = $request->id_persona;

        /*$res = DB::table('moises.persona_natural')
            ->where('num_documento', $dni)
            ->select('id_persona')
            ->get();
        foreach ($res as $datos) {
            $id_persona = $datos->id_persona;
        }*/
        $destino = 'gth'; //por ahora
        $uid = hash("md2", (string) microtime());



        $result = DB::table('moises.persona_natural_trabajador')
            ->where('id_persona', $id_persona)
            ->update(
                [
                    'id_tipo_comision_pens' => $request->id_tipo_comision_pens,
                    'cuspp' => $request->cuspp,
                    'fec_inicioafp' =>  $request->fec_inicioafp,
                    'fec_inicioonp' => $request->fec_inicioonp,
                    'esaludvida' =>  $request->esaludvida,
                    //'esaludvida_url' =>   $essaludname, // $esaludvida_url ||
                    'esdiscapacitado' => $request->esdiscapacitado,
                    'id_tipo_discapacidad' => $request->id_tipo_discapacidad,
                    //'discapacitado_url' =>  $discapacidadname,  // $discapacido_url ||
                    'cuentasueldo' => $request->cuentasueldo,
                    //'cuentasueldo_url' => $cuentaname, // $cuentasueldo_url ||
                    'id_periodo_remu' => $request->id_periodo_remu,
                    'descuentodiezmo' => $request->descuentodiezmo,
                    //'descuentodiezmo_url' => $diezmoname,
                    'menoredad' => $request->menoredad,
                    //'menoredad_url' => $menorname, // $menoredad_url ||
                    'id_regimen_pensionaria' => $request->id_regimen_pensionaria,
                    'id_banco' => $request->id_banco,
                    'cod_autogenerado' => $request->cod_autogenerado
                ]
            );

        $essaludname = $request->essaludname;
        $essaludFile = $request->file('esaludvida_url');
        if ($essaludname != "") {
            $archivo = $essaludname;
            if (file_exists($destino . '/' . $essaludname)) {
                unlink($destino . '/' . $essaludname);
            }
            $essaludFile->move($destino, $archivo);
            DB::table('moises.persona_natural_trabajador')
                ->where('id_persona', $id_persona)
                ->update(
                    ['esaludvida_url' =>   $essaludname]
                );
        }

        $discapacidadname = $request->discapacidadname;
        $discapacidadFile = $request->file('discapacido_url');
        if ($discapacidadname != "") {
            $archivo = $discapacidadname;
            if (file_exists($destino . '/' . $discapacidadname)) {
                unlink($destino . '/' . $discapacidadname);
            }
            $discapacidadFile->move($destino, $archivo);
            DB::table('moises.persona_natural_trabajador')
                ->where('id_persona', $id_persona)
                ->update(
                    ['discapacitado_url' =>  $discapacidadname]
                );
        }

        $cuentaname = $request->cuentaname;
        $cuentaFile = $request->file('cuentasueldo_url');
        if ($cuentaname != "") {
            $archivo = $cuentaname;
            if (file_exists($destino . '/' . $cuentaname)) {
                unlink($destino . '/' . $cuentaname);
            }
            $cuentaFile->move($destino, $archivo);
            DB::table('moises.persona_natural_trabajador')
                ->where('id_persona', $id_persona)
                ->update(
                    ['cuentasueldo_url' => $cuentaname]
                );
        }

        $diezmoname = $request->diezmoname;
        $diezmoFile = $request->file('descuentodiezmo_url');
        if ($diezmoname != "") {
            $archivo = $diezmoname;
            if (file_exists($destino . '/' . $diezmoname)) {
                unlink($destino . '/' . $diezmoname);
            }
            $diezmoFile->move($destino, $archivo);
            DB::table('moises.persona_natural_trabajador')
                ->where('id_persona', $id_persona)
                ->update(
                    ['descuentodiezmo_url' => $diezmoname]
                );
        }

        $menorname = $request->menorname;
        $menorFile = $request->file('menoredad_url');
        if ($menorname != "") {
            $archivo = $menorname;
            if (file_exists($destino . '/' . $menorname)) {
                unlink($destino . '/' . $menorname);
            }
            $menorFile->move($destino, $archivo);
            DB::table('moises.persona_natural_trabajador')
                ->where('id_persona', $id_persona)
                ->update(
                    ['menoredad_url' => $menorname]
                );
        }
        // } else {
        if ($result) {
            $nerror = 0;
        } else {
            $nerror = 1;
        }
        // }
        if ($result) {
            $response = [
                'success' => true,
                'message' => '',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede modificar',
            ];
        }
        return $response;
    }



    /// ============ los add
    // agregar direcciones
    public static function addDireccion($request)
    {
        $comentario = $request->comentario;
        //$depto = $request->depto;
        $direccion = $request->direccion;
        $es_activo = $request->es_activo;
        $eslegal = $request->eslegal;
        $id_persona = $request->id_persona;
        $id_tipodireccion = $request->id_tipodireccion;
        //$id_tiposeccion = $request->id_tiposeccion;
        $id_tipovia = $request->id_tipovia;
        $id_tipozona = $request->id_tipozona;
        $id_ubigeo = $request->id_ubigeo;
        $map_latitud = $request->map_latitud;
        $map_longitud = $request->map_longitud;
        //$provincia = $request->provincia;
        $referencia = $request->referencia;
        //$tiposeccion = $request->tiposeccion;
        $tipovia = $request->tipovia;
        $tipozona = $request->tipozona;

        $num_via = $request->num_via;
        $departamento = $request->departamento;
        $interior = $request->interior;
        $manzana = $request->manzana;
        $lote = $request->lote;
        $kilometro = $request->kilometro;
        $blok = $request->blok;
        $etapa = $request->etapa;

        $save = DB::table('moises.persona_direccion')->insert(
            [
                'id_persona' =>  $id_persona,
                'id_tipodireccion' =>  $id_tipodireccion,
                'id_tipovia' => $id_tipovia,
                'id_tipozona' =>  $id_tipozona,
                //'id_tiposeccion' => $id_tiposeccion,
                'direccion' =>  $direccion,
                'map_latitud' => $map_latitud,
                'map_longitud' => $map_longitud,
                'comentario' => $comentario,
                'es_activo' => $es_activo,
                'id_ubigeo' => $id_ubigeo,
                'tipovia' => $tipovia,
                'tipozona' => $tipozona,
                //'tiposeccion' => $tiposeccion,
                'num_via' => $num_via,
                'referencia' => $referencia,
                'departamento' => $departamento,
                'interior' => $interior,
                'manzana' => $manzana,
                'lote' => $lote,
                'kilometro' => $kilometro,
                'blok' => $blok,
                'etapa' => $etapa
            ]
        );
        if ($save) {
            $response = [
                'success' => true,
                'message' => '',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede insertar',
            ];
        }
        return $response;
    }
    public static function personalInformation($request)
    {
        // dd('hhh', $id_anho, $id_entidad);
        $nerror = 0;
        $id_persona_new = 0;
        $documento_url = '';
        $msgerror = '';
        $message = '';
        $telefono = '';
        for ($i = 1; $i <= 200; $i++) {
            $msgerror .= '0';
        }
        $id_persona = intval($request->id_persona);
        $nombre     = $request->nombre;
        $paterno    = $request->paterno;
        $materno    = $request->materno;
        $sexo       = $request->sexo;
        $fec_nacimiento = $request->fec_nacimiento;
        $id_tipoestadocivil = $request->id_tipoestadocivil;
        $id_tipopais = $request->id_tipopais;
        $id_ubigeo = $request->id_ubigeo;
        $id_tipodocumento = $request->id_tipodocumento;
        $num_documento = $request->num_documento;
        $fec_caducidad = $request->fec_caducidad;
        $id_tiposangre = $request->id_tiposangre;
        $telefono = $request->telefono || '';

        $unapellido = $request->unapellido;

        $celular = $request->celular;
        $correo = $request->correo;
        $correo_inst = $request->correo_inst;

        //AGREGAR LO DE LA RELIGIÓN
        $cargo_iglesia      = $request->cargo;
        $fecha_bautizo      = $request->fecha_bautizo;
        $tiene_cargo        = $request->t_cargo;
        $tlf_autoridad      = $request->telefono_atoridad;
        //$id_tipoautoridadiglesia    = $request->id_tipoautoridadiglesia;
        $id_tipoautoridadiglesia    = $request->t_autoridad;
        $nombre_autoridad   = $request->autoridad;
        //$id_tiporeligion    = $request->id_tiporeligion;
        $id_tiporeligion    = $request->religion;
        $iglesia            = $request->iglesia;

        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin MOISES.PERSONA_TRABAJADOR.SP_DATOS_PERSONALES(
                                    :P_ID_PERSONA, 
                                    :P_NOMBRE, 
                                    :P_PATERNO, 
                                    :P_MATERNO,
                                    :P_SEXO,
                                    :P_FEC_NACIMIENTO,
                                    :P_ID_TIPOESTADOCIVIL,
                                    :P_ID_TIPOPAIS,
                                    :P_ID_UBIGEO,
                                    :P_ID_TIPODOCUMENTO, 
                                    :P_NUM_DOCUMENTO, 
                                    :P_FEC_CADUCIDAD, 
                                    :P_ID_TIPOSANGRE,
                                    :P_TELEFONO, 
                                    :P_CELULAR,
                                    :P_CORREO,
                                    :P_CORREO_INST,
                                    :P_IDTIPORELIGION,
                                    :P_IGLESIA,
                                    :P_FECHA_BAUTIZO,
                                    :P_TIENE_CARGO,
                                    :P_CARGO_IGLESIA,
                                    :P_ID_TIPOAUTORIDADIGLESIA,
                                    :P_NOMBRE_AUTORIDAD,
                                    :P_TLF_AUTORIDAD, 
                                    :P_UNAPELLIDO,
                                    :P_ERROR, 
                                    :P_MSGERROR,
                                    :P_ID_PERSONA_NEW
                                    ); end;");
        $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
        $stmt->bindParam(':P_NOMBRE', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':P_PATERNO', $paterno, PDO::PARAM_STR);
        $stmt->bindParam(':P_MATERNO', $materno, PDO::PARAM_STR);
        $stmt->bindParam(':P_SEXO', $sexo, PDO::PARAM_STR);
        $stmt->bindParam(':P_FEC_NACIMIENTO', $fec_nacimiento, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_TIPOESTADOCIVIL', $id_tipoestadocivil, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_TIPOPAIS', $id_tipopais, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_UBIGEO', $id_ubigeo, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_TIPODOCUMENTO', $id_tipodocumento, PDO::PARAM_INT);
        $stmt->bindParam(':P_NUM_DOCUMENTO', $num_documento, PDO::PARAM_STR);
        $stmt->bindParam(':P_FEC_CADUCIDAD', $fec_caducidad, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_TIPOSANGRE', $id_tiposangre);
        $stmt->bindParam(':P_TELEFONO', $telefono, PDO::PARAM_STR);
        $stmt->bindParam(':P_CELULAR', $celular, PDO::PARAM_STR);
        $stmt->bindParam(':P_CORREO', $correo, PDO::PARAM_STR);
        $stmt->bindParam(':P_CORREO_INST', $correo_inst, PDO::PARAM_STR);
        $stmt->bindParam(':P_IDTIPORELIGION', $id_tiporeligion, PDO::PARAM_INT);
        $stmt->bindParam(':P_IGLESIA', $iglesia, PDO::PARAM_STR);
        $stmt->bindParam(':P_FECHA_BAUTIZO', $fecha_bautizo, PDO::PARAM_STR);
        $stmt->bindParam(':P_TIENE_CARGO', $tiene_cargo, PDO::PARAM_STR);
        $stmt->bindParam(':P_CARGO_IGLESIA', $cargo_iglesia, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_TIPOAUTORIDADIGLESIA', $id_tipoautoridadiglesia);
        $stmt->bindParam(':P_NOMBRE_AUTORIDAD', $nombre_autoridad, PDO::PARAM_STR);
        $stmt->bindParam(':P_TLF_AUTORIDAD', $tlf_autoridad, PDO::PARAM_STR);
        $stmt->bindParam(':P_UNAPELLIDO', $unapellido, PDO::PARAM_STR);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_PERSONA_NEW', $id_persona_new, PDO::PARAM_INT);

        $stmt->execute();

        if ($nerror == 0) {
            if ($id_persona == 0) {
                $array = array();

                $adirecciones = json_decode($request->direcciones);
                foreach ($adirecciones as $datos) {
                    $items = (object) $datos;
                    $activo = 0;
                    if ($items->id_tipodireccion == 4) {
                        $activo = 1;
                    }
                    $datos = [
                        'id_persona' => $id_persona_new,
                        'id_tipodireccion' => $items->id_tipodireccion,
                        'id_tipovia' => $items->id_tipovia,
                        'id_tipozona' => $items->id_tipozona,
                        'id_tiposeccion' => $items->id_tiposeccion,
                        'direccion' => $items->direccion,
                        'map_latitud' => $items->map_latitude,
                        'map_longitud' => $items->map_longitud,
                        'comentario' => $items->comentario,
                        'id_ubigeo' => $items->id_ubigeo,
                        'tipovia' => $items->tipovia,
                        'tipozona' => $items->tipozona,
                        'tiposeccion' => $items->tiposeccion,
                        'referencia' => $items->referencia,
                        'eslegal' => $items->eslegal,
                        'es_activo' => $activo

                    ];
                    $array[] = $datos;
                }

                if (count($array) > 0) {
                    DB::table('moises.persona_direccion')->insert($array);
                }
            }

            $filename = $request->file;
            $file = $request->file('filedoc');
            if ($file) {
                $archivo = $filename;
                $path = 'gth';
                if (file_exists($path . '/' . $filename)) {
                    unlink($path . '/' . $filename);
                }
                $file->move($path, $archivo);
                DB::table('moises.persona_documento')
                    ->where('id_persona', $id_persona_new)
                    ->where('id_tipodocumento', $request->id_tipodocumento)
                    ->where('num_documento', $request->num_documento)
                    ->update(['documento_url' => $filename]);

                DB::table('moises.persona_natural')
                    ->where('id_persona', $id_persona_new)
                    ->update(['documento_url' => $filename]);
            }

            $signname = $request->sign;
            $sign = $request->file('signdoc');
            if ($sign) {
                $archivo = $signname;
                $path = 'gth';
                if (file_exists($path . '/' . $signname)) {
                    unlink($path . '/' . $signname);
                }
                $sign->move($path, $archivo);
                DB::table('moises.persona_natural')
                    ->where('id_persona', $id_persona_new)
                    ->update(['firma' => $signname]);
            }
        }
        $return = [
            'nerror' => $nerror,
            'msgerror' => $msgerror,
            'id_persona' => $id_persona,
            'id_tipoestadocivil' => $id_tipoestadocivil,
            'message' => $message
        ];


        return $return;
    }

    // para agregar al segundo paso
    public static function academicoSocial($request)
    {
        $nerror = 0;
        $msgerror = '';
        for ($i = 1; $i <= 200; $i++) {
            $msgerror .= '0';
        }
        $id_persona         = $request->id_persona;
        $anho_egreso        = $request->anho_egreso;

        $cod_universitario  = $request->cod_universitario;
        $educacion_peru     = $request->educacion_peru;
        $id_carrera_profesional = $request->id_carrera_profesional;
        $id_institucion     = $request->id_institucion;
        $id_regimen_institucion     = $request->id_regimen_institucion;
        $id_situacion_educativo     = $request->id_situacion_educativo;
        $id_tipo_institucion        = $request->id_tipo_institucion;
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin MOISES.PERSONA_TRABAJADOR.SP_DATOS_ACADEMICO_SOCIAL(
                                :P_ID_PERSONA,
                                :P_ID_SITUACION_EDUCATIVO, 
                                :P_EDUCACION_PERU,
                                :P_ID_REGIMEN_INSTITUCION,
                                :P_ID_TIPO_INSTITUCION,
                                :P_ID_INSTITUICION,
                                :P_ID_CARRERA_PROFESIONAL,
                                :P_ANHO_EGRESO,
                                :P_COD_UNIVERSITARIO,
                                :P_ERROR, 
                                :P_MSGERROR
                                ); end;");
        $stmt->bindParam(':P_ID_PERSONA', $id_persona);
        $stmt->bindParam(':P_ID_SITUACION_EDUCATIVO', $id_situacion_educativo);
        $stmt->bindParam(':P_EDUCACION_PERU', $educacion_peru);
        $stmt->bindParam(':P_ID_REGIMEN_INSTITUCION', $id_regimen_institucion);
        $stmt->bindParam(':P_ID_TIPO_INSTITUCION', $id_tipo_institucion);
        $stmt->bindParam(':P_ID_INSTITUICION', $id_institucion);
        $stmt->bindParam(':P_ID_CARRERA_PROFESIONAL', $id_carrera_profesional);
        $stmt->bindParam(':P_ANHO_EGRESO', $anho_egreso);
        $stmt->bindParam(':P_COD_UNIVERSITARIO', $cod_universitario);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute();

        $return = [
            'nerror' => $nerror,
            'msgerror' => $msgerror,
        ];
        return $return;
    }

    public static function getParents($id_persona)
    {
        $res = DB::table('moises.persona_natural_trabajador')
            ->where('id_persona', $id_persona)
            ->select(
                'padre_registrado',
                'id_padre',
                'padre_datos',
                'padre_tlf',
                'madre_registrado',
                'id_madre',
                'madre_datos',
                'madre_tlf'
            )
            ->get();
        return $res;
    }

    public static function updateParents($request)
    {
        $message = '';
        $nerror = 0;
        $success = false;
        $response = [];
        $id_persona = $request->id_persona;
        $datos = $request->datos;
        $toUpdate = json_decode($datos, true);
        $affected = DB::table('moises.persona_natural_trabajador')
            ->where('id_persona', $id_persona)
            ->update($toUpdate);
        if ($affected) {
            $message = 'Succesfull update';
            $success = true;
        } else {
            $nerror = 1;
            $message = 'Error - update parents';
        }
        $message = $toUpdate;
        $response = [
            'success' => $success,
            'message' => $message,
            'nerror' => $nerror
        ];
        return $response;
    }

    public static function dataFamily($request)
    {
        $response = [];
        $nerror = 0;
        $success = false;
        $message = '';
        $id_persona = $request->id_persona;
        $id_vinculo_familiar = ComunData::correlativo('moises.vinculo_familiar', 'id_vinculo_familiar');
        $destino = 'gth'; //por ahora
        $uid = hash("md2", (string) microtime());

        if ($id_vinculo_familiar > 0) {
            $response['vacio'] = 'no';
            $susname = $request->susname; //DOCUMENTO SUSTENTO FAMILIAR
            $sustento = $request->file('doc_sust_vf_url');
            if ($susname != "") {
                $archivo = $susname;
                if (file_exists($destino . '/' . $susname)) {
                    unlink($destino . '/' . $susname);
                }
                $sustento->move($destino, $archivo);
            }

            /*$docname = $request->docname;
            $documento = $request->file('documento_url');
            if ($docname != "") {
                $archivo = $docname;
                if (file_exists($destino . '/' . $docname)) {
                    unlink($destino . '/' . $docname);
                }
                $documento->move($destino, $archivo);
            }*/

            $disname = $request->disname; //DOCUMENTO SUSTENTO DISCAPACIDAD
            $discapacidad = $request->file('hijo_discapacidad_url');
            if ($disname != "") {
                $archivo = $disname;
                if (file_exists($destino . '/' . $disname)) {
                    unlink($destino . '/' . $disname);
                }
                $discapacidad->move($destino, $archivo);
            }


            $save = DB::table('moises.vinculo_familiar')->insert(
                [
                    'id_vinculo_familiar' =>  $id_vinculo_familiar,
                    'id_persona' =>  $id_persona,
                    'id_tipo_vinculo_familiar' =>  $request->id_tipo_vinculo_familiar,
                    'id_tipo_doc_sust_vf' =>  $request->id_tipo_doc_sust_vf,
                    'doc_sust_vf_url' =>  $susname, // archivo file
                    //'id_tipopais' =>  $request->id_tipopais,
                    //'registrado' =>  $request->registrado,
                    'id_registrado' =>  $request->id_registrado,
                    //'nombre' =>  $request->nombre,
                    //'paterno' =>  $request->paterno,
                    //'materno' =>  $request->materno,
                    //'fecha_nacimiento' =>  $request->fecha_nacimiento,
                    //'id_tipodocumento' =>  $request->id_tipo_documento,
                    //'num_documento' =>  $request->num_documento,
                    //'documento_url' =>  $docname, // archivo file
                    //'sexo' =>  $request->sexo,
                    'essalud' =>  $request->essalud,
                    'conyuge_trabaja' =>  $request->conyuge_trabaja,
                    'conyuge_planilla' =>  $request->conyuge_planilla,
                    'conyuge_otraemp' =>  $request->conyuge_otraemp,
                    'conyuge_tlf' =>  $request->conyuge_tlf,
                    'conyuge_fmat' =>  $request->conyuge_fmat,
                    //'hijo_nivel_superior' =>  $request->hijo_nivel_superior,
                    'hijo_discapacitado' =>  $request->hijo_discapacitado,
                    'id_tipo_discapacidad' =>  $request->id_tipo_discapacidad,
                    'hijo_discapacitado_url' =>  $disname, // archivo file
                    'num_doc_sustento' => $request->ndoc_sus
                ]
            );
            if ($save) {
                $success = true;
                $message = '';
            } else {
                $nerror = 1;
                $success = false;
                $message = 'No se puede insertar';
            }
        } else {
            $success = false;
            $message = 'No se ha generado correlativo';
        }
        $response = [
            'success' => $success,
            'message' => $message,
            'nerror' => $nerror,
        ];

        return $response;
    }

    public static function updateDataFamily($request)
    {
        $response = [];
        $nerror = 0;
        $success = false;
        $message = '';
        $destino = 'gth';
        $susname = $request->susname; //DOCUMENTO SUSTENTO FAMILIAR
        $sustento = $request->file('doc_sust_vf_url');
        if ($susname != "") {
            $archivo = $susname;
            if (file_exists($destino . '/' . $susname)) {
                unlink($destino . '/' . $susname);
            }
            $sustento->move($destino, $archivo);
            DB::table('moises.vinculo_familiar')
                ->where('id_vinculo_familiar', $request->id_vinculo_familiar)
                ->update([
                    'doc_sust_vf_url' =>  $susname
                ]);
        }
        $disname = $request->disname; //DOCUMENTO SUSTENTO DISCAPACIDAD
        $discapacidad = $request->file('hijo_discapacidad_url');
        if ($disname != "") {
            $archivo = $disname;
            if (file_exists($destino . '/' . $disname)) {
                unlink($destino . '/' . $disname);
            }
            $discapacidad->move($destino, $archivo);
            DB::table('moises.vinculo_familiar')
                ->where('id_vinculo_familiar', $request->id_vinculo_familiar)
                ->update([
                    'hijo_discapacitado_url' =>  $disname
                ]);
        }


        $save = DB::table('moises.vinculo_familiar')
            ->where('id_vinculo_familiar', $request->id_vinculo_familiar)
            ->update([
                'id_persona' =>  $request->id_persona,
                'id_tipo_vinculo_familiar' =>  $request->id_tipo_vinculo_familiar,
                'id_tipo_doc_sust_vf' =>  $request->id_tipo_doc_sust_vf,
                //'doc_sust_vf_url' =>  $susname,
                'id_registrado' =>  $request->id_registrado,
                'essalud' =>  $request->essalud,
                'conyuge_trabaja' =>  $request->conyuge_trabaja,
                'conyuge_planilla' =>  $request->conyuge_planilla,
                'conyuge_otraemp' =>  $request->conyuge_otraemp,
                'conyuge_tlf' =>  $request->conyuge_tlf,
                'conyuge_fmat' =>  $request->conyuge_fmat,
                'hijo_discapacitado' =>  $request->hijo_discapacitado,
                'id_tipo_discapacidad' =>  $request->id_tipo_discapacidad,
                //'hijo_discapacitado_url' =>  $disname,
                'num_doc_sustento' => $request->ndoc_sus
            ]);
        if (!$save) {
            $message = "error on update";
            $nerror = 1;
        }
        $response = [
            'success' => $save,
            'message' => $message,
            'nerror' => $nerror,
        ];

        return $response;
    }

    public static function addSuperiorNivel($request)
    {
        $nerror = 0;
        $message = '';
        $id_vinculo_familiar = $request->id_vinculo_familiar;
        $desde = $request->desde;
        $hasta    = $request->hasta;
        //$hijo_nivel_superior_url = $request->hijo_nivel_superior_url;
        //$file = $request->file('hijo_nivel_superior_url');
        $filename = $request->filename;
        $id_item = ComunData::correlativo('moises.vinculo_familiar_superior', 'id_item');
        if ($id_item > 0) {
            DB::table('moises.vinculo_familiar_superior')->insert(
                [
                    'id_item' =>  $id_item,
                    'id_vinculo_familiar' =>  $id_vinculo_familiar,
                    'desde' => $desde,
                    'hasta' =>  $hasta,
                    'hijo_nivel_superior_url' => $filename,
                ]
            );
            $destino = 'gth';
            $file = $request->file('hijo_nivel_superior_url');
            if ($filename != "") {
                if (file_exists($destino . '/' . $filename)) {
                    unlink($destino . '/' . $filename);
                }
                $file->move($destino, $filename);
            }
        } else {
            $nerror = 1;
        }
        $return = [
            'nerror' => $nerror,
            'message' => $message
        ];
        return $return;
    }

    public static function addCtasBank($data)
    {
        $nerror = 0;
        $toInsert = json_decode($data, true);
        $id_pbancaria = ComunData::correlativo('moises.persona_cuenta_bancaria', 'id_pbancaria');
        $toInsert['id_pbancaria'] = $id_pbancaria;
        $res = DB::table('moises.persona_cuenta_bancaria')->insert($toInsert);
        if (!$res) {
            $nerror = 1;
        }
        $return = [
            'nerror' => $nerror,
        ];
        return $return;
    }

    // para agregar cuentas bancarias
    public static function addBank($request)
    {
        //$file2 = $request->file('documento_url');
        $nerror = 0;
        /*if (!empty($file2)) {
            $ext = $file2->getClientOriginalExtension();
            $destino = 'gth'; //por ahora
            $uid = hash("md2", (string) microtime());
            $archivo     = $uid . '.' . strtolower($ext);*/
        //$documento_url = $archivo;
        //$file2->move($destino, $archivo);
        //if (!empty($documento_url)) {
        $id_persona = $request->id_persona;
        //$dni = $request->dni;
        /*$res = DB::table('moises.persona_natural')
            ->where('num_documento', $dni)
            ->select('id_persona')
            ->get();
        foreach ($res as $datos) {
            $id_persona = $datos->id_persona;
        }*/
        $id_pbancaria = ComunData::correlativo('moises.persona_cuenta_bancaria', 'id_pbancaria');
        $res = DB::table('moises.persona_cuenta_bancaria')->insert(
            [
                'id_pbancaria' => $id_pbancaria,
                'id_persona' =>  $id_persona,
                'cuenta' =>  $request->cuenta,
                'id_banco' => $request->id_banco,
                'id_tipoctabanco' => $request->id_tipoctabanco,
                'cci' => $request->cci,
                //'fec_inicio' => $request->fec_inicio,
                //'fec_fin' => $request->fec_fin,
                //'documento_url' => $documento_url,
                'activo' => '1',
            ]
        );
        /*} else {
                $nerror = 1;
            }*/
        /*} else {
            $nerror = 1;
        }*/
        $return = [
            'nerror' => $nerror,
        ];
        return $return;
    }

    ///=====================================

    // AREA DE BUSCADORES
    // -------------------------------------------------
    // Buscador de persona 
    public static function searchFirsPerson($id_persona, $url)
    {
        $sql = DB::table('moises.persona_natural pn')
            ->join('moises.persona pr', 'pr.id_persona', '=', 'pn.id_persona')
            ->join('moises.tipo_documento td', 'pn.id_tipodocumento', '=', 'td.id_tipodocumento', 'full outer')
            ->join('moises.tipo_pais tp', 'tp.id_tipopais', '=', 'pn.id_tipopais', 'full outer')
            ->join('moises.ubigueo ug', 'ug.id_ubigeo', '=', 'pn.id_ubigeo', 'full outer')
            ->where("pn.id_persona", $id_persona)
            ->select(
                'pr.id_persona',
                'pr.nombre as nombres',
                'pr.paterno',
                'pr.materno',
                'pn.sexo',
                'pn.fec_nacimiento',
                'pn.id_tipoestadocivil',
                'pn.id_tipodocumento',
                'td.siglas',
                'pn.num_documento',
                'pn.fec_caducidad',
                'pn.documento_url',
                DB::raw("'" . $url . "/gth/'||pn.documento_url AS url"),
                'pn.id_tipopais as id_pais',
                'tp.nombre',
                'pn.id_tiposangre',
                'pn.telefono',
                'pn.celular',
                'pn.correo',
                'pn.correo_inst',
                'pn.id_ubigeo',
                'ug.depto',
                'ug.pvcia',
                'ug.ditto',
                'pn.id_tiporeligion',
                'pn.iglesia',
                'pn.fecha_bautizo',
                'pn.tiene_cargo',
                'pn.cargo_iglesia',
                'pn.id_tipoautoridadiglesia',
                'pn.nombre_autoridad',
                'pn.tlf_autoridad',
                'pn.firma',
                'pr.unapellido',
                DB::raw("'" . $url . "/gth/'||pn.firma AS signurl")
            )
            ->get();
        if (sizeof($sql) > 0) {
            $archivo = $sql[0]->documento_url;
            $firma = $sql[0]->firma;
            if ($archivo) {
                $filePath = $sql[0]->url;
                $file = null;
                if (EmployeeData::get_http_response_code($filePath) == "200") {
                    $file = base64_encode(file_get_contents($filePath));
                }
                $sql[0]->file = $file;
            }
            if ($firma) {
                $filePath = $sql[0]->signurl;
                $sign = null;
                if (EmployeeData::get_http_response_code($filePath) == "200") {
                    $sign = base64_encode(file_get_contents($filePath));
                }
                $sql[0]->sign = $sign;
            }
        }

        return $sql;
    }

    public static function get_http_response_code($url)
    {
        $headers = get_headers($url);
        return substr($headers[0], 9, 3);
    }

    //// listas ========================================
    // trae las direcciones que cuenta la persona registrada
    public static function searchDireccion($id_persona)
    {
        $sql = DB::table('moises.persona_direccion pd')
            ->join('moises.tipo_direccion td', 'td.id_tipodireccion', '=', 'pd.id_tipodireccion')
            ->join('moises.tipo_via vi', 'vi.id_tipovia', '=', 'pd.id_tipovia')
            //->join('moises.tipo_seccion sec', 'sec.id_tiposeccion', '=', 'pd.id_tiposeccion')
            ->join('moises.tipo_zona zo', 'zo.id_tipozona', '=', 'pd.id_tipozona')
            ->join('moises.ubigueo ug', 'ug.id_ubigeo', '=', 'pd.id_ubigeo')
            ->join('moises.VW_UBIGEO pv', 'pv.id_ubigeo', '=', 'pd.id_ubigeo')
            ->where("pd.id_persona", $id_persona)
            //->where('pd.es_activo', '1')
            ->select(
                'pd.*',
                'pv.departamento as ub_dpto',
                'pv.provincia as ub_pvcia',
                'pv.ubigeo as ub_ubigeo',
                'pv.codigo as ub_codigo',
                'td.nombre as tipo_direccion',
                'vi.nombre as via',
                //'sec.nombre as seccion',
                'zo.nombre as zona',
                'ug.depto',
                'ug.pvcia',
                'ug.ditto',
                'ug.nombre'
            )->orderBy('pd.es_activo', 'desc')
            ->get();
        return $sql;
    }

    // traer la data de trabajadores 
    public static function listWorker($nombre, $per_page)
    {
        $querys = DB::table('moises.vw_trabajador t')
            ->join('moises.tipo_documento td', 'td.id_tipodocumento', '=', 't.id_tipodocumento')
            ->leftjoin('eliseo.plla_puesto pp', 'pp.id_puesto', '=', 't.id_puesto')
            ->leftjoin('eliseo.org_sede_area ora', 'ora.id_sedearea', '=', 't.id_sedearea')
            ->leftjoin('eliseo.org_area oa', 'oa.id_area', '=', 'ora.id_area')
            //->whereraw(ComunData::fnBuscar('t.nombre') . ' like ' . ComunData::fnBuscar("'%" . $nombre . "%'"))
            //->whereraw("(upper(replace(concat(concat(t.nombre,t.paterno),t.materno),' ','')) like upper(replace('%" . $nombre . "%',' ','')) or (t.num_documento like '%" . $nombre . "%'))")
            ->whereraw("(upper(replace(concat(concat(t.nombre,t.paterno),t.materno),' ','')) like upper(replace('%" . $nombre . "%',' ','')) 
            or upper(replace(concat(concat(regexp_substr(t.nombre ,'[^ ]+',1,1),t.paterno),t.materno),' ','')) like upper(replace('%" . $nombre . "%',' ',''))
            or upper(replace(concat(concat(regexp_substr(t.nombre ,'[^ ]+',1,2),t.paterno),t.materno),' ','')) like upper(replace('%" . $nombre . "%',' ',''))
            or (t.num_documento like '%" . $nombre . "%'))")
            ->select(
                DB::raw("t.id_persona, (t.nombre ||' '|| t.paterno ||' '|| t.materno) as persona"),
                DB::raw("td.siglas ||' - '|| t.num_documento as documento"),
                't.fecha_inicio',
                't.num_documento as dni',
                't.fecha_fin_previsto',
                'oa.nombre as area',
                'pp.nombre as puesto'
            )
            ->orderBy('persona', 'asc')
            ->paginate((int) $per_page);
        return $querys;
    }


    // para traer la data de academico y social
    //public static function listWorkerSocial($id_persona)
    public static function listWorkerAcademic($id_persona)
    {
        $sql = DB::table('moises.persona_natural pn')
            ->join('moises.persona_natural_trabajador pt', 'pn.id_persona', '=', 'pt.id_persona', 'full outer')
            ->join('moises.instituciones ins', 'ins.id_instituicion', '=', 'pt.id_instituicion', 'full outer')
            ->join('moises.carrera_profesional crr', 'crr.id_carrera_profesional', '=', 'pt.id_carrera_profesional', 'full outer')
            //->join('moises.persona par', 'par.id_persona', '=', 'pt.id_padre', 'full outer')
            //->join('moises.persona mar', 'mar.id_persona', '=', 'pt.id_madre', 'full outer')
            ->where("pn.id_persona", $id_persona)
            ->select(
                'pn.id_situacion_educativo',
                'pt.educacion_peru',
                'pt.id_regimen_institucion',
                'pt.id_tipo_institucion',
                'pt.id_instituicion',
                'ins.nombre as institucion',
                'pt.id_carrera_profesional',
                'crr.nombre  as carrera',
                'pt.anho_egreso',
                'pt.cod_universitario'
            )
            ->get();
        return $sql;
    }

    // traer la data DE PARENTESCO POR TRABAJADOR
    public static function parentWorker($id_persona, $url)
    {
        $sql = DB::table('moises.vinculo_familiar vf')

            ->join('moises.tipo_vinculo_familiar tcf', 'tcf.id_tipo_vinculo_familiar', '=', 'vf.id_tipo_vinculo_familiar', 'full outer')
            ->join('moises.tipo_doc_sust_vf tds', 'tds.id_tipo_doc_sust_vf', '=', 'vf.id_tipo_doc_sust_vf', 'full outer')
            ->join('moises.tipo_discapacidad ds', 'ds.id_tipo_discapacidad', '=', 'vf.id_tipo_discapacidad', 'full outer')
            ->join('moises.persona_natural pn', 'pn.id_persona', '=', 'vf.id_registrado')
            ->join('moises.persona p', 'p.id_persona', '=', 'pn.id_persona')
            ->join('moises.tipo_sexo sex', 'sex.sexo', '=', 'pn.sexo', 'full outer')
            ->join('moises.tipo_documento tp', 'tp.id_tipodocumento', '=', 'pn.id_tipodocumento', 'full outer')
            ->join('moises.tipo_pais pa', 'pa.id_tipopais', '=', 'pn.id_tipopais', 'full outer')
            ->where("vf.id_persona", '=', $id_persona)
            ->select(
                'vf.*',
                'p.nombre',
                'p.materno',
                'p.paterno',
                'pn.*',
                DB::raw("TO_CHAR(pn.fec_nacimiento,'DD/MM/YYYY') as nacimiento"),
                DB::raw("TO_CHAR(vf.conyuge_fmat,'DD/MM/YYYY') as matrimonio"),
                'tp.siglas as documento',
                'tcf.nombre as tipo_vinculo',
                'tds.nombre_corto as sustento_vf',
                'ds.nombre as discapacidad',
                'sex.nombre as sex',
                DB::raw("'" . $url . "/gth/'||vf.doc_sust_vf_url AS doc_sus_url"),
                DB::raw("'" . $url . "/gth/'||vf.hijo_discapacitado_url AS doc_disca_url"),
                'pa.nombre as pais'
            )
            ->orderBy('vf.id_persona')
            ->get();
        return $sql;
    }

    public static function parentSuperiorSonWorker($id_persona, $url)
    {
        /*$id_persona = '';

        $res = DB::table('moises.persona_natural')
            ->where('num_documento', $dni)
            ->select('id_persona')
            ->get();
        foreach ($res as $datos) {
            $id_persona = $datos->id_persona;
        }*/

        $sql = DB::table('moises.vinculo_familiar_superior vfs')
            ->where("vf.id_persona", '=', $id_persona)
            ->join('moises.vinculo_familiar vf', 'vf.id_vinculo_familiar', '=', 'vfs.id_vinculo_familiar')
            ->join('moises.persona_natural pn', 'pn.id_persona', '=', 'vf.id_registrado')
            ->join('moises.persona p', 'p.id_persona', '=', 'pn.id_persona')
            ->select(
                'vfs.id_item',
                // DB::raw("TO_CHAR(vfs.desde,'DD/MM/YYYY') as desde") ,
                // DB::raw("TO_CHAR(vfs.hasta,'DD/MM/YYYY') as hasta") ,
                'vfs.desde',
                'vfs.hasta',
                'p.*',
                'pn.*',
                DB::raw("'" . $url . "/gth/'||vfs.hijo_nivel_superior_url AS URL"),
                'vfs.hijo_nivel_superior_url',
                'vf.id_vinculo_familiar'
            )
            ->orderBy('vfs.hasta')
            ->get();
        return $sql;
    }

    // listar hijos mayores de edad para registro de nivel superior
    public static function hijoMayorSuperior($id_persona)
    {
        /*$id_persona = '';

        $res = DB::table('moises.persona_natural')
            ->where('num_documento', $dni)
            ->select('id_persona')
            ->get();
        foreach ($res as $datos) {
            $id_persona = $datos->id_persona;
        }*/

        $sql = DB::table('moises.vinculo_familiar vf')
            ->join('moises.persona_natural pn', 'pn.id_persona', '=', 'vf.id_registrado')
            ->join('moises.persona p', 'p.id_persona', '=', 'pn.id_persona')
            ->where("vf.id_persona", '=', $id_persona)
            ->where("vf.id_tipo_vinculo_familiar", '=', '05')
            //->where("vf.hijo_nivel_superior", '=', 'S')
            ->whereraw("(months_between(sysdate,pn.fec_nacimiento)/12)>=18")
            ->select(
                'vf.id_vinculo_familiar',
                'p.nombre',
                'p.paterno',
                'p.materno'
            )
            ->get();
        return $sql;
    }

    // trae data de aspecto laboral
    public static function getAspectoLaboral($id_persona, $url)
    {
        $sql = DB::table('moises.persona_natural_trabajador pnt')
            ->join('moises.persona_natural pn', 'pn.id_persona', '=', 'pnt.id_persona')
            ->join('moises.persona p', 'p.id_persona', '=', 'pnt.id_persona')
            ->leftJoin('moises.persona_direccion d', function ($join) {
                $join->on('pnt.id_persona', '=', 'd.id_persona')
                    ->where('d.id_tipodireccion', '=', '4')
                    ->where('d.es_activo', '=', '1');
            })
            ->leftJoin('moises.VW_UBIGEO pv', 'pv.id_ubigeo', '=', 'd.id_ubigeo')
            ->where("pn.id_persona", '=', $id_persona)
            ->select(
                'pn.id_persona',
                'pnt.id_tipo_comision_pens',
                'pnt.cuspp',
                'pnt.esaludvida',
                'pnt.esaludvida_url',
                'pnt.esdiscapacitado',
                'pnt.id_tipo_discapacidad',
                'pnt.discapacitado_url',
                'pnt.cuentasueldo',
                'pnt.cuentasueldo_url',
                'pnt.id_periodo_remu',
                'pnt.descuentodiezmo',
                'pnt.descuentodiezmo_url',
                'pnt.id_regimen_pensionaria',
                'pnt.id_banco',
                'pnt.menoredad',
                'pnt.menoredad_url',
                'pnt.fec_inicioafp',
                'pnt.fec_inicioonp',
                'pnt.cod_autogenerado',
                'pn.firma',
                'pn.num_documento',
                'p.nombre',
                'p.paterno',
                'p.materno',
                'd.direccion',
                'pv.distrito',
                'pv.provincia',
                'pv.departamento',
                DB::raw("'" . $url . "/gth/'||pnt.esaludvida_url AS doc_esaludvida"),
                DB::raw("'" . $url . "/gth/'||pnt.discapacitado_url AS doc_discapacitado"),
                DB::raw("'" . $url . "/gth/'||pnt.cuentasueldo_url AS doc_cuentasueldo"),
                DB::raw("'" . $url . "/gth/'||pnt.descuentodiezmo_url AS doc_descuentodiezmo"),
                DB::raw("'" . $url . "/gth/'||pnt.menoredad_url AS doc_menoredad"),
                DB::raw("'" . $url . "/gth/'||pn.firma AS firma_url")
            )
            ->get();
        if (sizeof($sql) > 0) {
            $firma = $sql[0]->firma;
            if ($firma) {
                $filePath = $sql[0]->firma_url;
                $sign = null;
                if (EmployeeData::get_http_response_code($filePath) == "200") {
                    $sign = base64_encode(file_get_contents($filePath));
                }
                $sql[0]->sign = $sign;
            }
        }
        return $sql;
    }

    // trae lista de las cuentas bancarias
    public static function getAccountBank($id_persona, $url)
    {
        $sql = DB::table('moises.persona_cuenta_bancaria pcb')
            ->join('moises.persona_natural pn', 'pn.id_persona', '=', 'pcb.id_persona')
            ->join('eliseo.tipo_cta_banco tcb', 'tcb.id_tipoctabanco', '=', 'pcb.id_tipoctabanco')
            ->join('eliseo.caja_entidad_financiera bnk', 'bnk.id_banco', '=', 'pcb.id_banco')
            ->where("pn.id_persona", '=', $id_persona)
            //->where("pcb.activo", '=', '1')
            ->select(
                'pcb.id_pbancaria',
                'pcb.cuenta',
                'pcb.id_banco',
                'bnk.nombre as nameBank',
                'pcb.id_tipoctabanco',
                'tcb.nombre  as n_cta',
                'pcb.cci',
                'pcb.activo'
            )
            ->orderBy('pcb.activo', 'desc')
            ->get();
        return $sql;
    }


    // ActuaLIZAR nivel superior
    public static function updateBank($request)
    {
        /*$file2 = $request->file('documento_url');
        if (!empty($file2)) {
            $ext = $file2->getClientOriginalExtension();
            $destino = 'gth'; //por ahora
            $uid = hash("md2", (string) microtime());
            $archivo     = $uid . '.' . strtolower($ext);
            $documento_url = $archivo;
            $file2->move($destino, $archivo);
            if (!empty($documento_url)) {

                $result = DB::table('moises.persona_cuenta_bancaria')
                    ->where('id_pbancaria', $request->id_pbancaria)
                    ->update(
                        [
                            'cuenta' =>  $request->cuenta,
                            'id_banco' => $request->id_banco,
                            'id_tipoctabanco' => $request->id_tipoctabanco,
                            'cci' => $request->cci,
                            'fec_inicio' => $request->fec_inicio,
                            'fec_fin' => $request->fec_fin,
                            'documento_url' => $documento_url,
                        ]
                    );
            } else {
                $nerror = 1;
            }
        } else {

            $result = DB::table('moises.persona_cuenta_bancaria')
                ->where('id_pbancaria', $request->id_pbancaria)
                ->update(
                    [
                        'cuenta' =>  $request->cuenta,
                        'id_banco' => $request->id_banco,
                        'id_tipoctabanco' => $request->id_tipoctabanco,
                        'cci' => $request->cci,
                        'fec_inicio' => $request->fec_inicio,
                        'fec_fin' => $request->fec_fin,
                    ]
                );
        }*/
        $result = DB::table('moises.persona_cuenta_bancaria')
            ->where('id_pbancaria', $request->id_pbancaria)
            ->update(
                [
                    'cuenta' =>  $request->cuenta,
                    'id_banco' => $request->id_banco,
                    'id_tipoctabanco' => $request->id_tipoctabanco,
                    'cci' => $request->cci
                ]
            );
        if ($result) {
            $response = [
                'success' => true,
                'message' => '',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede modificar',
            ];
        }
        return $response;
    }

    public static function getInformationAcademic($id_persona, $url)
    {
        $sql = DB::table('moises.persona_informacion_academica ia')
            ->where("ia.id_persona", '=', $id_persona)
            ->where("ia.estado", '=', "1")
            ->join('moises.carrera_profesional cp', 'cp.id_carrera_profesional', '=', 'ia.id_carrera', 'full outer')
            ->join('moises.instituciones ins', 'ins.ID_INSTITUICION', '=', 'ia.id_institucion', 'full outer')
            ->join('moises.tipo_pais pa', 'pa.id_tipopais', '=', 'ia.id_pais_procedencia', 'full outer')
            ->join('moises.situacion_educativa se', 'se.ID_SITUACION_EDUCATIVO', '=', 'ia.ID_SITUACION_EDUCATIVA', 'full outer')
            ->select(
                'ia.*',
                DB::raw("TO_CHAR(ia.FECHA_INGRESO,'DD/MM/YYYY') as fecha_ingreso"),
                DB::raw("TO_CHAR(ia.FECHA_EGRESO,'DD/MM/YYYY') as fecha_egreso"),
                DB::raw("TO_CHAR(ia.fecha_inscripcion,'DD/MM/YYYY') as fecha_inscripcion"),
                'ins.nombre as ins_nombre',
                'se.condicion as cond_sit',
                'cp.nombre as car_nombre',
                'se.nombre_corto',
                'pa.nombre as pais_proc',
                'pa.iso_a3 as iso',
                DB::raw("'" . $url . "/gth/'||ia.archivo AS archivo_url")
            )
            ->orderBy('ia.id_persona')
            ->get();
        if (sizeof($sql) > 0) {
            for ($i = 0; $i < sizeof($sql); $i++) {
                $archivo = $sql[$i]->archivo;
                if ($archivo) {
                    $filePath = $sql[$i]->archivo_url;
                    $file = null;
                    if (EmployeeData::get_http_response_code($filePath) == "200") {
                        $file = base64_encode(file_get_contents($filePath));
                    }
                    $sql[$i]->file = $file;
                }
            }
        }
        return $sql;
    }

    public static function getInfoSitPN($id_persona)
    {
        $sql = DB::table('moises.persona_natural ia')
            ->leftJoin('moises.situacion_educativa se', 'se.ID_SITUACION_EDUCATIVO', '=', 'ia.ID_SITUACION_EDUCATIVO')
            ->where("ia.id_persona", '=', $id_persona)
            ->select('se.*')
            //->get()
            ->first();
        return $sql;
    }

    public static function addInformationAcademic($request)
    {
        $response = [];
        $id_persona = $request->id_persona;
        $id_situacion_educativo = $request->id_situacion_educativo;
        $id_carrera_profesional = $request->id_carrera_profesional;
        $denominacion_grado = $request->denominacion_grado;
        $id_pais_procedencia = $request->id_pais_procedencia;
        $id_institucion = $request->id_institucion;
        $institucion_grado = $request->institucion_grado;
        $id_regimen_institucion = $request->id_regimen_institucion;
        $id_tipo_institucion = $request->id_tipo_institucion;
        $fecha_ingreso = $request->fecha_ingreso;
        $fecha_egreso = $request->fecha_egreso;
        $reg_sunedu = $request->reg_sunedu;
        $fecha_sunedu = $request->fecha_sunedu;
        $cod_universitario = $request->cod_universitario;
        $ap_univ = $request->ap_univ;
        //$tipo = $request->tipo;
        $estado_perf = $request->estado_perf;
        $grado_perf = $request->grado_perf;
        $denominacion_grado_perf = $request->denominacion_grado_perf;
        $id_pais_procedencia_perf = $request->id_pais_procedencia_perf;
        $id_regimen_perf = $request->id_regimen_perf;
        $id_institucion_perf = $request->id_institucion_perf;
        $institucion_perf = $request->institucion_perf;
        $filename = $request->filename;
        $file = $request->file('archivo');
        $destino = 'gth';
        if ($filename != "") {
            if (file_exists($destino . '/' . $filename)) {
                unlink($destino . '/' . $filename);
            }
            $file->move($destino, $filename);
        }

        $id_informacion_academica = ComunData::correlativo('moises.persona_informacion_academica', 'id_informacion_academica');
        $result = DB::table('moises.persona_informacion_academica')->insert(
            [
                "id_informacion_academica" => $id_informacion_academica,
                "id_persona" => $id_persona,
                "id_situacion_educativa" => $id_situacion_educativo,
                "id_carrera" => $id_carrera_profesional,
                "denominacion_grado" => $denominacion_grado,
                "id_pais_procedencia" => $id_pais_procedencia,
                "id_institucion" => $id_institucion,
                "institucion_grado" => $institucion_grado,
                "id_regimen" => $id_regimen_institucion,
                "id_tipo_inst" => $id_tipo_institucion,
                "fecha_ingreso" => $fecha_ingreso,
                "fecha_egreso" => $fecha_egreso,
                "inscripcion_sunedu" => $reg_sunedu,
                "fecha_inscripcion" => $fecha_sunedu,
                "codigo_universitario" => $cod_universitario,
                "ayuda_uni" => $ap_univ,
                //"tipo" => $tipo,
                "estado" => $request->estado,
                "estado_perf" => $estado_perf,
                "grado_perf" => $grado_perf,
                "denominacion_grado_perf" => $denominacion_grado_perf,
                "id_pais_procedencia_perf" => $id_pais_procedencia_perf,
                "id_regimen_perf" => $id_regimen_perf,
                "id_institucion_perf" => $id_institucion_perf,
                "institucion_perf" => $institucion_perf,
                "archivo" => $filename
            ]
        );

        if ($result) {
            //EmployeeData::setSituacionEducativa($id_persona);
            $response = [
                'success' => true,
                'message' => 'Se registró correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede insertar',
            ];
        }
        return $response;
    }

    public static function updateInformationAcademic($request)
    {
        $response = [];
        $result = false;
        $id_informacion_academica = $request->id_informacion_academica;
        $id_persona = $request->id_persona;
        $id_situacion_educativo = $request->id_situacion_educativo;
        $id_carrera_profesional = $request->id_carrera_profesional;
        $denominacion_grado = $request->denominacion_grado;
        $id_pais_procedencia = $request->id_pais_procedencia;
        $id_institucion = $request->id_institucion;
        $institucion_grado = $request->institucion_grado;
        $id_regimen_institucion = $request->id_regimen_institucion;
        $id_tipo_institucion = $request->id_tipo_institucion;
        $fecha_ingreso = $request->fecha_ingreso;
        $fecha_egreso = $request->fecha_egreso;
        $reg_sunedu = $request->reg_sunedu;
        $fecha_sunedu = $request->fecha_sunedu;
        $cod_universitario = $request->cod_universitario;
        $ap_univ = $request->ap_univ;
        //$tipo = $request->tipo;
        $estado_perf = $request->estado_perf;
        $grado_perf = $request->grado_perf;
        $denominacion_grado_perf = $request->denominacion_grado_perf;
        $id_pais_procedencia_perf = $request->id_pais_procedencia_perf;
        $id_regimen_perf = $request->id_regimen_perf;
        $id_institucion_perf = $request->id_institucion_perf;
        $institucion_perf = $request->institucion_perf;
        $estado = $request->estado;

        if ($estado == "0") {
            $result = DB::table('moises.persona_informacion_academica')
                ->where('id_informacion_academica', $id_informacion_academica)
                ->update(["estado" => $estado]);
        } else {
            $result = DB::table('moises.persona_informacion_academica')
                ->where('id_informacion_academica', $id_informacion_academica)
                ->update(
                    [
                        "id_persona" => $id_persona,
                        "id_situacion_educativa" => $id_situacion_educativo,
                        "id_carrera" => $id_carrera_profesional,
                        "denominacion_grado" => $denominacion_grado,
                        "id_pais_procedencia" => $id_pais_procedencia,
                        "id_institucion" => $id_institucion,
                        "institucion_grado" => $institucion_grado,
                        "id_regimen" => $id_regimen_institucion,
                        "id_tipo_inst" => $id_tipo_institucion,
                        "fecha_ingreso" => $fecha_ingreso,
                        "fecha_egreso" => $fecha_egreso,
                        "inscripcion_sunedu" => $reg_sunedu,
                        "fecha_inscripcion" => $fecha_sunedu,
                        "codigo_universitario" => $cod_universitario,
                        "ayuda_uni" => $ap_univ,
                        //"tipo" => $tipo,
                        "estado" => $estado,
                        "estado_perf" => $estado_perf,
                        "grado_perf" => $grado_perf,
                        "denominacion_grado_perf" => $denominacion_grado_perf,
                        "id_pais_procedencia_perf" => $id_pais_procedencia_perf,
                        "id_regimen_perf" => $id_regimen_perf,
                        "id_institucion_perf" => $id_institucion_perf,
                        "institucion_perf" => $institucion_perf
                    ]
                );
        }

        $destino = 'gth';
        $filename = $request->filename;
        $file = $request->file('archivo');
        if ($filename != "") {
            if (file_exists($destino . '/' . $filename)) {
                unlink($destino . '/' . $filename);
            }
            $file->move($destino, $filename);
            DB::table('moises.persona_informacion_academica')
                ->where('id_informacion_academica', $id_informacion_academica)
                ->update(["archivo" => $filename]);
        }
        if ($result) {
            //EmployeeData::setSituacionEducativa($id_persona);
            $response = [
                'success' => true,
                'message' => 'Modificado correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede modificar',
            ];
        }
        return $response;
    }

    public static function setSituacionEducativa($id_persona)
    {
        $res = true;
        $ia = DB::table('moises.persona_informacion_academica')->where('id_persona', $id_persona)->where("estado", "1")->max('id_situacion_educativa');
        $fb = DB::table('moises.persona_formacion_basica')->where('id_persona', $id_persona)->where("estado", "1")->max('id_situacion_educativa');
        $va = ($ia) ? $ia : 0;
        $vb = ($fb) ? $fb : 0;
        $vf = '';
        if (intval($va) > intval($vb)) {
            $vf = $va;
        } else {
            $vf = $vb;
        }
        $res = DB::table('moises.persona_natural')
            ->where('id_persona', $id_persona)
            ->update(["id_situacion_educativo" => $vf]);
        return $res;
    }

    public static function getBasicFormation($id_persona)
    {
        $sql = DB::table('moises.persona_formacion_basica fb')
            ->where("fb.id_persona", '=', $id_persona)
            ->where("fb.estado", '=', '1')
            ->join('moises.situacion_educativa se', 'se.ID_SITUACION_EDUCATIVO', '=', 'fb.ID_SITUACION_EDUCATIVA', 'full outer')
            ->select(
                'fb.*',
                DB::raw("TO_CHAR(fb.FECHA_INGRESO,'DD/MM/YYYY') as fecha_ingreso"),
                DB::raw("TO_CHAR(fb.FECHA_EGRESO,'DD/MM/YYYY') as fecha_egreso"),
                'se.nombre_corto'
            )
            ->orderBy('fb.ID_SITUACION_EDUCATIVA')
            ->get();
        return $sql;
    }

    public static function createBasicFormation($data)
    {
        $response = [];
        $toInsert = json_decode($data, true);
        $id_formacion_basica = ComunData::correlativo('moises.persona_formacion_basica', 'id_formacion_basica');
        $toInsert['id_formacion_basica'] = $id_formacion_basica;
        $affected = DB::table('moises.persona_formacion_basica')->insert($toInsert);


        if ($affected) {
            $response = [
                'success' => true
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede crear',
            ];
        }

        return $response;
    }

    public static function updateBasicFormation($id_basic_formation, $data)
    {
        $response = [];
        $toUpdate = json_decode($data, true);
        $affected = DB::table('moises.persona_formacion_basica')
            ->where('id_formacion_basica', $id_basic_formation)
            ->update($toUpdate);
        if ($affected) {
            $response = [
                'success' => true,
                'message' => 'Actualizado correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede actualizar',
            ];
        }

        return $response;
    }

    public static function uploadFileAcademic($request)
    {
        $response = [];
        $destino = 'gth';
        $affect = false;
        $filename = $request->filename;
        $file = $request->file('archivo');
        if ($filename != "") {
            if (file_exists($destino . '/' . $filename)) {
                unlink($destino . '/' . $filename);
            }
            $file->move($destino, $filename);
            $affect = DB::table($request->tabla)
                ->where($request->key, $request->id)
                ->update(["archivo" => $filename]);
        }
        if ($affect) {
            $response = [
                'success' => true,
                'message' => 'Modificado correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Error al modificar',
            ];
        }

        return $response;
    }

    public static function getAcademicTraining($id_persona, $url)
    {
        $sql = DB::table('moises.persona_formacion_capacitacion fc')
            ->where("fc.id_persona", '=', $id_persona)
            ->where("fc.estado", '=', '1')
            ->select(
                'fc.*',
                DB::raw("'" . $url . "/gth/'||fc.archivo AS url")
            )
            ->orderBy('fc.id_capacitacion')
            ->get();
        return $sql;
    }

    public static function createAcademicTraining($data)
    {
        $response = [];
        $toInsert = json_decode($data, true);
        $id_capacitacion = ComunData::correlativo('moises.persona_formacion_capacitacion', 'id_capacitacion');
        $toInsert['id_capacitacion'] = $id_capacitacion;
        $affected = DB::table('moises.persona_formacion_capacitacion')->insert($toInsert);
        if ($affected) {
            $response = [
                'success' => true
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede crear',
            ];
        }
        return $response;
    }

    public static function updateAcademicTraining($id_training, $data)
    {
        $response = [];
        $toUpdate = json_decode($data, true);
        $affected = DB::table('moises.persona_formacion_capacitacion')
            ->where('id_capacitacion', $id_training)
            ->update($toUpdate);
        if ($affected) {
            $response = [
                'success' => true,
                'message' => 'Actualizado correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede actualizar',
            ];
        }
        return $response;
    }

    public static function getAcademicArticle($id_persona, $url)
    {
        $sql = DB::table('moises.persona_acad_articulo fc')
            ->where("fc.id_persona", '=', $id_persona)
            ->where("fc.estado", '=', '1')
            ->select(
                'fc.*',
                DB::raw("'" . $url . "/gth/'||fc.archivo AS url")
            )
            ->orderBy('fc.id_articulo')
            ->get();
        return $sql;
    }

    public static function createAcademicArticle($data)
    {
        $response = [];
        $toInsert = json_decode($data, true);
        $id_articulo = ComunData::correlativo('moises.persona_acad_articulo', 'id_articulo');
        $toInsert['id_articulo'] = $id_articulo;
        $affected = DB::table('moises.persona_acad_articulo')->insert($toInsert);
        if ($affected) {
            $response = [
                'success' => true
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede crear',
            ];
        }
        return $response;
    }

    public static function updateAcademicArticle($id_article, $data)
    {
        $response = [];
        $toUpdate = json_decode($data, true);
        $affected = DB::table('moises.persona_acad_articulo')
            ->where('id_articulo', $id_article)
            ->update($toUpdate);
        if ($affected) {
            $response = [
                'success' => true,
                'message' => 'Actualizado correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede actualizar',
            ];
        }
        return $response;
    }

    public static function getAcademicProyection($id_persona, $url)
    {
        $sql = DB::table('moises.persona_acad_proyecto fc')
            ->where("fc.id_persona", '=', $id_persona)
            ->where("fc.estado", '=', '1')
            ->select(
                'fc.*',
                DB::raw("'" . $url . "/gth/'||fc.archivo AS url")
            )
            ->orderBy('fc.id_proyecto')
            ->get();
        return $sql;
    }

    public static function createAcademicProyection($data)
    {
        $response = [];
        $toInsert = json_decode($data, true);
        $id_proyecto = ComunData::correlativo('moises.persona_acad_proyecto', 'id_proyecto');
        $toInsert['id_proyecto'] = $id_proyecto;
        $affected = DB::table('moises.persona_acad_proyecto')->insert($toInsert);
        if ($affected) {
            $response = [
                'success' => true
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede crear',
            ];
        }
        return $response;
    }

    public static function updateAcademicProyection($id_project, $data)
    {
        $response = [];
        $toUpdate = json_decode($data, true);
        $affected = DB::table('moises.persona_acad_proyecto')
            ->where('id_proyecto', $id_project)
            ->update($toUpdate);
        if ($affected) {
            $response = [
                'success' => true,
                'message' => 'Actualizado correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede actualizar',
            ];
        }
        return $response;
    }

    public static function getAcademicBook($id_persona, $url)
    {
        $sql = DB::table('moises.persona_acad_libro fc')
            ->where("fc.id_persona", '=', $id_persona)
            ->where("fc.estado", '=', '1')
            ->select(
                'fc.*',
                DB::raw("'" . $url . "/gth/'||fc.archivo AS url")
            )
            ->orderBy('fc.id_libro')
            ->get();
        return $sql;
    }

    public static function createAcademicBook($data)
    {
        $response = [];
        $toInsert = json_decode($data, true);
        $id_libro = ComunData::correlativo('moises.persona_acad_libro', 'id_libro');
        $toInsert['id_libro'] = $id_libro;
        $affected = DB::table('moises.persona_acad_libro')->insert($toInsert);
        if ($affected) {
            $response = [
                'success' => true
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede crear',
            ];
        }
        return $response;
    }

    public static function updateAcademicBook($id_book, $data)
    {
        $response = [];
        $toUpdate = json_decode($data, true);
        $affected = DB::table('moises.persona_acad_libro')
            ->where('id_libro', $id_book)
            ->update($toUpdate);
        if ($affected) {
            $response = [
                'success' => true,
                'message' => 'Actualizado correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede actualizar',
            ];
        }
        return $response;
    }

    public static function getAcademicAsesoria($id_persona, $url)
    {
        $sql = DB::table('moises.persona_acad_asesoria fc')
            ->where("fc.id_persona", '=', $id_persona)
            ->where("fc.estado", '=', '1')
            ->select(
                'fc.*',
                DB::raw("'" . $url . "/gth/'||fc.archivo AS url")
            )
            ->orderBy('fc.id_asesoria')
            ->get();
        return $sql;
    }

    public static function createAcademicAsesoria($data)
    {
        $response = [];
        $toInsert = json_decode($data, true);
        $id_asesoria = ComunData::correlativo('moises.persona_acad_asesoria', 'id_asesoria');
        $toInsert['id_asesoria'] = $id_asesoria;
        $affected = DB::table('moises.persona_acad_asesoria')->insert($toInsert);
        if ($affected) {
            $response = [
                'success' => true
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede crear',
            ];
        }
        return $response;
    }

    public static function updateAcademicAsesoria($id_asesoria, $data)
    {
        $response = [];
        $toUpdate = json_decode($data, true);
        $affected = DB::table('moises.persona_acad_asesoria')
            ->where('id_asesoria', $id_asesoria)
            ->update($toUpdate);
        if ($affected) {
            $response = [
                'success' => true,
                'message' => 'Actualizado correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede actualizar',
            ];
        }
        return $response;
    }

    public static function getAcademicMembership($id_persona, $url)
    {
        $sql = DB::table('moises.persona_acad_membresia fc')
            ->where("fc.id_persona", '=', $id_persona)
            ->where("fc.estado", '=', '1')
            ->select(
                'fc.*',
                DB::raw("'" . $url . "/gth/'||fc.archivo AS urlfile")
            )
            ->orderBy('fc.id_membresia')
            ->get();
        return $sql;
    }

    public static function createAcademicMembership($data)
    {
        $response = [];
        $toInsert = json_decode($data, true);
        $id_membresia = ComunData::correlativo('moises.persona_acad_membresia', 'id_membresia');
        $toInsert['id_membresia'] = $id_membresia;
        $affected = DB::table('moises.persona_acad_membresia')->insert($toInsert);
        if ($affected) {
            $response = [
                'success' => true
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede crear',
            ];
        }
        return $response;
    }

    public static function updateAcademicMembership($id_membresia, $data)
    {
        $response = [];
        $toUpdate = json_decode($data, true);
        $affected = DB::table('moises.persona_acad_membresia')
            ->where('id_membresia', $id_membresia)
            ->update($toUpdate);
        if ($affected) {
            $response = [
                'success' => true,
                'message' => 'Actualizado correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede actualizar',
            ];
        }
        return $response;
    }

    public static function getAcademicJury($id_persona, $url)
    {
        $sql = DB::table('moises.persona_acad_jurado fc')
            ->where("fc.id_persona", '=', $id_persona)
            ->where("fc.estado", '=', '1')
            ->select(
                'fc.*',
                DB::raw("'" . $url . "/gth/'||fc.archivo AS url")
            )
            ->orderBy('fc.id_jurado')
            ->get();
        return $sql;
    }

    public static function createAcademicJury($data)
    {
        $response = [];
        $toInsert = json_decode($data, true);
        $id_jurado = ComunData::correlativo('moises.persona_acad_jurado', 'id_jurado');
        $toInsert['id_jurado'] = $id_jurado;
        $affected = DB::table('moises.persona_acad_jurado')->insert($toInsert);
        if ($affected) {
            $response = [
                'success' => true
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede crear',
            ];
        }
        return $response;
    }

    public static function updateAcademicJury($id_jurado, $data)
    {
        $response = [];
        $toUpdate = json_decode($data, true);
        $affected = DB::table('moises.persona_acad_jurado')
            ->where('id_jurado', $id_jurado)
            ->update($toUpdate);
        if ($affected) {
            $response = [
                'success' => true,
                'message' => 'Actualizado correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede actualizar',
            ];
        }
        return $response;
    }


    public static function getAcademicCategory($id_persona, $url)
    {
        $sql = DB::table('moises.persona_acad_categoria fc')
            ->where("fc.id_persona", '=', $id_persona)
            ->where("fc.estado", '=', '1')
            ->select(
                'fc.*',
                DB::raw("'" . $url . "/gth/'||fc.archivo AS url")
            )
            ->orderBy('fc.id_categoria')
            ->get();
        return $sql;
    }

    public static function createAcademicCategory($data)
    {
        $response = [];
        $toInsert = json_decode($data, true);
        $id_categoria = ComunData::correlativo('moises.persona_acad_categoria', 'id_categoria');
        $toInsert['id_categoria'] = $id_categoria;
        $affected = DB::table('moises.persona_acad_categoria')->insert($toInsert);
        if ($affected) {
            $response = [
                'success' => true
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede crear',
            ];
        }
        return $response;
    }

    public static function updateAcademicCategory($id_categoria, $data)
    {
        $response = [];
        $toUpdate = json_decode($data, true);
        $affected = DB::table('moises.persona_acad_categoria')
            ->where('id_categoria', $id_categoria)
            ->update($toUpdate);
        if ($affected) {
            $response = [
                'success' => true,
                'message' => 'Actualizado correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede actualizar',
            ];
        }
        return $response;
    }

    public static function getAcademicRegime($id_persona, $url)
    {
        $sql = DB::table('moises.persona_acad_regimen fc')
            ->where("fc.id_persona", '=', $id_persona)
            ->where("fc.estado", '=', '1')
            ->select(
                'fc.*',
                DB::raw("'" . $url . "/gth/'||fc.archivo AS url")
            )
            ->orderBy('fc.id_regimen')
            ->get();
        return $sql;
    }

    public static function createAcademicRegime($data)
    {
        $response = [];
        $toInsert = json_decode($data, true);
        $id_regimen = ComunData::correlativo('moises.persona_acad_regimen', 'id_regimen');
        $toInsert['id_regimen'] = $id_regimen;
        $affected = DB::table('moises.persona_acad_regimen')->insert($toInsert);
        if ($affected) {
            $response = [
                'success' => true
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede crear',
            ];
        }
        return $response;
    }

    public static function updateAcademicRegime($id_regimen, $data)
    {
        $response = [];
        $toUpdate = json_decode($data, true);
        $affected = DB::table('moises.persona_acad_regimen')
            ->where('id_regimen', $id_regimen)
            ->update($toUpdate);
        if ($affected) {
            $response = [
                'success' => true,
                'message' => 'Actualizado correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede actualizar',
            ];
        }
        return $response;
    }

    public static function getAcademicHour($id_persona, $url)
    {
        $sql = DB::table('moises.persona_acad_horas_semanales fc')
            ->where("fc.id_persona", '=', $id_persona)
            ->where("fc.estado", '=', '1')
            ->select(
                'fc.*'
            )
            ->orderBy('fc.id_horas_semanales')
            ->get();
        return $sql;
    }

    public static function createAcademicHour($data)
    {
        $response = [];
        $toInsert = json_decode($data, true);
        $id_horas_semanales = ComunData::correlativo('moises.persona_acad_horas_semanales', 'id_horas_semanales');
        $toInsert['id_horas_semanales'] = $id_horas_semanales;
        $affected = DB::table('moises.persona_acad_horas_semanales')->insert($toInsert);
        if ($affected) {
            $response = [
                'success' => true
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede crear',
            ];
        }
        return $response;
    }

    public static function updateAcademicHour($id_horas_semanales, $data)
    {
        $response = [];
        $toUpdate = json_decode($data, true);
        $affected = DB::table('moises.persona_acad_horas_semanales')
            ->where('id_horas_semanales', $id_horas_semanales)
            ->update($toUpdate);
        if ($affected) {
            $response = [
                'success' => true,
                'message' => 'Actualizado correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede actualizar',
            ];
        }
        return $response;
    }

    public static function getAcademicPrize($id_persona, $url)
    {
        $sql = DB::table('moises.persona_acad_distincion fc')
            ->where("fc.id_persona", '=', $id_persona)
            ->where("fc.estado", '=', '1')
            ->select(
                'fc.*',
                DB::raw("'" . $url . "/gth/'||fc.archivo AS url")
            )
            ->orderBy('fc.id_distincion')
            ->get();
        return $sql;
    }

    public static function createAcademicPrize($data)
    {
        $response = [];
        $toInsert = json_decode($data, true);
        $id_distincion = ComunData::correlativo('moises.persona_acad_distincion', 'id_distincion');
        $toInsert['id_distincion'] = $id_distincion;
        $affected = DB::table('moises.persona_acad_distincion')->insert($toInsert);
        if ($affected) {
            $response = [
                'success' => true
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede crear',
            ];
        }
        return $response;
    }

    public static function updateAcademicPrize($id_distincion, $data)
    {
        $response = [];
        $toUpdate = json_decode($data, true);
        $affected = DB::table('moises.persona_acad_distincion')
            ->where('id_distincion', $id_distincion)
            ->update($toUpdate);
        if ($affected) {
            $response = [
                'success' => true,
                'message' => 'Actualizado correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede actualizar',
            ];
        }
        return $response;
    }

    public static function getAcademicProfesional($id_persona, $url)
    {
        $sql = DB::table('moises.persona_acad_exp_profesional fc')
            ->where("fc.id_persona", '=', $id_persona)
            ->where("fc.estado", '=', '1')
            ->select(
                'fc.*'
            )
            ->orderBy('fc.id_experiencia')
            ->get();
        return $sql;
    }

    public static function createAcademicProfesional($data)
    {
        $response = [];
        $toInsert = json_decode($data, true);
        $id_experiencia = ComunData::correlativo('moises.persona_acad_exp_profesional', 'id_experiencia');
        $toInsert['id_experiencia'] = $id_experiencia;
        $affected = DB::table('moises.persona_acad_exp_profesional')->insert($toInsert);
        if ($affected) {
            $response = [
                'success' => true
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede crear',
            ];
        }
        return $response;
    }

    public static function updateAcademicProfesional($id_experiencia, $data)
    {
        $response = [];
        $toUpdate = json_decode($data, true);
        $affected = DB::table('moises.persona_acad_exp_profesional')
            ->where('id_experiencia', $id_experiencia)
            ->update($toUpdate);
        if ($affected) {
            $response = [
                'success' => true,
                'message' => 'Actualizado correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede actualizar',
            ];
        }
        return $response;
    }

    public static function getAcademicExperience($id_persona, $url)
    {
        $sql = DB::table('moises.persona_acad_exp_docencia fc')
            ->where("fc.id_persona", '=', $id_persona)
            ->where("fc.estado", '=', '1')
            ->select(
                'fc.*'
            )
            ->orderBy('fc.id_experiencia')
            ->get();
        return $sql;
    }

    public static function createAcademicExperience($data)
    {
        $response = [];
        $toInsert = json_decode($data, true);
        $id_experiencia = ComunData::correlativo('moises.persona_acad_exp_docencia', 'id_experiencia');
        $toInsert['id_experiencia'] = $id_experiencia;
        $affected = DB::table('moises.persona_acad_exp_docencia')->insert($toInsert);
        if ($affected) {
            $response = [
                'success' => true
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede crear',
            ];
        }
        return $response;
    }

    public static function updateAcademicExperience($id_experiencia, $data)
    {
        $response = [];
        $toUpdate = json_decode($data, true);
        $affected = DB::table('moises.persona_acad_exp_docencia')
            ->where('id_experiencia', $id_experiencia)
            ->update($toUpdate);
        if ($affected) {
            $response = [
                'success' => true,
                'message' => 'Actualizado correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede actualizar',
            ];
        }
        return $response;
    }

    public static function getAcademicAdmin($id_persona, $url)
    {
        $sql = DB::table('moises.persona_acad_exp_admin fc')
            ->where("fc.id_persona", '=', $id_persona)
            ->where("fc.estado", '=', '1')
            ->select(
                'fc.*'
            )
            ->orderBy('fc.id_experiencia')
            ->get();
        return $sql;
    }

    public static function createAcademicAdmin($data)
    {
        $response = [];
        $toInsert = json_decode($data, true);
        $id_experiencia = ComunData::correlativo('moises.persona_acad_exp_admin', 'id_experiencia');
        $toInsert['id_experiencia'] = $id_experiencia;
        $affected = DB::table('moises.persona_acad_exp_admin')->insert($toInsert);
        if ($affected) {
            $response = [
                'success' => true
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede crear',
            ];
        }
        return $response;
    }

    public static function updateAcademicAdmin($id_experiencia, $data)
    {
        $response = [];
        $toUpdate = json_decode($data, true);
        $affected = DB::table('moises.persona_acad_exp_admin')
            ->where('id_experiencia', $id_experiencia)
            ->update($toUpdate);
        if ($affected) {
            $response = [
                'success' => true,
                'message' => 'Actualizado correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede actualizar',
            ];
        }
        return $response;
    }

    public static function getAcademicLanguage($id_persona)
    {
        $sql = DB::table('moises.persona_acad_idioma fc')
            ->where("fc.id_persona", '=', $id_persona)
            ->where("fc.estado", '=', '1')
            ->select(
                'fc.*'
            )
            ->orderBy('fc.id_idioma')
            ->get();
        return $sql;
    }

    public static function createAcademicLanguage($data)
    {
        $response = [];
        $toInsert = json_decode($data, true);
        $id_idioma = ComunData::correlativo('moises.persona_acad_idioma', 'id_idioma');
        $toInsert['id_idioma'] = $id_idioma;
        $affected = DB::table('moises.persona_acad_idioma')->insert($toInsert);
        if ($affected) {
            $response = [
                'success' => true
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede crear',
            ];
        }
        return $response;
    }

    public static function updateAcademicLanguage($id_idioma, $data)
    {
        $response = [];
        $toUpdate = json_decode($data, true);
        $affected = DB::table('moises.persona_acad_idioma')
            ->where('id_idioma', $id_idioma)
            ->update($toUpdate);
        if ($affected) {
            $response = [
                'success' => true,
                'message' => 'Actualizado correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede actualizar',
            ];
        }
        return $response;
    }

    public static function getPersonData($id_persona, $url)
    {
        $sql = DB::table('moises.persona p')
            ->join('moises.persona_natural pn', 'pn.id_persona', '=', 'p.id_persona')
            ->join('moises.tipo_documento td', 'td.id_tipodocumento', '=', 'pn.id_tipodocumento')
            ->leftjoin('moises.tipo_pais tp', 'tp.id_tipopais', '=', 'pn.id_tipopais')
            ->where("p.id_persona", '=', $id_persona)
            ->select(
                'p.id_persona',
                'p.nombre',
                'p.paterno',
                'p.materno',
                'pn.id_tipodocumento',
                'tp.nombre as pais',
                'td.siglas as siglas',
                'pn.num_documento',
                'pn.sexo',
                'pn.id_tipopais',
                'pn.fec_nacimiento',
                'pn.correo',
                'pn.celular',
                'pn.telefono',
                'pn.documento_url',
                'pn.id_situacion_educativo',
                DB::raw("'" . $url . "/gth/'||pn.documento_url AS url")
            )
            ->get()->first();
        if ($sql) {
            $archivo = $sql->documento_url;
            if ($archivo) {
                $filePath = $sql->url;
                $file = null;
                if (EmployeeData::get_http_response_code($filePath) == "200") {
                    $file = base64_encode(file_get_contents($filePath));
                }
                $sql->file = $file;
            }
        }
        return $sql;
    }

    public static function regPersona($request)
    {
        $nerror                     = 0;
        $id_persona_new             = 0;
        $msgerror                   = '';
        $id_persona                 =   $request->id_persona;
        $nombre                     =   $request->nombre;
        $paterno                    =   $request->paterno;
        $materno                    =   $request->materno;
        $id_tipopais                =   $request->id_tipopais;
        $id_tipodocumento           =   $request->id_tipodocumento;
        $id_situacion_educativo     =   $request->id_situacion_educativo;
        $num_documento              =   $request->num_documento;
        $sexo                       =   $request->sexo;
        $fec_nacimiento             =   $request->fec_nacimiento;
        $correo                     =   $request->correo;
        $celular                    =   $request->celular;
        $telefono                   =   $request->telefono;
        $filename                   =   $request->filename;
        $file                       =   $request->file('archivo');
        $correo = ($correo == "" || $correo == null || $correo == "null") ? "" : $correo;
        $telefono = ($telefono == "" || $telefono == null || $telefono == "null") ? "" : $telefono;
        $celular = ($celular == "" || $celular == null || $celular == "null") ? "" : $celular;
        $id_situacion_educativo = ($id_situacion_educativo == "" || $id_situacion_educativo == null || $id_situacion_educativo == "null") ? "" : $id_situacion_educativo;
        $destino                    =   'gth';
        $a = "";
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin MOISES.REGISTRO_PERSONA.SP_REGISTRO_PERSONA(
                        :P_ID_PERSONA,
                        :P_NOMBRE, 
                        :P_PATERNO, 
                        :P_MATERNO,
                        :P_SEXO,
                        :P_FEC_NACIMIENTO,
                        :P_ID_TIPOPAIS,
                        :P_ID_TIPODOCUMENTO, 
                        :P_NUM_DOCUMENTO, 
                        :P_TELEFONO, 
                        :P_CELULAR,
                        :P_CORREO,
                        :P_ID_SITUACION_EDUCATIVO,
                        :P_ID_TIPOESTADOCIVIL,
                        :P_ID_PERSONA_NEW
                        ); end;");
        $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
        $stmt->bindParam(':P_NOMBRE', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':P_PATERNO', $paterno, PDO::PARAM_STR);
        $stmt->bindParam(':P_MATERNO', $materno, PDO::PARAM_STR);
        $stmt->bindParam(':P_SEXO', $sexo, PDO::PARAM_STR);
        $stmt->bindParam(':P_FEC_NACIMIENTO', $fec_nacimiento, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_TIPOPAIS', $id_tipopais, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_TIPODOCUMENTO', $id_tipodocumento, PDO::PARAM_INT);
        $stmt->bindParam(':P_NUM_DOCUMENTO', $num_documento, PDO::PARAM_STR);
        $stmt->bindParam(':P_TELEFONO', $telefono);
        $stmt->bindParam(':P_CELULAR', $celular);
        $stmt->bindParam(':P_CORREO', $correo);
        $stmt->bindParam(':P_ID_SITUACION_EDUCATIVO', $id_situacion_educativo);
        $stmt->bindParam(':P_ID_TIPOESTADOCIVIL', $a);
        $stmt->bindParam(':P_ID_PERSONA_NEW', $id_persona_new, PDO::PARAM_INT);
        //$stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        //$stmt->bindParam(':P_MSGERROR', $msgerror);
        $stmt->execute();


        if ($filename != null && $filename != "" && $filename != "null") {
            if (file_exists($destino . '/' . $filename)) {
                unlink($destino . '/' . $filename);
            }
            $file->move($destino, $filename);
            DB::table('moises.persona_natural')
                ->where('id_pesona', $id_persona)
                ->update(["documento_url" => $filename]);
        }

        $response = [
            'success' => true,
            'message' => $msgerror,
            'persona' => EmployeeData::getPersonData($id_persona_new, url(''))
        ];
        return $response;
    }
}
