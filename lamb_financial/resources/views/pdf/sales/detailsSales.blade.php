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
    .lamb-text {
        th { padding:  0.2rem !important; }

        td {
            padding: 0rem 0rem 0rem 0rem !important;

        }
    }

    p {
        margin-top: 0;
        margin-bottom: 0rem;
    }
    </style>
<div class="col-md-12 text-center head-info shadow font-size-10 lamb-title-table-report">
        <div class="row d-flex justify-content-between align-coxntent-center p-2 ">
          <p style="font-size: 8px; !important"><strong>DETALLE DE MOVIMIENTOS</strong></p>
          <div class="text-right">
                  <p style="font-size: 7px !important;" class="m-0">VOUCHER &nbsp;&nbsp; <span>  {{ $numero }}</span>&nbsp;&nbsp;</p>
                  <p style="font-size: 7px !important;" class="m-0">LOTE&nbsp;&nbsp;<span>  {{ $lote }}</span>&nbsp;&nbsp;</p>
                  <p style="font-size: 7px !important;" class="m-0">{{ $fecha }}&nbsp;&nbsp;</p>
                </div>
        </div>
      </div>
    <div  class="table-responsive">
    
            <table class="table table-striped table-sm table-bordered lamb-text"  id="tblData">
                    <thead style=" font-size: 8px !important">
                        <tr >
                            <th>#</th>
                            {{-- <th></th> --}}
                            <th>T.Doc</th>
                            <th>Serie-número</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Nivel</th>
                            <th>Cuenta</th>
                            <!-- <th>Nivel</th> -->
                            <th>Detalle</th>
                            <th>Débito</th>
                            <th>Crédito</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody style=" font-size: 7px !important"> 
                        <?php $i=1; ?>
                        @foreach($items as $item)
                        <tr >
                            <td scope="row" class="text-center">{{$i++}}</td>
                            <td>{{$item->id_comprobante}}</td>
                            <td class="lamb-tag"><code>{{$item->serie}}-{{$item->numero}}</code></td>
                            <td><code>{{$item->cliente}}</code></td>
                            <td class="text-right"> {{ number_format($item->total, 2, '.', ',') }}</td>
                            <td class="lamb-tag">{{$item->depto}}</td>
                            <td class="lamb-tag">{{$item->cuenta}}</td>
                            <td><code>{{$item->descripcion}}</code></td>
                            <td class="text-right">{{ number_format($item->debito, 2, '.', ',') }}</td>
                            <td class="text-right">{{ number_format($item->credito, 2, '.', ',') }} </td>
                            <td> 
                            {{$item->email}}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                     @foreach($total as $tol)
                        <tr >
                            <td colspan="8" class="text-right"> TOTAL</td>
                            <td  class="text-right"> {{ number_format($tol->debito, 2, '.', ',') }}</td>
                            <td  class="text-right"> {{ number_format($tol->credito, 2, '.', ',') }}</td>
                            <td></td>
                          </tr>
                    @endforeach
                    </tfoot>
                </table>
        
    </div>
@endsection
