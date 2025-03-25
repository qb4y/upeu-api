@extends('layouts.mc')
@section('content')

<div class="col-md-12">
    <div class="card">
        <div class="card-body">
            <h5 class="login-head"><i class="fa fa-lg fa fa-user"></i> Verifique sus datos</h5>
            <hr/>
            <div style="font-size: 12px;">En convenio con VisaNet podemos brindar este servicio de Pagos
                en linea de forma segura y confiable de la siguiente forma:
                <ol>
                    <li>Verifique que sus datos personales sean los correctos.</li>
                    <li>Verifique el importe a pagar en "soles", luego click en "Acepto los términos y condiciones de Pago".</li>
                    <li>Luego click en "Pagar con VISA" Cuando ya haya cargado el formulario de VisaNet, ingresará los datos de su tarjeta de crédito o débito
                        afiliado a Verify by Visa, para luego mostrar en pantalla un mensaje de confirmación de
                        transacción exitosa.
                    </li>
                    <li><b>Nota:</b> Es importante que no cierre la ventana del navegador hasta que el sistema le haya mostrado
                        o indicado que su transferencia fue exitosa.
                    </li>
                </ol>
                y listo, que facil es realizar sus Pagos.<br><strong>¡Juntos logramos tus sueños!.</strong>

            </div>
            
            <div>
                
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        @if($prod=='N')
                        <p class="text-danger"><b>Estas en ambiente de pruebas</b></p>
                        @endif
                        <table class="table table-bordered table-striped">
                        <tbody>
                        <tr>
                            <th colspan="2" class="text-center"><b>Datos Personales</b></th>
                        </tr>
                        <tr>
                            <th>DNI/CE:</th>
                            <td>{{$datosrecibidos['numdoc']}}</td>
                        </tr>
                        <tr>
                            <th>Nombres:</th>
                            <td>{{$datosrecibidos['nombres']}}</td>
                        </tr>
                        <tr>
                            <th>Apellidos:</th>
                            <td>{{$datosrecibidos['apellidos']}}</td>
                        </tr>
                        <tr>
                            <th>Código:</th>
                            <td>{{$datosrecibidos['codigo']}}</td>
                        </tr>
                        <tr>
                            <th>EAP/Nivel:</th>
                            <td>{{$datosrecibidos['eap']}}</td>
                        </tr>

                        <tr>
                            <th>Concepto:</th>
                            <td>{{$datosrecibidos['concepto']}}</td>
                        </tr>
                        <tr>
                            <th>Importe:</th>
                            <td class="text-danger">
                                <h4><b>
                                        S/{{number_format($datosrecibidos['importe'], 2, '.', ',')}} 
                                </b></h4>
                                        <?php
                                        if($datosrecibidos['moneda']=='1'){
                                            echo 'USD/ '.number_format($datosrecibidos['importe_dol'], 2, '.', ',');
                                        }
                                        ?>
                                
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2" class="text-center"><b>Datos para Tarjeta</b></th>
                        </tr>
                        <tr>
                            <th>Nombres:</th>
                            <td>{{$datosrecibidos['nombres_visa']}}</td>
                        </tr>
                        <tr>
                            <th>Apellidos:</th>
                            <td>{{$datosrecibidos['apellidos_visa']}}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{$datosrecibidos['email_visa']}}</td>
                        </tr>

                        </tbody>
                        </table>


                        <div class="custom-control custom-checkbox mr-sm-2">
                            <input type="checkbox" class="custom-control-input" id="terminos">
                            <label class="custom-control-label" for="terminos">Acepto los</label>
                            <a href="javascript:fnterminos();">términos y condiciones </a> de Pago
                        </div>

                        <div style="width: 100%;" class="text-center" id="pagovisa">
                        <?php
                        $url='https://server.punto-web.com/gateway/PagoWebDn.asp';
                        if($prod=='N'){
                            $url='https://server.punto-web.com/gateway/PagoWebDn.asp';
                        }
                        
                        ?>
                        <form name="frm" action="<?php echo $url ?>" method='post'>
                            <input type="hidden" name="I1" value="<?php echo $datosrecibidos['codcomercio']?>">
                            <input type="hidden" name="I2" value="<?php echo $datosrecibidos['numorden']?>">
                            <input type="hidden" name="I3" value="<?php echo $datosrecibidos['importe']?>">
                            <input type="hidden" name="I4" value="<?php echo $datosrecibidos['moneda']?>">
                            <input type="hidden" name="I5" value="<?php echo $datosrecibidos['fechamc']?>">
                            <input type="hidden" name="I6" value="<?php echo $datosrecibidos['horamc']?>">
                            <input type="hidden" name="I7" value="<?php echo $datosrecibidos['aleatorio']?>">
                            <input type="hidden" name="I8" value="<?php echo $datosrecibidos['id_personal']?>">
                            <input type="hidden" name="I9" value="<?php echo $datosrecibidos['pais']?>">
                            <input type="hidden" name="I10" value="<?php echo $strHash ?>">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-sign-in fa-lg fa-fw"></i>Pagar con MC</button>
                        </form>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
    
    <div class="modal fade" tabindex="-1" id="modalterminos" role="dialog" aria-hidden="true" aria-labelledby="modalcontrollabel">
            <div class="modal-dialog modal-lg">
              <div class="modal-content"></div>
            </div>
    </div>

</div>


@endsection