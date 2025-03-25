@extends('layouts.visapayment')
@section('content')
<div class="col-md-6 offset-md-3">

    <div class="card" id="PrintArea">
        <div class="card-body">
            <h4 class="login-head text-center">Gracias por realizar tus pagos online</h4>
            <hr/>
            <div class="span5" style="font-size: 12px;">
                <div  style="margin: 0px 10px 0px 0; display:block;margin:0 auto;  clear: both; margin-top: 5px; width: 100%; text-align: center;"><img src="{{asset('img/logos.png')}}" class="img-rounded" /></div>
                <p>Nuestra frase motora "Juntos en un mismo esfuerzo" es justamente a lo que apelamos. Al realizar tus pagos  estas formando parte del desarrollo de la Universidad Peruana Unión.</p>
                <p>Por eso estamos agradecidos por cumplir con tus pagos.</p>
                <p>Nos encanta tenerlo como nuestro alumno, le enviamos un cariñoso saludo, esperando que el Señor le bendiga abundantemente.</p>
                 
                <h5>Universidad Peruana Unión y Gerencia Financiera</h5>
                <p><strong>finanzasalumnos@upeu.edu.pe</strong></p>
            </div>
            <hr/>
            <div>
                
                <div class="row">
                    <div class="col-md-12 ">
                        <h5>Detalle del comprobante de transacción</h5>
                        <table class="table table-bordered table-striped">

                            <tbody>

                            <tr>
                                <th class="span3">Respuesta:</th>
                                <td>
                                    <?php
                                    if ($respuesta['respuesta'] == '1') { ?>
                                        <span style="color: #619646;">(<?php echo $respuesta['dsc_cod_accion']?>)</span>
                                    <?php
                                    } else { ?>
                                        <span style="color: #f00;">(<?php echo $respuesta['dsc_cod_accion'] ?>)</span>
                                    <?php
                                    } ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="span3">Número de Operacion:</th>
                                <td><?php echo $respuesta['numorden'] ?></td>
                            </tr>
                            <tr>
                                <th class="span3">Nombres y Apellidos:</th>
                                <td><?php echo $datosrecibidos['nombres'] ?> <?php echo $datosrecibidos['apellidos'] ?></td>
                            </tr>

                            <tr>
                                <th class="span3">Número de tarjeta:</th>
                                <td><?php echo $respuesta['pan'] ?></td>
                            </tr>
                            <tr>
                                <th class="span3">Fecha y hora:</th>
                                <td><?php echo $respuesta['fechahora'] ?></td>
                            </tr>
                            <tr>
                                <th class="span3">Moneda:</th>
                                <td>Soles</td>
                            </tr>
                            <tr>
                                <th class="span3">Monto Pagado:</th>
                                <td><?php echo number_format($respuesta['importe'], 2, '.', ',')  ?></td>
                            </tr>
                            
                            <tr>
                                <th class="span3">Concepto:</th>
                                <td><?php echo $respuesta['operacion'] ?></td>
                            </tr>
                            @if(substr($respuesta['id_respuesta'], 0, 1) =='B' or substr($respuesta['id_respuesta'], 0, 1) =='F')
                            <tr>
                                <th class="span3">Documento:</th>
                                <td>
                                     <a href="{{route('visapayment/apidoc')}}" target="_blank"> <?php echo $respuesta['id_respuesta'] ?></a>    
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <th class="span3">Origen:</th>
                                <td><?php echo $respuesta['id_origen'] ?></td>
                            </tr>
                            </tbody>
                        </table>
                        
                    </div>
                </div>

            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-12 text-center">
            <a href="#" onclick="javascript:imprimir();">Imprimir</a> una cópia
        </div>
    </div>
    
    
</div>


@endsection

