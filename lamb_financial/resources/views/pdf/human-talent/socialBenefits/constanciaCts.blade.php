 <?php

class NumerosEnLetras

{

    private static $UNIDADES = [

        '',

        'un ',

        'dos ',

        'tres ',

        'cuatro ',

        'cinco ',

        'seis ',

        'siete ',

        'ocho ',

        'nueve ',

        'diez ',

        'once ',

        'doce ',

        'trece ',

        'catorce ',

        'quince ',

        'dieciseis ',

        'diecisiete ',

        'dieciocho ',

        'diecinueve ',

        'veinte '

    ];



    private static $DECENAS = [

        'venti',

        'treinta ',

        'cuarenta ',

        'cincuenta ',

        'sesenta ',

        'setenta ',

        'ochenta ',

        'noventa ',

        'cien '

    ];



    private static $CENTENAS = [

        'ciento ',

        'doscientos ',

        'trescientos ',

        'cuatrocientos ',

        'quinientos ',

        'seiscientos ',

        'setecientos ',

        'ochocientos ',

        'novecientos '

    ];



    public static function convertir($number, $currency = '', $format = false, $decimals = '')

    {

        $base_number = $number;

        $converted = '';

        $decimales = '';



        if (($base_number < 0) || ($base_number > 999999999)) {

            return 'No es posible convertir el numero en letras';

        }



        $div_decimales = explode('.',$base_number);



        if(count($div_decimales) > 1){

            $base_number = $div_decimales[0];

            $decNumberStr = (string) $div_decimales[1];

            if(strlen($decNumberStr) == 2){

                $decNumberStrFill = str_pad($decNumberStr, 9, '0', STR_PAD_LEFT);

                $decCientos = substr($decNumberStrFill, 6);

                $decimales = self::convertGroup($decCientos);

            }

        }



        $numberStr = (string) $base_number;

        $numberStrFill = str_pad($numberStr, 9, '0', STR_PAD_LEFT);

        $millones = substr($numberStrFill, 0, 3);

        $miles = substr($numberStrFill, 3, 3);

        $cientos = substr($numberStrFill, 6);



        if (intval($millones) > 0) {

            if ($millones == '001') {

                $converted .= 'un millon ';

            } else if (intval($millones) > 0) {

                $converted .= sprintf('%smillones ', self::convertGroup($millones));

            }

        }



        if (intval($miles) > 0) {

            if ($miles == '001') {

                $converted .= 'mil ';

            } else if (intval($miles) > 0) {

                $converted .= sprintf('%smil ', self::convertGroup($miles));

            }

        }



        if (intval($cientos) > 0) {

            if ($cientos == '001') {

                $converted .= 'un ';

            } else if (intval($cientos) > 0) {

                $converted .= sprintf('%s ', self::convertGroup($cientos));

            }

        }



        if($format){

            if(empty($decimales)){

                $valor_convertido =  ucfirst($converted) . ' con 00/100 '.$currency;

            } else {

                $valor_convertido =  ucfirst($converted) .'con '. $decNumberStr . '/100 '.$currency;

            }

        }else{

            if(empty($decimales)){

                $valor_convertido = ucfirst($converted) . $currency;

            } else {

                $valor_convertido = ucfirst($converted) . $currency. ' con ' . $decimales . $decimals;

            }

        }



        return $valor_convertido;

    }



    private static function convertGroup($n)

