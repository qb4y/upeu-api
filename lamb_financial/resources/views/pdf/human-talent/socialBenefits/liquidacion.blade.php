<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />

  <title>LIQUIDACIÓN DE BENEFICIOS SOCIALES</title>

  <style>
    .text-center{
        text-align: center;
    }
    .text-left{
        text-align: left;
    }
    .text-right{
        text-align: right;
    }
    .bold{
        font-weight: bold;
    }
    table,td,th{
        border: 1px solid black;
        border-collapse: collapse;
    }
    .border-right{
        border-right: 1px solid black;
    }
    table{
        width: 100%;
        font-size: 12px;
        font-family: "Arial Narrow";
        font-weight: 100;
        transform:scaleY(1.2);
    }
    table,.no-border{
        border:0px;
        border-collapse: collapse;
    }
    table,.border-top{
        border-bottom: 0px;
        border-left: 0px;
        border-right: 0px;
        border-collapse: collapse;
    }
    table,.border-bottom{
        border-top: 0px;
        border-left: 0px;
        border-right: 0px;
        border-collapse: collapse;
    }
    table,.border-left{
        border-bottom: 0px;
        border-top: 0px;
        border-right: 0px;
        border-collapse: collapse;
    }
    table,.border-right{
        border-bottom: 0px;
        border-left: 0px;
        border-top: 0px;
        border-collapse: collapse;
    }
    
    .header {
    }
    .header> .title{
        margin-top:-50px;
        padding:0px;
        font-weight:bold;
        text-transform:uppercase;
    }
    .header> .subtitle{
        padding:0px;
        font-weight:bold;
        text-transform:uppercase;
        margin-top:-5px;
    }
    .nomdocument{
        padding:0px;
        font-weight:bold;
        text-transform:uppercase;
        font-size: 0.78rem;
        margin-top:-10px;
        text-align: center;
        background-color:#BFBFBF;
        border-bottom: 1px solid black;
        border-top: 1px solid black;
    }
    .bold{
        font-weight: bold;
    }
    .logo{
        position:absolute;
        left: 0;
    }
    .divider-title{
        font-weight:bold;
        text-transform:uppercase;
        background-color:#BFBFBF;
        background-color:#BFBFBF;
        border-left: 0px;
        border-right: 0px;
        border-bottom: 1px solid black;
        border-top: 1px solid black;
    }
  </style>

</head>

