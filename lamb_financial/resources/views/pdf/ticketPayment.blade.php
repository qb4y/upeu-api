<!DOCTYPE html>

<html>
<head lang="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>

        .table {
            margin-top: 0px !important;
            margin-right: 0px !important;
            margin-left: 0px;
        }

        p.upeu {
            text-align: center;
            font-size: 10px;
            top: 10px !important;
            margin-top: 10px !important;
            padding-top: 20px !important;
        }

        p.boleta {
            background-color: rgba(123, 125, 119, 1);
            border-radius: 1.5px;
            text-align: center;
            font-size: 8px;
            color: #FFFFFF;
            margin-top: -50px !important;
            padding-top: 0px !important;
        }

        .remuneration {
            font-size: 8px;
            font-family: "Times New Roman", Georgia, Serif;
        }

        p.item {
            font-size: 8px;
            font-family: "Times New Roman", Georgia, Serif;
            text-align: center;
            margin-top: 0px;
            margin-bottom: 0px;

        }

        .per-info {
            font-size: 8px;
            font-family: "Times New Roman", Georgia, Serif;
            margin-top: 0px;
            margin-bottom: 0px;
        }

        .title-items {
            font-size: 8px;
            font-family: "Times New Roman", Georgia, Serif;
            margin-top: 0px;
            margin-bottom: 0px;
        }

        td {
            margin-left: 0px !important;
            margin-right: 0px !important;
        }

        .table-signature {
            margin-top: 0px !important;
            font-size: 15px;
            margin-left: 250px !important;
            font-family: "Times New Roman", Georgia, Serif;
        }

        signature {
            margin-top: 15px !important;
            font-size: 10px;
            border: 50px !important;
            margin: 50px !important;
            margin-left: 200px !important;
            font-family: "Times New Roman", Georgia, Serif;
        }

        signature-2 {
            margin-top: 15px !important;
            font-size: 10px;
            border: 50px !important;
            margin: 50px !important;
            margin-left: 250px !important;
            font-family: "Times New Roman", Georgia, Serif;
            background-image: url("https://upload.wikimedia.org/wikipedia/en/thumb/6/63/IMG_%28business%29.svg/1200px-IMG_%28business%29.svg.png");
        }

        .line-vertical {
            margin-left: 0px;
            margin-right: 0px;
            height: 130px;
            border-right: 2px ridge black;
        }

        hr {
            margin-top: 0px;
            margin-bottom: 0px;
        }

        .table-td-items-2 {
            margin-left: 200px !important;
            margin-right: 0px !important;
        }

        .item-one {
            margin-right: 50px !important;
        }

        .item-one-neto-pagar {
            margin-right: 80px !important;
        }

        .item-two {
            margin-right: -50px !important;
        }

        .text-emp {

            margin-top: 20px !important;
            margin-bottom: 0px !important;

        }

        .text-emp-text {
            font-size: 10px;
            margin-top: 0px !important;
            margin-bottom: 0px !important;
            font-family: "Times New Roman", Georgia, Serif;
        }

        .fecha {
            font-size: 10px;
        }

        .line-total-trab {
            margin-right: -50px !important;
        }

        .espacio {
            margin-right: 50px !important;
            margin-right: 50px !important;
        }

        img {
            margin-top: -10px !important;
            padding-top: -10px !important;
            margin-bottom: -10px !important;
            padding-bottom: -10px !important;
        }
    </style>

    <meta charset="UTF-8">
    <title></title>

