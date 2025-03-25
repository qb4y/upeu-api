
<!DOCTYPE html>
<!--[if IE 8]>          <html class="ie ie8"> <![endif]-->
<!--[if IE 9]>          <html class="ie ie9"> <![endif]-->
<!--[if gt IE 9]><!-->  
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"><!-- End Required meta tags -->
    <title>Print-Lamb</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Impresión">
    <!-- FAVICONS -->
 

    <style>
    
    @page {
        margin: 30px;
    }
    body{
        font-family: sans-serif;
        margin-top: 5px;
        margin-right: 5px;
        margin-left: 5px;
    }
    table {
        width: 100%;
    }
   
    .titulo {
        background-color: {{ $objCert->boleta_title_background }};
        font-weight: bold;
        font-size: 12px;
        color: #FFF;
        text-align: center;
        border: 1px solid {{ $objCert->boleta_title_background }};
    }
    .subtitulo{
        font-weight: bold;
        font-size: 15px;
        text-align: center;
    }
    .subtitulo1{
        font-size: 13px;
        text-align: center;
    }
    .td{
        font-size: 10px;
        padding-left: 4px;
        padding-right: 4px;
 
    }
    .td-neg{
        font-size: 12px;
        font-weight: bold;
        padding-left: 4px;
        padding-right: 4px;
    
    }
    .td-center{
        font-size: 10px;
        text-align: center;
        padding-left: 4px;
        padding-right: 4px;

    }
    .td-r{
        font-size: 10px;
        text-align: right;
        padding-left: 4px;
        padding-right: 4px;
  
    }
    .td-r-neg{
        font-size: 10px;
        text-align: right;
        font-weight: bold;
        padding-left: 4px;
        padding-right: 4px;

    }
    .td-borde{
        border: 1px solid {{ $objCert->boleta_title_background }};
    }
    
    </style>

