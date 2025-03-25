<!DOCTYPE html>
<html lang="en">
  <head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>{{$data['datos']['entidad']}} - Ficha Financiera - Resumen</title>
    <style>
      table{
          width: 100%;
          font-family: "Arial Narrow";
          font-weight: 100;
      }
      table,td,th{
        border: 1px solid black;
        border-collapse: collapse;
      }
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
      .capitalize{
          text-transform:capitalize;
      }
      .totales{
        font-weight: bold;
        color:black;
      }
      .header{
        text-align:center;
        font-family: "Calibri,Candara,Segoe,Segoe UI,Optima,Arial,sans-serif" !important;
      }
      .uppercase{
        text-transform:uppercase;
      }
      .header-tr{
        background-color:#336699;
        color:white;
      }
      .header>h2{
        padding:0;
        margin:0;
      }
      .header>h3{
        padding:0;
        margin:0;
      }
      .header>h4{
        padding:0;
        margin-top:0;
      }
      .red{
        color:red;
      }
      .min-width-td{
        width:18%;
      }
    </style>
  </head>
  <body>
    <header class="header">
      <h2><b>Ficha Financiera - Resumen</b></h2>
      <h3><b>{{$data['datos']['entidad']}} - {{$data['datos']['month']}}</b></h3>
      <h4>{{$data['datos']['fondo']}}</h4>
    </header>
    <main>
        <table>
            <thead>
              <tr class="header-tr">
                <th width="11%" class="text-center uppercase">Cuenta</th>
                <th class="text-left uppercase">Descripción</th>
                <th width="16%" class="text-center uppercase">ASSINET</th>
                <th width="16%" class="text-center uppercase">APS</th>
                <th width="16%" class="text-center uppercase">Diferencia</th>
              </tr>
            </thead>
            <tbody>
            @if(count($data['items'])>0))
              @php
                $totalAssinet=0;
                $totalAPS=0;
                $total=0;
                $old=$data['items'][count($data['items'])-1];
              @endphp
            
            @foreach($data['items'] as $item)
              @php
                $totalAssinet=$totalAssinet+$item->assinet;
                $totalAPS=$totalAPS+$item->aps;
                $total=$total+$item->diferencia;
              @endphp
              @if($old->parent_cuenta!=$item->parent_cuenta)
                <tr>
                  <td class="text-center capitalize bold">{{$item->parent_cuenta}}</td>
                  <td class="text-left capitalize bold">{{$item->parent_nombre}}</td>
                  <td></td>
                  <td></td>
                  <td></td>
                </tr>
              @endif
            <tr>
                <td class="text-center">{{$item->cuenta}}</td>
                <td class="text-left">{{$item->nombre}}</td>
                @if($item->assinet>=0)
                <td class="text-right">S/. {{number_format($item->assinet,2)}}</td>
                @else
                <td class="text-right red">S/. {{number_format($item->assinet,2)}}</td>
                @endif
                @if($item->aps>=0)
                <td class="text-right">S/. {{number_format($item->aps,2)}}</td>
                @else
                <td class="text-right red">S/. {{number_format($item->aps,2)}}</td>
                @endif
                @if($item->diferencia>=0)
                <td class="text-right">S/. {{number_format($item->diferencia,2)}}</td>
                @else
                <td class="text-right red">S/. {{number_format($item->diferencia,2)}}</td>
                @endif
                
                
            </tr>
            @php
            $old=$item;
            @endphp
            @endforeach
            @else
            @endif
            </tbody>
            <tfoot>
            <tr class="totales">
                <td colspan="2" class="text-right">Totales</td>
                @if($totalAssinet>=0)
                <td class="text-right">S/. {{number_format($totalAssinet,2)}}</td>
                @else
                <td class="text-right red">S/. {{number_format($totalAssinet,2)}}</td>
                @endif
                @if($totalAPS>=0)
                <td class="text-right">S/. {{number_format($totalAPS,2)}}</td>
                @else
                <td class="text-right red">S/. {{number_format($totalAPS,2)}}</td>
                @endif
                @if($totalAPS>=0)
                <td class="text-right">S/. {{number_format($total,2)}}</td>
                @else
                <td class="text-right red">S/. {{number_format($total,2)}}</td>
                @endif
            </tr>
            </tfoot>
        </table>
        <div>
        </div>
    </main>
   </body>
</html>