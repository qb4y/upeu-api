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
            <div class="col-md-8 align-items-center justify-content-center text-center" >
                RESUMEN DE VENTAS
            </div>
            <div class="col-md-4 align-items-center justify-content-end text-right" style="font-size: 8px">
                <p>VOUCHER &nbsp;&nbsp; <span> {{$numero}}</span>&nbsp;&nbsp;</p>
                <p>LOTE&nbsp;&nbsp;  <span>  {{ $lote }}</span>&nbsp;&nbsp;</p>
                <p>{{$fecha}}&nbsp;&nbsp;</p>
            </div>
        </div>
    <div  class="table-responsive">
    
        <table class="table table-striped table-sm table-bordered" id="tblData" >
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cuenta</th>
                    <th>Nivel</th>
                    <th>Débito</th>
                    <th>Crédito</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=1; ?>
                @foreach($resumen as $item)
                <tr  >
                    <td>{{$i++}}</td>
                    <td> <em class="lamb-tag">{{$item->cuenta}}</em> {{$item->cuenta_n}}</td>
                    <td><em class="lamb-tag">{{$item->depto}}</em> {{$item->depto_n}}</td>
                    <td class="text-right">{{ number_format($item->debito, 2, '.', ',') }}</td>
                    <td class="text-right">{{ number_format($item->credito, 2, '.', ',') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
              @foreach($total as $tol)
              <tr>
                <td colspan="3" class="text-right"> TOTAL</td>
                <td  class="text-right"> {{ number_format($tol->debito, 2, '.', ',') }}</td>
                <td  class="text-right"> {{ number_format($tol->credito, 2, '.', ',') }}</td>
              </tr>
              @endforeach
            </tfoot>
            </table>



            <div class="row lamb-head text-light text-uppercase font-weight-bold p-2 bg-theme">
                <div style="fond-size: 8px !important;" class="col-md-8 align-items-center justify-content-center text-center" >
                   COSTO DE VENTAS
                </div>
                {{-- <div class="col-md-4 align-items-center justify-content-end text-right" style="font-size: 8px">
                    <p>VOUCHER &nbsp;&nbsp; <span> {{$numero}}</span>&nbsp;&nbsp;</p>
                    <p>LOTE&nbsp;&nbsp;  <span>  {{ $lote }}</span>&nbsp;&nbsp;</p>
                    <p>{{$fecha}}&nbsp;&nbsp;</p>
                </div> --}}
            </div>
        <div  class="table-responsive">
        
            <table class="table table-striped table-sm table-bordered" id="tblData" >
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cuenta</th>
                        <th>Nivel</th>
                        <th>Débito</th>
                        <th>Crédito</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i=1; ?>
                    @foreach($resumenD as $iten)
                    <tr  >
                        <td>{{$i++}}</td>
                        <td> <em class="lamb-tag">{{$iten->cuenta}}</em> {{$iten->cuenta_n}}</td>
                        <td><em class="lamb-tag">{{$iten->depto}}</em> {{$iten->depto_n}}</td>
                        <td class="text-right">{{ number_format($iten->debito, 2, '.', ',') }}</td>
                        <td class="text-right">{{ number_format($iten->credito, 2, '.', ',') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                  @foreach($totalD as $tola)
                  <tr>
                    <td colspan="3" class="text-right"> TOTAL</td>
                    <td  class="text-right"> {{ number_format($tola->debito, 2, '.', ',') }}</td>
                    <td  class="text-right"> {{ number_format($tola->credito, 2, '.', ',') }}</td>
                  </tr>
                  @endforeach
                </tfoot>
                </table>





            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <table class="table-signature">
                <tr>
                  

                    @if ($id_depto == 5 )
                        <td>_____________________________<br>
                            Vo.Bo. Tesoreria Servicios<br>
                        </td>
                        <td>____________________<br>
                            Vo.Bo. Asistente Ventas
                        </td>
                        <td>___________________________<br>
                            Vo.Bo. Contador
                        </td>
                    @else
                        <td>_____________________________<br>
                            Vo.Bo. Finanzas Alumno, Postgrado <br>
                            y Cajero
                        </td>
                        <td>____________________<br>
                            Vo.Bo. Tesorería
                        </td>
                        <td>___________________________<br>
                            Vo.Bo. Director General <br>
                            Financiero Contable
                        </td>
                    @endif
                </tr>
            </table>
    </div>
@endsection
