@extends('layouts.pdf')
@section('content')

    <table align="center" class="table table-sm table-striped table-bordered">
        
    <thead class="text-center">
            <tr>
            <th colspan="10">
                <div style="font-size: 14px">
                <h5> Resumen de cuentas compras</h5>
                </div>
            </th>
            <th   colspan="3">
                <div style="font-size: 8px">
                Documento
                <p>{{ $n_vou }}</p>
                lote
                <p>{{ $lote}}</p>
                <p><em>{{ $v_fecha}}</em></p>
                </div>
            </th>
            </tr>
    </thead>
    <thead style="font-size: 10px">
            <tr class="font-weight-bold">
                <th>#</th>
                <th class="text-center text-primary" >Cuenta</th>
                <th class="text-center text-primary" >Nombre</th>
                <th class="text-center text-primary" >Depto.</th>
                <th class="text-center text-primary" >Nombre Depto.</th>
                <th class="text-center text-primary" >Base 1</th>
                <th class="text-center text-primary" >IGV 1 </th>
                <th class="text-center text-primary" >Base 2</th>
                <th class="text-center text-primary" >IGV 2</th>
                <th class="text-center text-primary" >Base 3</th>
                <th class="text-center text-primary" >IGV 3</th>
                <th class="text-center text-primary" >Debito</th>
                <th class="text-center text-primary" >Credito</th>
            </tr>
        </thead>
        <tbody style="font-size: 10px">
        <?php
        $i=0;
        ?>
        @foreach($detalle as $deta)
        <tr>
            <td class="text-center">{{$i + 1}}</td>
            <td class="text-center"> {{$deta->cuenta}}</td>
            <td >  {{$deta->cuenta_n}} </td>
            <td class="text-center"> {{$deta->depto}}</td>
            <td > {{$deta->depto_n}} </td>
            <td class="text-center"> {{$deta->base1}} </td>
            <td class="text-center"> {{$deta->igv1}} </td>
            <td class="text-center"> {{$deta->base2}} </td>
            <td class="text-center"> {{$deta->igv2}} </td>
            <td class="text-center"> {{$deta->base3}} </td>
            <td class="text-center"> {{$deta->igv3}} </td>
            <td class="text-right"> {{$deta->debito}} </td>
            <td class="text-right"> {{$deta->credito}} </td>
        </tr>
        <?php
        $i++;
        ?>
        @endforeach
        </tbody>
        <tfoot>
        @foreach($total as $deta)
            <tr *ngFor="let t of dTotal">
                <td colspan="11" class="text-right"> Total: </td>
                <td class="text-right"><strong><em> {{ number_format($deta->debito, 2, '.', ',') }}  </em></strong> </td>
                <td class="text-right"> <strong><em> {{ number_format($deta->credito, 2, '.', ',') }} </em> </strong></td>
            </tr>
        @endforeach
        </tfoot>
    </table>


    <table align="center" class="table table-sm table-striped table-bordered lamb-text">
        
            <thead class="text-center">
                    <tr>
                            <th colspan="13">
                                    <div style="font-size: 14px">
                                      <h5> Resumen de variación de existencias</h5>
                                    </div>
                                  </th>
                    
                    </tr>
            </thead>
            <thead style="font-size: 10px">
                    <tr class="font-weight-bold">
                        <th>#</th>
                        <th class="text-center text-primary" >Cuenta</th>
                        <th class="text-center text-primary" >Nombre</th>
                        <th class="text-center text-primary" >Depto.</th>
                        <th class="text-center text-primary" >Nombre Depto.</th>
                        <th class="text-center text-primary" >Base 1</th>
                        <th class="text-center text-primary" >IGV 1 </th>
                        <th class="text-center text-primary" >Base 2</th>
                        <th class="text-center text-primary" >IGV 2</th>
                        <th class="text-center text-primary" >Base 3</th>
                        <th class="text-center text-primary" >IGV 3</th>
                        <th class="text-center text-primary" >Debito</th>
                        <th class="text-center text-primary" >Credito</th>
                    </tr>
                </thead>
                <tbody style="font-size: 10px">
                <?php
                $i=0;
                ?>
                @foreach($detalleD as $deta)
                <tr>
                    <td class="text-center">{{$i + 1}}</td>
                    <td class="text-center"> {{$deta->cuenta}}</td>
                    <td >  {{$deta->cuenta_n}} </td>
                    <td class="text-center"> {{$deta->depto}}</td>
                    <td > {{$deta->depto_n}} </td>
                    <td class="text-center"> {{$deta->base1}} </td>
                    <td class="text-center"> {{$deta->igv1}} </td>
                    <td class="text-center"> {{$deta->base2}} </td>
                    <td class="text-center"> {{$deta->igv2}} </td>
                    <td class="text-center"> {{$deta->base3}} </td>
                    <td class="text-center"> {{$deta->igv3}} </td>
                    <td class="text-right"> {{$deta->debito}} </td>
                    <td class="text-right"> {{$deta->credito}} </td>
                </tr>
                <?php
                $i++;
                ?>
                @endforeach
                </tbody>
                <tfoot>
                @foreach($totalD as $deta)
                    <tr *ngFor="let t of dTotal">
                        <td colspan="11" class="text-right"> Total: </td>
                        <td class="text-right"><strong><em> {{ number_format($deta->debito, 2, '.', ',') }}  </em></strong> </td>
                        <td class="text-right"> <strong><em> {{ number_format($deta->credito, 2, '.', ',') }} </em> </strong></td>
                    </tr>
                @endforeach
                </tfoot>
            </table>

            <br>
            <br>
            <br>
            <table class="table-signature">
                <tr>
                    <td>_____________________________<br>
                        Vo.Bo. Provisiones
                    </td>
                    <td>____________________<br>
                        Vo.Bo. Tesorería
                    </td>
                    <td>___________________________<br>
                        Vo.Bo. Director General <br>
                         Financiero Contable
                    </td>
                </tr>
            </table>
    <style>
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
    .lamb-text {
        /* font-size: 0.75rem; */
        th { padding:  0.3rem !important; }
        td {
            padding: 0.05rem !important;
        }
    }
    </style>
@endsection
