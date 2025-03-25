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
        .texto {
            font-size: 10px !important;
            padding-top: 0px !important;
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
                                            @if($d->ver_hijo=='S')
                                                <td  colspan="2">{{$d->nombre}}</td>
                                            @endif
                                            @if($d->ver_hijo=='N')
                                                <td>{{$d->nombre}}</td>
                                            @endif
                                            @if($d->ver_hijo=='')
                                                <td><ul><li  type="square">{{$d->nombre}}</li></ul></td>
                                            @endif
                                            @if($d->ver_hijo!='S')
                                            <td  class="text-right" style="border-left: 0">{{number_format($d->importe,2)}}</td>
                                            @endif 

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
                                            <th colspan="2" class="text-center" style="text-transform: uppercase">{{$k}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($itm as $crd)
                                            <tr>
                                                
                                                @if($crd->ver_hijo=='S')
                                                    <td  colspan="2">{{$crd->nombre}}</td>
                                                @endif
                                                @if($crd->ver_hijo=='N')
                                                    <td>{{$crd->nombre}}</td>
                                                @endif
                                                @if($crd->ver_hijo=='')
                                                    <td><ul><li  type="square">{{$crd->nombre}}</li></ul></td>
                                                @endif
                                                @if($crd->ver_hijo!='S')
                                                <td  class="text-right" style="border-left: 0">{{number_format($crd->importe,2)}}</td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td><b>Total de Descuentos</b></td>
                                                <td class="text-righ"><b>{{number_format($contract->total_credito, 2)}}</b></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                @endforeach
                               
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
                                            {{$i+2}}{{isset($ordinal[$i+1]) ? $ordinal[$i+1] : ''}} armada,
                                            {{$p->fecha_inicio}}.
                                            <b>S./{{property_exists($contract, 'mensual_ens_resi')?number_format($contract->mensual_ens_resi, 2) : ''}}</b>
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
                            <img src="{{public_path('img/1.png')}}" class="icon-status"/>
                        @elseif($course['codigo_estado_mov_current'] == 'R')
                            <img src="{{public_path('img/0.png')}}" class="icon-status"/>
                        @endif
                    @else
                        @if($course['codigo_estado_movimiento'] == null)
                            <img src="{{public_path('img/1.png')}}" class="icon-status"/>
                        @elseif($course['codigo_estado_movimiento'] == 'R')
                            <img src="{{public_path('img/0.png')}}" class="icon-status"/>
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
            <td class="text-center" colspan="2"><b> CONTRATO DE SERVICIOS EDUCATIVOS DE FORMACIÓN PROFESIONAL  @if ($sedeParam['id_depto'] == '8')   TÉCNICA   @else   UNIVERSITARIA    @endif</b></td>
        </tr>
        <tr>
            <td colspan="2">
                Conste por el presente documento, el Contrato de Servicios Educativos de Formación Profesional
                 @if ($sedeParam['id_depto'] == '8') Técnica @else Universitaria @endif, que celebran de una parte <b>UNIVERSIDAD PERUANA UNIÓN</b>, a la que en adelante
                se le denominará <b>LA UNIVERSIDAD</b>, con R.U.C. N° 20138122256, con domicilio legal
                en {{$sedeParam['address']}}, debidamente representada por su
                <b>Apoderado(a) {{$sedeParam['resp']}}</b>,
                identificada con <b>D.N.I. N° {{$sedeParam['respDocument']}}</b> facultada según poder inscrito en el
                asiento A00065  
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


                {{--<br>
                Contrato que se rige por la Constitución Política del Estado, el Código Civil, la Ley General de
                Educación N° 28044, la Ley Universitaria N° 30220, la Ley de Protección a la Economía Familiar
                Respecto
                del Pago de Pensiones en Institutos, Escuelas Superiores, Universidades y Escuelas de Posgrado
                Públicos
                y Privados N° 29947, el Estatuto y los Reglamentos de <b>LA UNIVERSIDAD</b> y las demás normas
                legales
                aplicables, así como por las condiciones contenidas en las siguientes cláusulas:--}}
                <br/><br/>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>PRIMERA: MARCO LEGAL. </b>- El presente contrato de servicios educativos de formación profesional
                técnica, además de las
                cláusulas del mismo, se  rige por las disposiciones legales vigentes, sin ser esta enumeración taxativa,
                por las siguientes:
                <br>
                <ul> @if ($sedeParam['id_depto'] == '8') 
                    <li>1.1. Constitución Política del Perú </li>
                    <li>1.2. Código Civil. </li>
                    <li>1.3. Ley General de Educación N° 28044. </li>
                    <li>1.4. Ley Universitaria N° 30220. </li>
                    <li>1.5. Ley N° 30512, Ley de Institutos y Escuelas de Educación Superior y de la Carrera Pública de sus Docentes. </li>
                    <li>1.6. Decreto Supremo N° 010-2017-MINEDU, Reglamento de la Ley N° 30512, Ley de Institutos y Escuelas de Educación Superior y de la Carrera Pública de sus Docentes.  </li>
                    <li>1.7. Ley de Protección a la Economía Familiar Respecto del Pago de Pensiones en Institutos, Escuelas Superiores, Universidades y Escuelas de Posgrado Públicos y Privados N° 29947. </li>
                    <li>1.8. Estatuto de LA UNIVERSIDAD. </li>
                    <li>1.9. Reglamentos del IEST Privado "Adventistas del Titicaca". </li>
                    <li>1.10. Reglamentos de LA UNIVERSIDAD. </li>
                    <li>1.11. Otras normas legales aplicables. </li>

                    @else 
                    
                    <li>1.1. Constitución Política del Perú</li>
                    <li>1.2. El Código Civil.</li>
                    <li>1.3. Ley General de Educación N° 28044.</li>
                    <li>1.4. Ley Universitaria N° 30220.</li>
                    <li>1.5. Ley de Protección a la Economía Familiar Respecto del Pago de Pensiones en Institutos,
                        Escuelas Superiores, Universidades y Escuelas de Posgrado Públicos y Privados N° 29947.
                    </li>
                    <li>1.6. El Estatuto de LA UNIVERSIDAD.</li>
                    <li>1.7. Los Reglamentos de LA UNIVERSIDAD.</li>
                    <li>1.8. Otras normas legales aplicables.</li>
                    @endif
                </ul>
                <br><br>
            </td>
        </tr>
        <tr>
            <td colspan="2">
            @if ($sedeParam['id_depto'] == '8') 
            
            <b>SEGUNDA: DE LAS PARTES.</b><br>

            <b>2.1. LA UNIVERSIDAD </b> es una persona jurídica de derecho privado sin fines de lucro, creada por la Ley ° 23758 y denominada como tal por la Ley N° 26542, 
            dedicada a la formación profesional universitaria a través de sus Facultades y Escuelas Profesionales, según los niveles y modalidades de estudios presencial 
            y semipresencial y a distancia, en funcionamiento en la ciudad de Lima, Juliaca y Tarapoto; asimismo cuenta con un centro universitario de prestación de 
            servicios educativos no universitario denominado IEST Privado "Adventistas del Titicaca" (en adelante, ISTAT), el cual, según ley, tiene autonomía económica, 
            administrativa y académica. 

            <b>2.2. EL(A) ESTUDIANTE</b> es una persona natural, con mayoría de edad, con plena capacidad de goce y de ejercicio, quien en pleno uso y ejercicio de sus 
            facultades físicas, mentales y civiles y por decisión voluntaria y libre ha procedido a elegir, seguir, cursar, estudios de formación técnica o profesional técnica 
            ofertada por LA UNIVERSIDAD, en su campus Juliaca, a través del ISTAT. No obstante ser mayor de edad, EL(A) ESTUDIANTE declara y acredita que El(A) 
            Responsable Financiero por sus estudios es:  {{ isset($info['nom_resp']) ? $info['nom_resp'] : 'Sin responsable'}} con
                    D.N.I. {{ isset($info['num_doc_resp']) ? $info['num_doc_resp'] : 'Sin número'}}.

            @else 
                <b>SEGUNDA: DE LAS PARTES.</b><br>-La UNIVERSIDAD es una persona jurídica de derecho
                privado sin
                fines de lucro,
                creada por la Ley ° 23758 y denominada como tal por la Ley N° 26542, dedicada a la formación
                profesional
                universitaria a través de sus Facultades y Escuelas Profesionales, según los niveles y
                modalidades de
                estudios presencial y semipresencial y a distancia, en funcionamiento en la ciudad de Lima, Juliaca y
                Tarapoto. EL(A) ESTUDIANTE es una persona
                natural, con
                mayoría
                de edad, con plena capacidad de goce y de ejercicio, quien en pleno uso y ejercicio de sus
                facultades
                físicas, mentales y civiles y por decisión voluntaria a procedido a elegir, seguir, cursar,
                estudios de
                formación profesional universitaria en la carrera profesional ofertada por la <b>UNIVERSIDAD</b>, en su
                campus Lima. No obstante ser mayor de edad, EL(A) ESTUDIANTE declara y acredita que El(A) Responsable
                Financiero
                por sus estudios
                es:
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


            @endif


            
                <br/><br/>
            </td>
        </tr>
        <tr>
            <td colspan="2">

            @if ($sedeParam['id_depto'] == '8') 
            
            <b>TERCERA: OBJETO DEL CONTRATO.- </b> 
            El objeto del presente contrato es brindar por parte de LA UNIVERSIDAD a EL(A) ESTUDIANTE, los servicios educativos de formación técnica o 
            profesional técnica ofertados por LA UNIVERSIDAD a través del ISTAT, en el programa de estudios libremente elegido por EL(A) ESTUDIANTE.

            @else 
                <b>TERCERA</b>: OBJETO DEL CONTRATO. - EL(A) ESTUDIANTE por decisión libre, acepta y declara que
                contrata
                los
                servicios educativos de formación profesional universitaria ofertados por la UNIVERSIDAD
                en la
                carrera profesional elegida.
                <br/><br/>
            @endif
            </td>
        </tr>

        <tr>
            <td colspan="2">
            @if ($sedeParam['id_depto'] == '8') 
                <b>CUARTA: REQUISITOS.- </b>
                EL(A) ESTUDIANTE declara que, para cursar estudios en el programa de estudios elegida y ofertada por LA UNIVERSIDAD a través del ISTAT, ha 
                seguido, obtenido, culminado previa y válidamente según corresponda sus estudios de educación básica regular o técnicos profesionales o profesionales técnicos 
                o universitarios y dentro del marco de la legislación educativa o superior no universitaria o superior universitaria, y que sobre los mismos no existe 
                cuestionamiento extrajudicial o judicial o administrativo, civil o penal alguno, y caso contrario asume la responsabilidad en caso fueran invalidados o nulos
                 y otorga el derecho y facultad a LA UNIVERSIDAD de declarar la invalidez o nulidad de los estudios cursados en la misma.

            @else 
            
                <b>CUARTA: DE LOS REQUISITOS.- EL(A) ESTUDIANTE</b> declara que, para cursar estudios en la
                carrera
                profesional
                elegida y ofertada por <b>LA UNIVERSIDAD</b>, ha seguido, obtenido previa y válidamente según
                corresponda sus
                estudios de educación básica regular o técnicos profesionales o profesionales técnicos o
                universitarios
                y dentro del marco de la legislación educativa o superior no universitaria o superior
                universitaria, y
                que sobre los mismos no existe cuestionamiento extrajudicial o judicial o administrativo, civil
                o penal
                alguno, y caso contrario asume la responsabilidad en caso fueran invalidados o nulos y otorga el
                derecho
                y facultad a <b>LA UNIVERSIDAD</b> de declarar la invalidez o nulidad de los estudios cursados
                en la
                misma.
            @endif 
                <br/><br/>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>QUINTA</b>: OBLIGACIONES DE LA UNIVERSIDAD. - LA UNIVERSIDAD, en virtud del
                presente contrato, se obliga a:
                <br/>
                <ul>
                    <li>5.1. Ofrecer una educación integral, de acuerdo con los fines de la Educación Adventista y
                        los planes
                        de
                        estudio de la carrera correspondiente.
                    </li>
                    <li>5.2. Desarrollar los planes y programas de estudios establecidos, a través de las sesiones de
                        aprendizaje, en los escenarios apropiados, que resguarden la vida y salud pública, dispuestos
                        por LA UNIVERSIDAD y autorizados por las disposiciones legales.
                    </li>
                    <li>
                        5.3. Brindar el acceso, uso y disposición de los medios educativos a través de plataformas,
                        tecnologías y aplicativos, tales como: LAMB o portal académico, LMS, y otras creadas y por
                        crearse, en los días y tiempos habilitados y asignados, en el contexto de su marco axiológico y
                        estatutario vigente.
                    </li>
                    <li>
                        5.4. Proporcionar periódicamente a EL(A) ESTUDIANTE informe sobre su avance académico.
                    </li>
                    <li>5.5. Cumplir y exigir
                        el cumplimiento del Estatuto y de los demás reglamentos  @if ($sedeParam['id_depto'] == '8') del ISTAT. @else de LA UNIVERSIDAD. @endif 
                    </li>
                    <li>5.6. Proporcionar
                        periódicamente a EL(A) ESTUDIANTE, un estado de cuenta a través del portal del
                        estudiante, al
                        cual
                        tiene libre acceso, a fin de que este(a) pueda verificar su situación o regularizar su
                        deuda,
                        comunicándole, cada vez que estime necesario, verbalmente y/o por escrito,
                        requerimientos de
                        ponerse
                        al día en el pago de pensiones atrasadas.
                        <br>
                        Para efectos del requerimiento de pago, ambas partes convienen de común acuerdo en que
                        podrán
                        ser @if ($sedeParam['id_depto'] == '8')  a través de @endif :

                        <ul>
                            <li>5.6.1. El requerimiento verbal</li>
                            <li>5.6.2. El requerimiento por escrito.</li>
                            <li>5.6.3. El requerimiento a través de la entrega de su estado de cuenta.</li>
                            <li>5.6.4. El requerimiento a través de medio electrónico (al correo electrónico y teléfono
                                móvil
                                consignado en la parte introductoria de este contrato, por mensajes de texto, whatsapp,
                                llamadas, videos y otros medios).
                            </li>
                            <li>5.6.5. El requerimiento a través de otras formas de comunicación valederos.</li>
                        </ul>
                    </li>
                    <li>
                        5.7. Ofrecer y tener habilitado a través su página Web bibliotecas electrónicas a las cuales
                        EL(A) ESTUDIANTE @if ($sedeParam['id_depto'] == '8') podrá  @else que pueden @endif  acceder con su respectivo usuario y contraseña, en el día y
                        tiempo
                        habilitado para su uso y disposición, sin más restricciones que las establecidas en el marco
                        axiológico y estatutario de LA UNIVERSIDAD y descritos en el presente contrato. @if ($sedeParam['id_depto'] == '8') El ISTAT  @else  LA
                        UNIVERSIDAD  @endif
                        también cuenta con el servicio de biblioteca en su campus, conforme las disposiciones  @if ($sedeParam['id_depto'] == '8') legales  @endif vigentes.
                    </li>
                    <li>
                        5.8 Otras señaladas en el presente contrato o en el Estatuto o demás reglamentos  @if ($sedeParam['id_depto'] == '8') del ISTAT y   @else de  @endif LA  UNIVERSIDAD.
                    </li>
                </ul>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>SEXTA</b>: OBLIGACIONES DE EL(A) ESTUDIANTE. EL(LA) ESTUDIANTE se obliga a: <br>
                <ul>
                    <li>6.1. Pagar oportunamente los costos del servicio educativo (matrícula y pensiones) conforme
                        al
                        cronograma de pagos de la cláusula sétima, en la cuenta bancaria recaudadora que
                        determine LA
                        UNIVERSIDAD, con el código que se asignará a EL(A) ESTUDIANTE para este
                        fin, o
                        directamente en la Caja de LA UNIVERSIDAD.
                    </li>
                    <li>6.2. Acudir y participar de las reuniones que se convoquen por @if ($sedeParam['id_depto'] == '8') por el ISTAT @else  LA UNIVERSIDAD. @endif </li>
                    <li>6.3. Observar en todo momento el Estatuto, el Reglamento del Estudiante Unionista y los demás
                        reglamentos y
                        normas
                        que rigen la vida   @if ($sedeParam['id_depto'] == '8') estudiantil del ISTAT  @else universitaria de LA UNIVERSIDAD @endif , los que declara conocer y se obliga a acatar
                        y obedecer de manera voluntaria.
                    </li>
                    <li>
                        6.4. Reconocer que @if ($sedeParam['id_depto'] == '8') el ISTAT  @else LA UNIVERSIDAD @endif es una entidad @if ($sedeParam['id_depto'] == '8') educativa @endif promovida 
                        @if ($sedeParam['id_depto'] == '8') LA UNIVERSIDAD  @else por la Iglesia Adventista del Séptimo    Día (IASD)  @endif 
                        y que la vida @if($sedeParam['id_depto'] == '8')  del estudiante   @else  universitaria  @endif : servicio educativo y actividades conexas y derivadas,
                        son reguladas en el marco axiológico y filosófico de su Promotora.

                    </li>
                    <li>
                        6.5. Hacer uso del servicio educativo, sus medios educativos, tecnologías y/o plataformas: LAMB,
                        LMS y demás aplicativos , creados o por crearse, en el día y tiempo, momento asignado y
                        habilitado, observando el marco axiológico y estatutario de LA UNIVERSIDAD, de no desarrollo de
                        actividades de servicio educativo: enseñanza, aprendizaje, investigación y gestión, y servicios
                        conexos o derivados, en el día sábado, considerado desde las dieciocho (18) horas del día
                        viernes hasta las dieciocho (18) horas del día sábado.
                    </li>
                    <li>
                        6.6. Suscribir y cumplir el Compromiso de Honor.
                    </li>
                    <li>6.7. Suscribir y cumplir la Declaración Jurada respecto a su Responsable Financiero (Si la
                        información declarada fuera falsa perderá los beneficios que se le hayan otorgado y no
                        podrá
                        accederá a ningún tipo de becas y/o descuentos).
                    </li>
                    <li>6.8. Respetar y valorar a todas las personas que integran la comunidad educativa @if ($sedeParam['id_depto'] == '8') del ISTAT @else universitaria @endif .
                    </li>
                    <li>6.9. Cumplir sus obligaciones académicas y no académicas en los plazos, tiempos y condiciones
                        establecidas por @if ($sedeParam['id_depto'] == '8') el ISTAT @else LA UNIVERSIDAD @endif, el docente, tutor o la autoridad académica o
                        administrativa
                        respectiva.
                    </li>
                </ul>
                <ul>
                    <li>6.10. Cumplir los reglamentos académicos, de disciplina, propiedad intelectual y otros
                        vinculados al quehacer universitario en los escenarios de aprendizaje disponibles para los
                        estudiantes y autorizado por las disposiciones legales, y asumir las consecuencias y sanciones
                        en caso de incumplimiento.
                    </li>
                    <li>6.11. Mantener, observar y promover una conducta y comportamiento adecuado, de dominio propio,
                        respeto, sobre las autoridades @if ($sedeParam['id_depto'] == '8') del ISTAT @else universitarias @endif, docentes, tutores o personal de LA
                        UNIVERSIDAD y
                        de honestidad e integridad sobre los bienes, valores o enseres de propiedad de los
                        mismos.
                    </li>
                    <li>6.12. Acatar y someterse a los procesos y procedimientos establecidos para los servicios
                        contratados,
                        los disciplinarios y otros normados por @if ($sedeParam['id_depto'] == '8') el ISTAT @else LA UNIVERSIDAD @endif.
                    </li>
                    <li>6.13. Asistir obligatoriamente a las convocatorias, sesiones o reuniones académicas, sociales,
                        formativas o educativas dispuestas por @if ($sedeParam['id_depto'] == '8') el ISTAT  @else LA UNIVERSIDAD @endif o el docente o tutor o la
                        autoridad
                        académica, bajo sanción académica o disciplinaria.
                    </li>
                    <li>6.14. Asumir el aumento del costo del servicio educativo por la modificación e incremento de
                        asignaturas o cursos asolicitud @if ($sedeParam['id_depto'] == '8') propia   @endif de EL(A) ESTUDIANTE.
                    </li>
                    <li>6.15. Las demás que expresamente se señalen en el presente contrato, en las normas legales, el
                        Estatuto y en los reglamentos respectivos @if ($sedeParam['id_depto'] == '8') del ISTAT @else de LA UNIVERSIDAD @endif.
                    </li>
                </ul>
                <br>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <br><br>
                <b> SÉPTIMA</b>: LA MATRÍCULA. @if ($sedeParam['id_depto'] == '8')  El ISTAT   @else  LA UNIVERSIDAD   @endif establece que:
                <br>
                <ul>

                @if ($sedeParam['id_depto'] == '8')   
                <li>7.1. El costo de la matrícula en
                S/.{{ isset($infoPayment->matricula) ? $infoPayment->matricula : ''}}
                        ({{ isset($infoPayment->matriculatxt) ? $infoPayment->matriculatxt : ''}}),
                        {{isset($enrrollmentDiscountText) ? (' y descuentos: '.$enrrollmentDiscountText.'. Siendo estos descuentos de carácter temporal para el presente ciclo contratado.') : ''}}
                       
                        monto que no excede el importe de la pensión de enseñanza, de acuerdo a lo establecido por la Ley N° 28044 (Ley General de Educación), la Ley N° 30220 (Ley Universitaria), la Ley N° 29947 (Ley de  Protección a la Economía Familiar Respeto del Pago de Pensiones en Institutos, Escuelas Superiores, Universidades y Escuelas de Posgrado Públicos y Privados), la Ley N° 30512 (Ley de Institutos y Escuelas de Educación Superior y de la Carrera Pública de sus Docentes) y las demás leyes aplicables.

            </li>
                @else  
                <li>7.1. El costo de la matrícula en

                        S/.{{ isset($infoPayment->matricula) ? $infoPayment->matricula : ''}}
                        ({{ isset($infoPayment->matriculatxt) ? $infoPayment->matriculatxt : ''}}),
                        {{isset($enrrollmentDiscountText) ? (' y descuentos: '.$enrrollmentDiscountText.'. Siendo estos descuentos de carácter temporal para el presente ciclo contratado.') : ''}}
                        Monto que no excede el
                        importe de la pensión de enseñanza (carga completa de créditos por ciclo), de acuerdo a lo
                        establecido
                        por la Ley General de Educación N° 28044, la Ley Universitaria N° 30220, la Ley N° 29947 (Ley de
                        Protección a la Economía Familiar Respeto del Pago de Pensiones en Institutos, Escuelas
                        Superiores,
                        Universidades y Escuelas de Posgrado Públicos y Privados) y las demás leyes aplicables.
                    </li>
                @endif
                    


                    <li>
                        7.2. @if ($sedeParam['id_depto'] == '8')  Ambas partes señalan que el ISTAT   @else  LA UNIVERSIDAD  @endif, previo a la suscripción del presente documento y aceptación por
                        EL(LA)
                        ESTUDIANTE de
                        los costos, le ha brindado información sobre las condiciones
                        económicas a las que se ajustará la prestación del servicio educativo en forma escrita, veraz
                        suficiente
                        y
                        apropiada.
                        Queda establecido que no se condiciona la inscripción y/o matrícula al pago de las
                        contribuciones
                        denominadas voluntarias.

                    </li>
                    <li>
                        7.3. En ningún supuesto habrá devolución de matrícula a EL(A)
                        ESTUDIANTE,
                        excepto
                        cuando éste mismo decida hacer el retiro temporal o definitivo del semestre académico, @if ($sedeParam['id_depto'] == '8') maximo   @endif dentro de
                        la primera semana del inicio de clases y en el porcentaje establecido en el reglamento
                        correspondiente.
                    </li>
                    <li>
                        7.4. Para toda matrícula a un siguiente ciclo académico, EL(A)
                        ESTUDIANTE
                        solo
                        podrá hacerlo si no tiene ninguna deuda pendiente con  @if ($sedeParam['id_depto'] == '8')  el ISTAT   @else  LA UNIVERSIDAD  @endif, de conformidad con
                        lo
                        dispuesto en
                        la Ley @if ($sedeParam['id_depto'] == '8')  N° 29947, Ley @endif de Protección a la Economía Familiar Respecto del Pago de Pensiones en Institutos,
                        Escuelas
                        Superiores, Universidades y Escuelas de Posgrado Públicos y Privados N° 29947.
                    </li>
                    @if($sedeParam['id_depto'] == '1')
                    <li>
                        7.5. En caso EL(LA) ESTUDIANTE opte por utilizar los servicios de las residencias
                         universitarias, en la matricula se efectuará el pago del 50 % del valor de la residencia 
                         universitaria, y la diferencia en las pensiones, según cronograma de pagos señalado en el 
                         contrato
                    </li>
                    @endif
                </ul>
                <br>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <b> OCTAVA</b>: LAS PENSIONES.

                <ul>
                    <li>
                        8.1. El monto de la pensión por el derecho de enseñanza del semestre
                        académico {{$info['semestre']}}, es el
                        que se especifica en el ACUERDO DE FINANCIACION-CONTRATO
                        N° {{ isset($info['codigo_contrato']) ? $info['codigo_contrato'] : 'S/N'}} con su
                        respectivo fraccionamiento en cuotas
                        durante el mismo semestre académico, más el concepto de matrícula, tal como lo establece la Ley
                        N° 29571 @if ($sedeParam['id_depto'] == '8')  (Código de Protección y Defensa del Consumidor).   @else  (Código de  Defensa y Protección al Consumidor)  @endif.
                    </li>
                    <li>
                        8.2. EL(A) ESTUDIANTE, de así considerarlo y de manera voluntaria, podrá cancelar en una sola
                        armada el monto de la pensión de enseñanza del semestre académico, en forma adelantada, sin
                        embargo, NO ESTA OBLIGADO(A) a ello.
                    </li>
                    <li>
                        8.3. Las pensiones se pagan
                        en {{$info['codigo_tipo_contrato'] == '2' ? 'cuatro (4)' : 'cinco (5)'}}
                        armadas, para facilitar el pago a EL(A) ESTUDIANTE; se trata de
                        {{$info['codigo_tipo_contrato'] == '2' ? 'tres (3)' : 'cuatro (4)'}} pensiones mensuales
                        pagaderas en
                        {{$info['codigo_tipo_contrato'] == '2' ? 'cuatro (4)' : 'cinco (5)'}} armadas.
                        EL(A)ESTUDIANTE
                        podrá
                        pagar, de manera
                        voluntaria, la primera armada conjuntamente con la matrícula.

                    </li>
                    @if ($sedeParam['id_depto'] != '8')  
                     
                    
                    <li>
                        8.4. DESCUENTOS: En caso de pago adelantado al
                        contado de todo el semestre académico (matrícula y pensiones de enseñanza), habrá un porcentaje
                        de descuento
                        solo en las pensiones de enseñanza. Este descuento no se aplica a la matrícula ni al monto
                        equivalente a la primera armada o cuota de la modalidad de pago en armadas regulares.
                        El valor de la pensión por servicio educativo y su respectivo cronograma de pagos, están
                        especificados en el ACUERDO DE
                        FINANCIACION-
                        CONTRATO N° {{ isset($info['codigo_contrato']) ? $info['codigo_contrato'] : 'S/N'}} que EL(A)
                        ESTUDIANTE declara haberlo suscrito, conocerlo y estar totalmente
                        conforme,
                        antes de firmar el presente documento.

                    </li>

                    @endif
              
                    <li> @if ($sedeParam['id_depto'] == '8')  8.4.   @else 8.5.    @endif
                        PAGO DE INTERESES MORATORIOS:Conforme lo señala la Ley N°
                        29571
                        (Código de Protección y Defensa del Consumidor), la tasa del costo efectivo anual incluye todas
                        las
                        cuotas e intereses, todos los cargos y
                        comisiones. En razón al mismo, EL(A) ESTUDIANTE que incumpla con el pago indicado en el
                        ACUERDO
                        DE
                        FINANCIACION-CONTRATO N° {{ isset($info['codigo_contrato']) ? $info['codigo_contrato'] : 'S/N'}}
                        , estará
                        sujeto(a) a una carga de interés moratorio equivalente a la tasa
                        del interés interbancario dispuesta por el Banco de Reserva del Perú, de acuerdo a la Ley N°
                        29947 (Ley
                        de Protección a la Economía Familiar Respeto del Pago de Pensiones en Institutos, Escuelas
                        Superiores,
                        Universidades y Escuelas de Posgrado Públicos y Privados), a partir de la fecha de constitución
                        en mora
                        de EL(A)ESTUDIANTE. Este interés moratorio se calculará desde la constitución en mora de EL(A)
                        ESTUDIANTE hasta la fecha efectiva del pago.

                    </li>
                    <li>
                    @if ($sedeParam['id_depto'] == '8')  8.5.   @else 8.6.    @endif
 En caso de que EL(A) ESTUDIANTE dejara de
                        cancelar
                        dos (02)
                        pensiones consecutivas, LA UNIVERSIDAD le(la) citará a una reunión para dar tratamiento
                        al
                        problema, en
                        la cual se podrá suscribir un acuerdo (TRANSACCIÓN EXTRAJUDICIAL), que establezca la
                        re-programación del
                        pago de las cuotas dentro del semestre académico, culminando el mismo en la última fecha del
                        cronograma de
                        pago. De
                        incumplirse dicho acuerdo o de no lograrse ningún entendimiento, EL(A) ESTUDIANTE faculta
                        a LA
                        UNIVERSIDAD a INICIAR UN PROCESO EJECUTIVO, por el monto total más los cargos, comisiones,
                        intereses e
                        indemnizaciones, así como los costos y costas que genere el proceso. Dicho Proceso Judicial se
                        dará de
                        conformidad con lo estipulado en los artículos 1219° (inciso 1) y siguientes del Código Civil y
                        según lo
                        previsto en el numeral 5 del artículo 693° del Código Procesal Civil.


                    </li>

                </ul>
                <br>
            </td>
        </tr>
        {{--<tr>
            <td colspan="2">
                <ul >


                </ul>
            </td>
        </tr>--}}
        {{--<tr>
            <td colspan="2">


                <br><br>

                <br>
                <br>
            </td>
        </tr>--}}
        {{--<tr>
            <td colspan="2">
            </td>
        </tr>
        <tr>
            <td colspan="2">
            </td>
        </tr>--}}
        <tr>
            <td colspan="2">


                @if(count($coursesCreditDiscount) > 0)
                    <b>LA UNIVERSIDAD</b>, entendiendo las condiciones sociales por la coyuntura de la emergencia
                    sanitaria
                    nacional, que hace imposible que EL(A) ESTUDIANTE realice sus prácticas, suspende el cobro de los
                    créditos por hora de práctica, hace uso de la art.
                    Condicion suspendida , en la cual, los creditos por hora de practica los que serán recien cobrados
                    cuando se empiece a dar el servicio. Quedan para este contrato suspendidos los cobros de los
                    siguientes créditos de práctica , y (solo se estará cobrando la parte practica teórica):
                    <br>
                    <br>

                    <table class="table  table-sm table-bordered" style="width: 75%; margin: 0 auto">
                        <thead>
                        <tr>
                            <th class="text-center">CURSO</th>
                            <th class="text-center">Crédito/PRACTICA</th>
                            <th class="text-center">Valorización</th>
                            <th class="text-center">Sub total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($coursesCreditDiscount as $course)
                            <tr>
                                <td> {{$course->nombre}}</td>
                                <td class="text-center">{{$course->cp}}</td>
                                <td class="text-center">{{number_format($course->valorizacion, 2)}}</td>
                                <td class="text-center">{{number_format($course->subtotal, 2)}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <td><b>Total credito</b></td>
                            <td class="text-center"><b>{{$coursesCreditDiscountTotalCp}}</b></td>
                            <td class="text-center"><b> Total </b></td>
                            <td class="text-center"><b>{{number_format($coursesCreditDiscountTotal, 2)}}</b></td>
                        </tr>
                        </tfoot>

                    </table>
                    <br><br>



                @endif


            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b> NOVENA</b>: BECAS:
                Las becas se otorgarán de acuerdo a lo que señale el Reglamento @if ($sedeParam['id_depto'] == '8')  del ISTAT   @else de Becas de LA  UNIVERSIDAD    @endif.
                <br><br>
                <b> DÉCIMA</b>: ENTREGA DE CERTIFICADOS E INFORMACION A CENTRALES PRIVADAS DE INFORMACION DE
                RIESGOS
                <ul>
                    <li>10.1. Los certificados de estudio sólo serán entregados a EL(A) ESTUDIANTE que se
                        encuentre al día con el pago de las matrículas y pensiones de enseñanza; no habrá lugar a la
                        entrega
                        de documentos académicos oficiales, tales como: constancias, certificados, records
                        académicos,
                        reportes y otros similares de los semestres académicos adeudados o no pagados.
                    </li>
                    <li>10.2. Ambas partes acuerdan que LA UNIVERSIDAD queda facultada a reportar negativamente
                        a EL(A)
                        ESTUDIANTE a las Centrales Privadas de Información de Riesgos por incumplimiento del pago
                        de matrícula, pensiones u otros cargos durante el semestre o semestres anteriores.
                    </li>
                </ul>
                <br>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b> DÉCIMO PRIMERA</b>: DE LA DURACIÓN O PLAZO.
                La duración del presente contrato equivale a la duración del semestre académico, y en general a
                dieciseis (16) semanas académicas como mínimo, computado desde el primer día de clases @if ($sedeParam['id_depto'] == '8')  (03/04/2023)    @else (20/03/2023)   @endif hasta el final
                del semestre. @if ($sedeParam['id_depto'] == '8') En la ocurrencia   @else  En caso   @endif de caso
                fortuito
                o fuerza mayor, el plazo de duración del presente contrato será modificado o ampliado automáticamente,
                hasta
                el
                término de la reprogramación académica autorizada por LA UNIVERSIDAD.
                <br><br>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>DÉCIMO SEGUNDA</b>: CAUSALES DE RESOLUCIÓN. El presente contrato se resolverá, terminará, por
                una de
                las siguientes causas:
                <ul>
                    <li>
                        12.1. Por mutuo acuerdo entre las partes.
                    </li>
                    <li>12.2. Por incumplimiento por parte de EL(A) ESTUDIANTE del Estatuto, reglamentos y
                        normas @if ($sedeParam['id_depto'] == '8') del ISTAT y  @endif de LA
                        UNIVERSIDAD, que impliquen sanción de expulsión o retiro definitivo.
                    </li>
                    <li>12.3. Incumplimiento de las obligaciones por EL(A) ESTUDIANTE. En su caso LA
                        UNIVERSIDAD
                        determina las
                        acciones legales correspondientes.
                    </li>
                    <li>12.4. Unilateralmente por decisión de EL(A) ESTUDIANTE, en razón del retiro definitivo
                        o temporal, y previo
                        cumplimiento @if ($sedeParam['id_depto'] == '8') de pago   @endif de las deudas pendientes, y comunicación a LA UNIVERSIDAD por
                        escrito, con una anticipación de 20 días hábiles. A EL(A) ESTUDIANTE se le cobra las
                        pensiones a
                        la fecha de solicitud del retiro. En ningún caso habrá devolución de la matrícula, excepto en
                        los
                        casos contemplados en el reglamento correspondiente; o de las pensiones de semestre académicos
                        anteriores a la fecha del retiro.
                    </li>
                </ul>

                <br>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>@if ($sedeParam['id_depto'] == '8')  DÉCIMA    @else DÉCIMO   @endif TERCERA</b>: REAJUSTES, CUOTAS EXTRAORDINARIAS E INCREMENTO DE @if ($sedeParam['id_depto'] == '8') MATRÍCULA Y   @endif PENSIÓN. LA UNIVERSIDAD
                podrá reajustar la pensión mensual si las condiciones económicas generales se ven deterioradas, la
                misma que deberá ser sustentada y comunicada a EL(A) ESTUDIANTE, dentro de la vigencia del ciclo
                mes inmediato anterior. Asimismo, el costo de la siguiente matrícula y del siguiente ciclo podrá ser
                incrementado, lo cual será comunicado a EL(A) ESTUDIANTE en el ciclo inmediato anterior. 
                @if ($sedeParam['id_depto'] != '8') EL(A)
                ESTUDIANTE
                que
                oportunamente no
                acreditó ser merecedor del descuento o ayuda económica que le hubiera otorgado, perderá dicho
                beneficio,
                reajustandolo al monto correspondiente en la última armada del ciclo.   @endif
                <br><br>
            </td>
        </tr>

        @if ($sedeParam['id_depto'] != '8') 

            <tr>
                <td colspan="2">
                    <b>DÉCIMO CUARTA</b>: DE LOS DESCUENTOS A EL(A) ESTUDIANTE: Estos descuentos se aplicarán
                    de
                    acuerdo a
                    lo establecido en la directiva de descuentos correspondiente (Reglamentos de descuentos:
                    Normatividad
                    Institucional).
                    <br><br>
                </td>
            </tr>

    @endif
        
        <tr>
            <td colspan="2">
                <b> @if ($sedeParam['id_depto'] == '8')  DÉCIMA CUARTA  @else  DÉCIMO QUINTA    @endif  : ACEPTACIÓN, CONFORMIDAD Y SUSCRIPCIÓN.</b> Ambas partes reconocen que el
                presente
                Contrato se formaliza, perfecciona y surte todos los efectos de suscripción, en todos sus
                extremos,
                contenido y alcances, vinculando a las partes, con la suscripción del soporte físico de este
                contrato o
                con la aceptación electrónica de los mismos contratantes, lo que será hecho cuando EL(A)
                ESTUDIANTE pulse
                el botón ACEPTO o al realizar la matrícula del semestre académico y al acuse de recibo
                otorgada a
                LA UNIVERSIDAD. Esta comunicación se realizará al correo electrónico o al teléfono móvil
                consignado en la
                parte
                introductoria de
                este contrato, que EL(A) ESTUDIANTE declara que es el medio oficial de comunicación
                (por cualesquier medio o red social que de constancia o acuse de recibo o conformidad) con
                LA UNIVERSIDAD respecto a los efectos y ejecución del presente contrato.

                <br><br>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>@if ($sedeParam['id_depto'] == '8')  DÉCIMA QUINTA  @else  DÉCIMO SEXTA    @endif</b>: JURISDICCIÓN Y COMPETENCIA. En caso de controversia sobre el contenido y
                alcance
                del presente contrato y su objeto, ambas partes renuncian el Juez de su domicilio y se someten a
                la
                jurisdicción de los jueces y tribunales de la {{$sedeParam['court']}}. Dejando
                constancia que
                en todo momento buscaran la solución pacífica y armoniosa de sus diferencias previamente y
                mutuas
                concesiones.Estando conformes con todo lo estipulado, normado, lo ratifica y acusa recibo de su
                aceptación plena, total y libre de las condiciones en este contrato, y sin necesidad de
                suscribirlo
                físicamente.
                <br><br>
            </td>
        </tr>
        {{--<tr>
            <td colspan="2">

            </td>

        </tr>--}}
       
        <tr>
            <td colspan="2">


            @if ($sedeParam['id_depto'] == '8') 
            <b>DÉCIMA SEXTA:</b> SERVICIO EDUCATIVO EN EL CONTEXTO DE EMERGENCIA. - 
En el contexto de la emergencia coyunturales (Sanitarias, Sociales, etc. ) y por mandato de las disposiciones legales vigentes que regulan el funcionamiento del ISTAT, el servicio educativo de formación profesional técnico, materia del presente contrato y sus actividades académicas, conexas y derivadas son otorgadas en la modalidad no presencial, virtual u online, haciendo uso y a través de los medios educativos o tecnológicos, tales como LAMB o portal académico, LMS y otros, creados o por crearse, para el uso y disposición de EL(LA) ESTUDIANTE, en los días y tiempos asignados y habilitados, salvo las limitación de acceso y uso únicamente en el día viernes desde las dieciocho (18) horas hasta las dieciocho (18) horas del día sábado.
 @else  @if($info['codigo_tipo_contrato'] == '1')
                    <b>DÉCIMO SÉPTIMA: SEGUROS.</b> LA UNIVERSIDAD, como intermediaria, contrata el seguro
                    contra accidentes para todos los estudiantes de la modalidad de estudio presencial
                    (cabe recalcar que este no es un seguro de vida), el cual tendrá una duración de
                    {{(str_contains($info['semestre'], '-2') and ($info['w_enroll'] == '0')) ? '6': '6'}} meses apartir
                    del inicio de clases según cronograma
                    académico {{isset($info['semestre']) ? $info['semestre'] : ''}}
                    (no se asegura por matrículas de cursos extraprogramáticos y dirigidos).
                @endif
                <br><br>
                <b>@if($info['codigo_tipo_contrato'] == '1')
                        DÉCIMO OCTAVA:
                    @else
                        DÉCIMO SÉPTIMA:
                    @endif
                </b>
                
                SERVICIO EDUCATIVO EN EL CONEXTO DE EMERGENCIA SANITARIA. En el contexto de la emergencia sanitaria
                COVID-19 y por mandato de las disposiciones legales vigentes que regulan el funcionamiento @if ($sedeParam['id_depto'] == '8')  del ISTAT  @else  de LA UNIVERSIDAD    @endif,
                 el servicio educativo de formación profesional universitaria, materia del presente contrato
                y sus actividades académicas, conexas y derivadas son otorgadas en la modalidad no presencial, virtual u
                online, haciendo uso y a través de los medios educativos o tecnológicos, tales como LAMB o portal
                académico, LMS y otros, creados o por crearse, para el uso y disposición de EL(LA) ESTUDIANTE, en los
                días y tiempos asignados y habilitados, salvo las limitación de acceso y uso únicamente en el día
                viernes desde las dieciocho (18) horas hasta las dieciocho (18) horas del día sábado.
                <br><br>
                Estando conformes con todo lo estipulado, normado, lo ratifican y acusa recibo de su aceptación plena,
                total y libre de las condiciones en este contrato, y sin necesidad de suscribirlo físicamente.   @endif

            </td>

        </tr>     

        <tr>
            <td colspan="2" class="table-justify">
                <b>@if ($sedeParam['id_depto'] == '8')  DÉCIMA SÉPTIMA  @else  DÉCIMA NOVENA    @endif </b>: SERVICIO DE RESIDENCIAS UNIVERSITARIAS. - 
                    En caso de que EL(LA) ESTUDIANTE opte @if ($sedeParam['id_depto'] == '8') de manera libre y voluntaria     @endif
                  por el servicio de residencias universitarias, se regirá de acuerdo con lo establecido en la normativa
                  sobre Residencias Universitarias correspondiente (Reglamento de Residencias Universitarias,
                  el cual se halla en la Normatividad Institucional). Cabe precisar que se aplicarán restricciones
                  en el uso de los servicios de las residencias universitarias, cuando correspondan,
                  lo que es de conocimiento y es aceptado voluntariamente por EL(LA) ESTUDIANTE al emitirse y 
                  aceptar este contrato. <br><br>
                  Estando conformes con @if ($sedeParam['id_depto'] == '8') todas y cada una de     @endif las cláusulas y con todo lo estipulado y normado, lo ratifican y el acuse de recibo de
                  este documento de manera virtual es señal de la aceptación plena, total, libre y voluntaria
                  de todas y cada una de las condiciones de este contrato, y sin tener la necesidad de suscribirlo físicamente.
                <br>
            </td>
        </tr>
        <tr>
            <td class="text-center" colspan="2" style="border: none"><br><br>
                <span class="text-center">{{$nowDate}}</span>
                <br><br><br>
            </td>
        </tr>
        <tr style="border: none">
            <td class="text-center" style="border: none">


            <!--div class="text-center">
            <img src="{{asset('finances-student-contract/firma-contrato-fin.png')}}"  height="118" />
            <label>________________________________________</label><br>

            <span>{{$sedeParam['resp']}}</span><br>
            <span>{{$sedeParam['apo']}}</span><br>
            <span>UNIVERSIDAD PERUANA UNI&Oacute;N</span>
                </div-->
                @if($sedeParam['id_depto'] == '5')
                <div class="signature">
                    <!-- <img src="{{public_path('img/signatures/sig_upeu_juliaca.png')}}" width="200"/> -->
                    <img src="{{public_path('img/signatures/sig_upeu.png')}}" width="220"/>
                    <div class="signature-text texto">
                        <label>________________________________________</label><br>
                        <span>{{$sedeParam['resp']}}</span><br>
                        <span>{{$sedeParam['apo']}}</span><br>
                        <span>UNIVERSIDAD PERUANA UNI&Oacute;N</span>
                    </div>
                </div>
                @elseif($sedeParam['id_depto'] == '8')
                
            <br><br><br>
                <br><br><br>
                <div class="signature">
                    <!-- <img src="{{public_path('img/signatures/sig_upeu_juliaca.png')}}" width="200"/> -->
                    <img src="{{public_path('img/signatures/sig_upeu.png')}}" width="220"/>
                    <div class="signature-text texto">
                        <label>________________________________________</label><br>
                        <span>{{$sedeParam['resp']}}</span><br>
                        <span>{{$sedeParam['apo']}}</span><br>
                        <span>UNIVERSIDAD PERUANA UNI&Oacute;N</span>
                    </div>
                </div>
                    @elseif($sedeParam['id_depto'] == '6')
                    <div class="signature">
                    <!-- <img src="{{public_path('img/signatures/sig_upeu_tarapoto.png')}}" width="220"/> -->
                    <img src="{{public_path('img/signatures/sig_upeu.png')}}" width="220"/>
                        <div class="signature-text texto">
                            <label>________________________________________</label><br>
                            <span>{{$sedeParam['resp']}}</span><br>
                            <span>{{$sedeParam['apo']}}</span><br>
                            <span>UNIVERSIDAD PERUANA UNI&Oacute;N</span>
                        </div>
                    </div>
                    @elseif($sedeParam['id_depto'] == '1')
                    <div class="signature">
                        <img src="{{public_path('img/signatures/sig_upeu.png')}}" width="220"/>
                        <div class="signature-text texto">
                            <label>________________________________________</label><br>
                            <span>{{$sedeParam['resp']}}</span><br>
                            <span>{{$sedeParam['apo']}}</span><br>
                            <span>UNIVERSIDAD PERUANA UNI&Oacute;N</span>
                        </div>
                    </div>
                    @endif
                
            </td>
            <td class="text-center texto" style="border: none"><br><br><br><br><br><br><br><br><br><br><br>
                ______________________________<br>
                {{ isset($info['nom_persona']) ? $info['nom_persona'] : 'Sin nombre'}} <br>
                {{ isset($info['nom_documento']) ? $info['nom_documento'] : 'Sigla'}}
                : {{ isset($info['num_documento']) ? $info['num_documento'] : 'Sin documento'}} EL(A) ESTUDIANTE <br>
                Universidad Peruana Unión<br>
                {{$sedeParam['depto']}}


            </td>
        </tr>
    </table>
@endsection