<body>
    <main>
        <div class="header">
            @if($data['datos']['id_empresa'] == 201)
                <div style="text-align: left;padding-right:2px"><img width="50" height="50" src="{{url('/img/upeu.png')}}"></div>
            @endif
            @if($data['datos']['id_empresa'] == 207)
                <div style="text-align: left;padding-right:2px"><img width="50" height="50" src="{{url('/img/logo_2.png')}}"></div>
            @endif

            <div style="text-align: center">
            <h4 class="title">{{$data['datos']['empresa']}}</h4>
            <h4 class="subtitle">{{$data['datos']['ruc']}}</h4>
            </div>
        </div>
        <div class="nomdocument">LIQUIDACIÓN DE BENEFICIOS SOCIALES</div>
        <br>
        <?php if ($data['items'] ){?>
        <table style="font-size: 0.60rem;">
                <tr>
                    <td width="5%" class="text-right no-border bold"></td>
                    <td width="18%" colspan="2" class="no-border">Apellidos y Nombres</td>
                    <td width="6%" colspan="2" class="text-center no-border bold">:</td>
                    <td width="31%" class="text-left bold no-border">{{$data['items']->nom_persona}}</td>
                    <td width="40%" colspan="4" class="text-left no-border"></td>
                </tr>
                <tr>
                    <td width="5%" class="text-right no-border bold"></td>
                    <td width="18%" colspan="2" class="no-border">DNI N°</td>
                    <td width="6%" colspan="2" class="text-center no-border bold">:</td>
                    <td width="31%" class="text-left bold no-border">{{$data['items']->num_documento}}</td>
                    <td width="40%" colspan="4" class="text-left no-border"></td>
                </tr>
                <tr>
                    <td width="5%" class="text-right no-border bold"></td>
                    <td width="18%" colspan="2" class="no-border">Condición</td>
                    <td width="6%" colspan="2" class="text-center no-border bold">:</td>
                    <td width="31%" class="text-left bold no-border">{{$data['items']->categoria}}</td>
                    <td width="40%" colspan="4" class="text-left no-border"></td>
                </tr>
                <tr>
                    <td width="5%" class="text-right no-border bold"></td>
                    <td width="18%" colspan="2" class="no-border">Cargo</td>
                    <td width="6%" colspan="2" class="text-center no-border bold">:</td>
                    <td width="31%" class="text-left bold no-border">{{$data['items']->cargo}}</td>
                    <td width="40%" colspan="4" class="text-left no-border"></td>
                </tr>
                <tr>
                    <td width="5%" class="text-right no-border bold"></td>
                    <td width="18%" colspan="2" class="no-border">Motivo del Cese</td>
                    <td width="6%" colspan="2" class="text-center no-border bold">:</td>
                    <td width="31%" class="text-left bold no-border">{{$data['items']->tipo_cese}}</td>
                    <td width="40%" colspan="4" class="text-left no-border"></td>
                </tr>
                <tr>
                    <td width="5%" class="text-right no-border bold"></td>
                    <td width="18%" colspan="2" class="no-border">Sucursal</td>
                    <td width="6%" colspan="2" class="text-center no-border bold">:</td>
                    <td width="31%" class="text-left bold no-border"></td>
                    <td width="40%" colspan="4" class="text-left no-border"></td>
                </tr>
                <tr>
                    <td width="5%" class="text-right no-border bold"></td>
                    <td width="18%" colspan="2" class="no-border">Fecha de Ingreso</td>
                    <td width="6%" colspan="2" class="text-center no-border bold">:</td>
                    <td width="31%" class="text-left bold no-border">{{date('d/m/Y', strtotime($data['items']->fec_entidad)) }}</td>
                    <td width="40%" colspan="4" class="text-left no-border"></td>
                </tr>
                <tr>
                <td width="5%" class="text-right no-border bold"></td>
                <td width="18%" colspan="2" class="no-border">Fecha de Cese</td>
                <td width="6%" colspan="2" class="text-center no-border bold">:</td>
                <td width="31%" class="text-left bold no-border">{{date('d/m/Y', strtotime($data['items']->fecha_cese)) }}</td>
                <td width="40%" colspan="4" class="text-left no-border"></td>
                </tr>
                <tr>
                    <td width="5%" class="text-right no-border bold"></td>
                    <td width="18%" colspan="2" class="no-border">Sistema de Pensión</td>
                    <td width="6%" colspan="2" class="text-center no-border bold">:</td>
                    <td width="31%" class="text-left bold no-border">{{$data['items']->sistema_pension}}</td>
                    <td width="40%" colspan="4" class="text-left no-border"></td>
                </tr>
        </table>
        <br>
        <br>
        <table style="font-size: 0.60rem;">
                <tr class="border-bottom">
                    <td width="2%" class="text-center no-border ">I.</td>
                    <td  width="35%" colspan="2"  class=" border-bottom bold" >
                    REMUNERACIÓN COMPUTABLE
                    </td>
                    <td width="5%" class="text-center no-border bold">:</td>
                    <td width="10%" class="text-center border-bottom  bold">
                    CTS
                    </td>
                    <td  width="10%"  class="text-center border-bottom bold">
                    Grat.
                    </td>
                    <td  width="10%"  class="text-center border-bottom bold">
                    Vacac.
                    </td>
                    <td width="28%" class="text-center no-border bold"></td>
                </tr>
                <tr>
                    <td width="2%" class="text-center no-border "></td>
                    <td width="30%" colspan="2" class="no-border">Sueldo Básico</td>
                    <td width="5%" class="text-center no-border bold">:</td>
                    <td width="10%" class="text-right no-border">{{number_format($data['items']->basico_cts, 2)}}</td>
                    <td width="10%"  class="text-right no-border ">{{number_format($data['items']->basico_grat, 2)}}</td>
                    <td width="10%"  class="text-right no-border">{{number_format($data['items']->basico_vac, 2)}}</td>
                    <td width="33%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="2%" class="text-center no-border "></td>
                    <td width="30%" colspan="2" class="no-border">Asignación Familiar</td>
                    <td width="5%" class="text-center no-border bold">:</td>
                    <td width="10%" class="text-right no-border">{{number_format($data['items']->prima_infantil_cts, 2)}}</td>
                    <td width="10%"  class="text-right no-border ">{{number_format($data['items']->prima_infantil_grat, 2)}}</td>
                    <td width="10%"  class="text-right no-border">{{number_format($data['items']->prima_infantil_vac, 2)}}</td>
                    <td width="33%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="2%" class="text-center no-border "></td>
                    <td width="30%" colspan="2" class="no-border">Remuneración en Especie</td>
                    <td width="5%" class="text-center no-border bold">:</td>
                    <td width="10%" class="text-right no-border">{{number_format($data['items']->remun_especie_cts, 2)}}</td>
                    <td width="10%"  class="text-right no-border ">{{number_format($data['items']->remun_especie_grat, 2)}}</td>
                    <td width="10%"  class="text-right no-border">{{number_format($data['items']->remun_especie_vac, 2)}}</td>
                    <td width="33%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="2%" class="text-center no-border "></td>
                    <td width="30%" colspan="2" class="no-border">Bonificación Depreciación Vehicular</td>
                    <td width="5%" class="text-center no-border bold">:</td>
                    <td width="10%" class="text-right no-border">{{number_format($data['items']->remun_variable_cts, 2)}}</td>
                    <td width="10%"  class="text-right no-border ">{{number_format($data['items']->remun_variable_grat, 2)}}</td>
                    <td width="10%"  class="text-right no-border">{{number_format($data['items']->remun_variable_vac, 2)}}</td>
                    <td width="33%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="2%" class="text-center no-border "></td>
                    <td width="30%" colspan="2" class="no-border">Asignaciones Libre Disponibilidad</td>
                    <td width="5%" class="text-center no-border bold">:</td>
                    <td width="10%" class="text-right no-border">{{number_format($data['items']->viaticos_ld_cts, 2)}}</td>
                    <td width="10%"  class="text-right no-border ">{{number_format($data['items']->viaticos_ld_grat, 2)}}</td>
                    <td width="10%"  class="text-right no-border">{{number_format($data['items']->viaticos_ld_vac, 2)}}</td>
                    <td width="33%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="2%" class="text-center no-border "></td>
                    <td width="30%" colspan="2" class="no-border">Promedio de Gratificación</td>
                    <td width="5%" class="text-center no-border bold">:</td>
                    <td width="10%" class="text-right border-bottom">{{number_format($data['items']->ult_grati_sexto, 2)}}</td>
                    <td width="10%"  class="text-right border-bottom"></td>
                    <td width="10%"  class="text-right border-bottom"></td>
                    <td width="33%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="2%" class="text-center no-border "></td>
                    <td width="30%" colspan="2" class="no-border">Total Remuneración</td>
                    <td width="5%" class="text-center no-border bold">:</td>
                    <td width="10%" class="text-right border-bottom">{{number_format($data['items']->cts, 2)}}</td>
                    <td width="10%"  class="text-right border-bottom ">{{number_format($data['items']->grat_vac, 2)}}</td>
                    <td width="10%"  class="text-right border-bottom">{{number_format($data['items']->vac, 2)}}</td>
                    <td width="33%"  class="text-right no-border"></td>
                </tr>
        </table>
        <br>
        <br>
        <table style="font-size: 0.60rem;">
                <tr>
                    <td width="2%" class="text-center no-border"></td>
                    <td width="98%" colspan="7" class="divider-title">1 COMPENSACIÓN POR TIEMPO DE SERVICIOS</td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border">Período por Liquidar</td>
                    <td width="10%" class="no-border text-right">:</td>
                    <td width="23%"  class="text-left  no-border">{{$data['items']->anhos_cts_plr}} años, {{$data['items']->meses_cts_plr}} meses, {{$data['items']->dias_cts_plr}} días</td>
                    <td width="2%"  class="text-right no-border"></td>
                    <td width="7%"  class="text-right no-border"></td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border">Faltas del período</td>
                    <td width="10%" class="no-border text-right">:</td>
                    <td width="23%"  class="text-left no-border ">{{$data['items']->faltas_cts}} días.</td>
                    <td width="2%"  class="text-right no-border"></td>
                    <td width="7%"  class="text-right no-border"></td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border">Período de Liquidación</td>
                    <td width="10%" class="no-border text-right">:</td>
                    <td width="23%"  class="text-left no-border ">{{$data['items']->anhos_cts_pln}} años, {{$data['items']->meses_cts_pln}} meses, {{$data['items']->dias_cts_pln}} días</td>
                    <td width="2%"  class="text-right no-border"></td>
                    <td width="7%"  class="text-right no-border"></td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="32%" colspan="2" class="no-border">Del {{date('d/m/Y', strtotime($data['items']->cts_fecha)) }} al {{date('d/m/Y', strtotime($data['items']->fecha_cese)) }}</td>
                    <td width="32%" colspan="3" class="no-border"></td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border text-right"></td>
                    <td width="10%" class="no-border">Meses</td>
                    <td width="23%"  class="text-left no-border ">:{{number_format($data['items']->cts, 2)}} / 12 * {{$data['items']->meses_cts_pln}}</td>
                    <td width="2%"  class="text-right no-border ">:</td>
                    <td width="7%"  class="text-right no-border">{{number_format($data['items']->cts>0?(($data['items']->cts/12)*$data['items']->meses_cts_pln):0, 2)}}</td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border text-right"></td>
                    <td width="10%" class="no-border">Días</td>
                    <td width="23%"  class="text-left no-border ">:{{number_format($data['items']->cts, 2)}} / 12 * {{$data['items']->dias_cts_pln}}</td>
                    <td width="2%"  class="text-right no-border">:</td>
                    <td width="7%"  class="text-right border-bottom">{{number_format($data['items']->cts>0?((($data['items']->cts/12)/30)*$data['items']->dias_cts_pln):0, 2)}}</td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border text-right"></td>
                    <td width="10%" class="no-border">Total CTS</td>
                    <td width="23%"  class="text-right no-border "></td>
                    <td width="2%"  class="text-right  no-border">:</td>
                    <td width="7%"  class="text-right no-border">{{number_format($data['items']->cts_total, 2)}}</td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
        </table>
        <br>
        <br>
        <table style="font-size: 0.60rem;">
                <tr>
                    <td width="2%" class="text-center no-border"></td>
                    <td width="98%" colspan="7" class="divider-title">2 GRATIFICACIÓN TRUNCA</td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border">Período por Liquidar</td>
                    <td width="10%" class="text-right no-border">:</td>
                    <td width="23%"  class="text-left no-border ">{{$data['items']->meses_grat_trunc_plr}} meses.</td>
                    <td width="2%"  class="text-right no-border"></td>
                    <td width="7%"  class="text-right no-border"></td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border">Faltas del período</td>
                    <td width="10%" class="text-right no-border">:</td>
                    <td width="23%"  class="text-left no-border ">{{$data['items']->faltas_grat_trunc}} días.</td>
                    <td width="2%"  class="text-right no-border"></td>
                    <td width="7%"  class="text-right no-border"></td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border">Período de Liquidación</td>
                    <td width="10%" class="text-right no-border">:</td>
                    <td width="23%"  class="text-left no-border ">{{$data['items']->meses_grat_trunc_plr}} meses.</td>
                    <td width="2%"  class="text-right no-border"></td>
                    <td width="7%"  class="text-right no-border"></td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="32%" colspan="2" class="no-border">Del {{date('d/m/Y', strtotime($data['items']->grat_vac_fecha)) }} al {{date('d/m/Y', strtotime($data['items']->fecha_cese)) }}</td>
                    <td width="32%" colspan="3" class="no-border"></td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border text-right"></td>
                    <td width="10%" class="no-border">Meses</td>
                    <td width="23%"  class="text-left no-border ">:{{number_format($data['items']->grat_vac, 2)}} / 6 * {{$data['items']->meses_grat_trunc_plr}}</td>
                    <td width="2%"  class="text-right no-border ">:</td>
                    <td width="7%"  class="text-right no-border">{{number_format($data['items']->grat_vac>0?(($data['items']->grat_vac/6)*$data['items']->meses_grat_trunc_plr):0, 2)}}</td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border text-right"> </td>
                    <td width="33%" colspan="2" class="no-border">Bonificación  extraordinaria - 9%</td>
                    <td width="2%"  class="text-right  no-border">:</td>
                    <td width="7%"  class="text-right border-bottom">{{number_format($data['items']->bonif_extra,2)}}</td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border text-right"> </td>
                    <td width="33%" colspan="2" class="no-border">Total Gratificación Truncas</td>
                    <td width="2%"  class="text-right  no-border">:</td>
                    <td width="7%"  class="text-right no-border">{{number_format($data['items']->grat_trunc_total, 2)}}</td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
        </table>
        <br>
        <br>
        <table style="font-size: 0.60rem;">
                <tr>
                    <td width="2%" class="text-center no-border"></td>
                    <td width="98%" colspan="7" class="divider-title">3 VACACIONES TRUNCAS</td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border">Período por Liquidar</td>
                    <td width="10%" class="text-right no-border">:</td>
                    <td width="23%"  class="text-left no-border ">{{$data['items']->meses_vac_plr}} meses, {{$data['items']->dias_vac_plr}} dias.</td>
                    <td width="2%"  class="text-right no-border"></td>
                    <td width="7%"  class="text-right no-border"></td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border">Faltas del período</td>
                    <td width="10%" class="text-right no-border">:</td>
                    <td width="23%"  class="text-left no-border ">{{$data['items']->faltas_vac}} días.</td>
                    <td width="2%"  class="text-right no-border"></td>
                    <td width="7%"  class="text-right no-border"></td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border">Período de Liquidación</td>
                    <td width="10%" class="text-right no-border">:</td>
                    <td width="23%"  class="text-left no-border ">{{$data['items']->meses_vac_pln}} meses, {{$data['items']->dias_vac_pln}} dias.</td>
                    <td width="2%"  class="text-right no-border"></td>
                    <td width="7%"  class="text-right no-border"></td>
                    <td width="26%"  class="text-right no-border"></td>

                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="32%" colspan="2" class="no-border">Del {{date('d/m/Y', strtotime($data['items']->vac_fecha)) }} al {{date('d/m/Y', strtotime($data['items']->fecha_cese)) }}</td>
                    <td width="32%" colspan="3" class="no-border"></td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border text-right"></td>
                    <td width="10%" class="no-border">Meses</td>
                    <td width="23%"  class="text-left no-border ">:{{number_format($data['items']->vac, 2)}} / 12 * {{$data['items']->meses_vac_pln}}</td>
                    <td width="2%"  class="text-right no-border ">:</td>
                    <td width="7%"  class="text-right no-border">{{number_format($data['items']->vac>0?(($data['items']->vac/12)*$data['items']->meses_vac_pln):0, 2)}}</td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border text-right"></td>
                    <td width="10%" class="no-border">Días</td>
                    <td width="23%"  class="text-left no-border ">:{{number_format($data['items']->vac, 2)}}/ 12 / 30 * {{$data['items']->dias_vac_pln}}</td>
                    <td width="2%"  class="text-right no-border">:</td>
                    <td width="7%"  class="text-right border-bottom">{{number_format($data['items']->vac>0?((($data['items']->vac/12)/30)*$data['items']->dias_vac_pln):0, 2)}}</td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border text-right"> </td>
                    <td width="33%" colspan="2" class="no-border">Total Vacaciones truncas</td>
                    <td width="2%"  class="text-right  no-border">:</td>
                    <td width="7%"  class="text-right no-border">{{number_format($data['items']->vac_trunc_total, 2)}}</td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
        </table>
        <br>
        <br>
        <table style="font-size: 0.60rem;">
                <tr>
                    <td width="2%" class="text-center no-border"></td>
                    <td width="98%" colspan="7" class="divider-title">4 VACACIONES GANADAS Y NO GOZADAS</td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border">Período por Liquidar</td>
                    <td width="10%" class="text-right no-border">:</td>
                    <td width="23%"  class="text-left no-border ">{{$data['items']->meses_pend_plr}} meses, {{$data['items']->dias_pend_plr}} dias.</td>
                    <td width="2%"  class="text-right no-border"></td>
                    <td width="7%"  class="text-right no-border"></td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border">Vacaciones acumuladas</td>
                    <td width="10%" class="text-right no-border">:</td>
                    <td width="23%"  class="text-left no-border ">{{$data['items']->vac_acumul}} días.</td>
                    <td width="2%"  class="text-right no-border"></td>
                    <td width="7%"  class="text-right no-border"></td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border">Período de Liquidación</td>
                    <td width="10%" class="text-right no-border">:</td>
                    <td width="23%"  class="text-left no-border ">{{$data['items']->dias_pend_pln}} dias.</td>
                    <td width="2%"  class="text-right no-border"></td>
                    <td width="7%"  class="text-right no-border"></td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border text-right"></td>
                    <td width="10%" class="no-border">Días</td>
                    <td width="23%"  class="text-left no-border ">:{{number_format($data['items']->vac, 2)}} / 30 * {{$data['items']->neto_dias_pend}}</td>
                    <td width="2%"  class="text-right no-border">:</td>
                    <td width="7%"  class="text-right border-bottom">{{number_format(($data['items']->vac/30)*$data['items']->neto_dias_pend,2)}}</td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border text-right"> </td>
                    <td width="33%" colspan="2" class="no-border">Total Vacaciones truncas</td>
                    <td width="2%"  class="text-right  no-border">:</td>
                    <td width="7%"  class="text-right no-border">{{number_format($data['items']->vac_pend_total,2)}}</td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
        </table>
        <br>
        <br>
        <table style="font-size: 0.60rem;">
                <tr>
                    <td width="2%" class="text-center no-border"></td>
                    <td width="98%" colspan="7" class="divider-title">5 APORTES: SISTEMA DE PENSIONES</td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border">{{$data['items']->sistema_pension}} - {{$data['items']->sistema_pension_taza}} %</td>
                    <td width="10%" class="border-bottom"></td>
                    <td width="23%"  class="text-left no-border"></td>
                    <td width="2%"  class="text-right no-border"></td>
                    <td width="7%"  class="text-right border-bottom">{{number_format($data['items']->sist_pens_total, 2)}}</td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border">Otros</td>
                    <td width="10%" class="border-bottom"></td>
                    <td width="23%"  class="text-left no-border"></td>
                    <td width="2%"  class="text-right no-border"></td>
                    <td width="7%"  class="text-right border-bottom"></td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border"></td>
                    <td width="10%" class="border-bottom"></td>
                    <td width="23%"  class="text-left no-border"></td>
                    <td width="2%"  class="text-right no-border"></td>
                    <td width="7%"  class="text-right border-bottom"></td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%" colspan="2" class="text-center no-border"></td>
                    <td width="22%"  class="no-border text-right"> </td>
                    <td width="33%" colspan="2" class="no-border">Total Descuentos</td>
                    <td width="2%"  class="text-right  no-border"></td>
                    <td width="7%"  class="text-right no-border">{{number_format($data['items']->sist_pens_total, 2)}}</td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
                <tr>
                    <td width="5%"  colspan="2" class="text-center no-border"></td>
                    <td width="32%" colspan="2" class="no-border"><b>TOTAL A PAGAR  ( 1 + 2 + 3 + 4 - 5 )</b></td>
                    <td width="23%"  class="text-left no-border "></td>
                    <td width="2%"  class="text-right no-border"></td>
                    <td width="7%"  class="text-right border-bottom">{{number_format($data['items']->total_pagar, 2)}}</td>
                    <td width="31%"  class="text-right no-border"></td>
                </tr>
        </table>
        <br>
        <br>
        <table style="font-size: 0.60rem;">
                <tr>
                    <td width="2%" class="text-center no-border ">II.</td>
                    <td  width="35%" colspan="2"  class="no-border bold" >
                    DECLARACIÓN JURADA
                    </td>
                    <td width="5%" class="text-center no-border bold"></td>
                    <td width="10%" class="text-center no-border  bold">
                    </td>
                    <td  width="10%"  class="text-center no-border bold">
                    </td>
                    <td  width="10%"  class="text-center no-border bold">
                    </td>
                    <td width="28%" class="text-center no-border bold"></td>
                </tr>
                <tr>
                    <td colspan="8" class="no-border">
                        <p style="font-size: 0.65rem; margin-left: 0.4cm; margin-right: 0.5cm;"> Yo, {{$data['items']->nom_persona}}, DECLARO estar conforme con la presente liquidación
                        de beneficios sociales  por los servicios que he prestado en la {{$data['datos']['empresa']}}, con {{$data['datos']['ruc']}} y domiciliado en {{$data['datos']['direccion_legal']}},
                        declaro recibir la suma de S/. {{number_format($data['items']->total_pagar, 2)}} ({{$data['items']->text_total_pagar}}).</p>
                        <p style="font-size: 0.70rem; margin-right: 0.5cm;" class="text-right">{{$data['datos']['nom_ciudad']}}, {{$data['datos']['date_description']}}</p>
                    </td>
                </tr>
        </table>
        <div style="margin-top:1.6cm; margin-left: 1cm; margin-right: 0.5cm;">
            <table  style="font-size: 0.60rem; ">
                <tbody>
                    <tr>
                        <td width="41%"class="border-top text-center "><p class="text-center" style="margin-top:0mm;"> <span class="bold">{{$data['items']->nom_persona}}</span>  <br> DNI. {{$data['items']->num_documento}}</p></td>
                        <td width="8%" class="no-border text-center "><p class="text-center "></p></td>
                        <td width="41%" class="border-top text-center "><p class="text-center" style="margin-top:0mm;"> <span class="bold">{{$data['datos']['representante']}}</span> <br> REPRESENTANTE LEGAL</p></td>
                    </tr>
                </tbody>
            </table>
        </div>
<?php } else {?>
<h3 class="text-center bold">No se econtró información para mostrar</h3>
<?php } ?>
    </main>
</body>
</html>
