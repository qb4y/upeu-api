<!DOCTYPE html>
<html>
<head lang="en">

    <meta charset="UTF-8">
    <title></title>
    <style>
        strong {
            font-size: 20px;
        }

        hr {
            margin: 0px;
        }

        .hr-content {
            margin-top: -8px !important;
            margin-bottom: -8px !important;
        }

        .strong-content-title {
            margin-top: -8px !important;
            margin-bottom: 0px !important;
        }

        .art-nro {
            margin-top: -40px !important;
            margin-bottom: 0px !important;
        }

        .div-art-nro {
            margin-bottom: 10px !important;
        }

        hr {
            padding: 0.2px;
            background: black;
        }

        .certifica-p {
            font-size: 20px;
        }

        .certifica {
            margin-left: 39%;
            margin-right: 39%;
        }

        .certifica p {
            margin-top: -10px;
            margin-bottom: -10px;
        }

        .table {
            margin-left: -10px !important;
            margin-right: 30px !important;
            margin-top: -0px !important;
            margin-bottom: 6px !important;
        }

        td {
            margin-left: 90px !important;
        }

        .div-item-tile {
            font-size: 10px;
            margin-top: -10px !important;
            margin-bottom: -18px !important;
            padding-bottom: 5px !important;
        }

        .item-title {
            font-size: 15px !important;
            margin-top: -20px !important;
            padding-top: -10px !important;
            margin-bottom: -10px !important;
        }

        tbody {
            margin-top: 20px !important;
        }

        .table-content {
            padding-top: 0px !important;
            margin-bottom: 10px !important;
        }

        .item {
            margin-top: -10px !important;
            margin-bottom: 10px !important;
        }

        .total-renta {
            font-size: 15px !important;
        }

        .td-uni-imp {
            margin-left: 320px;
        }

        .table-main {
            margin-left: -70px !important;
        }

        .title-item-d {
            margin-left: 10px !important;
            font-size: 15px !important;
        }

        .signature {
            margin-top: 30px !important;

        }

        .title-item-item {
            margin-top: -10px !important;
            padding-top: -10px !important;
        }

    </style>

