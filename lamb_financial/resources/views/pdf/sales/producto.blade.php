@extends('layouts.pdf')
@section('content')

<h4 align="center">REPORTE DE PRODUCTOS VENDIDOS</h4>
<div class="table-responsive">
              <table class="table table-striped table-sm table-bordered">
                  <thead>
                      <tr>
                          <th>#</th>
                          <th>Producto</th>
                          <th>Cantidad</th>
                          <th class="text-right">Importe</th>
          
                      </tr>
                  </thead>
                  <tbody style="font-size: 10px">
                  <?php
            $i=0;
            $totalImp = 0;
            $totalCant = 0;
            ?>
          @foreach($dataProducto as $items)
                      <tr>
                          <td>{{$i}}</td>
                          <td>{{$items->nombre}}</td>
                          <td class="text-right">{{$items->cantidad}}</td>
                          <td class="text-right">{{$items->importe}}</td>
                    
                      </tr>
                      <?php
        $i++;
        $totalImp+= $items->importe;
        $totalCant+= $items->cantidad;
        ?>
            @endforeach
            <tr>
          <td colspan="2" class="text-center">Total</td>
          <td class="text-right">{{$totalCant}}</td>
          <td class="text-right">{{$totalImp}}</td>
          </tr>
                  </tbody>
                  </table>
                  </div>  
@endsection