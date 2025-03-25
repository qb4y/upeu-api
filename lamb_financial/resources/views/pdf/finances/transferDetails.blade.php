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

      <div class="row lamb-head text-light text-uppercase font-weight-bold p-2 bg-theme">
            <div class="col-md-8 align-items-center justify-content-center text-center" >
                DETALLE DE TRANSFERENCIAS
            </div>
            <div class="col-md-4 align-items-center justify-content-end text-right" style="font-size: 8px">
                <p>VOUCHER &nbsp;&nbsp; <span> {{$numero}}</span>&nbsp;&nbsp;</p>
                <p>LOTE&nbsp;&nbsp;  <span>  {{ $lote }}</span>&nbsp;&nbsp;</p>
                <p>{{$fecha}}&nbsp;&nbsp;</p>
            </div>
        </div>
    

        <div  class="table-responsive">
       
        <table class="table table-striped table-sm table-bordered lamb-text"  id="tblData">
                    <thead style=" font-size: 8px !important">
                    <tr>
                        <th>#</th>
                        <th>Serie-número y Alumno</th>
                        <th>Fecha</th>
                        <th>Glosa</th>
                        <th>Importe</th>
                        <th>Cuenta</th>
                        <th>Cta Cte</th>
                        <th>Nivel</th>
                        <th>Detalle</th>
                        <th>Débito</th>
                        <th>Crédito</th>  
                    </tr>
                </thead>
                <tbody style=" font-size: 7px !important">
                <?php $i=1; ?>
                    @foreach($items as $item)
                    <tr  >
                        <td>{{$i++}}</td>
                        <td>{{$item->serie}}-{{$item->numero}} {{$item->alumno}}</td>
                        <td>{{$item->fecha}}</td>
                        <td>{{$item->glosa}}</td>
                        <td>{{$item->importe}}</td>
                        <td>{{$item->cuenta}}</td>
                        <td>{{$item->cuenta_cte}}</td>
                        <td>{{$item->depto}}</td>
                        <td>{{$item->descripcion}}</td>
                        <td>{{$item->debito}}</td>
                        <td>{{$item->credito}}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot style=" font-size: 7px !important">
                  @foreach($total as $tol)
                  <tr>
                    <td colspan="9" class="text-right"> TOTAL</td>
                    <td  class="text-right"> {{   $tol->debito  }}</td>
                    <td  class="text-right"> {{   $tol->credito  }}</td>
                  </tr>
                  @endforeach
                </tfoot>
                </table>
    
    
            <div  class="table-responsive">
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
        </div>

@endsection
