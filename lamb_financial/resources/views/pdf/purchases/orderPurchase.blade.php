<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orden de Compra</title>
    <link rel="stylesheet" href="css/order-purchase.css" media="all" />
</head>
<body>
    <div id="cabecera">
        <table>
            <tbody>
                <tr>
                    <td colspan="1" id="logo">
                        <img src="img/logo-upeu2.png">
                    </td>
                    <td colspan="7">
                        <table id="table_contacto">
                            <tbody class="text-center">
                                <tr><td>Carretera Central Km. 19 Ñaña</td></tr>
                                <tr><td>Telf.: 618-6300 / Anexo: 6885</td></tr>
                                <tr><td>Cel.: 989059395</td></tr>
                                <tr><td>E-mail: logistica@upeu.edu.pe
                                </td></tr>
                            </tbody>
                        </table>
                    </td>
                    <td colspan="4">
                        <table id="table_ruc">
                            <tbody>
                                <tr id="title"><td>ORDEN DE COMPRA</td></tr>
                                <tr id="subtitle"><td>R.U.C. 20138122256</td></tr>
                                <tr id="num"><td>Nº {{$serie}}</td></tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    @foreach($cabecera as $c)
    <div id="descripcion">
        <table>
            <tbody>
                <tr>
                    <td colspan="2" class="text-dark">PROVEEDOR:</td>
                    <td colspan="5">{{$c->proveedor}}</td>
                    <td colspan="2" class="text-dark">RUC:</td>
                    <td colspan="3">{{$c->ruc}}</td>
                </tr>
                <tr>
                    <td colspan="2"  class="text-dark">DIRECCIÓN:</td>
                    <td colspan="10">{{$c->lugar_entrega}}</td>
                </tr>
                <tr>
                    <td colspan="2"  class="text-dark">TELÉFONO:</td>
                    <td colspan="5">{{$c->num_telefono}}</td>
                    <td colspan="2"  class="text-dark">FAX:</td>
                    <td colspan="3">-</td>
                </tr>
                <tr>
                    <td colspan="2"  class="text-dark">ATENCIÓN</td>
                    <td colspan="10">-</td>
                </tr>
                <tr>
                    <td colspan="2"  class="text-dark">FECHA DE PEDIDO:</td>
                    <td colspan="10">{{$c->fecha_pedido}}</td>
                </tr>
                <tr>
                    <td colspan="2"  class="text-dark">FECHA DE ENTREGA:</td>
                    <td colspan="10">{{$c->fecha_entrega}}</td>
                </tr>
                <tr>
                    <td colspan="2"  class="text-dark">ÁREA DEL SOLICITANTE: </td>
                    <td colspan="10" class="text-left">{{$c->area}}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div id="pago">
        <table>
            <tbody>
                <tr>
                    <td colspan="2" class="text-dark">CONDICIÓN</td>
                    <td colspan="2">
                    @if(strVal($c->es_credito) == 'N')
                        <input type="checkbox" checked/>
                    @else
                        <input type="checkbox"/>
                    @endif    
                    CONTADO</td>
                    <td colspan="1">&#8212></td>
                    <td colspan="2">
                    @if(strVal($c->medio_pago) == 'EFECTIVO')
                        <input type="checkbox" checked/>
                    @else
                        <input type="checkbox"/>
                    @endif      
                    EFECTIVO</td>
                    <td colspan="2">
                    @if(strVal($c->medio_pago) == 'CHEQUE')
                        <input type="checkbox" checked/>
                    @else
                        <input type="checkbox"/>
                    @endif     
                    CHEQUE</td>
                    <td colspan="2">
                    @if(strVal($c->medio_pago) != 'CHEQUE' && strVal($c->medio_pago) != 'EFECTIVO')
                        <input type="checkbox" checked/> OTRO: {{$c->medio_pago}}
                    @else
                        <input type="checkbox"/> OTRO: -
                    @endif 
                    </td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td colspan="2">
                    @if(strVal($c->es_credito) == 'S')
                        <input type="checkbox" checked/>
                    @else
                        <input type="checkbox"/>
                    @endif    
                    CRÉDITO</td>
                    <td colspan="1">&#8212></td>
                    <td colspan="2"> 
                    @if(strVal($c->es_credito) == 'S')
                        <input type="checkbox" checked/> DIAS {{$c->dias_credito}}
                    @else
                        <input type="checkbox"/> DIAS
                    @endif   
                    </td>
                    <td colspan="2">
                    @if(strVal($c->es_credito) == 'S')
                        <input type="checkbox" checked/> CUOTAS {{$c->cuotas}}
                    @else
                        <input type="checkbox"/> CUOTAS
                    @endif   
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    @endforeach
    <div id="detalle">
        <table>
            <thead>
                <tr>
                    <td colspan="1">ITEM</td>
                    <td colspan="1" class="text-center">CANT.</td>
                    <td colspan="1">CODIG.</td>
                    <td colspan="5">DESCRIPCIÓN</td>
                    <td colspan="1">UNID.</td>
                    <td colspan="1">P.U</td>
                    <td colspan="2">TOTAL</td>
                </tr>
            </thead>
            <tbody>
                {{$i=0}}
                @foreach($detalle as $item)
                <tr>
                    <td colspan="1" class="text-center">{{++$i}}</td>
                    <td colspan="1" class="text-center">{{$item->cantidad}}</td>
                    <td colspan="1" class="text-center"></td>
                    <td colspan="5" class="text-left">{{$item->detalle}}</td>
                    <td colspan="1" class="text-center"></td>
                    <td colspan="1" class="text-right">S./ {{number_format($item->precio,2)}}</td>
                    <td colspan="2" class="text-right">S./ {{number_format($item->total,2)}}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                @foreach($totales as $item)
                <tr>
                    <td colspan="2" class="text-dark">INCLUYE IGV :</td>
                    <td colspan="1">
                    @if(strVal($item->con_igv) == 'S')
                        <input type="checkbox" checked/>
                    @else
                        <input type="checkbox"/>
                    @endif       
                    SI</td>
                    <td colspan="1">
                    @if(strVal($c->con_igv) == 'N')
                        <input type="checkbox" checked/>
                    @else
                        <input type="checkbox"/>
                    @endif       
                    NO</td>
                    <td colspan="4"></td>
                    <td colspan="2" class="dest text-dark" >SUB TOTAL</td>
                    <td colspan="2" class="dest text-right">S/. {{number_format($item->subtotal,2)}}</td>
                </tr>
                <tr>
                    <td colspan="8"></td>
                    <td colspan="2" class="dest text-dark">IGV</td>
                    <td colspan="2" class="dest text-right">S/. {{number_format($item->igv,2)}}</td>
                </tr>
                <tr>
                    <td colspan="8"></td>
                    <td colspan="2" class="dest text-dark">TOTAL</td>
                    <td colspan="2" class="dest text-right">S/. {{number_format($item->total,2)}}</td>
                </tr>
                @endforeach
            </tfoot>
        </table>
    </div>
    <div id="observaciones">
        <label class="text-dark">OBSERVACIONES:</label>
        <br>
        @foreach($cabecera as $c)
        &#8212> Lugar de entrega: <strong>{{$c->lugar_entrega}}</strong>
        @endforeach
        <br>
        &#8212> Emitir la factura a nombre de: <strong> Universidad Peruana Unión
        </strong>

        <table id="table_firmas">
            <tbody>
                <tr>
                    <td>
                        <div class="caja"></div>
                        <label>Gerencia Administrativa</label>               
                    </td>
                    <td>
                        <div class="caja"></div>
                        <label>Jefe de Adquisiciones </label>
                    </td>
                    <td>
                        <div class="caja"></div>
                        <label>Responsable de Compras</label>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <label class="text-dark">NOTA:</label>
        Esta orden debe adjuntarse a la guia y/o factura.<br>
        El envio debe coincidir con la orden de compra en la cantidad y soles, no existe responsabilidad por exceso.<br>
        Esta orden de compra en caso de no ser atendida en la fecha establecida quedará ANULADA.
    </div>
</body>
</html>

