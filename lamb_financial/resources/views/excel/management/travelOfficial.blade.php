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
          
            <td  rowspan="4" class="text-center"><img width="60" height="60" src="{{ $data['datos']['empresa']->logo }}"></td>
          @endif
          <td colspan="6" style="text-align: center!important;vertical-align:center;"><b>Presupuesto de Viajes</b></td>
        </tr>
        <tr >
          <td></td>
          <td colspan="6" class="text-center"><b>ENTIDAD: {{$data['datos']['id_entidad']}} -{{$data['datos']['entidad']}}</b></td>
        </tr>
        <tr >
          <td></td>
          <td colspan="6" class="text-center"><b>{{$data['datos']['periodo']}}</b></td>
        </tr>
        <tr >
          <td></td>
          <td colspan="6" class="text-center"><b>Resumen del Presupuesto y ejecución de gastos de viaje
          </b></td>
        </tr>
      </tbody>
    </table>
    
    <table class="border-bottom">
      <thead>
        <tr class="text-center head-info shadow font-size-10" style="color: #e9ecef !important;">
            <th colspan="7" class="text-center border-p">{{$data['datos']['persona']->nom_persona}}
            </th>
        </tr>
        </thead>
      <thead>
        <tr >
          <th colspan='2' class="text-center border-p">Datos Generales</th>
          <th colspan='5' class="text-center border-p">Información Adicional</th>
        </tr>
      </thead>
      <tbody>
        <tr >
          <td colspan="2" style="border-left: 3px solid #000000">Nacimiento</td>
          <td colspan="2">Estado Civil</td>
          <td colspan="3" style="border-right: 3px solid #000000!important">Telefono</td>
        </tr>
        <tr >
          <td colspan="2">{{date('d/m/Y',strtotime($data['datos']['persona']->fecha_nac))}}</td>
          <td colspan="2">{{$data['datos']['persona']->estado_civil}}</td>
          <td colspan="3">{{$data['datos']['persona']->telefono}}</td>
        </tr>
        <tr >
          <td colspan="2">Edad</td>
          <td colspan="2">País</td>
          <td colspan="3">Dirección</td>
        </tr>
        <tr >
          <td colspan="2">{{$data['datos']['persona']->edad}}</td>
          <td colspan="2">{{$data['datos']['persona']->pais}}</td>
          <td colspan="3">{{$data['datos']['persona']->direccion}}</td>
        </tr>
        <tr >
          <td colspan="2">Documento</td>
          <td colspan="2">Sexo</td>
          <td colspan="3">Correo</td>
        </tr>
        <tr >
          <td colspan="2">{{$data['datos']['persona']->doc_number}}</td>
          <td colspan="2" style="border-bottom: 3px solid #000000">{{$data['datos']['persona']->sexo}}</td>
          <td colspan="3" style="border-bottom: 3px solid #000000">{{$data['datos']['persona']->email}}</td>
        </tr>
      </tbody>
    </table>
      
    
      <table>
        <thead>
          <tr class="text-center head-info shadow font-size-10" style="color: #e9ecef !important;">
            <th class="border-p">Mes</th>
            <th class="border-p">Depto.</th>
            <th class="border-p">Cta.Cte</th>
            <th class="border-p">Saldo Ant</th>
            <th class="border-p">Pto. Gastos</th>
            <th class="border-p">Ejec. Gastos</th>
            <th class="border-p">Saldo</th>
            <th class="border-p">Saldo Acumulado</th>
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
                  <td colspan="8" style="background-color: #CED4DA;" class="border-p"><b>{{$value->depto}}</b></td>
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
                </tr>
              
              @endif
              @if($parentOld!==$parentNew and $i!==0)
                <tr>
                  <td colspan="8" style="background-color: #CED4DA;" class="border-p"><b>{{$value->depto}}</b></td>
                </tr>
              @endif
    
              <tr>
                <td class="border-p">{{$value->mes}}</td>
                <td class="border-p">{{$value->id_depto}}</td>
                <td class="border-p">{{$value->id_ctacte}}</td>
                <td class="border-p">{{$value->saldo_anterior == 0 ? '-':$value->saldo_anterior}}</td>
                <td class="border-p">{{$value->pto_gasto == 0 ? '-':$value->pto_gasto}}</td>
                <td class="border-p">{{$value->eje_gasto == 0 ? '-':$value->eje_gasto}}</td>
                <td class="border-p">{{$value->saldo}}</td>
                <td class="border-p">{{$value->saldo_acumulado}}</td>
    
              </tr>
    
               @if($i > 0 and $i === (count($data['items'])-1))
                <tr>
                  <td class="border-p" colspan="3"><b>Totales</b></td>
                  <td class="border-p"></td>
                  <!-- <td class="border-p"><b>{{number_format(totalSaldoAnt($data, $data['items'][$i]->id_depto), 2)}}</b></td> -->
                  <td class="border-p">{{number_format(totalPto($data, $data['items'][$i]->id_depto), 2)}}</td>
                  <td class="border-p">{{number_format(totalEject($data, $data['items'][$i]->id_depto), 2)}}</td>
                  <td class="border-p">{{number_format(totalSaldo($data, $data['items'][$i]->id_depto), 2)}}</td>
                  <td class="border-p"></td>
                </tr>
              @endif
              
              <!-- @if($i === (count($data['items'])-1)) {
                <tr>
                  <td colspan="3" class="border-p"><b>Total</b></td>
                  <td class="border-p">{{number_format(totalGenAnt($data), 2)}}</td>
                  <td class="border-p">{{number_format(totalGenPto($data), 2)}}</td>
                  <td class="border-p">{{number_format(totalGenEject($data), 2)}}</td>
                  <td class="border-p">{{number_format(totalGenSaldo($data), 2)}}</td>
                  <td class="border-p"></td>
                </tr>
              @endif -->
    
              @php
              $i++;
              $parentOld=$value->id_depto;
              @endphp
            @endforeach
        </tbody>
      </table>
    
  </body>


</html>