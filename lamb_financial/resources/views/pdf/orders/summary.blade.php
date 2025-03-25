@extends('layouts.pdf')
@section('content')
<style type="text/css" media="screen">

.font-size-10 {
  font-size: 10px !important;
}

.head-info {
	background-color: #7f264a;
	color: white;
	padding: 4px;
	text-transform: uppercase;
	font-weight: 600;
	font-size: .80rem;
	
}

.text-truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.text-left {
  text-align: left !important;
}

.text-right {
  text-align: right !important;
}

.text-center {
  text-align: center !important;
}

.page-break {
    page-break-after: always;
}

</style>

  <table class="table table-sm table-bordered">
      <thead >
          <tr>
              <th class="text-center" ƒ rowspan="2" style="width: 30px">#</th>
              <th class="text-center" colspan="5" >REGISTRO DE PEDIDOS <br><span></span></th>
              <th class="text-center" colspan="2" >
              Voucher:  {{$voucherData[0]->numero}}-{{$voucherData[0]->fecha}} <br>
                Lote: - {{$voucherData[0]->lote}}
                
              </th>
          </tr>
          <tr>
              <!-- <th class="text-center">#</th> -->
              <th class="text-center" >Numero</th>
              <th class="text-center">Fecha voucher</th>
              <th class="text-center">Fecha</th>
              <th class="text-center">De</th>
              <th class="text-center">A</th>
              <th class="text-center">Detalle</th>
              <th class="text-center">Importe</th>
          </tr>
      </thead>
      <tbody style="font-size: 10px">
      <?php
            $i=1;
            $total = 0;
            ?>
                @foreach($dataExpor as $k)
          <tr >
              <td style="width: 30px">{{$i}}</td>
              <td >{{$k->numero}}</td>
              <td>{{$k->fecha_voucher}}</td>
              <td>{{$k->fecha}}</td>
              <td>{{$k->origen}}</td>
              <td>{{$k->destino}}</td>
              <td>{{$k->motivo}}</td>

              <td class="text-right">{{$k->importe}}</td>
          </tr>

          <?php
                  $i++;
          $total+= $k->importe;
            ?>
          @endforeach
          <tr>
          <td colspan="7" class="text-right">Total</td>
          <td class="text-right">{{$total}}</td>
          </tr>
   
      </tbody>
      </table>

  @endsection