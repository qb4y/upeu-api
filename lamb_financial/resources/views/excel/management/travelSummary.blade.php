<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="{{'css/report/management/travelOficialxls.css'}}">
  </head>
  <body>

    <table style="border:0px !important;" class="no-border">
      <tbody>
        <tr>
          @if($data['datos']['empresa']->logo && $data['datos']['empresa']->logo!==null)
          
            <td  rowspan="2" class="text-center"><img width="80" height="80" src="{{ $data['datos']['empresa']->logo }}"></td>
          @endif
          <td colspan="8" style="text-align: center!important;vertical-align:center;"><b>Presupuesto de Viajes</b></td>
        </tr>
        <tr >
          <td></td>
          <td colspan="8" class="text-center"><b>{{$data['datos']['periodo']}}</b></td>
        </tr>
        <tr >
          <td colspan="9" class="text-left"><b>RUC: {{mb_strtoupper($data['datos']['empresa']->ruc, 'UTF-8')}}</b></td>
        </tr>
        <tr >
          <td colspan="9" class="text-left"><b>DENOMINACIÓN O RAZON SOCIAL: {{mb_convert_case($data['datos']['empresa']->nombre_legal, MB_CASE_TITLE, 'UTF-8')}}</b></td>
        </tr>

        <tr >
          <td colspan="9" class="text-left"><b>ENTIDAD: {{mb_convert_case($data['datos']['entidad']->materno, MB_CASE_TITLE, 'UTF-8')}}</b></td>
        </tr>
        <tr >
          <td colspan="9" class="text-center"><b>Resumen del Presupuesto y ejecución de gastos de viaje expresado en soles
        </tr>
      </tbody>
    </table>
    
      <table>
        <thead>
          <tr class="text-center head-info shadow font-size-10" style="color: #e9ecef !important;">
            <th class="border-p">Depto.</th>
            <th class="border-p">Funcionario</th>
            <th class="border-p">Cta.Cte</th>
            <th class="border-p">Saldo Ant</th>
            <th class="border-p">Pto. Gastos</th>
            <th class="border-p">Ejec. Gastos</th>
            <th class="border-p">Saldo</th>
            <th class="border-p">Saldo Acumulado</th>
            <th class="border-p">Saldo Ppto Anual</th>
          </tr>
        </thead>
        <tbody>
            @php
            $i = 0;
            $parentNew=null;
            $parentOld=null;
            @endphp
            @foreach($data['items'] as $key => $value)
              @php
              $parentNew=$value->id_depto;
              @endphp
              @if($i===0)
                <tr>
                  <td colspan="9" style="background-color: #CED4DA;" class="border-p"><b>{{$value->depto}}</b></td>
                </tr>
              
              @endif
    
              @if($i > 0 and $value->id_depto !== $data['items'][$i -1]->id_depto)
                <tr>
                  <td colspan="3" class="border-p"><b>Totales</b></td>
                  <td class="border-p"></td>
                  <!-- <td class="border-p"><b>{{number_format(totalSaldoAnt($data, $data['items'][$i -1]->id_depto), 2)}}</b></td> -->
                  <td class="border-p">{{number_format(totalPto($data, $data['items'][$i -1]->id_depto), 2)}}</td>
                  <td class="border-p">{{number_format(totalEject($data, $data['items'][$i -1]->id_depto), 2)}}</td>
                  <td class="border-p">{{number_format(totalSaldo($data, $data['items'][$i -1]->id_depto), 2)}}</td>
                  <td class="border-p"></td>
                  <td class="border-p"></td>
                </tr>
              
              @endif
              @if($parentOld!==$parentNew and $i!==0)
                <tr>
                  <td colspan="9" style="background-color: #CED4DA;" class="border-p"><b>{{$value->depto}}</b></td>
                </tr>
              @endif
    
              <tr>
                <td class="border-p">{{$value->id_depto}}</td>
                <td class="border-p">{{$value->funcionario}}</td>
                <td class="border-p">{{$value->id_ctacte}}</td>
                <td class="border-p">{{$value->saldo_anterior == 0 ? '-':$value->saldo_anterior}}</td>
                <td class="border-p">{{$value->pto_gasto == 0 ? '-':$value->pto_gasto}}</td>
                <td class="border-p">{{$value->eje_gasto == 0 ? '-':$value->eje_gasto}}</td>
                <td class="border-p">{{$value->saldo}}</td>
                <td class="border-p">{{$value->saldo_acumulado}}</td>
                <td class="border-p">{{$value->saldo_ppto_anual}}</td>
    
              </tr>
    
               @if($i > 0 and $i === (count($data['items'])-1))
                <tr>
                  <td class="border-p" colspan="3"><b>Totales</b></td>
                  <td class="border-p"></td>
                  <td class="border-p">{{number_format(totalPto($data, $data['items'][$i]->id_depto), 2)}}</td>
                  <td class="border-p">{{number_format(totalEject($data, $data['items'][$i]->id_depto), 2)}}</td>
                  <td class="border-p">{{number_format(totalSaldo($data, $data['items'][$i]->id_depto), 2)}}</td>
                  <td class="border-p"></td>
                  <td class="border-p"></td>
                </tr>
              @endif
              
    
              @php
              $i++;
              $parentOld=$value->id_depto;
              @endphp
            @endforeach
        </tbody>
      </table>
    
  </body>


</html>