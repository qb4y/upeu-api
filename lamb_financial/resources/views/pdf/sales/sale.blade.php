@extends('layouts.pdf')
@section('content')

<h4 align="center">REPORTE DE VENTA</h4>

    <div  class="table-responsive">
    <table class="table table-striped table-sm table-bordered" id="tblData">
        <thead>
            <tr>
                <th class="text-center" ƒ rowspan="2">#</th>
                <th class="text-center" colspan="9" >REPORTE DE CREDITO AL PERSONAL.</th>
            </tr>
            <tr>
                <!-- <th>#</th> -->
                <th>Voucher</th>
                <th>Fecha</th>
                <th>N° Tranferencia</th>
                <th>Cliente</th>
  
                <th>DNI</th>
                <th>Glosa</th>
                <th>Importe</th>
                <th>Comprobante</th>
                <!-- <th>Nivel ctr</th>
                <th>Desc. Nivel</th> -->
                <th>Usuario</th>

            </tr>
        </thead>
        <tbody >
        <?php
        $i=1;
        $total = 0;
        ?>
        @foreach($data as $k)
            <tr>
                <td>{{$i}}</td>
                <td>{{$k->id_voucher}}</td>
                <td>{{$k->fecha}}</td>
                <td>{{$k->numero}}</td>
                <td>{{$k->cliente}}</td>
                <td>{{$k->dni}}</td>
                <td>{{$k->glosa}}</td>
                <td  class="text-right">{{$k->importe}}</td>
                <td>{{$k->comprobante}}</td>
                <td>{{$k->cajero}}</td>
                <!-- <td class="text-center"><button style="border-radius: 3rem" class="btn btn-primary btn-sm" (click)="navigate(); localEstore(k)"><i class="fa fa-eye"></i></button></td> -->
            </tr>
            <?php
        $i++;
        ?>
            @endforeach

        </tbody>
        </table>
@endsection
