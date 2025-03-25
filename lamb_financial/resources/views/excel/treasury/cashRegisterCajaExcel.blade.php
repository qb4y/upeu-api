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
  
  .lamb-td {
    vertical-align: inherit;
  }
  
  .text-center {
    text-align: center !important;
  }
  
  .page-break {
      page-break-after: always;
  }
  
  </style>

<table>
  <thead>
    <tr class="text-center head-info shadow font-size-10" style="color: #e9ecef !important;">
        <th colspan="11" class="text-center">
          Resumen por Cajero ::  Caja UPeU | Arqueo de caja 
        </th>
    </tr>
    </thead>
    <thead>
      <tr>
          <th colspan="9">
              <div class="col-md-12 text-center  lamb-title-table-report">
                  <p><em> Fecha Impresi&oacute;n: </em> <strong> <?php echo date('d/m/Y'); ?></strong></p>
              </div>
          </th>
          <th colspan="2">
              <div class="col-md-12 text-center lamb-title-table-report">
                  VOUCHER:<span>  {{$voucher->numero}}</span><br>
                  LOTE: <span>  {{$voucher->lote}}</span><br>
                  {{$voucher->fecha}}
              </div>
          </th>
      </tr>
      </thead>
  <thead>
    <tr class="text-center">
      <th>Nro</th>
      <th>Dep.</th>
      <th>Cod Comercio</th>
      <th>Fecha</th>
      <th>Glosa</th>
      <th>Carnet</th>
      <th>Importe</th>
      <th>Nombre</th>
      <th>Referencia / voucher/ N°Pedido</th>
      <th>Cuenta</th>
      <th>Hora</th>

    </tr>
  </thead>
  <tbody>
    <?php 
    // $i=1;
    $j=1;
    $total = 0;
    $id_deposito = '';
    $deposito='';

    ?>
     @foreach($data as $item)
     @if($id_deposito!=$item->id_deposito)
      <tr>
        <td>{{$j}}</td>
        <td>{{$item->deposito}}</td>
        <td>{{$item->cod_comercio}}</td>
        <td>{{$item->fecha}}</td>
        <td>{{$item->glosa}}</td>
        <td>{{$item->codigo}}</td>
        <td>{{$item->importe}}</td>
        <td>{{$item->nombre}}</td>
        <td>{{$item->nro_operacion}}</td>
        <td>{{$item->cuenta}}</td>
        <td><code> {{$item->hora}} </code> </td>
      </tr>
      <?php $total = $total + $item->imp_pdf; ?>

      <?php
        $j++;
       ?>
     @else
      @if($deposito!=$item->id_deposito.$item->deposito)
        <tr>
          <td></td>
          <td></td>
          <td></td>
          <td>{{$item->fecha}}</td>
          <td>{{$item->glosa}}</td>
          <td>{{$item->codigo}}</td>
          <td>{{$item->importe}}</td>
          <td>{{$item->nombre}}</td>
          <td>{{$item->nro_operacion}}</td>
          <td>{{$item->cuenta}}</td>
          <td>{{$item->hora}}</td>
        </tr>
      @else
        <tr>
          <td colspan="9"></td>
          <td >{{$item->cuenta}}</td>
          <td></td>
        </tr>
      @endif
     @endif
     <?php
      $id_deposito=$item->id_deposito;
      $deposito = $item->id_deposito.$item->deposito;
     ?>
    @endforeach
    

    <tr>
        <td colspan="6">Total</td>
        <td>{{$total}}</td>
        <td colspan="5"></td>
    </tr>
    <tr>
      <td colspan="3">Cajero:</td> 
      <td colspan="8" class="text-left"><strong>{{$cajero}}</strong></td>
    </tr>
    <tr>
      <td colspan="3">Valido</td>
      <td colspan="8"></td>
    </tr>
  </tbody>
</table>