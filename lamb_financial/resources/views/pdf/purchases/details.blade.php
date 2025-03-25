@extends('layouts.pdf')
@section('content')

<table class="table table-sm table-striped lamb-table-compras lamb-text">
        <thead class="text-center">
            <tr>
              <th colspan="8">
                <div style="font-size: 14px">
                  <h5> Detalle del Registro de compras</h5>
                </div>
              </th>
              <th   colspan="3">
                <div style="font-size: 8px">
                Documento
                <p>{{ $n_vou }}</p>
                lote
                <p>{{ $lote }}</p>
                <p><em>{{ $v_fecha}}</em></p>
                </div>
            </th>
            </tr>
            <tr>
              <th colspan="11">
                <div>
                  <span>Mes: {{ $mes}} - Día: {{$fecha}}</span>
                </div>
              </th>
            </tr>
          </thead>
      <thead  style="font-size: 10px">
            <tr>
                <th>#</th>
                <th>Detalle</th>
                <th>B.I. 1</th>
                <th>IGV 1</th>
                <th>B.I.2</th>
                <th>IGV 2</th>
                <th>B.I. 3 </th>
                <th>IGV 3</th>
                <th>B.I. 4</th>
                <th>Otro</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody style="font-size: 10px">
            <?php
            $header='';
  
            ?>
            @foreach($data as $deta2)
                @if($header!=$deta2->cuenta.$deta2->depto.$deta2->cuenta_cte)
                <tr>
                    <th colspan="9">{{ $deta2->depto}}: {{  $deta2->depto_n }} - {{  $deta2->cuenta}}: {{  $deta2->cuenta_n}} - Cte: {{  $deta2->cuenta_cte}}</th>
                  <td colspan="2"></td>
                </tr>
                <?php
                $j=0;
                ?>
                 @endif
                
                  <tr>
                    <td class="text-center">{{$j + 1}}</td>
                      <td class="lamb-tg">
                          Doc: {{ $deta2->numero}} RUC: {{ $deta2->ruc}} &nbsp;
                          : {{ $deta2->depto_n}}/{{ $deta2->detalle }}
                        </td>
                        <td >{{ $deta2->base1 }}</td>
                        <td class="lamb-tg">{{ $deta2->igv1 }}</td>
                        <td >{{ $deta2->base2 }}</td>
                        <td class="text-center">{{$deta2->igv2 }}</td>
                        <td class="text-center">{{ $deta2->base3 }}</td>
                        <td class="text-center">{{ $deta2->igv3 }}</td>
                        <td class="text-center">{{ $deta2->base4 }}</td>
                        <td class="text-center">{{ $deta2->otros }}</td>
                        <td class="text-right">{{ $deta2->total}}</td>
                    </tr>
                    <?php
                    $j++;
                    ?>
               
                <?php
                $header=$deta2->cuenta.$deta2->depto.$deta2->cuenta_cte;
                ?>
            @endforeach
            </tbody>
    </table>
    <style>
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