</head>
@foreach($data as $item)
    <body>
    @foreach($item['datos'] as $item)


        <div>
            <p class="upeu">{{ $item['nombre'] }}</p>
        </div>
        <div>
            <p class="boleta">BOLETA DE PAGO DE REMUNERACIONES</p>
        </div>
        <div>
            <p class="item">Expresado en Soles</p>
        </div>
        <div>
            <p class="item">Decreto Supremo N. 15-72 del 28/09/72</p>
        </div>

        <div>
            <p class="item">RUC: {{ $item['id_ruc'] }}</p>
        </div>
        <div>
            <table class="table" align="center">

                <tbody class="per-info">

                <tr>
                    <td colspan="3">
                        <table class="table" align="center">


                            <tbody>
                            <tr>
                                <td class="title"><strong>Nombre:</strong></td>
                                <td class="body">{{ $item['employee']['nom_persona']}}</td>
                            </tr>
                            <tr>
                                <td class="title"><strong>Cargo</strong></td>
                                <td class="body">{{$item['employee']['nom_cargo']}}</td>
                            </tr>
                            <tr>
                                <td class="title"><strong>Codigo ESSALUD:</strong></td>
                                <td class="body">{{ $item['employee']['essalud']}}</td>
                            </tr>
                            <tr>
                                <td class="title"><strong>Codigo CUSS</strong></td>
                                <td class="body">{{$item['employee']['cuss']}}</td>
                            </tr>
                            <tr>
                                <td class="title"><strong>Fecha de Nacimiento</strong></td>
                                <td class="body">{{ $item['employee']['fec_nacimiento']}}</td>
                            </tr>
                            <tr>
                                <td class="title"><strong>Numero de DNI</strong></td>
                                <td class="body">{{ $item['employee']['num_documento']}}</td>
                            </tr>
                            </tbody>

                        </table>
                    </td>
                    <td colspan="3" class="table-td-items-2">
                        <table class="table" align="center">


                            <tbody>
                            <tr>
                                <td class="title_colum2"><strong>Mes de Pago</strong></td>
                                <td class="body_column2">{{ $item['employee']['mes']}}</td>
                            </tr>
                            <tr>
                                <td class="title_colum2"><strong>Fecha de Ingreso:</strong></td>
                                <td class="body_column2">{{ $item['employee']['fec_inicio']}}</td>
                            </tr>
                            <tr>
                                <td class="title_colum2"><strong>Fecha de Cese:</strong></td>
                                <td class="body_column2">{{ $item['employee']['fec_termino']}}</td>
                            </tr>
                            <tr>
                                <td class="title_colum2"><strong>Dias / Horas Trabajados:</strong></td>
                                <td class="body_column2">{{ $item['employee']['dh']}}</td>
                            </tr>
                            <tr>
                                <td class="title_colum2"><strong> Vacaciones:</strong></td>
                                <td class="body_column2">{{ $item['employee']['vacaciones']}}</td>
                            </tr>
                            <tr>
                                <td class="title_colum2"><strong>AFP:</strong></td>
                                <td class="body_column2">{{ $item['employee']['afp']}}</td>
                            </tr>
                            </tbody>

                        </table>
                    </td>
                </tr>


                </tbody>


            </table>

        </div>


        <div>
            <table class="table" align="center">

                <thead class="title-items">
                <tr>
                    <th colspan="3">INGRESOS</th>
                    <th colspan="3">APORTACIONES del Trabajador</th>
                    <th colspan="2">DESCUENTOS</th>

                </tr>

                </thead>
                <tbody>
                <tr>
                    <td colspan="8">
                        <hr>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="item-one">
                        <table class="table" align="center">
                            @foreach($item['remuneration'] as $detalle)
                                <tbody class="remuneration">
                                <tr>
                                    <td class="item-one">{{ $detalle['nombre']}}</td>
                                    <td align="right" class="item-two">{{ $detalle['importe']}}</td>
                                </tr>

                                </tbody>
                            @endforeach
                            @foreach($item['t_remu'] as $detalle)
                                <tbody class="remuneration">
                                <tr>
                                    <td align="left" class="item-one"><strong>TOTAL
                                            REMUNERACIONES</strong>
                                    </td>
                                    <td align="right" class="item-two">
                                        <strong>{{ $detalle['imp']}}</strong></td>
                                </tr>
                                </tbody>
                            @endforeach
                        </table>


                    </td>
                    <td class="line-vertical">

                    </td>
                    <td colspan="2" class="item-one">
                        <table class="table" align="center">
                            @foreach($item['retention'] as $detalle)
                                <tbody class="remuneration">
                                <tr>
                                    <td align="left" class="item-one">{{ $detalle['nombre']}}</td>
                                    <td align="right" class="item-two">{{ $detalle['importe']}}</td>
                                </tr>
                                </tbody>
                            @endforeach

                            @foreach($item['t_contri'] as $detalle)
                                <tbody class="remuneration">
                                <tr>
                                    <td align="left" class="item-one"><strong>Total Trabajador</strong></td>
                                    <td align="right" class="item-two"><strong>{{ $detalle['imp']}}</strong></td>
                                </tr>
                                </tbody>
                            @endforeach
                            <tbody class="remuneration">
                            <tr>
                                <td colspan="4">
                                    <hr class="line-total-trab">
                                </td>

                            </tr>
                            <tr>
                                <td colspan="2"><strong>APORTACIONES del Empleador</strong></td>
                            </tr>
                            </tbody>


                            @foreach($item['contribution'] as $detalle)
                                <tbody class="remuneration">
                                <tr>
                                    <td class="item-one">{{ $detalle['nombre']}}</td>
                                    <td align="right" class="item-two">{{ $detalle['importe']}}</td>
                                </tr>
                                </tbody>
                            @endforeach

                            @foreach($item['t_contri'] as $detalle)
                                <tbody class="remuneration">
                                <tr>
                                    <td class="item-one"><strong>Total aportes del empledor</strong></td>
                                    <td align="right" class="item-two"><strong>{{ $detalle['imp']}}</strong></td>
                                </tr>
                                </tbody>
                            @endforeach
                        </table>


                    </td>
                    <td class="line-vertical">

                    </td>
                    <td colspan="2" class="item-one">

                        <table class="table" align="center">
                            @foreach($item['diezmo'] as $detalle)
                                <tbody class="remuneration">
                                <tr>
                                    <td class="item-one">{{ $detalle['nombre']}}</td>
                                    <td align="right" class="item-two">{{ $detalle['importe']}}</td>
                                </tr>
                                </tbody>
                            @endforeach
                            @foreach($item['t_reten'] as $detalle)
                                <tbody class="remuneration">
                                <tr>
                                    <td class="item-one"><strong>TOTAL DESCUENTOS</strong></td>
                                    <td align="right" class="item-two">{{ $detalle['imp']}}</td>
                                </tr>
                                </tbody>
                            @endforeach
                        </table>

                    </td>


                </tr>
                <tr>
                    <td colspan="8">
                        <hr>
                    </td>
                </tr>
                <tr>
                    <td colspan="6"></td>
                    <td colspan="2">

                        <table class="table" align="center">
                            @foreach($item['t_neto'] as $detalle)
                                <tbody class="remuneration">
                                <tr>
                                    <td class="item-one-neto-pagar"><strong>NETO A PAGAR</strong></td>
                                    <td align="right" class="item-two"><strong> {{ $detalle['imp']}}</strong></td>
                                </tr>
                                </tbody>
                            @endforeach

                        </table>

                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="table-signature">
            <div>
                <p class="fecha" align="center">{{ $item['employee']['mes_name']}}</p>
            </div>

            <table class="table" align="center">

                <tbody>
                <tr>
                    <td class="signature" colspan="3">
                        @if($item['entity']=='7222')
                            <div><img src="/var/www/html/images/7222.png" width="50" height="40"></div>
                        @endif
                        @if($item['entity']=='7822')
                            <div><img src="/var/www/html/images/7822.png" width="50" height="40"></div>
                        @endif
                        @if($item['entity']=='17611')
                            <div><img src="/var/www/html/images/17611.png" width="50" height="40"></div>
                        @endif
                        <div>
                            <hr class="text-emp">
                        </div>
                        <p class="text-emp-text">
                            EMPLEADOR
                        </p>

                    </td>
                    <td colspan="2" class="espacio">

                    </td>
                    <td class="signature-2" colspan="3">

                        <div>
                            <hr class="text-emp">
                        </div>
                        <p class="text-emp-text">
                            Trabajador {{ $item['employee']['num_documento']}}
                        </p>

                    </td>
                </tr>
                </tbody>
            </table>
        </div>


    @endforeach
    </body>

@endforeach
</html>
