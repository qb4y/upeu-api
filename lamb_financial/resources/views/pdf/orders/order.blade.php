@extends('layouts.pdf')
@section('content')

<h4 align="center">ORDEN DE PEDIDO</h4>
<table align="center" class="table  table-sm table-bordered">
    <!-- *ngFor="let cabe of cabecera" -->
    <tbody style="font-size: 12px">
        <tr>
          <td class="cabeceras">
            <strong>N°  Pedido:</strong>
          </td>
          <!-- id_pedido_init -->
          <td style="color: rgb(8, 8, 8)" class=" text-center">
            {{$pedido->numero }}
          </td>
          <td class="cabeceras">
            <strong>Usuario:</strong>
          </td>
          <!-- id_pedido_init -->
          <td style="color: rgb(8, 8, 8)" class=" text-center">
            {{$pedido->usuarios }}
          </td>
        </tr>
        <tr>
          <td class="cabeceras">
            <strong>Area Origen:</strong>
          </td>
          <!-- id_pedido_init -->
          <td style="color: rgb(8, 8, 8)" class=" text-center">
            {{$pedido->nombre_areaorigen }}
          </td>
          <td class="cabeceras">
            <strong>Area Destino:</strong>
          </td>
          <td style="color: rgb(8, 8, 8)" class=" text-center">
            {{$pedido->nombre_areadestino}}
          </td>
        </tr>
        <tr>
          <td class="cabeceras">
            <strong>Fecha Pedido:</strong>
          </td>
          <td style="color: rgb(14, 13, 13)" class=" text-center">
            {{$pedido->fecha_pedido }}
          </td>
          <td class="cabeceras">
            <strong>Fecha Entrega:</strong>
          </td>
          <td style="color: rgb(5, 5, 5)" class=" text-center">
            {{$pedido->fecha_entrega }}
          </td>
        </tr>
        <tr>

          <td class="cabeceras">
            <strong>Tipo de Pedido:</strong>
          </td>
          <td style="color: rgb(7, 7, 7)" class=" text-center">
            {{$pedido->nombre_tipopedido }}
          </td>
          <td class="cabeceras">
            <strong>Motivo de Pedido:</strong>
          </td>
          <td style="color: rgb(7, 7, 7)" class=" text-center">
            {{$pedido->motivo}}
          </td>
        </tr>
      </tbody>

  </table>

    <table class="table  table-sm table-bordered">
      <thead style="font-size: 12px">
        <tr class="font-weight-bold">
          <th>#</th>
          <th class="text-center text-primary">Cantidad</th>
          <th class="text-center text-primary">Detalle</th>
          <th class="text-right text-primary">Precio</th>
          <th class="text-right text-primary">S/. Sub. Total</th>

        </tr>
      </thead>
      <tbody style="font-size: 12px"> 
        <?php
        $i=0;
        $total = 0;
        ?>
        @foreach($datdetalle as $deta)
        <tr>
          <td>{{$i}} </td>
          <td class="text-center"> {{$deta->cantidad}}</td>
          <td class="text-center"> {{empty($deta->nombre_articulo)? $deta->detalle: $deta->nombre_articulo}}</td>
          <td class="text-right">{{number_format($deta->precio, 2, '.', ',') }} </td>
          <td class="text-right">{{number_format($deta->cantidad * $deta->precio, 2, '.', ',') }}</td>

        </tr>
        <?php
        $total = $total +($deta->cantidad * $deta->precio);
        $i++;
        ?>
        @endforeach
        <tr class="text-right">
          <td></td>
          <td></td>
         
          <td colspan="2" class="text-right">Total S/.</td>
          <td class="text-right">{{ number_format($total, 2, '.', ',') }}</td>
        </tr>
      </tbody>
    </table>

    @if($pedido->proceso == 'Ejecutado')
    <div class=" d-flex align-items-center justify-content-between">
      <span><strong class="cabeceras">Pedido entregados</strong></span> <i style="color: rgb(104, 201, 7)" class="fa fa-check-circle-o" aria-hidden="true"></i>
    </div>
    <br>
    <table class="table table-sm table-bordered">
        <thead style="font-size: 12px">
          <tr class="font-weight-bold">
            <th>#</th>
            <th class="text-center text-primary">Cantidad</th>
            <th class="text-center text-primary">Detalle</th>
            <th class="text-right text-primary">Precio</th>
            <th class="text-right text-primary">S/. Sub. Total</th>

          </tr>
        </thead>
        <tbody style="font-size: 10px">
          <?php
            $i=0;
            $total = 0;
            ?>
            @foreach($datentrega as $deta)
            <tr>
                <td>{{$i}} </td>
                <td class="text-center"> {{$deta->cantidad}}</td>
                <td class="text-center"> {{empty($deta->nombre_articulo)? $deta->detalle: $deta->nombre_articulo}}</td>
                <td class="text-right">{{number_format($deta->precio, 2, '.', ',') }} </td>
                <td class="text-right">{{number_format($deta->cantidad * $deta->precio, 2, '.', ',') }}</td>

            </tr>
              <?php
              $total = $total +($deta->cantidad * $deta->precio);
              $i++;
              ?>
            @endforeach
            <tr class="text-right">
                <td></td>
                <td></td>

                <td colspan="2" class="text-right">Total S/.</td>
                <td class="text-right">{{ number_format($total, 2, '.', ',') }}</td>
            </tr>
                 

        </tbody>
      </table>
    @endif
@endsection