</head>

    @foreach($data as $item)
        @foreach($item['datos'] as $item)
            <body>
            <div class="strong-content-title" align="center">
                <p class="strong-content-title"><strong>CERTIFICADO DE RENTAS Y RETENCIONES POR RENTAS DE</strong></p>
                <p class="strong-content-title"><strong>QUINTA CATEGORIA</strong></p>
            </div>
            <div align="center" class="div-art-nro">
                <p class="art-nro">( Art. 45 del D.S. N° 122-94-EF, Reglamento de la Ley de IR)</p>
            </div>
            <div>
                <hr>
                <div align="center" class="hr-content">
                    <p class="hr-content"><strong>EJERCICIO {{ $item['year']}}</strong></p>
                </div>
                <hr>
            </div>
            <div>
                <p>
                    La Asociación Iglesia Adventista del Séptimo Día Peruana del Norte, con RUC N° 20538633021,
                    domiciliada
                    en ,
                    representada por el señor {{ $item['nombre']}} con DNI N° {{ $item['documento']}}
                </p>
            </div>
            <div class="certifica" align="center">
                <p>
                    <strong class="certifica-p">CERTIFICA</strong>
                </p>
                <hr>
            </div>
            <div>
                <p>
                    Que a Don(ña) {{ $item['nombre']}}, con DNI N° {{ $item['documento']}}, en su calidad de trabajador
                    se
                    le ha
                    retenido el importe de: {{ $item['retenciones']}} como pago a cuenta del Impuesto a la Renta
                    correspondiente al Ejercicio
                    gravable {{ $item['year']}}, calculado en base a las siguientes rentas:

                </p>
            </div>
            <div>
                <table class="table-main">
                    <tbody>
                    <tr>
                        <td>
                            <div class="content-items">
                                <div class="item">
                                    <div class="div-item-tile">
                                        <p class="item-title"><strong> 1.- RENTA BRUTA</strong></p>
                                    </div>
                                    <div class="table-content">
                                        <table class="table">
                                            <tbody>

                                            <tr>
                                                <td colspan="4">a.- Sueldos o Salarios</td>
                                                <td align="center"> S/.</td>
                                                <td align="right"> {{ $item['a']}}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"> b.- Gratificaciones</td>
                                                <td align="center"> S/.</td>
                                                <td align="right">{{ $item['b']}}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"> c.- Gratificaciones Extraordinarias</td>
                                                <td align="center"> S/.</td>
                                                <td align="right">{{ $item['c']}}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4">d.- Bonificaciones, Asignaciones</td>
                                                <td align="center"> S/.</td>
                                                <td align="right">{{ $item['d']}}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"> e.- Otros conceptos remunerativos</td>
                                                <td align="center"> S/.</td>
                                                <td align="right"> {{ $item['e']}}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4">f.- Asignación Familiar</td>
                                                <td align="center"> S/.</td>
                                                <td align="right">{{ $item['f']}}</td>
                                            </tr>

                                            <tr>
                                                <td colspan="4">g.- Horas Extras</td>
                                                <td align="center"> S/.</td>
                                                <td align="right"> {{ $item['g']}}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4">h.- Remuneraciones Empresas Anteriores</td>
                                                <td align="center"> S/.</td>
                                                <td align="right">{{ $item['h']}}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4">i.- Vacaciones</td>
                                                <td align="center"> S/.</td>
                                                <td align="right">{{ $item['i']}}</td>
                                            </tr>

                                            <tr>
                                                <td colspan="4">j.- Prestaciones alimentarias</td>
                                                <td align="center"> S/.</td>
                                                <td align="right">{{ $item['j']}}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td colspan="2" class="td-uni-imp">
                                                    <hr>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="total-renta"><strong>TOTAL RENTA BRUTA</strong>
                                                </td>
                                                <td align="center"> S/.</td>
                                                <td align="right">{{ $item['renta_bruta']}}</td>
                                            </tr>

                                            <tr>
                                                <td colspan="6" class="title-item-d"><strong> 2.- DEDUCCIONES DE LA
                                                        RENTA DE
                                                        5TA. CATEGORIA</strong></td>

                                            </tr>
                                            <tr>
                                                <td colspan="4" class="title-item-d"> 7 Unidades Impositivas Tributarias
                                                    (UIT)
                                                </td>
                                                <td align="center"> S/.</td>
                                                <td align="right">{{ $item['uit']}}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td colspan="2" class="td-uni-imp">
                                                    <hr>
                                                </td>
                                            </tr>


                                            <tr>
                                                <td colspan="6" class="title-item-d"><strong> 3.- RENTA NETA</strong>
                                                </td>

                                            </tr>
                                            <tr>
                                                <td colspan="4" class="title-item-item"></td>
                                                <td align="center" class="title-item-item"> S/.</td>
                                                <td align="right" class="title-item-item"> {{ $item['renta_neta']}}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="title-item-d"><strong>4.- IMPUESTO A LA
                                                        RENTA</strong></td>

                                            </tr>
                                            <tr>
                                                <td colspan="4" class="title-item-item"></td>
                                                <td align="center" class="title-item-item"> S/.</td>
                                                <td align="right" class="title-item-item">{{ $item['imp_renta']}}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="title-item-d"><strong> 5.- ( - ) TOTAL
                                                        RETENCIONES
                                                        EFECTUADAS</strong></td>

                                            </tr>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td colspan="2" class="td-uni-imp">
                                                    <hr>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="title-item-item"></td>
                                                <td align="center" class="title-item-item"> S/.</td>
                                                <td align="right"
                                                    class="title-item-item"> {{ $item['retenciones']}}</td>
                                            </tr>

                                            <tr>
                                                <td colspan="6" class="title-item-d"><strong> 6.- SALDO A REGULARIZAR O
                                                        SALDO A FAVOR</strong></td>

                                            </tr>
                                            <tr>
                                                <td colspan="4" class="title-item-item"></td>
                                                <td align="center" class="title-item-item">S/.</td>
                                                <td align="right" class="title-item-item">{{ $item['saldo']}}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td colspan="2" class="td-uni-imp">
                                                    <hr>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div>
                <p>jueves 07 de Diciembre de 2017</p>
            </div>
            <div class="signature">
                <table class="table signature">
                    <tbody>
                    <tr>
                        <td colspan="4">
                            <hr>
                        </td>
                        <td>
                            <hr>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" align="center"> Murillo Anton, Walter Sixto</td>
                        <td align="center">{{ $item['nombre']}}</td>
                    </tr>
                    <tr>
                        <td colspan="4" align="center"> Representante Legal</td>
                        <td align="center">{{ $item['documento']}}
                            Trabajador
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            </body>
        @endforeach
    @endforeach

</html>