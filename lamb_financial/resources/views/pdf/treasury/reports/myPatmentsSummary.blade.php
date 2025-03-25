@extends('layouts.pdf')
@section('content')

    <style type="text/css" media="screen">
        .lamb-head {
            background-color: #7f264a !important;
            color: white;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .font-size-10 {
            font-size: 10px !important;
        }
        .table-signature {
            width: 100%;
            font-size: 10px !important;
        }

        .table-signature td {
            text-align: center;
        }

        p {
        margin-top: 0;
        margin-bottom: 0rem;
    }
    </style>
        <div class="row lamb-head text-light text-uppercase font-weight-bold p-2 bg-theme">
            <div class="col-md-8 align-items-center justify-content-center text-center" > RESUMEN DE PAGOS </div>
            <div class="col-md-4 align-items-center justify-content-end text-right" style="font-size: 8px">
                    {{-- <p class="m-0">VOUCHER &nbsp;&nbsp; <span>  {{ $voucherDatas->numero }}</span>&nbsp;&nbsp;</p>
                    <p class="m-0">LOTE&nbsp;&nbsp;<span>  {{$voucherDatas->lote || '-' }}</span>&nbsp;&nbsp;</p>
                    <p class="m-0">{{ $voucherDatas->fecha }}&nbsp;&nbsp;</p> --}}

                    <p>VOUCHER &nbsp;&nbsp; <span> {{$numero}}</span>&nbsp;&nbsp;</p>
                    <p>LOTE&nbsp;&nbsp;  <span>  {{ $lote }}</span>&nbsp;&nbsp;</p>
                    <p>{{$fecha}}&nbsp;&nbsp;</p>
            </div>
        </div>
        
        <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped" >
                        {{-- <thead class="text-center lamb-head">
                                <tr >
                                <th colspan="6">
                                    <div style="font-size: 14px">
                                    <h5> Resumen de cuentas compras</h5>
                                    </div>
                                </th>
                                <th   colspan="1">
                                        <div style="font-size: 8px">
                                                <p class="m-0">VOUCHER &nbsp;&nbsp; <span>  {{ $voucherDatas->numero }}</span>&nbsp;&nbsp;</p>
                                                <p class="m-0">LOTE&nbsp;&nbsp;<span>  {{$voucherDatas->lote || '-' }}</span>&nbsp;&nbsp;</p>
                                                <p class="m-0">{{ $voucherDatas->fecha }}&nbsp;&nbsp;</p>
                                    </div>
                                </th>
                                </tr>
                        </thead> --}}
                  <thead>
                  <tr>
                    <th scope="col" class="text-center">Nro</th>
                    <th scope="col">N° Departamento</th>
                    <th scope="col">Departamento</th>
                    <th scope="col">N° Cuenta</th>
                    <th scope="col">Cuenta</th>
                    <th scope="col"class="text-right">Debito</th>
                    <th scope="col" class="text-right">Credito</th>
                  </tr>
                  </thead>
                  <tbody > 
                    <?php $i=1; ?>
                    @foreach($mainReport as $item)
                    <tr >
                      <th scope="row" class="text-center">{{$i++}}</th>
                      <td class="lamb-tag">{{$item->depto}}</td>
                      <td>{{$item->depto_n}}</td>
                      <td class="lamb-tag">{{$item->cuenta}}</td>
                      <td>{{$item->cuenta_n}}</td>
                      <td class="text-right">{{ number_format($item->asdebito, 2, '.', ',') }}</td>
                      <td class="text-right"> {{ number_format($item->credito, 2, '.', ',') }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                  <tfoot>
                    @foreach($total as $deta)
                    <tr >
                      <td colspan="5" class="text-right" > <strong> TOTAL </strong></td>
                      <td class="text-right"> {{ number_format($deta->asdebito, 2, '.', ',') }} </td>
                      <td class="text-right"> {{ number_format($deta->credito, 2, '.', ',') }}  </td>
                    </tr>
                    @endforeach
                  </tfoot>
                </table>

                <br>
                <br>
                <br>
                <table class="table-signature">
                    <tr>
                        <td>_______________________________________________<br>
                            Vo.Bo. Cajero
                        </td>
                        <td>_______________________________________________<br>
                            VB Tesorería
                        </td>
                    </tr>
                </table>
              </div>



@endsection

    