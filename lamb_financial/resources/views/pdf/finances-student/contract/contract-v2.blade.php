@extends('layouts.pdfcontract')
@section('content')
    <style>
        html {
            font-family: "sans-serif";
        }

        .section-body {
            margin-top: 4px;
            margin-bottom: 4px;
        }

        .section-body .header-sc span {
            align-self: stretch;
        }

        .section-body .header-sc span {
            color: #000;
            font-family: "sans-serif";
            font-size: 10px;
            font-weight: 400;
        }

        .section-body .body-sc article {
            color: #000;
            font-family: "sans-serif";
            font-size: 9px;

            font-weight: 400;
            line-height: normal;
            margin-top: 8px;
            text-align: justify;
        }

        .table-head {
            padding: 4px 8px;
            align-items: center;
            gap: 10px;
            align-self: stretch;
            border-radius: 9px 9px 0px 0px !important;
            border-bottom: 1px dashed #286BA1;
            background: #E0F5FF;
            overflow: hidden;
        }

        .table-sub {
            border-right: 1px dashed #286ba1;
            background: #e0f5ff;
            text-align: center;
            color: #286ba1;

            font-family: "sans-serif";
            font-size: 9px;

            font-weight: 700;
            line-height: normal;
        }

        .item-sub {
            padding: 4px 8px;
            font-size: 8px;
            border-right: 1px dashed #b4bbca;
            border-bottom: 1px dashed #b4bbca;
        }

        .nb {
            border-right: 0;
        }

        .sim-sb {
            color: #000;
            font-family: "sans-serif";

            font-weight: 400;
            line-height: normal;
        }

        .row-items {
            color: #000;
            font-family: "sans-serif";
            font-size: 8px;

            font-weight: 400;
            line-height: normal;
            text-align: center;
        }

        .nbr {
            border-bottom: none !important;
        }

        .nrr {
            border-right: none !important;
        }

        .icon-status {
            border: none !important;
        }

        .item-cont {
            border-right: 1px dashed #b4bbca;
            border-bottom: 1px dashed #b4bbca;
            padding: 4px;
        }

        .mon-for {
            text-align: end;
        }

        .item-hd-center {
            text-align: center;
        }



        .table-hd {
            color: #286ba1;
            font-family: "sans-serif";
            font-size: 8px;

            font-weight: 700;
            line-height: normal;
            background: #e0f5ff;
            align-items: center;
            padding: 4px;
            margin: 0;
        }

        .table-cn {
            color: #000;
            font-family: "sans-serif";
            font-size: 8px;

            font-weight: 400;
            line-height: normal;
            padding: 4px 4px 4px 8px;
        }

        .bb {
            border-bottom: 1px dashed #286ba1;
        }

        .br {
            border-right: 1px dashed #286ba1;
        }

        .bc {
            border-bottom: 1px dashed #b4bbca;
        }

        .texto {
            font-size: 10px !important;
            padding-top: 0px !important;
        }

        .cnum {
            width: 18px;
        }

        .ccon {
            max-width: 58px;
        }

        .text-center {
            text-align: center;
        }

        .main-img {
            background: url("{{ asset('images/marca-agua.png') }}");
            background-position: center center;
            background-repeat: no-repeat;
            background-size: auto;
            height: 750px;
            /*width: 365px !important;*/
            /* opacity: 0.25; */
        }

        .trtrtr {
            width: 90px;
            border-radius: 0 0 0 9px;
            background: url("{{ $photo }}");
            background-position: center center;
            background-repeat: no-repeat;
            background-size: cover;
        }

        .inline-container {
            white-space: nowrap;
            /* Evita que los elementos se muevan a la siguiente línea */
        }

        .inline-container img,
        .inline-container span {
            display: inline-block;
            vertical-align: middle;
            margin-right: 5px;
        }
    </style>
    <div class="main-img">

        <div>
            <table style="border: none;width:100%;margin:0">
                <tbody>
                    <tr>
                        <td></td>
                        <td colspan="2" class="text-center">
                            <h3 style="margin-top: -10px;">
                                <span style="color:#003264;font-size:12px" class="text-center">ACUERDO DE FINANCIACIÓN -
                                    CONTRATO Nº:
                                    {{ isset($info['codigo_contrato']) ? $info['codigo_contrato'] : 'S/N' }}</span>
                            </h3>
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <div style="margin-bottom: 8px;margin-top:-10px">
                <table
                    style='border-radius: 9px;
            border: 1px solid #b4bbca;
            width: 100%;
            padding: 0;
            margin: 8px 0;
            border-spacing: 0;
            border-collapse: separate;
            overflow: hidden;'>
                    <tbody>
                        <tr>
                            <td colspan="7" class="table-hd table-head">
                                <div class="inline-container">
                                    <img src="{{ public_path('icons/p_check.svg') }}" />
                                    <span>Información del alumno</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td rowspan="6" class="trtrtr">
                            </td>
                            <td class="table-hd bb br item-hd-center cnum">01</td>
                            <td class="table-hd bb br ccon">Código</td>
                            <td class="table-cn bc">{{ isset($info['codigo']) ? $info['codigo'] : 'Sin codigo' }}</td>
                            <td class="table-hd bb br item-hd-center cnum">07</td>
                            @if ($modeContract == 'V')
                                <td class="table-hd bb br ccon">Créditos Variados</td>
                                <td class="table-cn bc">{{ $tcreditoInVariation }}</td>
                            @else
                                <td class="table-hd bb br ccon">Créditos</td>
                                <td class="table-cn bc">{{ $tcredito }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td class="table-hd bb br item-hd-center cnum">02</td>
                            <td class="table-hd bb br ccon">Alumno</td>
                            <td class="table-cn bc">{{ isset($info['nom_persona']) ? $info['nom_persona'] : 'Sin nombre' }}
                            </td>
                            <td class="table-hd bb br item-hd-center cnum">08</td>
                            <td class="table-hd bb br ccon">Semestre</td>
                            <td class="table-cn bc">{{ isset($info['semestre']) ? $info['semestre'] : '' }}</td>
                        </tr>
                        <tr>
                            <td class="table-hd bb br item-hd-center cnum">03</td>
                            <td class="table-hd bb br ccon">
                                {{ isset($info['nom_documento']) ? $info['nom_documento'] : 'Sigla: ' }}</td>
                            <td class="table-cn bc">
                                {{ isset($info['num_documento']) ? $info['num_documento'] : 'Sin numero' }}</td>
                            <td class="table-hd bb br item-hd-center cnum">09</td>
                            <td class="table-hd bb br ccon">Ciclo</td>
                            <td class="table-cn bc">{{ isset($info['ciclo']) ? $info['ciclo'] : '' }}</td>
                        </tr>
                        <tr>
                            <td class="table-hd bb br item-hd-center cnum">04</td>
                            <td class="table-hd bb br ccon">Carrera</td>
                            <td class="table-cn bc">
                                {{ isset($info['nom_programa']) ? $info['nom_programa'] : 'Sin programa' }}</td>
                            <td class="table-hd bb br item-hd-center cnum">10</td>
                            <td class="table-hd bb br ccon">Resp. Financiero</td>
                            <td class="table-cn bc">{{ isset($info['nom_resp']) ? $info['nom_resp'] : '' }}</td>
                        </tr>
                        <tr>
                            <td class="table-hd bb br item-hd-center cnum">05</td>
                            <td class="table-hd bb br ccon">Direc. Laboral</td>
                            <td class="table-cn bc">{{ isset($info['direccion']) ? $info['direccion'] : '' }}</td>
                            <td class="table-hd bb br item-hd-center cnum">11</td>
                            <td class="table-hd bb br ccon">Teléfono resp.</td>
                            <td class="table-cn bc">
                                {{ isset($info['num_telefono_resp']) ? $info['num_telefono_resp'] : '' }}</td>
                        </tr>
                        <tr>
                            <td class="table-hd br item-hd-center cnum">06</td>
                            <td class="table-hd br ccon">Teléfono</td>
                            <td class="table-cn">{{ isset($info['celular']) ? $info['celular'] : '' }}</td>
                            <td class="table-hd br item-hd-center cnum">12</td>
                            <td class="table-hd br ccon">Dirección resp.</td>
                            <td class="table-cn">{{ isset($info['direccion_resp']) ? $info['direccion_resp'] : '' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="margin-bottom: 8px;">
                <table
                    style='border-radius: 9px;
            border: 1px solid #b4bbca;
            width: 100%;
            padding: 0;
            margin: 8px 0;
            border-spacing: 0;
            border-collapse: separate;
            overflow: hidden;'>
                    <tbody>
                        <tr>
                            <td colspan="4" class="table-hd table-head">
                                <div class="inline-container">
                                    <img src="{{ public_path('icons/payment.svg') }}" />
                                    <span>Detalle de Pago</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="table-sub" style="width: 50%">COBROS</td>
                            <td colspan="2" class="table-sub" style="border-right: none">
                                DESCUENTOS
                            </td>
                        </tr>
                        <?php
                        $a = count($debits) + 1;
                        $desc = $credits['descuentos'] ?? [];
                        $b = count($desc) + 1;
                        $mayor = 0;
                        if ($a > $b) {
                            $mayor = $a;
                        } else {
                            $mayor = $b;
                        }
                        
                        $pays = [];
                        for ($i = 0; $i < $mayor; $i++) {
                            $columna1 = '';
                            $columna2 = '0';
                            $columna3 = '';
                            $columna4 = '0';
                        
                            $rowsA = 1;
                            $rowsB = 1;
                        
                            $showA = true;
                            $showB = true;
                        
                            $tc = false;
                            $td = false;
                        
                            if ($a - 1 > $i) {
                                $db = (object) $debits[$i];
                                $columna1 = $db->nombre ?? '';
                                $columna2 = $db->importe ?? '0';
                            } else {
                                $rowsA = $mayor - $a + 1;
                                if ($i == $a - 1) {
                                    $tc = true;
                                    $columna1 = 'Total de Cobros';
                                    $columna2 = floatval(strval($contract->total_debito ?? 0));
                                } else {
                                    $showA = false;
                                }
                            }
                        
                            if ($b - 1 > $i) {
                                $mn = (object) $desc[$i];
                                $columna3 = $mn->nombre ?? '';
                                $columna4 = $mn->importe ?? '';
                            } else {
                                $rowsB = $mayor - $b + 1;
                                if ($i == $b - 1) {
                                    $td = true;
                                    $columna3 = 'Total de Descuentos';
                                    $columna4 = floatval(strval($contract->total_credito ?? 0));
                                } else {
                                    $showB = false;
                                }
                            }
                        
                            $pago = new stdClass();
                            $pago->c1 = $columna1;
                            $pago->c2 = floatval($columna2);
                            $pago->c3 = $columna3;
                            $pago->c4 = floatval($columna4);
                        
                            $pago->rowsA = $rowsA;
                            $pago->rowsB = $rowsB;
                            $pago->showA = $showA;
                            $pago->showB = $showB;
                        
                            $pago->tc = $tc;
                            $pago->td = $td;
                        
                            array_push($pays, $pago);
                        }
                        
                        ?>

                        @foreach ($pays as $d)
                            <tr>
                                @if ($d->showA)
                                    <td rowspan="{{ $d->rowsA }}" class="item-sub sim-sb" style="padding:4px 8px">
                                        @if ($d->tc)
                                            <span><strong>{{ $d->c1 }}</strong></span>
                                        @else
                                            <span>{{ $d->c1 }}</span>
                                        @endif
                                    </td>
                                    <td rowspan="{{ $d->rowsA }}" class="item-sub sim-sb"
                                        style="text-align:end;padding:4px 8px">
                                        @if ($d->tc)
                                            <span
                                                style="color:#286BA1;float:right"><strong>{{ $d->c2 > 0 ? number_format($d->c2, 2) : '' }}</strong></span>
                                        @else
                                            <span
                                                style="float:right">{{ $d->c2 > 0 ? number_format($d->c2, 2) : '' }}</span>
                                        @endif
                                    </td>
                                @endif
                                @if ($d->showB)
                                    <td rowspan="{{ $d->rowsB }}" class="item-sub sim-sb" style="padding:4px 8px">
                                        @if ($d->td)
                                            <span><strong>{{ $d->c3 }}</strong></span>
                                        @else
                                            <span>{{ $d->c3 }}</span>
                                        @endif
                                    </td>
                                    <td rowspan="{{ $d->rowsB }}" class="item-sub sim-sb mon-for"
                                        style="border-right: none;padding:4px 8px">
                                        @if ($d->td)
                                            <span
                                                style="color:#286BA1;float:right"><strong>{{ $d->c4 > 0 ? number_format($d->c4, 2) : '' }}</strong></span>
                                        @else
                                            <span
                                                style="float:right">{{ $d->c4 > 0 ? number_format($d->c4, 2) : '' }}</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach

                        @if ($plain and isset($plain->cuotas))
                            <tr>
                                <td colspan="4"
                                    style="
                  padding: 9px;
                  color: #000;
                  text-align: center;
                  font-family: 'sans-serif';
                  font-size: 9px;
                  
                  font-weight: 400;
                  line-height: 15px;                  
                ">
                                    Importe total de contrato académico:
                                    <strong>S./{{ number_format($contract->total, 2) }}</strong> <br />
                                    @if ($plain->cuotas == '1')
                                        Debitado en matrícula por pago al Contado - Plan {{ $plain->cuotas }}:
                                        <strong>S/.{{ number_format($contract->contado, 2) }}</strong>
                                    @else
                                        Debitado en matrícula por pago en Armadas - Plan {{ $plain->cuotas }}:
                                        <strong>S/.{{ number_format($contract->matricula1cuota, 2) }}</strong>
                                    @endif
                                </td>
                            </tr>
                        @endif
                        
                        {{-- @else --}}
                            <tr>
                                <td colspan="4" style="padding: 8px; 
                border-top: 1px dashed #B4BBCA;">
                                    <table style="font-size: 9px;text-align:center;width:400px;margin-left:33%">
                                        <thead>
                                            <tr>
                                                <th colspan="4" style="text-align:center">Pagos pendientes</th><br>
                                            </tr>
                                            <tr>
                                                <th style="border-bottom: 1px solid #B4BBCA;padding:4px 8px">N° de armada
                                                </th>
                                                <th style="border-bottom: 1px solid #B4BBCA;padding:4px 8px">Fecha de
                                                    cobranza de cuota</th>
                                                <th style="border-bottom: 1px solid #B4BBCA;padding:4px 8px">Monto</th>
                                                <th style="border-bottom: 1px solid #B4BBCA;padding:4px 8px">Fecha de
                                                    vencimiento</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($plains as $i => $p)
                                                <tr>
                                                    <td style="border-bottom: 1px dashed #B4BBCA;padding:4px 8px">
                                                        {{ $i + 2 }}{{ isset($ordinal[$i + 1]) ? $ordinal[$i + 1] : '' }}
                                                        armada</td>
                                                    <td style="border-bottom: 1px dashed #B4BBCA;padding:4px 8px">
                                                        {{ $p->fecha_inicio }}.</td>
                                                    <td
                                                        style="border-bottom: 1px dashed #B4BBCA;padding:4px 8px; font-weight: bold;">
                                                        S./{{ property_exists($contract, 'mensual_ens_resi') ? number_format($contract->mensual_ens_resi, 2) : '' }}
                                                    </td>
                                                    <td style="border-bottom: 1px dashed #B4BBCA;padding:4px 8px">Fec.
                                                        Venc.: {{ $p->fecha_fin }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        {{-- @endif --}}



                    </tbody>
                </table>
            </div>

            <div>
                <table
                    style='border-radius: 9px;
            border: 1px solid #b4bbca;
            width: 100%;
            padding: 0;
            margin: 8px 0;
            border-spacing: 0;
            border-collapse: separate;
            overflow: hidden;'>
                    <tbody>
                        <tr>
                            <td colspan="8" class="table-hd table-head">
                                <div class="inline-container">
                                    <img src="{{ public_path('icons/courses.svg') }}" />
                                    <span>Asignaturas</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="table-sub">Nº</td>
                            <td class="table-sub">Ciclo</td>
                            <td class="table-sub">Nombre de asignatura/Docente</td>
                            <td class="table-sub">GP.</td>
                            <td class="table-sub">Cr.</td>
                            <td class="table-sub">Hr.</td>
                            <td class="table-sub">EAP</td>
                            <td class="table-sub" style="border-right: none">Selec.</td>
                        </tr>
                        @foreach ($courses as $course)
                            @if ($loop->iteration < count($courses))
                                <tr class="row-items">
                                    <td class="item-cont">{{ $loop->iteration }}</td>
                                    <td class="item-cont">{{ $course['ciclo'] }}</td>
                                    <td class="item-cont" style="text-align: start">
                <span style="color: #000; font-family: 'sans-serif';font-size: 8px; line-height: normal;">
                  <strong>{{ $course['nombre_curso_2'] }}</strong>
                    @if ($course['codigo_curso_modo'] == 'L-31803' || $course['codigo_curso_ui'] == 'UI01')
                        <strong style="color: #000;font-family: 'sans-serif';font-size: 8px;line-height: normal;">
                            (L-31803)
                        </strong>
                    @endif

                  </span>
                                        <br />
                                        <span
                                            style="
                    color: #000;
                    font-family: 'sans-serif';
                    font-size: 8px;
                    font-style: italic;
                    line-height: normal;
                  ">{{ $course['nombre_docente'] }}</span>
                                    </td>
                                    <td class="item-cont">{{ $course['grupo'] }}</td>
                                    <td class="item-cont">{{ $course['credito'] }}</td>
                                    <td class="item-cont">{{ $course['ht'] }}</td>
                                    <td class="item-cont">{{ $course['nombre_escuela'] }}</td>
                                    <td class="item-cont nrr">
                                        @if ($modeContract == 'V')
                                            @if ($course['codigo_estado_mov_current'] == 'I')
                                                <img src="{{ public_path('img/1.svg') }}" class="icon-status"
                                                    style="border: none !important;" />
                                            @elseif($course['codigo_estado_mov_current'] == 'R')
                                                <img src="{{ public_path('img/0.svg') }}" class="icon-status" />
                                            @endif
                                        @else
                                            @if ($course['codigo_estado_movimiento'] == null)
                                                <img src="{{ public_path('img/1.svg') }}" class="icon-status"
                                                    style="border: none !important;" />
                                            @elseif($course['codigo_estado_movimiento'] == 'R')
                                                <img src="{{ public_path('img/0.svg') }}" class="icon-status" />
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endif

                            @if ($loop->iteration == count($courses))
                                <tr class="row-items">
                                    <td class="item-cont nbr">{{ $loop->iteration }}</td>
                                    <td class="item-cont nbr">{{ $course['ciclo'] }}</td>
                                    <td class="item-cont nbr" style="text-align: start">
                                        <span
                                            style="
                    color: #000;
                    font-family: 'sans-serif';
                    font-size: 8px;
                    line-height: normal;
                  ">
                  <strong>{{ $course['nombre_curso_2'] }}</strong> 
                  {{-- @if ($course['course_as'] == '1') 
                  <strong style="color: #000;font-family: 'sans-serif';font-size: 8px;line-height: normal;">(*)</strong> 
                  @endif --}}

                    @if ($course['codigo_curso_modo'] == 'L-31803' || $course['codigo_curso_ui'] == 'UI01')
                        <strong style="color: #000;font-family: 'sans-serif';font-size: 8px;line-height: normal;">
                            (L-31803)
                        </strong>
                    @endif
                  
                </span>
                                        <br />
                                        <span
                                            style="
                    color: #000;
                    font-family: 'sans-serif';
                    font-size: 8px;
                    font-style: italic;
                    line-height: normal;
                  ">{{ $course['nombre_docente'] }}</span>
                                    </td>
                                    <td class="item-cont nbr">{{ $course['grupo'] }}</td>
                                    <td class="item-cont nbr">{{ $course['credito'] }}</td>
                                    <td class="item-cont nbr">{{ $course['ht'] }}</td>
                                    <td class="item-cont nbr">{{ $course['nombre_escuela'] }}</td>
                                    <td class="item-cont nbr nrr">
                                        @if ($modeContract == 'V')
                                            @if ($course['codigo_estado_mov_current'] == 'I')
                                                <img src="{{ public_path('img/1.svg') }}" class="icon-status" />
                                            @elseif($course['codigo_estado_mov_current'] == 'R')
                                                <img src="{{ public_path('img/0.svg') }}" class="icon-status" />
                                            @endif
                                        @else
                                            @if ($course['codigo_estado_movimiento'] == null)
                                                <img src="{{ public_path('img/1.svg') }}" class="icon-status" />
                                            @elseif($course['codigo_estado_movimiento'] == 'R')
                                                <img src="{{ public_path('img/0.svg') }}" class="icon-status" />
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        @if (count($courses) == 0) 
                            <tr style="font-size: 11px">
                                <td class="text-center" colspan="8"> no hay cursos seleccionados</td>
                            </tr>
                        @endif

                    </tbody>
                </table>
                @if ($course_AS == '1')
                <span style="color: #000;font-family: 'sans-serif';font-size: 8px;line-height: normal;">
                    <strong>Cursos con asistencia docente (*)</strong>
                </span>
                @endif

                
                @if ($coursesL31803->isNotEmpty())
                    <span style="display: block;color: #000;font-family: 'sans-serif';font-size: 8px;line-height: normal;">
                        <strong>Curso obligatorio de investigación SUNEDU (L-31803)</strong>
                    </span>
                @endif
            </div>

            <div class="text-center" style="margin-top:16px">
                <span
                    style="
            color: #000;
            font-size: 10px;
            text-align: center;
            font-family: 'sans-serif';
          ">CONTRATO
                    DE SERVICIOS EDUCATIVOS DE FORMACIÓN PROFESIONAL UNIVERSITARIA</span>
            </div>
            <div>
                <article
                    style="
            color: #000;
            text-align: justify;
            font-size: 8px;
            font-weight: 400;
            line-height: normal;
            margin-top: 8px;
          ">
                    Conste por el presente documento, el Contrato de Servicios Educativos de Formación Profesional
                    @if ($sedeParam['id_depto'] == '8')
                        Técnica
                    @else
                        Universitaria
                    @endif, que celebran de una parte <b>UNIVERSIDAD PERUANA UNIÓN</b>, a la que en
                    adelante
                    se le denominará <b>LA UNIVERSIDAD</b>, con R.U.C. N° 20138122256, con domicilio legal
                    en {{ $sedeParam['address'] }}, debidamente representada por su
                    <b>Apoderado(a) {{ $sedeParam['resp'] }}</b>,
                    identificada con <b>D.N.I. N° {{ $sedeParam['respDocument'] }}</b> facultada según poder inscrito en el
                    asiento A00065
                    de la partida 01894897 del Libro de Asociaciones del Registro de Personas Jurídicas de la
                    Oficina Registral de Lima, y de la otra parte
                    el(a) Sr.(ta.) <b>{{ isset($info['nom_persona']) ? $info['nom_persona'] : 'Sin nombre' }}</b>,
                    identificado(a)
                    con {{ isset($info['nom_documento']) ? $info['nom_documento'] : 'Sin Sigla' }}
                    N° {{ isset($info['num_documento']) ? $info['num_documento'] : 'Sin documento' }}, quien
                    señala como domicilio para los efectos de este contrato
                    en {{ isset($info['direccion']) ? $info['direccion'] : 'Sin direccion' }}, con teléfono móvil
                    N° {{ isset($info['num_telefono']) ? $info['num_telefono'] : 'Sin número' }},
                    con correo electrónico {{ isset($info['correo']) ? $info['correo'] : 'Sin correo' }}, a
                    quien en adelante se le denominará <b>EL(A) ESTUDIANTE</b>, quien declara
                    y acredita como su <b>responsable financiero</b>
                    a:
                    @if (isset($info['tipo_resp']) and $info['tipo_resp'] == 'PRONABEC')
                        PRONABEC con número de RUC: 20546798152 - PROGRAMA NACIONAL DE BECAS Y CREDITO EDUCATIVO y
                        domiciliado(a)
                        en Av. Arequipa N° 1935, distrito de Lince provincia, y departamento de Lima,
                    @else
                        {{ isset($info['nom_resp']) ? $info['nom_resp'] : 'Sin responsable' }} con
                        D.N.I. N°{{ isset($info['num_doc_resp']) ? $info['num_doc_resp'] : 'Sin número' }} y
                        domiciliada
                        en {{ isset($info['direccion_resp']) ? $info['direccion_resp'] : 'Sin direccion' }}, con
                        teléfono
                        móvil
                        N° {{ isset($info['num_telefono_resp']) ? $info['num_telefono_resp'] : 'Sin número telefono' }}
                        ,
                    @endif
                    <strong>en los términos y condiciones de las cláusulas siguientes:</strong>
                </article>
            </div>

            <div class="section-body">
                <div class="header-sc">
                    <span> <strong>I. PRIMERA:</strong> MARCO LEGAL. </span>
                </div>

                <div class="body-sc">
                    <article>
                        El presente contrato de servicios educativos de formación profesional técnica, además de las
                        cláusulas del mismo, se rige por las disposiciones legales vigentes, sin ser esta enumeración
                        taxativa, por las siguientes:
                    </article>
                </div>

                <div class="body-sc"
                    style="
            display: flex;
            padding-left: 8px;
            justify-content: center;
            align-items: center;
            gap: 10px;
            align-self: stretch;
            margin-top: 4px;
          ">
                    <article>
                        1.1. Constitución Política del Perú. <br />
                        1.2. El Código Civil. <br />
                        1.3. Ley General de Educación N° 28044. <br />
                        1.4. Ley Universitaria N° 30220. <br />
                        1.5. Ley de Protección a la Economía Familiar Respecto del Pago de
                        Pensiones en Institutos, Escuelas Superiores, Universidades y
                        Escuelas de Posgrado Públicos y Privados N° 29947. <br />
                        1.6. El Estatuto de LA UNIVERSIDAD. <br />
                        1.7. Los Reglamentos de LA UNIVERSIDAD. <br />
                        1.8. Otras normas legales aplicables.
                    </article>
                </div>
            </div>

            <div class="section-body">
                <div class="header-sc">
                    <span> <strong>II. SEGUNDA:</strong> DE LAS PARTES. </span>
                </div>

                <div class="body-sc">
                    <article>
                        La UNIVERSIDAD es una persona jurídica de derecho privado sin fines de lucro, creada por la Ley °
                        23758 y denominada como tal por la Ley N° 26542, dedicada a la formación profesional universitaria a
                        través de sus Facultades y Escuelas Profesionales, según los niveles y modalidades de estudios
                        presencial y semipresencial y a distancia, en funcionamiento en la ciudad de Lima, Juliaca y
                        Tarapoto.
                        EL(A) ESTUDIANTE es una persona natural, con mayoría de edad, con plena capacidad de goce y de
                        ejercicio, quien en pleno uso y ejercicio de sus facultades físicas, mentales y civiles y por
                        decisión
                        voluntaria a procedido a elegir, seguir, cursar, estudios de formación profesional universitaria en
                        la carrera profesional ofertada por la UNIVERSIDAD, en su campus Lima. Al no ser mayor de edad,
                        EL(A) ESTUDIANTE declara y acredita que El(A) Responsable Financiero por sus estudios es:
                        @if (isset($info['tipo_resp']) and $info['tipo_resp'] == 'PRONABEC')
                            PRONABEC con número de RUC: 20546798152.
                        @else
                            {{ isset($info['nom_resp']) ? $info['nom_resp'] : 'Sin responsable' }} con
                            D.N.I. {{ isset($info['num_doc_resp']) ? $info['num_doc_resp'] : 'Sin número' }}.
                        @endif

                        @if (isset($info['es_beca']) and $info['es_beca'] != '1')
                            El Responsable Financiero de EL(A)
                            ESTUDIANTE es {{ isset($info['nom_persona']) ? $info['nom_persona'] : 'Sin nombre' }}
                            ,
                            identificado
                            con {{ isset($info['nom_documento']) ? $info['nom_documento'] : 'Sigla' }}
                            N° {{ isset($info['num_documento']) ? $info['num_documento'] : 'Sin documento' }}.
                        @endif
                    </article>
                </div>
            </div>

            <div class="section-body">
                <div class="header-sc">
                    <span> <strong>III. TERCERA:</strong> OBJETO DEL CONTRATO. </span>
                </div>

                <div class="body-sc">
                    <article>
                        EL(A) ESTUDIANTE por decisión libre, acepta y declara que contrata los servicios educativos de
                        formación profesional universitaria ofertados por la UNIVERSIDAD en la carrera profesional elegida.
                    </article>
                </div>
            </div>

            <div class="section-body">
                <div class="header-sc">
                    <span> <strong>IV. CUARTA:</strong> DE LOS REQUISITOS. </span>
                </div>

                <div class="body-sc">
                    <article>
                        EL(A) ESTUDIANTE declara que, para cursar estudios en la carrera profesional elegida y ofertada por
                        LA UNIVERSIDAD, ha seguido, obtenido previa y válidamente según corresponda sus estudios de
                        educación básica regular o técnicos profesionales o profesionales técnicos o universitarios y dentro
                        del marco de la legislación educativa o superior no universitaria o superior universitaria, y que
                        sobre los mismos no existe cuestionamiento extrajudicial o judicial o administrativo, civil o penal
                        alguno, y caso contrario asume la responsabilidad en caso fueran invalidados o nulos y otorga el
                        derecho y facultad a LA UNIVERSIDAD de declarar la invalidez o nulidad de los estudios cursados en
                        la misma.
                    </article>
                </div>
            </div>

            <div class="section-body">
                <div class="header-sc">
                    <span>
                        <strong>V. QUINTA:</strong> OBLIGACIONES DE LA UNIVERSIDAD.
                    </span>
                </div>

                <div class="body-sc">
                    <article>
                        LA UNIVERSIDAD, en virtud del presente contrato, se obliga a:
                    </article>
                </div>

                <div
                    style="
            margin-top: 4px;
            display: flex;
            padding-left: 8px;
            justify-content: center;
            align-items: center;
            gap: 10px;
            align-self: stretch;
          ">
                    <article
                        style="
              color: #000;
              text-align: justify;
              font-family: 'sans-serif';
              font-size: 8px;
              font-weight: 400;
              line-height: normal;
            ">
                        5.1. Ofrecer una educación integral, de acuerdo con los fines de la Educación Adventista y los
                        planes de estudio de la carrera correspondiente.<br />
                        5.2. Desarrollar los planes y programas de estudios establecidos, a través de las sesiones de
                        aprendizaje, en los escenarios apropiados, que resguarden la
                        vida y salud pública, dispuestos por LA UNIVERSIDAD y autorizados por las disposiciones legales.
                        <br />
                        5.3. Brindar el acceso, uso y disposición de los medios educativos a través de plataformas,
                        tecnologías y aplicativos, tales como: LAMB o portal académico, LMS, y otras creadas y por crearse,
                        en los días y tiempos habilitados y asignados, en el contexto de su marco axiológico y estatutario
                        vigente. <br />
                        5.4. Proporcionar periódicamente a EL(A) ESTUDIANTE informe sobre su avance académico. <br />
                        5.5. Cumplir y exigir el cumplimiento del Estatuto y de los demás reglamentos de LA UNIVERSIDAD.
                        <br />
                        5.6. Proporcionar periódicamente a EL(A) ESTUDIANTE, un estado de cuenta a través del portal del
                        estudiante, al cual tiene libre acceso, a fin de que este(a) pueda verificar su situación o
                        regularizar su deuda, comunicándole, cada vez que estime necesario, verbalmente y/o por escrito,
                        requerimientos de ponerse al día en el pago de pensiones atrasadas.<br />
                        Para efectos del requerimiento de pago, ambas partes convienen de común acuerdo en que podrán ser :
                        <br />
                        <span style="padding-left: 16px;">5.6.1. El requerimiento verbal</span><br>
                        <span style="padding-left: 16px;">5.6.2. El requerimiento por escrito.</span><br>
                        <span style="padding-left: 16px;">5.6.3. El requerimiento a través de la entrega de su estado de
                            cuenta.</span><br>
                        <span style="padding-left: 16px;">5.6.4. El requerimiento a través de medio electrónico (al correo
                            electrónico y teléfono móvil consignado en la parte introductoria
                            de este contrato, por mensajes de texto, whatsapp, llamadas,
                            videos y otros medios).</span><br>
                        <span style="padding-left: 16px;">5.6.5. El requerimiento a través de otras formas de comunicación
                            valederos.</span><br>

                        5.7. Ofrecer y tener habilitado a través su página Web bibliotecas electrónicas a las cuales EL(A)
                        ESTUDIANTE que pueden acceder con su respectivo usuario y contraseña, en el día y tiempo habilitado
                        para su uso y disposición, sin más restricciones que las establecidas en el marco axiológico y
                        estatutario de LA UNIVERSIDAD y descritos en el presente contrato. LA UNIVERSIDAD también cuenta con
                        el servicio de biblioteca en su campus, conforme las disposiciones vigentes.
                        <br />
                        5.8 Otras señaladas en el presente contrato o en el Estatuto o demás reglamentos de LA UNIVERSIDAD.
                    </article>
                </div>
            </div>

            <div class="section-body">
                <div class="header-sc">
                    <span>
                        <strong>VI. SEXTA:</strong> OBLIGACIONES DE EL(A) ESTUDIANTE.
                    </span>
                </div>

                <div class="body-sc">
                    <article>EL(LA) ESTUDIANTE se obliga a:</article>
                </div>

                <div
                    style="
            display: flex;
            padding-left: 8px;
            justify-content: center;
            align-items: center;
            gap: 10px;
            align-self: stretch;
          ">
                    <article
                        style="
              color: #000;
              text-align: justify;
              font-family: 'sans-serif';
              font-size: 8px;
              font-weight: 400;
              line-height: normal;
            ">
                        6.1. Pagar oportunamente los costos del servicio educativo
                        (matrícula y pensiones) conforme al cronograma de pagos de la
                        cláusula sétima, en la cuenta bancaria recaudadora que determine LA
                        UNIVERSIDAD, con el código que se asignará a EL(A) ESTUDIANTE para
                        este fin, o directamente en la Caja de LA UNIVERSIDAD. <br />
                        6.2. Acudir y participar de las reuniones que se convoquen por LA
                        UNIVERSIDAD. <br />
                        6.3. Observar en todo momento el Estatuto, el Reglamento del
                        Estudiante Unionista y los demás reglamentos y normas que rigen la
                        vida universitaria de LA UNIVERSIDAD , los que declara conocer y se
                        obliga a acatar y obedecer de manera voluntaria. <br />
                        6.4. Reconocer que LA UNIVERSIDAD es una entidad promovida por la
                        Iglesia Adventista del Séptimo Día (IASD) y que la vida
                        universitaria : servicio educativo y actividades conexas y
                        derivadas, son reguladas en el marco axiológico y filosófico de su
                        Promotora.
                        <br />
                        6.5. Hacer uso del servicio educativo, sus medios educativos,
                        tecnologías y/o plataformas: LAMB, LMS y demás aplicativos , creados
                        o por crearse, en el día y tiempo, momento asignado y habilitado,
                        observando el marco axiológico y estatutario de LA UNIVERSIDAD, de
                        no desarrollo de actividades de servicio educativo: enseñanza,
                        aprendizaje, investigación y gestión, y servicios conexos o
                        derivados, en el día sábado, considerado desde las dieciocho(18)
                        horas del día viernes hasta las dieciocho (18) horas del día sábado.
                        <br />
                        6.6. Suscribir y cumplir el Compromiso de Honor. <br /> 6.7.
                        Suscribir y cumplir la Declaración Jurada respecto a su Responsable
                        Financiero (Si la información declarada fuera falsa perderá los
                        beneficios que sele hayan otorgado y no podrá accederá a ningún tipo
                        de becas y/o descuentos). <br />
                        6.8. Respetar y valorar a todas las personas que integran la
                        comunidad educativa universitaria . <br />
                        6.9. Cumplir sus obligaciones académicas y no académicas en los
                        plazos, tiempos y condiciones establecidas por LA UNIVERSIDAD , el
                        docente, tutor o la autoridad académica o administrativa respectiva.
                        <br />
                        6.10. Cumplir los reglamentos académicos, de disciplina, propiedad
                        intelectual y otros vinculados al quehacer universitario en los
                        escenarios de aprendizaje disponibles para los estudiantes y
                        autorizado por las disposiciones legales, y asumir las consecuencias
                        y sanciones en caso de incumplimiento. <br />
                        6.11. Mantener, observar y promover una conducta y comportamiento
                        adecuado, de dominio propio, respeto, sobre las autoridades
                        universitarias, docentes, tutores o personal de LA UNIVERSIDAD y de
                        honestidad e integridad sobre los bienes, valores o enseres de
                        propiedad de los mismos. <br />
                        6.12. Acatar y someterse a los procesos y procedimientos
                        establecidos para los servicios contratados, los disciplinarios y
                        otros normados por LA UNIVERSIDAD .
                        <br />
                        6.13. Asistir obligatoriamente a las convocatorias, sesiones o
                        reuniones académicas, sociales, formativas o educativas dispuestas
                        por LA UNIVERSIDAD o el docente o tutor o la autoridad académica,
                        bajo sanción académica o disciplinaria. <br />
                        6.14. Asumir el aumento del costo del servicio educativo por la
                        modificación e incremento de asignaturas o cursos asolicitud de
                        EL(A) ESTUDIANTE. <br />
                        6.15. Las demás que expresamente se señalen en el presente contrato,
                        en las normas legales, el Estatuto y en los reglamentos respectivos
                        de LA UNIVERSIDAD .
                    </article>
                </div>
            </div>

            <div class="section-body">
                <div class="header-sc">
                    <span> <strong>VII. SÉPTIMA:</strong> LA MATRÍCULA. </span>
                </div>

                <div class="body-sc">
                    <article>LA UNIVERSIDAD establece que:</article>
                </div>

                <div
                    style="
            display: flex;
            padding-left: 8px;
            justify-content: center;
            align-items: center;
            gap: 10px;
            align-self: stretch;
          ">
                    <article
                        style="
              color: #000;
              text-align: justify;
              font-family: 'sans-serif';
              font-size: 8px;
              font-weight: 400;
              line-height: normal;
            ">
                        7.1. El costo de la matrícula en

                        S/.{{ isset($infoPayment->matricula) ? $infoPayment->matricula : '' }}
                        ({{ isset($infoPayment->matriculatxt) ? $infoPayment->matriculatxt : '' }}),
                        {{ isset($enrrollmentDiscountText) ? ' y descuentos: ' . $enrrollmentDiscountText . '. Siendo estos descuentos de carácter temporal para el presente ciclo contratado.' : '' }}
                        Monto que no excede el
                        importe de la pensión de enseñanza (carga completa de créditos por ciclo), de acuerdo a lo
                        establecido
                        por la Ley General de Educación N° 28044, la Ley Universitaria N° 30220, la Ley N° 29947 (Ley de
                        Protección a la Economía Familiar Respeto del Pago de Pensiones en Institutos, Escuelas
                        Superiores,
                        Universidades y Escuelas de Posgrado Públicos y Privados) y las demás leyes aplicables.
                        <br />
                        7.2. LA UNIVERSIDAD , previo a la suscripción del presente documento
                        y aceptación por EL(LA) ESTUDIANTE de los costos, le ha brindado
                        información sobre las condiciones económicas a las que se ajustará
                        la prestación del servicio educativo en forma escrita, veraz
                        suficiente y apropiada. Queda establecido que no se condiciona la
                        inscripción y/o matrícula al pago de las contribuciones denominadas
                        voluntarias.
                        <br />
                        7.3. En ningún supuesto habrá devolución de matrícula a EL(A)
                        ESTUDIANTE, excepto cuando éste mismo decida hacer el retiro
                        temporal o definitivo del semestre académico, dentro de la primera
                        semana del inicio de clases y en el porcentaje establecido en el
                        reglamento correspondiente. <br />
                        7.4. Para toda matrícula a un siguiente ciclo académico, EL(A)
                        ESTUDIANTE solo podrá hacerlo si no tiene ninguna deuda pendiente
                        con LA UNIVERSIDAD , de conformidad con lo dispuesto en la Ley de
                        Protección a la Economía Familiar Respecto del Pago de Pensiones en
                        Institutos, Escuelas Superiores, Universidades y Escuelas de
                        Posgrado Públicos y Privados N° 29947.
                        <br />
                        7.5. En caso EL(LA) ESTUDIANTE opte por utilizar los servicios de
                        las residencias universitarias, en la matricula se efectuará el pago
                        del 50 % del valor de la residencia universitaria, y la diferencia
                        en las pensiones, según cronograma de pagos señalado en el contrato
                    </article>
                </div>
            </div>

            <div class="section-body">
                <div class="header-sc">
                    <span> <strong>VIII. OCTAVA:</strong> LAS PENSIONES. </span>
                </div>

                <div
                    style="
                        display: flex;
                        padding-left: 8px;
                        justify-content: center;
                        align-items: center;
                        gap: 10px;
                        align-self: stretch;
                    ">
                    <article
                        style="
                        color: #000;
                        text-align: justify;
                        font-family: 'sans-serif';
                        font-size: 8px;
                        
                        font-weight: 400;
                        line-height: normal;
                        ">
                        8.1. El monto de la pensión por el derecho de enseñanza del semestre
                        académico {{ $info['semestre'] }}, es el que se especifica en el ACUERDO DE
                        FINANCIACION-CONTRATO N° {{ isset($info['codigo_contrato']) ? $info['codigo_contrato'] : 'S/N' }}
                        con su respectivo
                        fraccionamiento en cuotas durante el mismo semestre académico, más
                        el concepto de matrícula, talcomo lo establece la Ley N° 29571 .
                        <br />
                        8.2. EL(A) ESTUDIANTE, de así considerarlo y de manera voluntaria,
                        podrá cancelar en una sola armada el monto de la pensión de
                        enseñanza del semestre académico, en forma adelantada, sin embargo,
                        NO ESTA OBLIGADO(A) a ello. <br />
                        8.3. Las pensiones se pagan en
                        {{ $info['codigo_tipo_contrato'] == '2' ? 'cuatro (4)' : 'cinco (5)' }} armadas, para facilitar el
                        pago a EL(A) ESTUDIANTE; se trata de
                        {{ $info['codigo_tipo_contrato'] == '2' ? 'tres (3)' : 'cuatro (4)' }} pensiones mensuales
                        pagaderas en {{ $info['codigo_tipo_contrato'] == '2' ? 'cuatro (4)' : 'cinco (5)' }} armadas.
                        EL(A)ESTUDIANTE podrá pagar, de
                        manera voluntaria, la primera armada conjuntamente con la matrícula.
                        <br />
                        8.4. DESCUENTOS: En caso de pago adelantado al contado de todo el
                        semestre académico (matrícula y pensiones de enseñanza), habrá un
                        porcentaje de descuento solo en las pensiones de enseñanza. Este
                        descuento no se aplica a la matrícula ni al monto equivalente a la
                        primera armada o cuota de la modalidad de pago en armadas regulares.
                        El valor de la pensión por servicio educativo y su respectivo
                        cronograma de pagos, están especificados en el ACUERDO DE
                        FINANCIACION-CONTRATO N° {{ isset($info['codigo_contrato']) ? $info['codigo_contrato'] : 'S/N' }}
                        que EL(A) ESTUDIANTE declara
                        haberlo suscrito, conocerlo y estar totalmente conforme, antes de
                        firmar el presente documento. <br />
                        8.5 PAGO DE INTERESES MORATORIOS:Conforme lo señala la Ley N° 29571 (Código de Protección y Defensa
                        del
                        Consumidor), la tasa del costo efectivo anual incluye todas las cuotas e intereses, todos los cargos
                        y
                        comisiones. En razón al mismo, EL(A) ESTUDIANTE que incumpla con el pago indicado en el ACUERDO DE
                        FINANCIACION-CONTRATO N° {{ isset($info['codigo_contrato']) ? $info['codigo_contrato'] : 'S/N' }} ,
                        estará sujeto(a) a una carga de interés moratorio equivalente a
                        la tasa del interés interbancario dispuesta por el Banco de Reserva del Perú, de acuerdo a la Ley N°
                        29947 (Ley de Protección a la Economía Familiar Respeto del Pago de Pensiones en Institutos,
                        Escuelas
                        Superiores, Universidades y Escuelas de Posgrado Públicos y Privados), a partir de la fecha de
                        constitución en mora de EL(A)ESTUDIANTE.
                        Este interés moratorio se calculará desde la constitución en mora de EL(A) ESTUDIANTE hasta la fecha
                        efectiva del pago. <br />
                        8.6. En caso de que EL(A) ESTUDIANTE dejara de cancelar dos (02) pensiones consecutivas, LA
                        UNIVERSIDAD le(la) citará a una reunión para dar tratamiento al problema, en la cual se podrá
                        suscribir un acuerdo
                        (TRANSACCIÓN EXTRAJUDICIAL), que establezca la re-programación del pago de las cuotas dentro del
                        semestre académico, culminando el mismo en la última fecha del cronograma de pago. De incumplirse
                        dicho acuerdo
                        o de no lograrse ningún entendimiento, EL(A) ESTUDIANTE faculta a LA UNIVERSIDAD a INICIAR UN
                        PROCESO EJECUTIVO, por el monto total más los cargos, comisiones, intereses e indemnizaciones, así
                        como los costos
                        y costas que genere el proceso. Dicho Proceso Judicial se dará de conformidad con lo estipulado en
                        los artículos 1219° (inciso 1) y siguientes del Código Civil y según lo previsto en el numeral 5 del
                        artículo
                        693° del Código Procesal Civil. 
                        @if(count($coursesCreditDiscount) > 0)
                        <br><br>
                        <b>LA UNIVERSIDAD</b>, entendiendo las condiciones sociales por la coyuntura de la emergencia
                        sanitaria nacional, que hace imposible que EL(A) ESTUDIANTE realice sus prácticas, suspende el cobro
                        de los
                        créditos por hora de práctica, hace uso de la art. Condición suspendida , en la cual, los créditos
                        por hora de práctica
                        los que serán recién cobrados cuando se empiece a dar el servicio. Quedan para este contrato
                        suspendidos los cobros de los
                        siguientes créditos de práctica , y (solo se estará cobrando la parte práctica teórica):
                        @endif
                    </article>

                </div>
                @if(count($coursesCreditDiscount) > 0)
                <div>
                    <table
                        style='border-radius: 9px;
                    border: 1px solid #b4bbca;
                    width: 100%;
                    padding: 0;
                    margin: 8px 0;
                    border-spacing: 0;
                    border-collapse: separate;
                    overflow: hidden;'>
                        <tbody>
                            <tr>
                                <td colspan="4" class="table-hd table-head">
                                    <div class="inline-container">
                                        <img src="{{ public_path('icons/payment.svg') }}" />
                                        <span>Descuentos</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="table-sub text-center">CURSO</th>
                                <th class="table-sub text-center">Crédito/PRÁCTICA</th>
                                <th class="table-sub text-center">Valorización</th>
                                <th class="table-sub text-center nrr">Sub total</th>
                            </tr>
                            @foreach($coursesCreditDiscount as $course)
                            <tr
                                style="
                            color: #000;
                            font-family: 'sans-serif';
                            font-size: 8px;
                            line-height: normal;
                          ">
                                <td class="item-cont">{{$course->nombre}}</td>
                                <td class="item-cont">{{$course->cp}}</td>
                                <td class="item-cont">{{ number_format($course->valorizacion, 2) }}</td>
                                <td class="item-cont nrr">{{ number_format($course->subtotal, 2) }}</td>
                            </tr>
                            @endforeach

                        </tbody>
                        <tfoot>
                            <tr
                                style="
                            color: #000;
                            font-family: 'sans-serif';
                            font-size: 8px;
                            line-height: normal;
                          ">
                                <td class="item-cont nbr"><b>Total credito</b></td>
                                <td class="item-cont nbr text-center"><b>{{ $coursesCreditDiscountTotalCp }}</b></td>
                                <td class="item-cont nbr text-center"><b> Total </b></td>
                                <td class="item-cont nbr nrr text-center"><b>{{ number_format($coursesCreditDiscountTotal, 2) }}</b></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>

            <div class="section-body">
                <div class="header-sc">
                    <span> <strong>IX. NOVENA:</strong> BECAS. </span>
                </div>

                <div class="body-sc">
                    <article>
                        Las becas se otorgarán de acuerdo a lo que señale el Reglamento de
                        Becas de LA UNIVERSIDAD .
                    </article>
                </div>
            </div>

            <div class="section-body">
                <div class="header-sc">
                    <span>
                        <strong>X. DÉCIMA:</strong> ENTREGA DE CERTIFICADOS E INFORMACION A
                        CENTRALES PRIVADAS DE INFORMACION DE RIESGOS.
                    </span>
                </div>

                <div
                    style="
            display: flex;
            padding-left: 8px;
            justify-content: center;
            align-items: center;
            gap: 10px;
            align-self: stretch;
          ">
                    <article
                        style="
              color: #000;
              text-align: justify;
              font-family: 'sans-serif';
              font-size: 8px;
              
              font-weight: 400;
              line-height: normal;
            ">
                        10.1. Los certificados de estudio sólo serán entregados a EL(A)
                        ESTUDIANTE que se encuentre al día con el pago de las matrículas y
                        pensiones de enseñanza; no habrá lugar a la entrega de documentos
                        académicos oficiales, tales como: constancias, certificados, records
                        académicos, reportes y otros similares de los semestres académicos
                        adeudados o no pagados.
                        <br />
                        10.2. Ambas partes acuerdan que LA UNIVERSIDAD queda facultada a
                        reportar negativamente a EL(A) ESTUDIANTE a las Centrales Privadas
                        de Información de Riesgos por incumplimiento del pago de matrícula,
                        pensiones u otros cargos durante el semestre o semestres anteriores.
                    </article>
                </div>
            </div>

            <div class="section-body">
                <div class="header-sc">
                    <span>
                        <strong>XI. DÉCIMO PRIMERA:</strong> DE LA DURACIÓN O PLAZO.
                    </span>
                </div>

                <div class="body-sc">
                    <article>
                        La duración del presente contrato equivale a la duración del
                        semestre académico, y en general a dieciseis (16) semanas académicas
                        como mínimo, computado desde el primer día de clases hasta el final
                        del semestre. En caso de caso fortuito o fuerza mayor, el plazo de
                        duración del presente contrato será modificado o ampliado
                        automáticamente, hasta el término de la reprogramación académica
                        autorizada por LA UNIVERSIDAD.
                    </article>
                </div>
            </div>

            <div class="section-body">
                <div class="header-sc">
                    <span>
                        <strong>XII. DÉCIMO SEGUNDA:</strong> CAUSALES DE RESOLUCIÓN.
                    </span>
                </div>

                <div class="body-sc">
                    <article>
                        El presente contrato se resolverá, terminará, por una de las
                        siguientes causas:
                    </article>
                </div>

                <div
                    style="
            display: flex;
            padding-left: 8px;
            justify-content: center;
            align-items: center;
            gap: 10px;
            align-self: stretch;
          ">
                    <article
                        style="
              color: #000;
              text-align: justify;
              font-family: 'sans-serif';
              font-size: 8px;
              
              font-weight: 400;
              line-height: normal;
            ">
                        12.1. Por mutuo acuerdo entre las partes.
                        <br />
                        12.2. Por incumplimiento por parte de EL(A) ESTUDIANTE del Estatuto,
                        reglamentos y normas de LA UNIVERSIDAD, que impliquen sanción de
                        expulsión o retiro definitivo. <br />
                        12.3. Incumplimiento de las obligaciones por EL(A) ESTUDIANTE. En su
                        caso LA UNIVERSIDAD determina las acciones legales correspondientes.
                        <br />
                        12.4. Unilateralmente por decisión de EL(A) ESTUDIANTE, en razón del
                        retiro definitivo o temporal, y previo cumplimiento de las deudas
                        pendientes, y comunicación a LA UNIVERSIDAD por escrito, con una
                        anticipación de 20 días hábiles. A EL(A) ESTUDIANTE se le cobra las
                        pensiones a la fecha de solicitud del retiro. En ningún caso habrá
                        devolución de la matrícula, excepto en los casos contemplados en el
                        reglamento correspondiente; o de las pensiones de semestre
                        académicos anteriores a la fecha del retiro.
                    </article>
                </div>
            </div>

            <div class="section-body">
                <div class="header-sc">
                    <span>
                        <strong>XIII. DÉCIMO TERCERA:</strong> REAJUSTES, CUOTAS
                        EXTRAORDINARIAS E INCREMENTO DE PENSIÓN
                    </span>
                </div>

                <div class="body-sc">
                    <article>
                        LA UNIVERSIDAD podrá reajustar la pensión mensual si las condiciones
                        económicas generales se ven deterioradas, la misma que deberá ser
                        sustentada y comunicada a EL(A) ESTUDIANTE, dentro de la igencia del
                        ciclo mes inmediato anterior. Asimismo, el costo de la siguiente
                        matrícula y del siguiente ciclo podrá ser incrementado, lo cual será
                        comunicado a EL(A) ESTUDIANTE en el ciclo inmediato anterior. EL(A)
                        ESTUDIANTE que oportunamente no acreditó ser merecedor del descuento
                        o ayuda económica que le hubiera otorgado, perderá dicho beneficio,
                        reajustandolo al monto correspondiente en la última armada del
                        ciclo.
                    </article>
                </div>
            </div>

            <div class="section-body">
                <div class="header-sc">
                    <span>
                        <strong>XIV. DÉCIMO CUARTA:</strong> DE LOS DESCUENTOS A EL(A)
                        ESTUDIANTE.
                    </span>
                </div>

                <div class="body-sc">
                    <article>
                        Estos descuentos se aplicarán de acuerdo a lo establecido en la directiva de descuentos
                        correspondiente
                        (Reglamentos de descuentos: Normatividad Institucional). Considerando la modalidad de pago de su
                        ciclo académico.
                    </article>
                </div>
            </div>

            <div class="section-body">
                <div class="header-sc">
                    <span>
                        <strong>XV. DÉCIMO QUINTA:</strong> ACEPTACIÓN, CONFORMIDAD Y
                        SUSCRIPCIÓN.
                    </span>
                </div>

                <div class="body-sc">
                    <article>
                        Ambas partes reconocen que el presente Contrato se formaliza,
                        perfecciona y surte todos los efectos de suscripción, en todos sus
                        extremos, contenido y alcances, vinculando a las partes, con la
                        suscripción del soporte físico de este contrato o con la aceptación
                        electrónica de los mismos contratantes, lo que será hecho cuando
                        EL(A) ESTUDIANTE pulse el botón ACEPTO o al realizar la matrícula
                        del semestre académico y al acuse de recibo otorgada a LA
                        UNIVERSIDAD. Esta comunicación se realizará al correo electrónico o
                        al teléfono móvil consignado en la parte introductoria de este
                        contrato, que EL(A) ESTUDIANTE declara que es el medio oficial de
                        comunicación (por cualesquier medio o red social que de constancia o
                        acuse de recibo o conformidad) con LA UNIVERSIDAD respecto a los
                        efectos y ejecución del presente contrato.
                    </article>
                </div>
            </div>

            <div class="section-body">
                <div class="header-sc">
                    <span>
                        <strong>XVI. DÉCIMO SEXTA:</strong> JURISDICCIÓN Y COMPETENCIA.
                    </span>
                </div>

                <div class="body-sc">
                    <article>
                        JURISDICCIÓN Y COMPETENCIA. En caso de controversia sobre el
                        contenido y alcance del presente contrato y su objeto, ambas partes
                        renuncian el Juez de su domicilio y se someten a la jurisdicción de
                        los jueces y tribunales de la {{ $sedeParam['court'] }}.
                        Dejando constancia queen odo momento buscaran la solución pacífica y
                        armoniosa de sus diferencias previamente y mutuas
                        concesiones.Estando conformes con todo lo estipulado, normado, lo
                        ratifica y acusa recibo de su aceptación plena, total y libre de las
                        condiciones en este contrato, y sin necesidad de suscribirlo
                        físicamente.
                    </article>
                </div>
            </div>

            @if ($info['codigo_tipo_contrato'] == '1')
                <div class="section-body">
                    <div class="header-sc">
                        <span> <strong>XVII. DÉCIMO SÉPTIMA:</strong> SEGUROS. </span>
                    </div>

                    <div class="body-sc">
                        <article>
                            LA UNIVERSIDAD, como intermediaria, contrata el seguro contra
                            accidentes para todos los estudiantes de la modalidad de estudio
                            presencial (cabe recalcar que este no es un seguro de vida), el cual
                            tendrá una duración de
                            {{ (str_contains($info['semestre'], '-2') and $info['w_enroll'] == '0') ? '6' : '6' }} meses
                            apartir del inicio de clases según
                            cronograma académico {{ $info['semestre'] }} (no se asegura por matrículas de cursos
                            extraprogramáticos y dirigidos).
                        </article>
                    </div>
                </div>
            @endif
            <div class="section-body">
                <div class="header-sc">
                    <span>
                        <strong
                            @if ($info['codigo_tipo_contrato'] == '1') >XVIII. DÉCIMO OCTAVA:
                            @else
                            XVII. DÉCIMO SÉPTIMA: @endif
                            </strong> SERVICIO EDUCATIVO EN EL CONEXTO DE EMERGENCIA SANITARIA.
                    </span>
                </div>

                <div class="body-sc">
                    <article>
                        En el contexto de la emergencia sanitaria COVID-19 y por mandato de
                        las disposiciones legales vigentes que regulan el funcionamiento de
                        LA UNIVERSIDAD , el servicio educativo de formación profesional
                        universitaria, materia del presente contrato y sus actividades
                        académicas, conexas y derivadas son otorgadas en la modalidad no
                        presencial, virtual u online, haciendo uso y a través de los medios
                        educativos o tecnológicos, tales como LAMB o portal académico, LMS y
                        otros, creados o por crearse, para el uso y disposición de EL(LA)
                        ESTUDIANTE, en los días y tiempos asignados y habilitados, salvo las
                        limitación de acceso y uso únicamente en el día viernes desde las
                        dieciocho (18) horas hasta las dieciocho (18) horas del día sábado.
                    </article>
                    <article>
                        Estando conformes con todo lo estipulado, normado, lo ratifican y
                        acusa recibo de su aceptación plena, total y libre de las
                        condiciones en este contrato, y sin necesidad de suscribirlo
                        físicamente.
                    </article>
                </div>
            </div>

            <div class="section-body">
                <div class="header-sc">
                    <span>
                        <strong
                            @if ($info['codigo_tipo_contrato'] == '1') >XIX. DÉCIMA NOVENA:
                            @else
                            XVIII. DÉCIMO OCTAVA: @endif
                            </strong> SERVICIO DE RESIDENCIAS UNIVERSITARIAS.
                    </span>
                </div>

                <div class="body-sc">
                    <article>
                        En caso de que EL(LA) ESTUDIANTE opte por el servicio de residencias
                        universitarias, se regirá de acuerdo con lo establecido en la
                        normativa sobre Residencias Universitarias correspondiente
                        (Reglamento de Residencias Universitarias, el cual se halla en la
                        Normatividad Institucional). Cabe precisar que se aplicarán
                        restricciones en el uso de los servicios de las residencias
                        universitarias, cuando correspondan, lo que es de conocimiento y es
                        aceptado voluntariamente por EL(LA) ESTUDIANTE al emitirse y aceptar
                        este contrato.
                    </article>

                    <article>
                        Estando conformes con las cláusulas y con todo lo estipulado y
                        normado, lo ratifican y el acuse de recibo de este documento de
                        manera virtual es señal de la aceptación plena, total, libre y
                        voluntaria de todas y cada una de las condiciones de este contrato,
                        y sin tener la necesidad de suscribirlo físicamente.
                    </article>
                </div>
            </div>

            <table style="border: none;width:100%;margin-top:16px">
                <tbody>
                    <tr>
                        <td></td>
                        <td colspan="2" class="text-center">
                            <span class="texto text-center">{{ $nowDate }}</span>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-center" style="border: none;width:40%">

                            @if ($sedeParam['id_depto'] == '5')
                                <div class="signature">
                                    <img src="{{ public_path('img/signatures/sig_upeu.png') }}" width="220" />
                                    <div class="texto">
                                        <label>________________________________________</label><br>
                                        <span>{{ $sedeParam['resp'] }}</span><br>
                                        <span>{{ $sedeParam['apo'] }}</span><br>
                                        <span>UNIVERSIDAD PERUANA UNI&Oacute;N</span>
                                    </div>
                                </div>
                            @elseif($sedeParam['id_depto'] == '8')
                                <div class="signature">
                                    <img src="{{ public_path('img/signatures/sig_upeu.png') }}" width="220" />
                                    <div class="texto">
                                        <label>________________________________________</label><br>
                                        <span>{{ $sedeParam['resp'] }}</span><br>
                                        <span>{{ $sedeParam['apo'] }}</span><br>
                                        <span>UNIVERSIDAD PERUANA UNI&Oacute;N</span>
                                    </div>
                                </div>
                            @elseif($sedeParam['id_depto'] == '6')
                                <div class="signature">
                                    <img src="{{ public_path('img/signatures/sig_upeu.png') }}" width="220" />
                                    <div class="texto">
                                        <label>________________________________________</label><br>
                                        <span>{{ $sedeParam['resp'] }}</span><br>
                                        <span>{{ $sedeParam['apo'] }}</span><br>
                                        <span>UNIVERSIDAD PERUANA UNI&Oacute;N</span>
                                    </div>
                                </div>
                            @elseif($sedeParam['id_depto'] == '1')
                                <div class="signature">
                                    <img src="{{ public_path('img/signatures/sig_upeu.png') }}" width="220" />
                                    <div class="texto">
                                        <label>________________________________________</label><br>
                                        <span>{{ $sedeParam['resp'] }}</span><br>
                                        <span>{{ $sedeParam['apo'] }}</span><br>
                                        <span>UNIVERSIDAD PERUANA UNI&Oacute;N</span>
                                    </div>
                                </div>
                            @endif

                        </td>
                        <td class="text-center" style="border: none;width:40%">
                            <div class="signature">
                                <div class="texto">
                                    <br><br><br><br><br><br><br><br><br><br>
                                    <label>________________________________________</label><br>
                                    <span>{{ isset($info['nom_persona']) ? $info['nom_persona'] : 'Sin nombre' }}</span><br>
                                    <span>{{ isset($info['nom_documento']) ? $info['nom_documento'] : 'Sigla' }}
                                        :
                                        {{ isset($info['num_documento']) ? $info['num_documento'] : 'Sin documento' }}
                                        EL(A) ESTUDIANTE</span><br>
                                    <span>UNIVERSIDAD PERUANA UNIÓN</span>
                                </div>
                            </div>
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

@endsection