    {

        $output = '';



        if ($n == '100') {

            $output = "cien ";

        } else if ($n[0] !== '0') {

            $output = self::$CENTENAS[$n[0] - 1];

        }



        $k = intval(substr($n,1));



        if ($k <= 20) {

            $output .= self::$UNIDADES[$k];

        } else {

            if(($k > 30) && ($n[2] !== '0')) {

                $output .= sprintf('%sy %s', self::$DECENAS[intval($n[1]) - 2], self::$UNIDADES[intval($n[2])]);

            } else {

                $output .= sprintf('%s%s', self::$DECENAS[intval($n[1]) - 2], self::$UNIDADES[intval($n[2])]);

            }

        }



        return $output;

    }

}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Constancia de Deposito de CTS</title>
    <style type="text/css">
        hr {
            page-break-after: always;
            border: 0;
        }
        body {
            margin-bottom:10mm;
            font-family: "Arial Narrow";
		    text-align: justify;
            font-size:12pt;
        }
        .text-center{
            text-align: center;
        }
        .text-left{
            text-align: left;
        }
        .text-right{
            text-align: right;
        }
        .text-de-under {
            text-decoration: underline;
        }
        .bold{
            font-weight: bold;
        }
        .logo{
            position:absolute;
            left: 0;
        }

        .foto-entidad{
            position:absolute;
            right: 145;
        }

         table{
        width: 100%;
        }

    table,td,th{
        border: 1px solid black;
        border-collapse: collapse;
    }
    table,.no-border{
        border:0px;
        border-collapse: collapse;
    }
    table,.border-top{
        border-bottom: 0px;
        border-left: 0px;
        border-right: 0px;
        border-collapse: collapse;
    }
    table,.border-bottom{
        border-top: 0px;
        border-left: 0px;
        border-right: 0px;
        border-collapse: collapse;
    }

    table,.border-bottom-dotted{
        border-top: 0px;
        border-left: 0px;
        border-right: 0px;
        border-style: dotted;
        color: black;
    }

    

    table,.border-bottom-top{
        border-left: 0px;
        border-right: 0px;
        border-collapse: collapse;
    }


    table,.border-left{
        border-bottom: 0px;
        border-top: 0px;
        border-right: 0px;
        border-collapse: collapse;
    }
    table,.border-right{
        border-bottom: 0px;
        border-left: 0px;
        border-top: 0px;
        border-collapse: collapse;
    }
    .bold{
        font-weight: bold;
    }
        .divide{
            height: 3px;
        }
        .color-blue{
            color: blue !important;
        }

        .d-client{
            font-size: 0.9rem;
            margin-top: -3.5mm;
            margin-left:11mm;
        }

        .d-description{
            font-size: 0.87rem;
            margin-top: -3.5mm;
            margin-left:11mm;
        }

        .text-de-top {
            text-decoration: overline;
        }
    </style>
  </head>
  <body>

  <header>
    </header>
    <main>
    
    @if($data['datos']['id_empresa'] == 201)
      <div class="logo"><img width="100" height="100" src="{{url('/img/upeu.png')}}"></div>
    @endif
    @if($data['datos']['id_empresa'] == 207)
    <div class="logo"><img width="100" height="100" src="{{url('/img/logo_2.png')}}" ></div>
    @endif

      <!-- <div class="foto-entidad"><img width="78" height="100" src="https://visafoto.com/img/docs/ao_visa.jpg"></div> -->
      <div class="text-right bold">{{$data['datos']['entidad']}}</div>
      <div class="text-right"> Telf. {{ $data['datos']['telephone'] }}</div>
      <div class="text-right" style="font-size: 0.95rem;">{{ $data['datos']['direccion_legal'] }}</div>
      <br>
    <h5 class="text-de-under text-center">CONSTANCIA DE DEPÓSITO DE LA COMPENSACIÓN POR TIEMPO DE SERVICIO</h5>
      <p style="font-size: 0.95rem;">La {{$data['datos']['empresa']}} - {{$data['datos']['entidad']}},
       con  {{$data['datos']['ruc']}}, con oficina en {{$data['datos']['direccion_legal']}}, representado 
       por {{$data['datos']['representante']}}, identificado con DNI Nº {{$data['datos']['documento']}}, en aplicación del artículo 24 
       del TUO del del D.Leg. 650 Ley de Compensación por Tiempo de Servicios aprobado mediante
        D.S. 001-97-TR, Otorga la presente contancia del Déposito de la Compensación por Tiempo de Servicio a:</p>
    <table>
      <thead></thead>
      <tbody>
        <tr>
            <td width="11%" class="text-right no-border bold"></td>
            <td width="18%" colspan="2" class="no-border">APELLIDOS</td>
            <td width="6%" colspan="2" class="text-center no-border bold">:</td>
            <td width="31%" class="text-left bold no-border">{{$data['person']->paterno}} {{$data['person']->materno}}</td>
            <td width="34%" colspan="4" class="text-left no-border"></td>
          </tr>
        <tr>
            <td width="11%" class="text-right no-border bold"></td>
            <td width="18%" colspan="2" class="no-border">NOMBRE</td>
            <td width="6%" colspan="2" class="text-center no-border bold">:</td>
            <td width="31%" class="text-left bold no-border">{{$data['person']->nombre}}</td>
            <td width="34%" colspan="4" class="text-left no-border"></td>
        </tr>
        <tr>
            <td width="11%" class="text-right no-border bold"></td>
            <td width="18%" colspan="2" class="no-border">DNI N°</td>
            <td width="6%" colspan="2" class="text-center no-border bold">:</td>
            <td width="31%" class="text-left bold no-border">{{$data['person']->num_documento}}</td>
            <td width="34%" colspan="4" class="text-left no-border"></td>
        </tr>
        <tr>
            <td width="11%" class="text-right no-border bold"></td>
            <td width="18%" colspan="2" class="no-border">FECHA DE DEPÓSITO</td>
            <td width="6%" colspan="2" class="text-center no-border bold">:</td>
            <td width="31%" class="text-left bold no-border">{{$data['datos']['fecha_deposito']}}</td>
            <td width="34%" colspan="4" class="text-left no-border"></td>
        </tr>
        <tr>
          <td width="11%" class="text-right no-border bold"></td>
          <td width="18%" colspan="2" class="no-border">N° DE CUENTA</td>
          <td width="6%" colspan="2" class="text-center no-border bold">:</td>
          <td width="31%" class="text-left bold no-border">{{$data['datos']['cta_bancaria']}}</td>
          <td width="34%" colspan="4" class="text-left no-border"></td>
        </tr>
        <tr>
            <td width="11%" class="text-right no-border bold"></td>
            <td width="18%" colspan="2" class="no-border">BANCO</td>
            <td width="6%" colspan="2" class="text-center no-border bold">:</td>
            <td width="31%" class="text-left bold no-border">{{$data['datos']['nombre_banco']}}</td>
            <td width="34%" colspan="4" class="text-left no-border"></td>
        </tr>
        <tr>
            <td width="11%" class="text-right no-border bold"></td>
            <td width="18%" colspan="2" class="no-border">MONEDA</td>
            <td width="6%" colspan="2" class="text-center no-border bold">:</td>
            <td width="31%" class="text-left bold no-border">{{$data['datos']['moneda']}}</td>
            <td width="34%" colspan="4" class="text-left no-border"></td>
        </tr>
      </tbody>
    </table>
    <p>Por los siguientes montos y periodos:</p>
    <p class="bold">1. <span class="text-de-under">Periodo que se liquida :</span></p>
    <p style="font-size: 0.95rem;">Del: <span class="bold">{{date('d/m/Y', strtotime($data['person']->ingreso)) }}</span> al <span class="bold">{{date('d/m/Y', strtotime($data['person']->salida)) }} </span>: equivalente a {{$data['person']->meses}} Meses y {{$data['person']->dias}} Días.</p>
    <p class="bold">2. <span class="text-de-under">Remuneración Computable :</span></p>
    <table>
      <thead></thead>
      <tbody>
        <tr>
            <td width="6%" class="text-center no-border bold"></td>
            <td width="28%" colspan="2" class="no-border">Básico</td>
            <td width="21%" class="text-right no-border">S/.</td>
            <td width="6%" colspan="2" class="text-right no-border bold">{{ number_format($data['person']->basico, 2) }}</td>
            <td width="39%" colspan="4" class="text-left no-border"></td>
          </tr>
        <tr>
            <td width="6%" class="text-center no-border bold"></td>
            <td width="28%" colspan="2" class="no-border">Prima Infantil</td>
            <td width="21%" class="text-right no-border">S/.</td>
            <td width="6%" colspan="2" class="text-right no-border bold">{{ number_format($data['person']->prima_infantil, 2) }}</td>
            <td width="39%" colspan="4" class="text-left no-border"></td>
        </tr>
        <tr>
            <td width="6%" class="text-center no-border bold"></td>
            <td width="28%" colspan="2" class="no-border">Remuneración en Especie</td>
            <td width="21%" class="text-right no-border">S/.</td>
            <td width="6%" colspan="2" class="text-right no-border bold">{{ number_format($data['person']->remun_especie, 2) }}</td>
            <td width="39%" colspan="4" class="text-left no-border"></td>
        </tr>
        <tr>
            <td width="6%" class="text-center no-border bold"></td>
            <td width="28%" colspan="2" class="no-border">Bonificación Depreciación Vehicular</td>
            <td width="21%" class="text-right no-border">S/.</td>
            <td width="6%" colspan="2" class="text-right no-border bold">{{ number_format($data['person']->remun_variable, 2) }}</td>
            <td width="39%" colspan="4" class="text-left no-border"></td>
        </tr>
        <tr>
            <td width="6%" class="text-center no-border bold"></td>
            <td width="28%" colspan="2" class="no-border">Viaticos Libre Disposición</td>
            <td width="21%" class="text-right no-border">S/.</td>
            <td width="6%" colspan="2" class="text-right no-border bold">{{ number_format($data['person']->viaticos_ld, 2) }}</td>
            <td width="39%" colspan="4" class="text-left no-border"></td>
        </tr>
        <tr>
            <td width="6%" class="text-center no-border bold"></td>
            <td width="28%" colspan="2" class="no-border">Gratificación (un sexto)</td>
            <td width="21%" class="text-right no-border">S/.</td>
            <td width="6%" colspan="2" class="text-right no-border bold">{{ number_format($data['person']->d_grati, 2) }}</td>
            <td width="39%" colspan="4" class="text-left no-border"></td>
        </tr>
        <tr>
            <td width="6%" class="text-center no-border bold"></td>
            <td width="28%" colspan="2" class="no-border">Bonificación Especial Voluntaria</td>
            <td width="21%" class="text-right no-border">S/.</td>
            <td width="6%" colspan="2" class="text-right no-border bold">{{ number_format($data['person']->bon_esp_voluntaria, 2) }}</td>
            <td width="39%" colspan="4" class="text-left no-border"></td>
        </tr>
        <tr>
          <td width="6%" class="text-center no-border bold"></td>
          <td width="28%" colspan="2" class="no-border">Bonificación por cargo</td>
          <td width="21%" class="text-right no-border">S/.</td>
          <td width="6%" colspan="2" class="text-right no-border bold">{{ number_format($data['person']->bon_cargo, 2) }}</td>
          <td width="39%" colspan="4" class="text-left no-border"></td>
        </tr>
        <tr>
          <td width="6%" class="text-center no-border bold"></td>
          <td width="28%" colspan="2" class="no-border">Comisiones</td>
          <td width="21%" class="text-right no-border">S/.</td>
          <td width="6%" colspan="2" class="text-right no-border bold">{{ number_format($data['person']->comisiones, 2) }}</td>
          <td width="39%" colspan="4" class="text-left no-border"></td>
        </tr>
        <tr>
            <td width="11%" class="text-right no-border"></td>
            <td width="28%" colspan="2" class="no-border bold">Total</td>
            <td width="21%" class="text-right no-border bold">S/.</td>
            <td width="6%" colspan="2" class="text-right border-bottom-top bold">{{ number_format($data['person']->rc_cts, 2) }}</td>
            <td width="39%" colspan="4" class="text-left no-border"></td>
        </tr>
      </tbody>
    </table>
    <p class="bold">3. <span class="text-de-under">Cálculo :</span></p>
    <table>
      <thead></thead>
      <tbody>
        <tr>
            <td width="11%" class="text-right no-border bold"></td>
            <td width="28%"  class="no-border">Por los meses</td>
            <td width="25%" class="text-left no-border">S/. {{ number_format($data['person']->rc_cts, 2) }} / 12 * {{ $data['person']->meses }}</td>
            <td width="6%"  class="text-right no-border bold">=</td>
            <td width="6%"  class="text-right no-border ">S/.</td>
            <td width="6%"  class="text-right no-border bold">{{ number_format($data['person']->x_meses, 2) }}</td>
            <td width="18%"  class="text-right no-border"></td>
          </tr>
        <tr>
            <td width="11%" class="text-right no-border bold"></td>
            <td width="28%"  class="no-border">Por los días</td>
        <td width="25%" class="text-left no-border">S/. {{ number_format($data['person']->rc_cts, 2) }} / 360 * {{ $data['person']->dias }}</td>
            <td width="6%"  class="text-right no-border bold">=</td>
            <td width="6%"  class="text-right no-border ">S/. </td>
            <td width="6%"  class="text-right border-bottom bold">{{ number_format($data['person']->x_dias, 2) }}</td>
            <td width="18%"  class="text-right no-border"></td>
        </tr>
        <tr>
            <td width="11%" class="text-right no-border bold"></td>
            <td width="28%"  class="no-border">Total Deposito CTS</td>
            <td width="31%" colspan="2" class="text-right no-border"></td>
            <td width="6%"  class="text-right no-border ">S/.</td>
            <td width="6%"  class="text-right border-bottom bold">{{ number_format($data['person']->total, 2) }}</td>
            <td width="18%" class="text-right no-border"></td>
        </tr>
      </tbody>
    </table>
    <p>Son: {{strtoupper(NumerosEnLetras::convertir($data['person']->total,'soles',true,''))}} </p> 
    <br>
    <p>Yo  {{$data['person']->paterno}} {{$data['person']->materno}}, {{$data['person']->nombre}} con DNI:{{$data['datos']['documento']}} Declaro haber recibido dicho importe y recibido la presente constancia de Deposito de CTS.</p>
    <p>{{$data['datos']['nom_ciudad']}}, {{$data['datos']['date_text_doc']}}</p>
    <br>
    <br>
    <br>
    <table>
        <tbody>
            <tr>
                <td width="41%" class="border-top "><p class="text-center" style="margin-top:0mm;"> <span class="bold">{{$data['datos']['representante']}}</span> <br> Representante Legal</p></td>
                <td width="8%" class="no-border"><p class="text-center "></p></td>
                <td width="41%"class="border-top" ><p class="text-center" style="margin-top:0mm;"> <span class="bold">{{$data['person']->paterno}} {{$data['person']->materno}}, {{$data['person']->nombre}}</span>  <br> Trabajador</p></td>
            </tr>
        </tbody>
    </table> 
  </main>
   </body>
</html>