@extends('layouts.pdfacademic')
@section('content')
    <style>
        .text-center {
            text-align: center;
        }

        .text-righ {
            text-align: right;
        }

        .houry-cell {
            padding: 0 !important;
        }

        /*
                .breakNow {
                    !*page-break-inside: avoid;*!
                    page-break-after: always;
                }*/

        ul {
            margin-top: 0;
            margin-bottom: 0;
            margin-left: -2rem;
            list-style-type: none;
        }

        li {
            margin-bottom: 0;
        }

        .table-justify {
            text-align: justify;
        }

        .table-textual tr td {
            border: none !important;
        }

        img.icon-status {
            width: 16px;
            height: 16px;
        }

        .nul {
            counter-reset: item;
        }

        .nul li {
            display: block
        }

        .nul li:before {
            content: counters(item, ".") " ";
            counter-increment: item
        }
    </style>


    <table style="margin-left: 0" border="0"> {{--PROVISIONAL--}}
        <thead>
        <tr>
            {{--            <td colspan="6"></td>--}}
            <td colspan="22" class="text-center">
                <b>ACUERDO DE FINANCIACI&Oacute;N - CONTRATO
                    N°: {{ isset($info['codigo_contrato']) ? $info['codigo_contrato'] : 'S/N'}}</b>
            </td>
            {{--            <td colspan="6"></td>--}}
        </tr>

        <tr>
            {{--            <th colspan="6"></th>--}}
            <td colspan="2">
                <img alt="perfil"
                     src="{{$photo}}"
                     class="photo " itemprop="logo" width="100">
            </td>
            <td colspan="10">
                <table class="tabla table-sm ">

                    <tbody>
                    <tr>
                        <td>C&oacute;digo:</td>
                        <td>{{ isset($info['codigo']) ? $info['codigo'] : 'Sin codigo'}}</td>
                    </tr>
                    <tr>
                        <td>Alumno:</td>
                        <td>{{ isset($info['nom_persona']) ? $info['nom_persona'] : 'Sin nombre'}}</td>
                    </tr>
                    <tr>
                        <td>{{ isset($info['nom_documento']) ? $info['nom_documento'] : 'Sigla: '}}</td>
                        <td>{{ isset($info['num_documento']) ? $info['num_documento'] : 'Sin numero'}}</td>
                    </tr>
                    <tr>
                        <td>Carrera:</td>
                        <td>{{ isset($info['nom_programa']) ? $info['nom_programa'] : 'Sin programa'}}</td>
                    </tr>
                    <tr>
                        <td>Resp. financ:</td>
                        <td>{{ isset($info['nom_resp']) ? $info['nom_resp'] : ''}}</td>
                    </tr>
                    <tr>
                        <td>Direc. Laboral:</td>
                        <td>{{isset($info['direccion_resp']) ? $info['direccion_resp'] : ''}}</td>
                    </tr>
                    <tr>
                        <td>Telefono:</td>
                        <td>{{isset($info['num_telefono_resp'])? $info['num_telefono_resp'] : ''}}</td>
                    </tr>
                    @if($modeContract == 'V')
                        <tr>
                            <td>Cr&eacute;ditos variados:</td>
                            <td>{{$tcreditoInVariation}}</td>
                        </tr>
                    @else
                        <tr>
                            <td>Cr&eacute;ditos:</td>
                            <td>{{$tcredito}}</td>
                        </tr>
                    @endif

                    <tr>
                        <td>Semestre:</td>
                        <td>{{isset($info['semestre'])? $info['semestre'] : ''}}</td>
                    </tr>

                    </tbody>
                </table>
            </td>
            <td colspan="10">
                <table class="table  table-sm table-bordered">
                    <thead>
                    <tr>
                        <th class="text-center">COBROS</th>
                        <th class="text-center">DESCUENTOS</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <table style="width: 100%">
                                <tbody>
                                @foreach($debits as $d)
                                    <tr>
                                        <td>{{$d->nombre}}</td>
                                        <td class="text-righ">{{number_format($d->importe,2)}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td><b>Total de cobros</b></td>
                                    <td class="text-righ"><b>{{number_format($contract->total_debito, 2)}}</b></td>
                                </tr>
                                </tfoot>
                            </table>
                            <div style="clear: both;"></div>
                        </td>
                        <td>


                            @foreach($credits as $k => $itm)

                                <table style="width: 100%">
                                    <thead>
                                    <tr>
                                        <th colspan="2" class="text-center"
                                            style="text-transform: uppercase">{{$k}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($itm as $crd)
                                        <tr>
                                            <td style="border-right: 0">{{$crd->nombre}}</td>
                                            <td style="border-left: 0"
                                                class="text-righ">{{number_format($crd->importe,2)}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>

                                </table>
                            @endforeach
                            <table style="width: 100%">
                                <tr>
                                    <td colspan="2" class="text-center">
                                        <b>Total de Descuentos: S./{{number_format($contract->total_credito, 2)}} </b>
                                    </td>
                                </tr>
                            </table>
                            <div style="clear: both;"></div>
                        </td>
                    </tr>
                    @if($plain and isset($plain->cuotas) and $plain->cuotas == '1')
                        <tr>
                            <td class="text-center" colspan="2" rowspan="6">
                                <br>
                                Importe total de contrato acad&eacute;mico:
                                <b>S./{{number_format($contract->total, 2)}}</b><br><br>
                                @if($plain->cuotas == '1')
                                    <label>Debitado en matrícula por pago al Contado - Plan {{$plain->cuotas}}:
                                        S/.<strong style="">{{number_format($contract->contado, 2)}}</strong>
                                    </label>
                                @else
                                    <label>Debitado en matrícula por pago en Armadas - Plan {{$plain->cuotas}}:
                                        <strong
                                                style="">S/.{{number_format($contract->matricula1cuota, 2)}}</strong>
                                    </label>
                                @endif
                            </td>
                        </tr>
                        <tr>
                        </tr>
                        <tr>
                        </tr>
                        <tr>
                        </tr>
                        <tr>
                        </tr>
                        <tr>
                        </tr>
                    @else

                        <tr>
                            <td class="text-center" colspan="2">
                                Importe total de contrato acad&eacute;mico:
                                <b>S./{{number_format($contract->total, 2)}}</b><br>
                                @if(isset($plain->cuotas) and $plain->cuotas == '1')
                                    <label>Debitado en matrícula por pago al Contado - Plan {{$plain->cuotas}}:
                                        S/.<strong style="">{{number_format($contract->contado, 2)}}</strong>
                                    </label>
                                @else
                                    <label>Debitado en matrícula por pago en Armadas -
                                        Plan {{isset($plain->cuotas) ? $plain->cuotas : 'N/D'}}:
                                        <strong
                                                style="">S/.{{number_format($contract->matricula1cuota, 2)}}</strong>
                                    </label>
                                @endif
                                <br>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-center">
                                <b>Pagos pendientes</b><br>
                                <ul>
                                    @foreach($plains as $i => $p)
                                        <li>
                                            {{$p->nro_cuota}}{{isset($ordinal[$i+1]) ? $ordinal[$i+1] : ''}} armada,
                                            {{$p->fecha_inicio}}.
                                            <b>S./{{property_exists($contract, 'mensual')?number_format($contract->mensual, 2) : ''}}</b>
                                            Fec. Venc.: {{$p->fecha_fin}}

                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endif
                    </tbody>

                </table>
                <div style="clear: both;"></div>
            </td>
        </tr>
        </thead>
    </table>
    <table class="table table-bordered table-sm" style="width: 100%">
        <thead>
        <tr class="text-center">
            <th>#</th>
            <TH>Ciclo</TH>
            <TH>Curso/Profesor</TH>
            <TH>GP.</TH>
            <TH>Cr.</TH>
            <TH>Hr.</TH>
            <TH>EAP</TH>
            <TH></TH>
        </tr>
        </thead>
        <tbody>
        @foreach($courses as $course)

            <tr style="font-size: 11px">
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">{{$course['ciclo']}}</td>
                <td class="text-left">{{$course['nombre_curso']}}<br>
                    {{$course['nombre_docente']}}
                    @if(count($course['practicas']) > 0)
                        <br>
                        @foreach($course['practicas'] as $prac)
                            <span style="padding-left: 5px">
                            {{$prac['codigo']}}, {{$prac['nombre']}}
                            </span>
                            <br>
                            <span style="padding-left: 5px">
                            {{$prac['docente']}}
                            </span>
                        @endforeach

                    @endif
                    <br>

                    @if($modeContract == 'V')
                        @if($course['codigo_estado_mov_current'] == 'I')
                            <span style="color: darkblue;">({{$course['estado_movimiento_current']}})</span>
                        @elseif($course['codigo_estado_mov_current'] == 'R')
                            <span style="color: darkred;">({{$course['estado_movimiento_current']}})</span>
                        @endif
                    @else
                        @if($course['codigo_estado_movimiento'] == 'R')
                            <span style="color: darkred;">(Retirado)</span>
                        @endif
                    @endif

                </td>
                <td class="text-center">{{$course['grupo']}}</td>
                <td class="text-center">{{$course['credito']}}</td>
                <td class="text-center">{{$course['ht']}}</td>
                <td class="text-center">{{$course['nombre_escuela']}}</td>
                <td class="text-center">
                    @if($modeContract == 'V')
                        @if($course['codigo_estado_mov_current'] == 'I')
                            <img src="{{asset('img/1.png')}}" class="icon-status"/>
                        @elseif($course['codigo_estado_mov_current'] == 'R')
                            <img src="{{asset('img/0.png')}}" class="icon-status"/>
                        @endif
                    @else
                        @if($course['codigo_estado_movimiento'] == null)
                            <img src="{{asset('img/1.png')}}" class="icon-status"/>
                        @elseif($course['codigo_estado_movimiento'] == 'R')
                            <img src="{{asset('img/0.png')}}" class="icon-status"/>
                        @endif
                    @endif

                </td>
            </tr>

        @endforeach
        </tbody>
        @if(count($courses) == 0)
            <tbody>
            <tr style="font-size: 11px">
                <td class="text-center" colspan="7"> no hay cursos seleccionados</td>
            </tr>
            </tbody>
        @endif
    </table>
    <br>

    @if(count($horary)>0)
        <div class="container">
            @foreach($horary as $keyTipo => $valuesTipo)
                <div class="text-center"><b>{{$keyTipo}}</b></div> {{--tipos--}}

                @foreach($valuesTipo as $keyModule => $valuesModule)
                    <div style=" margin: 0 auto; width: 100%;">
                        <div class="text-center"
                             style="font-size: 12px; padding-bottom: 5px">{{$keyModule}}</div> {{--periodos--}}

                        @foreach($valuesModule as $module)

                            <table class="table  table-sm">
                                <tr>
                                    @foreach($module['horario'] as $keyTurno=>$valuesHorario) {{--turno--}}
                                    <td>
                                        <table class="table  table-sm table-bordered" style="font-size: 8px">
                                            <thead>
                                            <tr class="text-center">
                                                <td colspan="8" style="font-size: 11px">
                                                    {{$keyTurno}}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th></th>
                                                @foreach($module['header'] as $day)
                                                    <th class="subtitle-2" style="width: 1rem"> {{$day}}</th>
                                                @endforeach
                                            </tr>
                                            </thead>
                                            <tbody>

                                            @foreach($valuesHorario as $horario)
                                                <tr>
                                                    <td class="caption text-center">
                                                        {{$horario['hora_inicio']}} <br> {{$horario['hora_fin']}}
                                                    </td>
                                                    @foreach($module['header'] as $day)
                                                        <td class="text-center" style="margin: 0;padding: 1px;">
                                                            @if(array_key_exists($day, $horario))
                                                                <div style="color: white; border-radius: 25%; background-color: #96344E; padding: 2px; height: 1.6rem; font-size: 11px">
                                                                    @if($horario[$day]['type'] != 'T')
                                                                        <span>{{$horario[$day]['id']}} {{$horario[$day]['type']}}</span>
                                                                    @elseif($horario[$day]['type'] == 'T')


                                                                        <p style="padding-top: -10px!important; font-weight: bold">{{$horario[$day]['id']}}</p>
                                                                    @endif
                                                                </div>
                                                            @endif

                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach

                                            </tbody>
                                        </table>
                                    </td>
                                    @endforeach
                                </tr>
                            </table>




                        @endforeach
                    </div>
                @endforeach

            @endforeach
        </div>
    @endif



    {{--    <table class="table table-sm mb-0 border-0 table-justify table-textual" align="center" border="0"--}}
    {{--           style="border: 0; page-break-before: always;">--}}
    <table class="table table-sm mb-0 border-0 table-justify table-textual" align="center" border="0"
           style="border: 0; ">
        <tr>
            <td class="text-center" colspan="2">
                <b>CONTRATO DE SERVICIOS EDUCATIVOS POR TALLER DE INVESTIGACIÓN</b></td>
        </tr>
        <tr>
            <td colspan="2">
                Conste por el presente documento, el Contrato de Servicios Educativos por Taller de Investigación, que
                celebran de una parte <b>UNIVERSIDAD PERUANA UNIÓN</b>, a la que en adelante
                se le denominará <b>LA UNIVERSIDAD</b>, con R.U.C. N° 20138122256, con domicilio legal
                en {{$sedeParam['address']}}, debidamente representada por su
                <b>Apoderada {{$sedeParam['resp']}}</b>,
                identificada con <b>D.N.I. N° {{$sedeParam['respDocument']}}</b> facultada según poder inscrito en el
                asiento A00049
                de la partida 01894897 del Libro de Asociaciones del Registro de Personas Jurídicas de la
                Oficina Registral de Lima, y de la otra parte
                el(a) Sr.(ta.) <b>{{ isset($info['nom_persona']) ? $info['nom_persona'] : 'Sin nombre'}}</b>,
                identificado(a)
                con {{ isset($info['nom_documento']) ? $info['nom_documento'] : 'Sin Sigla'}}
                N° {{ isset($info['num_documento']) ? $info['num_documento'] : 'Sin documento'}}, quien
                señala como domicilio para los efectos de este contrato
                en {{ isset($info['direccion']) ? $info['direccion'] : 'Sin direccion'}}, con teléfono móvil
                N° {{ isset($info['num_telefono']) ? $info['num_telefono'] : 'Sin número'}},
                con correo electrónico {{ isset($info['correo']) ? $info['correo'] : 'Sin correo'}}, a
                quien en adelante se le denominará <b>EL(A) ESTUDIANTE</b>, quien declara
                y acredita como su <b>responsable financiero</b>
                a:
                @if(isset($info['tipo_resp']) and $info['tipo_resp'] == 'PRONABEC')
                    PRONABEC con número de RUC: 20546798152 - PROGRAMA NACIONAL DE BECAS Y CREDITO EDUCATIVO  y
                    domiciliado(a)
                    en  Av. Arequipa N° 1935, distrito de Lince provincia, y departamento de Lima,
                @else
                    {{ isset($info['nom_resp']) ? $info['nom_resp'] : 'Sin responsable'}} con
                    D.N.I.  N°{{ isset($info['num_doc_resp']) ? $info['num_doc_resp'] : 'Sin número'}} y
                    domiciliada
                    en {{ isset($info['direccion_resp']) ? $info['direccion_resp'] : 'Sin direccion'}}, con
                    teléfono
                    móvil
                    N° {{ isset($info['num_telefono_resp']) ? $info['num_telefono_resp'] : 'Sin número telefono'}}
                    ,
                @endif
                en los términos y condiciones de las cláusulas siguientes:
                <br/><br/>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>PRIMERA: MARCO LEGAL. </b>- El presente contrato de servicios educativos, además de las cláusulas del
                mismo, se rige por las disposiciones legales vigentes, sin ser esta enumeración taxativa, por las
                siguientes:
                <br>
                <ul>
                    <li>1.1 Constitución Política del Perú</li>
                    <li>1.2 El Código Civil.</li>
                    <li>1.3 Ley General de Educación N° 28044.</li>
                    <li>1.4 Ley Universitaria N° 30220.</li>
                    <li>1.5 Ley de Protección a la Economía Familiar Respecto del Pago de Pensiones en Institutos,
                        Escuelas Superiores, Universidades y Escuelas de Posgrado Públicos y Privados N° 29947.
                    </li>
                    <li>1.6 El Estatuto de LA UNIVERSIDAD.</li>
                    <li>1.7 El Reglamento General de LA UNIVERSIDAD.</li>
                    <li>1.8 Los demás Reglamentos de LA UNIVERSIDAD.</li>
                    <li>1.9 Otras normas legales aplicables.</li>
                </ul>
                <br><br>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>SEGUNDA: DE LAS PARTES.</b><br>-La UNIVERSIDAD es una persona jurídica de derecho
                privado sin fines de lucro, creada por la Ley ° 23758 y denominada como tal por la Ley N° 26542,
                dedicada a la formación profesional universitaria a través de sus Facultades y Escuelas Profesionales,
                según los niveles y modalidades de estudios presencial y semipresencial y a distancia, en funcionamiento
                en la ciudad de Lima, Juliaca y Tarapoto. EL(A) ESTUDIANTE es una persona natural, con
                mayoría de edad, con plena capacidad de goce y de ejercicio, quien en pleno uso y ejercicio de sus
                facultades físicas, mentales y civiles y por decisión voluntaria a procedido a elegir, seguir, cursar,
                estudios de formación profesional universitaria en la carrera profesional ofertada por la
                <b>UNIVERSIDAD</b>, en su
                campus Lima. No obstante ser mayor de edad, EL(A) ESTUDIANTE declara y acredita que El(A) Responsable
                Financiero por sus estudios es:
                @if(isset($info['tipo_resp']) and $info['tipo_resp'] == 'PRONABEC')
                    PRONABEC con número de RUC: 20546798152.
                @else
                    {{ isset($info['nom_resp']) ? $info['nom_resp'] : 'Sin responsable'}} con
                    D.N.I. {{ isset($info['num_doc_resp']) ? $info['num_doc_resp'] : 'Sin número'}}.
                @endif

                @if(isset($info['es_beca']) and $info['es_beca'] != '1')
                    El Responsable Financiero de EL(A)
                    ESTUDIANTE es {{ isset($info['nom_persona']) ? $info['nom_persona'] : 'Sin nombre'}}
                    ,
                    identificado
                    con {{ isset($info['nom_documento']) ? $info['nom_documento'] : 'Sigla'}}
                    N° {{ isset($info['num_documento']) ? $info['num_documento'] : 'Sin documento'}}.
                @endif
                <br/><br/>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>TERCERA</b>: OBJETO DEL CONTRATO. - EL(A) ESTUDIANTE por decisión libre, acepta y declara que
                contrata los servicios educativos por Taller de Investigación ofertados por la UNIVERSIDAD.
                <br/><br/>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <b>CUARTA: DE LOS REQUISITOS.- EL(A) ESTUDIANTE</b> declara que, para hacer uso del servicio ofertado
                por
                <b>LA UNIVERSIDAD</b> ha seguido, obtenido previa y válidamente según corresponda el grado académico de
                bachiller y dentro del marco de la legislación educativa o superior no universitaria o superior
                universitaria, y que sobre los mismos no existe cuestionamiento extrajudicial o judicial o
                administrativo, civil o penal alguno, y caso contrario asume la responsabilidad en caso fueran
                invalidados o nulos y otorga el derecho y facultad a <b>LA UNIVERSIDAD</b> de declarar resuelto el
                presente contrato de pleno derecho, de conformidad con lo dispuesto en el artículo 1430° del Código
                Civil.

                <br/><br/>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>QUINTA</b>: OBLIGACIONES DE LA UNIVERSIDAD. - LA UNIVERSIDAD, en virtud del
                presente contrato, se obliga a:
                <br/>
                <ul>
                    <li>5.1 Desarrollar el programa de estudio establecido, a través de las sesiones de aprendizaje, en
                        los escenarios apropiados, que resguarden la vida y salud pública, dispuestos por LA UNIVERSIDAD
                        y autorizados por las disposiciones legales.
                    </li>
                    <li>5.2 Brindar el acceso, uso y disposición de los medios educativos a través de plataformas,
                        tecnologías y aplicativos, tales como: LAMB o portal académico, PatMOS, y otras creadas y por
                        crearse, en los días y tiempos habilitados y asignados, en el contexto de su marco axiológico y
                        estatutario vigente.
                    </li>
                    <li>
                        5.3 Cumplir y exigir el cumplimiento del Estatuto y de los demás reglamentos de LA UNIVERSIDAD.
                    </li>
                    <li>
                        5.4. Proporcionar periódicamente a EL(A) ESTUDIANTE, un estado de cuenta a través del portal del
                        estudiante, al cual tiene libre acceso, a fin de que este(a) pueda verificar su situación o
                        regularizar su deuda, comunicándole, cada vez que estime necesario, verbalmente y/o por escrito,
                        requerimientos de ponerse al día en el pago de cuotas atrasadas.
                        Para efectos del requerimiento de pago, ambas partes convienen de común acuerdo en que podrán
                        ser:
                        <ul>
                            <li>5.4.1 El requerimiento verbal</li>
                            <li>5.4.2 El requerimiento por escrito.</li>
                            <li>5.4.3 El requerimiento a través de la entrega de su estado de cuenta.</li>
                            <li>5.4.4 El requerimiento a través de medio electrónico (al correo electrónico y teléfono
                                móvil
                                consignado en la parte introductoria de este contrato, por mensajes de texto, whatsapp,
                                llamadas, videos y otros medios).
                            </li>
                            <li>5.4.5 El requerimiento a través de otras formas de comunicación valederos.</li>
                        </ul>
                    </li>

                    <li>
                        5.5 Ofrecer y tener habilitado a través su página Web bibliotecas electrónicas a las cuales
                        EL(A) ESTUDIANTE que pueden acceder con su respectivo usuario y contraseña, en el día y
                        tiempo
                        habilitado para su uso y disposición, sin más restricciones que las establecidas en el marco
                        axiológico y estatutario de LA UNIVERSIDAD y descritos en el presente contrato. LA
                        UNIVERSIDAD
                        también cuenta con el servicio de biblioteca en su campus, conforme las disposiciones vigentes.
                    </li>
                    <li>
                        5.6 Otras señaladas en el presente contrato o en el Estatuto o demás reglamentos de LA
                        UNIVERSIDAD.
                    </li>
                </ul>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>SEXTA</b>: OBLIGACIONES DE EL(A) ESTUDIANTE. EL(LA) ESTUDIANTE se obliga a: <br>
                <ul>
                    <li>6.1 Pagar oportunamente los costos del servicio educativo (Taller de Investigación) conforme al
                        cronograma de pagos, en la cuenta bancaria recaudadora que determine LA UNIVERSIDAD, con el
                        código que se asignará a EL(A) ESTUDIANTE para este fin, o directamente en la Caja de LA
                        UNIVERSIDAD.
                    </li>
                    <li>6.2 Asistir puntualmente a las sesiones programadas según fecha y horario establecido.</li>
                    <li>6.3 Asistir a las sesiones de asesoría, dispuestos por el asesor del Proyecto, e informar en
                        forma oportuna la realización de la misma.
                    </li>
                    <li>
                        6.4 Entregar el avance de los informes requeridos en el taller y los documentos exigidos en el
                        proceso de investigación en los tiempos programados.

                    </li>
                    <li>
                        6.5 Cumplir con presentar en los plazos determinados los documentos requeridos y pagos derivados
                        en cada fase del taller.
                    </li>
                    <li>
                        6.6 Cumplir con la sustentación de la tesis en formato articulo dentro del plazo reglamentario.
                    </li>
                    <li>6.7 El incumplimiento al proceso por cuenta de EL ESTUDIANTE, exime a LA UNIVERSIDAD de
                        responsabilidades por el avance y devoluciones por el servicio educativo ya brindado.
                    </li>
                    <li>6.8 Observar en todo momento el Estatuto, el Reglamento General, el Reglamento del Estudiante
                        Unionista y los demás reglamentos y normas que rigen la vida universitaria de LA UNIVERSIDAD,
                        los que declara conocer y se obliga a acatar y obedecer de manera voluntaria.
                    </li>
                    <li>6.9 Reconocer que LA UNIVERSIDAD es una entidad promovida por la Iglesia Adventista del Séptimo
                        Día (IASD) y que la vida universitaria: servicio educativo y actividades conexas y derivadas,
                        son reguladas en el marco axiológico y filosófico de su Promotora.
                    </li>
                    <li>6.10 Hacer uso del servicio educativo, sus medios educativos, tecnologías y/o plataformas: LAMB,
                        PatMOS y demás aplicativos, creados o por crearse, en el día y tiempo, momento asignado y
                        habilitado, observando el marco axiológico y estatutario de LA UNIVERSIDAD, de no desarrollo de
                        actividades de servicio educativo: enseñanza, aprendizaje, investigación y gestión, y servicios
                        conexos o derivados, en el día sábado, considerado desde las dieciocho (18) horas del día
                        viernes hasta las dieciocho (18) horas del día sábado.
                    </li>
                </ul>
                <ul>
                    <li>6.11 Suscribir y cumplir la Declaración Jurada respecto a su Responsable Financiero (Si la
                        información declarada fuera falsa perderá los beneficios que se le hayan otorgado y no podrá
                        accederá a ningún tipo de becas y/o descuentos).
                    </li>
                    <li>6.12 Respetar y valorar a todas las personas que integran la comunidad educativa universitaria.
                    </li>
                    <li>6.13 Cumplir los reglamentos académicos, de disciplina, propiedad intelectual y otros vinculados
                        al quehacer universitario en los escenarios de aprendizaje disponibles para los estudiantes y
                        autorizado por las disposiciones legales, y asumir las consecuencias y sanciones en caso de
                        incumplimiento.
                    </li>
                    <li>6.14 Mantener, observar y promover una conducta y comportamiento adecuado, de dominio propio,
                        respeto, sobre las autoridades universitarias, docentes, tutores o personal de LA UNIVERSIDAD y
                        de honestidad e integridad sobre los bienes, valores o enseres de propiedad de los mismos.
                    </li>
                    <li>6.15 Acatar y someterse a los procesos y procedimientos establecidos para los servicios
                        contratados, los disciplinarios y otros normados por LAUNIVERSIDAD.
                    </li>
                    <li>6.16 Las demás que expresamente se señalen en el presente contrato, en las normas legales, el
                        Estatuto y en los reglamentos respectivos de LA UNIVERSIDAD.
                    </li>
                </ul>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <br><br>
                <b> SÉPTIMA</b>: LOS PAGOS.
                <br>
                <ul>
                    <li>7.1 El monto por el derecho del servicio educativo por Taller de Investigación, es el que se
                        especifica en el ACUERDO DE FINANCIACION-CONTRATO N° 2020-1-14119 con su respectivo
                        fraccionamiento en cuotas durante el mismo, tal como lo establece la Ley N° 29571 (Código de
                        Defensa y Protección al Consumidor).
                    </li>


                    <li>
                        7.2 EL(A) ESTUDIANTE, de así considerarlo y de manera voluntaria, podrá cancelar en una sola
                        armada el monto, en forma adelantada, sin embargo, NO ESTA OBLIGADO(A) a ello.

                    </li>
                    <li>
                        7.3 Se pagan en tres (3) armadas, para facilitar el pago a EL(A) ESTUDIANTE.
                    </li>
                    <li>
                        7.4 DESCUENTOS: En caso de pago adelantado al contado de todo el servicio educativo, habrá un
                        porcentaje de descuento. El valor por servicio educativo y su respectivo cronograma de pagos,
                        están especificados en el ACUERDO DE FINANCIACION- CONTRATO N° 2020-1-14119 que EL(A) ESTUDIANTE
                        declara haberlo suscrito, conocerlo y estar totalmente conforme, antes de firmar el presente
                        documento.
                    </li>

                </ul>
                <br>
                <br>


            </td>
        </tr>

        <tr>
            <td colspan="2">
                <b> OCTAVA</b>: DE LA DURACIÓN O PLAZO. La duración del presente contrato computado desde el primer día de clases hasta el final según cronograma establecido. En caso de
                caso fortuito o fuerza mayor, el plazo de duración del presente contrato será modificado o ampliado
                automáticamente, hasta el término de la reprogramación académica autorizada por LA UNIVERSIDAD.


                <br><br>
                <b> NOVENA</b>: CAUSALES DE RESOLUCIÓN. El presente contrato se resolverá, terminará, por una de las
                siguientes causas:
                <ul>
                    <li>9.1 Por mutuo acuerdo entre las partes.
                    </li>
                    <li>9.2 Por incumplimiento por parte de EL(A) ESTUDIANTE del Estatuto, Reglamento General,
                        reglamentos y normas de LA UNIVERSIDAD, que impliquen sanción de expulsión o retiro definitivo.
                    </li>
                    <li>9.3 Incumplimiento de las obligaciones por EL(A) ESTUDIANTE. En su caso LA UNIVERSIDAD
                        determina las acciones legales correspondientes.
                    </li>
                    <li>9.4 Unilateralmente por decisión de EL(A) ESTUDIANTE, en razón del retiro definitivo, y previo
                        cumplimiento de las deudas pendientes, y comunicación a LA UNIVERSIDAD por escrito. A EL(A)
                        ESTUDIANTE se le cobra las pensiones a la fecha de solicitud del retiro. En ningún caso habrá
                        devolución de cuotas ya cobradas antes de la fecha de la solicitud de retiro y/o por tramites y
                        avances ya realizados o solicitados.
                    </li>
                </ul>
                <br><br>
            </td>

        </tr>

        <tr>
            <td colspan="2">

                <b> DÉCIMO: ACEPTACIÓN, CONFORMIDAD Y SUSCRIPCIÓN.</b> Ambas partes reconocen que el presente Contrato
                se formaliza, perfecciona y surte todos los efectos de suscripción, en todos sus extremos, contenido y
                alcances, vinculando a las partes, con la suscripción del soporte físico de este contrato o con la
                aceptación electrónica de los mismos contratantes, lo que será hecho cuando EL(A) ESTUDIANTE pulse el
                botón ACEPTO o al realizar la matrícula del semestre académico y al acuse de recibo otorgada a LA
                UNIVERSIDAD. Esta comunicación se realizará al correo electrónico o al teléfono móvil consignado en la
                parte introductoria de este contrato, que EL(A) ESTUDIANTE declara que es el medio oficial de
                comunicación (por cualesquier medio o red social que de constancia o acuse de recibo o conformidad) con
                LA UNIVERSIDAD respecto a los efectos y ejecución del presente contrato.

                <br><br>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b> DÉCIMO PRIMERA</b>: JURISDICCIÓN Y COMPETENCIA. En caso de controversia sobre el contenido y alcance
                del presente contrato y su objeto, ambas partes renuncian el Juez de su domicilio y se someten a la
                jurisdicción de los jueces y tribunales de la Corte Superior de Justicia de Lima. Dejando constancia
                que en todo momento buscaran la solución pacífica y armoniosa de sus diferencias previamente y mutuas
                concesiones. Estando conformes con todo lo estipulado, normado, lo ratifica y acusa recibo de su
                aceptación plena, total y libre de las condiciones en este contrato, y sin necesidad de suscribirlo
                físicamente.
                <br><br>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>DÉCIMO SEGUNDA</b>: SERVICIO EDUCATIVO EN EL CONEXTO DE EMERGENCIA SANITARIA. En el contexto de la
                emergencia sanitaria COVID-19 y por mandato de las disposiciones legales vigentes que regulan el
                funcionamiento de LA UNIVERSIDAD, el servicio educativo de formación profesional universitaria, materia
                del presente contrato y sus actividades académicas, conexas y derivadas son otorgadas en la modalidad no
                presencial, virtual u online, haciendo uso y a través de los medios educativos o tecnológicos, tales
                como LAMB o portal académico, PatMOS y otros, creados o por crearse, para el uso y disposición de EL(LA)
                ESTUDIANTE, en los días y tiempos asignados y habilitados, salvo las limitación de acceso y uso
                únicamente en el día viernes desde las dieciocho (18) horas hasta las dieciocho (18) horas del día
                sábado.

                <br><br>
                Estando conformes con todo lo estipulado, normado, lo ratifican y acusa recibo de su aceptación plena,
                total y libre de las condiciones en este contrato, y sin necesidad de suscribirlo físicamente.
            </td>
        </tr>

        {{--<tr>
            <td colspan="2">

            </td>

        </tr>--}}

        <tr>
            <br><br>
            <td class="text-center" colspan="2" style="border: none"><br>
                <span class="text-center">{{$nowDate}}</span>
                <br><br><br><br><br><br><br><br>
            </td>
        </tr>
        <tr style="border: none">
            <td class="text-center" style="border: none">
                <div class="signature">
                    <img src="{{asset('img/signatures/sig_upeu.png')}}" width="220"/>
                    <div class="signature-text">
                        <label for="">________________________________________</label><br>
                        <span>{{$sedeParam['resp']}}</span><br>
                        <span>{{$sedeParam['apo']}}</span><br>
                        <span>UNIVERSIDAD PERUANA UNI&Oacute;N</span>
                    </div>
                </div>
            </td>
            <td class="text-center" style="border: none">______________________________<br>
                {{ isset($info['nom_persona']) ? $info['nom_persona'] : 'Sin nombre'}} <br>
                {{ isset($info['nom_documento']) ? $info['nom_documento'] : 'Sigla'}}
                : {{ isset($info['num_documento']) ? $info['num_documento'] : 'Sin documento'}} EL(A) ESTUDIANTE <br>
                Universidad Peruana Unión<br>
                {{$sedeParam['depto']}}


            </td>
        </tr>
    </table>
@endsection
