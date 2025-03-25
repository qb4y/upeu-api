@extends('layouts.pdf')
@section('content')
<style type="text/css" media="screen">
.text {
  font-size: 8px !important;  
}
.head {
 font-size: 11px !important;       
}
.headTo {
 font-size: 9px !important;
 text-align: center !important;       
}
.foot {
 font-size: 13px !important;
 font-weight: bold !important;      
}
.sum {
 font-size: 10px !important;
 font-weight: bold !important;      
}
.text-right {
text-align: right !important;
}
.text-center {
text-align: center !important;
}
.headOne {
 font-size: 12px !important; 
 text-align: center !important;
 font-weight: bold !important;      
}
.celda {
  padding-top: 0px !important;
  padding-bottom: 0px !important;   
}
.colorcelda1 {
        background: white !important;
        color: black !important;
}
.colorcelda2 {
        background: #ecfbf2 !important;
        color: black !important;
}
</style>
<div class="headOne">ESTADO DE CUENTA ALUMNO</div>
<div  class="table-responsive">
<table>
        <tbody>
                <tr>
                        <td>
                        <img alt="perfil"
                                             src="{{$perfil['foto']}}"
                                             class="photo " itemprop="logo" width="50">
                        </td>
                        <td>
                                <div class="head">
                                &nbsp;&nbsp;&nbsp;Nombre:  {{$perfil['paterno']}} {{$perfil['materno']}} {{$perfil['nombre']}} <br>
                                &nbsp;&nbsp;&nbsp;Codigo:  {{$perfil['codigo']}}<br>
                                &nbsp;&nbsp;&nbsp;Email:  {{$perfil['correo']}}<br>
                                @if(isset($perfil['escuela']))
                                &nbsp;&nbsp;&nbsp;E.A.P:  {{$perfil['escuela']}}
                                @else
                                &nbsp;&nbsp;&nbsp;E.A.P: Ninguno
                                @endif
                                </div>
                        </td>

                </tr>
        </tbody>
</table>
</div>

        <table class="table table-striped table-sm table-bordered">
        @foreach($data as $items)
                <thead>
                <tr class="headTo">
                @if ($items['tipo'] == 'mov_academico')
                 <th colspan="9" >MOVIMIENTO ACADÉMICO</th>
                @elseif ($items['tipo'] == 'mov_ingles')
                <th colspan="9">MOVIMIENTO INGLES</th>
                @elseif ($items['tipo'] == 'mov_musica')
                <th colspan="9">MOVIMIENTO MÚSICA</th>
                @elseif ($items['tipo'] == 'mov_cepre')
                <th colspan="9">MOVIMIENTO CEPRE</th>
                @endif
                </tr>
                <tr style="background: rgb(216, 216, 216)" class="text">
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Voucher</th>
                        <th>Lote</th>
                        <th>Documento</th>
                        <th>Mov</th>
                        <th>Glosa</th>
                        <th>Débito</th>
                        <th>Crédito</th>
                      </tr>
                </thead>
                <tbody >
                <?php $i=1;
                $credito = 0;
                $debito = 0;?>
                @foreach($items['data'] as $item)
                  @if($item->contador%2 == 0) 
                  <tr class="text celda colorcelda1">
                    <td>{{$i++}}</td>
                    <td>{{$item->fecha}}</td>
                    <td>{{$item->voucher}}</td>
                    <td>{{$item->lote}}</td>
                    <td>{{$item->documento}}</td>
                    <td>{{$item->mov}}</td>
                    <td>{{$item->glosa}}</td>
                    <td class="text-right">{{number_format($item->credito, 2)}}</td>
                    <td class="text-right">{{number_format($item->debito, 2)}}</td>
                  </tr>
                  @else
                  <tr class="text celda colorcelda2">
                    <td>{{$i++}}</td>
                    <td>{{$item->fecha}}</td>
                    <td>{{$item->voucher}}</td>
                    <td>{{$item->lote}}</td>
                    <td>{{$item->documento}}</td>
                    <td>{{$item->mov}}</td>
                    <td>{{$item->glosa}}</td>
                    <td class="text-right">{{number_format($item->credito, 2)}}</td>
                    <td class="text-right">{{number_format($item->debito, 2)}}</td>
                  </tr>
                  @endif
                  <?php
                  $credito+=$item->credito;
                  $debito+=$item->debito;
                  ?>
                @endforeach
                  <tr class="sum">
                    <td class="text-right" colspan="7">Sumas:</td>
                    <td class="text-right">{{number_format($credito, 2) }}</td>
                    <td class="text-right">{{number_format($debito, 2) }}</td>
                  </tr>
                </tbody>
                @endforeach
                @foreach($saldo_final as $saldo)
                <tfoot >
                <tr class="foot">
                <td class="text-right " colspan="7">Total:</td>
                <td class="text-right" style="background: rgb(216, 216, 216)">{{number_format($saldo->debito, 2)}}</td>
                <td class="text-right" style="background: rgb(216, 216, 216)">{{number_format($saldo->credito, 2)}}</td>
                </tr>
                </tfoot>
                @endforeach
                </table>
        @if (count($saldo_filiales) > 0)
        <div class="row">
                <div class="col-md-4"></div>
                        <div  class="col-md-4" style="width: 50%; margin: 0 auto">
                                <table class="table table-striped table-sm table-bordered">
                                        <thead>
                                                <tr class="headTo">
                                                        <th colspan="5">SALDOS EN OTRA FILIAL</th>
                                                </tr>
                                                <tr style="background: rgb(216, 216, 216)" class="text">
                                                        <th>#</th>
                                                        <th>Sede</th>
                                                        <th>Crédito</th>
                                                        <th>Débito</th>
                                                        <th>Total</th>
                                                </tr>
                                        </thead>
                                        <tbody>
                                <?php $in=1;?>
                                @foreach($saldo_filiales as $sal)
                                                <tr class="text">
                                                        <td>{{$in++}}</td>
                                                        <td>{{$sal->sede}}</td>
                                                        <td class="text-right">{{number_format($sal->credito, 2) }}</td>
                                                        <td class="text-right">{{number_format($sal->debito, 2) }}</td>
                                                        <td class="text-right">{{number_format($sal->total, 2) }}</td>
                                                </tr>
                                @endforeach
                                        </tbody>
                                </table>
                        </div>
                <div></div>
        </div>
        @endif
@endsection