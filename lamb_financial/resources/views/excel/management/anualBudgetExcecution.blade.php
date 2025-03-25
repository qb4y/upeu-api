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
          
            <td  class="text-center"><img width="80" height="80" src="{{ $data['datos']['empresa']->logo }}"></td>
          @endif
          <td colspan="7"class="text-center" style="text-align: center!important;vertical-align:center!important;"><b>Ejecución Presupuestal Anual- Sin Saldos Iniciales</b></td>
        </tr>
        <tr >
          <td colspan="8" class="text-left"><b>RUC: {{mb_strtoupper($data['datos']['empresa']->ruc, 'UTF-8')}}</b></td>
        </tr>
        <tr >
          <td colspan="8" class="text-left"><b>DENOMINACIÓN O RAZON SOCIAL: {{mb_convert_case($data['datos']['empresa']->nombre_legal, MB_CASE_TITLE, 'UTF-8')}}</b></td>
        </tr>
        <tr>
          <td colspan="8" class="text-left"><b>ENTIDAD: {{mb_convert_case($data['datos']['entidad']->materno, MB_CASE_TITLE, 'UTF-8')}}</b></td>
        </tr>
      </tbody>
    </table>
    
    <!-- Resumen de Ejecución -->
    <div>
      <table>
        <tbody>
          <tr><td colspan="8" style="color: #000000 !important;"><b>Resumen de Ejecución</b></td></tr>
          <tr><td colspan="8" style="color: #000000 !important;"><b>{{$data['datos']['periodo']}}</b></td></tr>
        </tbody>
      </table>
      <table>
        <thead>
          <tr class="text-center head-info shadow font-size-10" style="color: #e9ecef !important;">
            <th class="border-p">Depto.</th>
            <th class="border-p">Departamento</th>
            <th class="border-p">Ejecutado</th>
            <th class="border-p">Saldo Ant</th>
            <th class="border-p">Pto. Gastos</th>
            <th class="border-p">Ejec. Ingresos</th>
            <th class="border-p">Ejec. Gastos</th>
            <th class="border-p">Saldo</th>
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
              $parentNew=$value->id_depto_pa;
              @endphp
              @if($i===0)
                <tr>
                  <td colspan="8" style="background-color: #CED4DA;" class="border-p"><b>{{$value->depto_pa}}</b></td>
                </tr>
              
              @endif

              @if($i > 0 and $value->id_depto_pa !== $data['items'][$i -1]->id_depto_pa)
                <tr>
                  <td colspan="4" class="border-p"><b>Totales</b></td>
                  <td class="border-p">{{number_format(totalPtoGasto_($data, $data['items'][$i -1]->id_depto_pa), 2)}}</td>
                  <td class="border-p">{{number_format(totalEjectIngreso_($data, $data['items'][$i -1]->id_depto_pa), 2)}}</td>
                  <td class="border-p">{{number_format(totalEjectGasto_($data, $data['items'][$i -1]->id_depto_pa), 2)}}</td>
                  <td class="border-p">{{number_format(totalSaldo_($data, $data['items'][$i -1]->id_depto_pa), 2)}}</td>
                </tr>
              
              @endif
              @if($parentOld!==$parentNew and $i!==0)
                <tr>
                  <td colspan="8" style="background-color: #CED4DA;" class="border-p"><b>{{$value->depto_pa}}</b></td>
                </tr>
              @endif

              <tr>
                <td class="border-p">{{$value->id_depto}}</td>
                <td class="border-p">{{$value->depto}}</td>
                <td class="border-p">{{number_format($value->porcentaje, 2).'%'}}</td>
                <td class="border-p">{{$value->saldo_anterior == 0?'-':number_format($value->saldo_anterior, 2)}}</td>
                <td class="border-p">{{$value->pto_gasto == 0?'-':number_format($value->pto_gasto, 2)}}</td>
                <td class="border-p">{{$value->eje_ingresos == 0?'-':number_format($value->eje_ingresos, 2)}}</td>
                <td class="border-p">{{$value->eje_gastos == 0?'-':number_format($value->eje_gastos, 2)}}</td>
                <td class="border-p">{{number_format($value->saldo, 2)}}</td>
              </tr>

              @if($i > 0 and $i === (count($data['items'])-1))

                <tr>
                  <td colspan="4" class="border-p text-left"><b>Totales</b></td>
                  <td class="border-p">{{number_format(totalPtoGasto_($data, $data['items'][$i]->id_depto_pa), 2)}}</td>
                  <td class="border-p">{{number_format(totalEjectIngreso_($data, $data['items'][$i]->id_depto_pa), 2)}}</td>
                  <td class="border-p">{{number_format(totalEjectGasto_($data, $data['items'][$i]->id_depto_pa), 2)}}</td>
                  <td class="border-p">{{number_format(totalSaldo_($data, $data['items'][$i]->id_depto_pa), 2)}}</td>
                </tr>
              @endif
              
              

              @php
              $i++;
              $parentOld=$value->id_depto_pa;
              @endphp
            @endforeach
        </tbody>
      </table>
    </div>

    <!-- Detalle de Gastos -->
    <div>
          <table>
            <tbody>
              <tr><td colspan="7" style="color: #000000 !important;"><b>Detalles de Gastos</b></td></tr>
              <tr><td colspan="7" style="color: #000000 !important;"><b>{{$data['datos']['periodo_']}}</b></td></tr>
            </tbody>
          </table>
          <table>
            <thead>
              <tr class="text-center head-info shadow font-size-10" style="color: #e9ecef !important;">
                <th class="border-p">Fecha</th>
                <th class="border-p">Código</th>
                <th class="border-p">Cuenta</th>
                <th class="border-p">Glosa</th>
                <th class="border-p">Debe</th>
                <th class="border-p">Haber</th>
                <th class="border-p">Saldo</th>
              </tr>
            </thead>
            <tbody>
                @php
                $i = 0;
                $parentNew=null;
                $parentOld=null;
                @endphp
                @foreach($data['itemsGastos'] as $key => $value)
                  @php
                  $parentNew=$value->id_mes;
                  @endphp
                  @if($i===0)
                    <tr>
                      <td colspan="7" style="background-color: #CED4DA;" class="border-p"><b>{{$value->mes}}</b></td>
                    </tr>
                  
                  @endif

                  @if($parentOld!==$parentNew and $i!==0)

                  <tr>
                      <td colspan="7" style="background-color: #CED4DA;" class="border-p"><b>{{$value->mes}}</b></td>
                    </tr>
              
                @endif
        
                  <tr>
                    <td class="border-p">{{date_format(date_create($value->fec_asiento),"d/m/Y")}}</td>
                    <td class="border-p">{{$value->codigo}}</td>
                    <td class="border-p">{{$value->cuenta}}</td>
                    <td class="border-p">{{$value->glosa}}</td>
                    <td class="border-p">{{$value->debe == 0 ? '-':number_format($value->debe, 2)}}</td>
                    <td class="border-p">{{$value->haber == 0 ? '-':number_format($value->haber, 2)}}</td>
                    <td class="border-p">{{number_format($value->saldo, 2)}}</td>
                  </tr>


                  @if($i === (count($data['itemsGastos'])-1))
                    <tr>
                      <td colspan="6" class="border-p text-right"><b>Total</b></td>
                      <td class="border-p text-right">{{number_format($value->saldo, 2)}}</td>
                    </tr>
                  @endif
                  
                  
        
                  @php
                  $i++;
                  $parentOld=$value->id_mes;
                  @endphp
                @endforeach
            </tbody>
          </table>
    </div>
        <!-- Detalle de Ingresos -->
        <div>
          <table>
            <tbody>
              <tr><td colspan="7" style="color: #000000 !important;"><b>Detalles de Ingreso</b></td></tr>
              <tr><td colspan="7" style="color: #000000 !important;"><b>{{$data['datos']['periodo_']}}</b></td></tr>
              <tr><td colspan="7" style="color: #000000 !important;"><b>Listado de Gastos realizados por departamento durante el año {{$data['datos']['periodo_']}}</b></td></tr>
            
            </tbody>
          </table>
          <table>
            <thead>
              <tr class="text-center head-info shadow font-size-10" style="color: #e9ecef !important;">
                <th class="border-p">Fecha</th>
                <th class="border-p">Código</th>
                <th class="border-p">Cuenta</th>
                <th class="border-p">Glosa</th>
                <th class="border-p">Debe</th>
                <th class="border-p">Haber</th>
                <th class="border-p">Saldo</th>
              </tr>
            </thead>
            <tbody>
                @php
                $i = 0;
                $parentNew=null;
                $parentOld=null;
                @endphp
                @foreach($data['itemsIngresos'] as $key => $value)
                  @php
                  $parentNew=$value->id_mes;
                  @endphp
                  @if($i===0)
                    <tr>
                      <td colspan="7" style="background-color: #CED4DA;" class="border-p"><b>{{$value->mes}}</b></td>
                    </tr>
                  
                  @endif

                  @if($parentOld!==$parentNew and $i!==0)

                  <tr>
                      <td colspan="7" style="background-color: #CED4DA;" class="border-p"><b>{{$value->mes}}</b></td>
                    </tr>
              
                @endif
        
                  <tr>
                    <td class="border-p">{{date_format(date_create($value->fec_asiento),"d/m/Y")}}</td>
                    <td class="border-p">{{$value->codigo}}</td>
                    <td class="border-p">{{$value->cuenta}}</td>
                    <td class="border-p">{{$value->glosa}}</td>
                    <td class="border-p">{{$value->debe == 0 ? '-':number_format($value->debe, 2)}}</td>
                    <td class="border-p">{{$value->haber == 0 ? '-':number_format($value->haber, 2)}}</td>
                    <td class="border-p">{{number_format($value->saldo, 2)}}</td>
                  </tr>


                  @if($i === (count($data['itemsIngresos'])-1))
                    <tr>
                      <td colspan="6" class="border-p text-right"><b>Total</b></td>
                      <td class="border-p text-right">{{number_format($value->saldo, 2)}}</td>
                    </tr>
                  @endif
                  
                  
        
                  @php
                  $i++;
                  $parentOld=$value->id_mes;
                  @endphp
                @endforeach
            </tbody>
          </table>
    </div>
  </body>


</html>