</head>
<body>
    <?php
     $ruta=asset($objCert->logo_boleta);
    ?>

    <table cellpadding="0" cellspacing="0">
        <tr>
            <td colspan="3" class="titulo">BOLETA DE PAGO DE REMUNERACIONES</td>
        </tr>
        <tr>
            <td><br/></td>
            <td><br/></td>
            <td><br/></td>
        </tr>
        <tr>
            <td style="width:15%;" rowspan="2" valign="top">
                <img src="{{ $ruta }}"  height="51" />
            </td>
            <td style="width:70%;" class="subtitulo">
                {{ $objEmp->nombre }}
            </td>
            <td style="width:15%;" rowspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td class="subtitulo1"><br/>
                Expresado en Soles<br/>{{ $objCert->boleta_ds_remuneraciones }}<br/>RUC: {{ $objEmp->id_ruc }} 

            </td>
        </tr>
        <tr>
            <td><br/></td>
        </tr>
    </table>
    <table>
        <tr>
            <td style="width:50%;">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="td-neg">Nombre:</td>
                        <td class="td">{{ $employee->nom_persona }}</td>
                    </tr>
                    <tr>
                        <td class="td-neg">Cargo:</td>
                        <td class="td">{{ $employee->nom_cargo }}</td>
                    </tr>
                    <tr>
                        <td class="td-neg">Código ESSALUD:</td>
                        <td class="td">{{ $employee->essalud }}</td>
                    </tr>
                    <tr>
                        <td class="td-neg">Código CUSS:</td>
                        <td class="td">{{ $employee->cuss }}</td>
                    </tr>
                    <tr>
                        <td class="td-neg">Fecha de Nacimiento:</td>
                        <td class="td">{{ $employee->fec_nacimiento }}</td>
                    </tr>
                    
                    <tr>
                        <td class="td-neg">Nro de documento de identidad:</td>
                        <td class="td">{{ $employee->num_documento }}</td>
                    </tr>
                </table>
            </td>
            <td style="width:50%;">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="td-neg">Mes de Pago:</strong></td>
                        <td class="td">{{ $employee->mes }}</td>
                    </tr>
                    <tr>
                        <td class="td-neg">Fecha de Ingreso:</td>
                        <td class="td">{{ $employee->fec_inicio }}</td>
                    </tr>
                    <tr>
                        <td class="td-neg">Fecha de Cese:</td>
                        <td class="td">{{ $employee->fec_termino }}</td>
                    </tr>
                    <tr>
                        <td class="td-neg">Dias / Horas Trabajados:</td>
                        <td class="td">{{ $employee->dh }}</td>
                    </tr>
                    <tr>
                        <td class="td-neg"> Vacaciones:</td>
                        <td class="td"> {{ $employee->vacaciones }}</td>
                    </tr>
                    <tr>
                        <td class="td-neg">AFP:</td>
                        <td class="td">{{ $employee->afp }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2"><br /></td>
        </tr>
    </table> 

    <table cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:34%;" valign="top" class="td-borde">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <th  colspan="2" class="titulo">INGRESOS</th>
                    </tr>
                    @foreach($dataIng as $row)
                    <tr>
                        <td style="width:70%;" class="td">{{ $row->nombre }}</td>
                        <td style="width:30%;" class="td-r">{{ number_format($row->importe,2,",",".") }}</td>
                    </tr>
                    @endforeach
                    @if($totalIng> 0)
                    <tr>
                        <td style="width:70%;" class="td-neg">TOTAL</td>
                        <td style="width:30%;" class="td-r-neg">{{ number_format($totalIng,2,",",".") }}</td>
                    </tr>
                    @endif
                </table>
            </td>
            <td style="width:33%;" valign="top" class="td-borde">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <th colspan="2" class="titulo">APORTES DEL TRABAJADOR</th>
                    </tr>
                    @foreach($dataRet as $row)
                    <tr>
                        <td style="width:70%;" class="td">{{ $row->nombre }}</td>
                        <td style="width:30%;" class="td-r">{{ number_format($row->importe,2,",",".") }}</td>
                    </tr>
                    @endforeach
                    @if($totalRet> 0)
                    <tr>
                        <td style="width:70%;" class="td-neg">TOTAL</td>
                        <td style="width:30%;" class="td-r-neg">{{ number_format($totalRet,2,",",".") }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th colspan="2" class="titulo">APORTES DEL EMPLEADOR</th>
                    </tr>
                    @foreach($dataApo as $row)
                    <tr>
                        <td style="width:70%;" class="td">{{ $row->nombre }}</td>
                        <td style="width:30%;" class="td-r">{{ number_format($row->importe,2,",",".") }}</td>
                    </tr>
                    @endforeach
                    @if($totalApo> 0)
                    <tr>
                        <td style="width:70%;" class="td-neg">TOTAL</td>
                        <td style="width:30%;" class="td-r-neg">{{ number_format($totalApo,2,",",".") }}</td>
                    </tr>
                    @endif
                </table>
            </td>
            <td style="width:33%;" valign="top" class="td-borde">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <th  colspan="2" class="titulo">DESCUENTOS</th>
                    </tr>
                    @foreach($dataDes as $row)
                    <tr>
                        <td style="width:70%;" class="td">{{ $row->nombre }}</td>
                        <td style="width:30%;" class="td-r">{{ number_format($row->importe,2,",",".") }}</td>
                    </tr>
                    @endforeach
                    @if($totalDes> 0)
                    <tr>
                        <td style="width:70%;" class="td-neg">TOTAL</td>
                        <td style="width:30%;" class="td-r-neg">{{ number_format($totalDes,2,",",".") }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>
    
    <table>
        <tr>
            <td><br /><br /></td>
        </tr>
        <tr>
            <td style="width:40%;">
                <table>
                    <tr>
                        <td class="td-neg">NETO A PAGAR</td>
                        <td class="td-neg">{{ number_format($neto,2,",",".") }}</td>
                    </tr>
                </table>
            </td>
            <td style="width:60%;" class="td-r">{{ $employee->mes_name }}</td>
        </tr>
    </table>
    <table cellpadding="0" cellspacing="0">
        <tr>
            <td><br /></td>
        </tr>
        <tr>
            <td><br /></td>
        </tr>
        <tr>
            <td style="width:33%;" class="td-center">
                @if($objCert->firma)
                <?php 
                $ruta=asset('img/'.$objCert->firma);
                ?>
                <img src="{{ $ruta }}"  height="40"/>
                @endif
            </td>
            <td style="width:34%;" rowspan="2"></td>
            <td></td>
        </tr>

        <tr>
            <td class="td-center">
                --------------------------------------------<br />EMPLEADOR<br />{{ $objCert->representante }}<br />DNI:
                {{ $objCert->num_documento }}<br /><br /></td>
            <td class="td-center">
                --------------------------------------------<br />TRABAJADOR<br />{{ $employee->nom_persona }}<br />DNI: {{ $employee->num_documento }}
            </td>
        </tr>
    </table>
    <table style="width:100%;font-size: 7px;">
        <tr>
            <td><br /></td>
        </tr>
    </table>
</body>
</html>

