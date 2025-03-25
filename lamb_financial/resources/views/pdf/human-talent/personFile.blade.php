<!DOCTYPE html>
<html lang="en">
  <head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>UPN - Ficha personal</title>
    <link rel="stylesheet" href="css/person_file_pdf.css"/>
  </head>
  <body>
    <header class="header">
        <div class="row">
            <div class="col-3">
                <div class="logo"><img width="80" height="80" src="{{$data['data_empresa']->logo}}"></div>
            </div>
            <div class="col-6">
                <div class="text-center bold">HOJA DE DATOS DEL PERSONAL</div>
                <div class="text-center bold" style="font-size: 0.85rem;">{{$data['data_empresa']->nombre_legal}}</div>
                <!-- <div class="text-center">Ofiicina tarapoto</div> -->
                <div class="text-center">RUC: {{$data['data_empresa']->ruc}}</div>
            </div>
            <div class="col-3">
                <div class="foto-perfil"><img width="78" height="100" src=""></div>
            </div>
        </div>
    </header>
    <main class="main">
        <table>
            <tbody>
                <tr>
                    <td width="1%" class="text-right no-border bold">1</td>
                    <td width="28%" colspan="2" class="no-border">APELLIDOS COMPLETOS</td>
                    <td width="25%" colspan="2" class="color-blue">{{$data['datos']['paterno']}}</td>
                    <td width="46%" colspan="4" class="color-blue">{{$data['datos']['materno']}}</td>
                </tr>
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="text-right no-border bold">2</td>
                    <td colspan="2" class="no-border">NOMBRES COMPLETOS</td>
                    <td colspan="6" class="color-blue">{{$data['datos']['nombre']}}</td>
                </tr>
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="text-right no-border bold">3</td>
                    <td colspan="2" class="no-border">TIPO DE DOC DE INDENTIDAD:</td>
                    <td class="color-blue">{{$data['datos']['tipo_documento']}}</td>
                    <td class="text-right no-border bold">4</td>
                    <td class="no-border">Nº DOCUMENTO</td>
                    <td colspan="3" class="color-blue">{{$data['datos']['num_documento']}}</td>
                </tr>
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="text-right no-border bold">5</td>
                    <td colspan="2" class="no-border">LUGAR DE NACIMIENTO</td>
                    <td class="color-blue">{{$data['datos']['lugar_nacimiento']}}</td>
                    <td class="text-right no-border bold">6</td>
                    <td colspan="2" class="no-border">FECHA DE NACIMIENTO</td>
                    <td colspan="2" class="color-blue">{{$data['datos']['fec_nacimiento']}}</td>
                </tr>
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="text-right no-border bold">7</td>
                    <td colspan="2" class="no-border">SEXO</td>
                    <td class="color-blue">{{$data['datos']['sexo']}}</td>
                    <td class="text-right no-border bold">8</td>
                    <td class="no-border">ESTADO CIVIL</td>
                    <td colspan="3" class="color-blue">{{$data['datos']['estado_civil']}}</td>
                </tr>
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="text-right no-border bold">9</td>
                    <td colspan="2" class="no-border">RELIGIÓN</td>
                    <td class="color-blue">{{$data['datos']['religion']}}</td>
                    <td colspan="5" class="no-border"></td>
                </tr>
                <tr>
                    <td class="text-right no-border bold">10</td>
                    <td colspan="8" class="no-border">DERECHOHABIENTES</td>
                </tr>
                <tr>
                    <td rowspan="2" class="no-border"></td>
                    <td rowspan="2" class="text-left">Parentesco</td>
                    <td rowspan="2" colspan="2" class="text-center">Apellidos y Nombres completos</td>
                    <td rowspan="2" class="text-center">N° Doc.</td>
                    <td colspan="2" class="text-center">Nacimiento</td>
                    <td rowspan="2" class="text-center">Sexo</td>
                    <td rowspan="2" class="text-center">Edad</td>
                </tr>
                <tr>
                    <td class="text-center">Lugar</td>
                    <td class="text-center">Fecha</td>
                </tr>
                @if(count($data['datoparentesco'])>0))
                @foreach($data['datoparentesco'] as $item)
                <tr>
                    <td class="no-border"></td>
                    <td class="color-blue">{{$item->parentesco}}</td>
                    <td colspan="2" class="text-center color-blue">{{$item->nom_persona}}</td>
                    <td class="text-center color-blue">{{$item->num_documento}}</td>
                    <td class="text-center color-blue">{{isset($item->lugar_nacimiento)?$item->lugar_nacimiento:''}}</td>
                    <td class="text-center color-blue">{{isset($item->fec_nacimiento)?$item->fec_nacimiento:''}}</td>
                    <td class="text-center color-blue">{{$item->sexo}}</td>
                    <td class="text-center color-blue">{{isset($item->edad)?$item->edad:''}}</td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td class="no-border"></td>
                    <td class="color-blue">---</td>
                    <td colspan="2" class="text-center color-blue">---</td>
                    <td class="text-center color-blue">---</td>
                    <td class="text-center color-blue">---</td>
                    <td class="text-center color-blue">---</td>
                    <td class="text-center color-blue">---</td>
                    <td class="text-center color-blue">---</td>
                </tr>
                @endif
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="text-right no-border bold">11</td>
                    <td class="no-border">PROFESIÓN</td>
                    <td colspan="2" class="color-blue">{{$data['datos']['profesion']}}</td>
                    <td class="text-right no-border bold">12</td>
                    <td class="no-border">TITULO/GRADO</td>
                    <td colspan="3" class="color-blue">{{$data['datos']['grado']}}</td>
                </tr>
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="text-right no-border bold">13</td>
                    <td class="no-border" colspan="8">DIRECCIÓN COMPLETA</td>
                </tr>
                <tr>
                    <td class="no-border"></td>
                    <td class="no-border">Av./Calle/Jr.</td>
                    <td colspan="7" class="color-blue">{{$data['datos']['direccion']}}</td>
                </tr>
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="no-border"></td>
                    <td class="no-border">Nº/Mz/Lt</td>
                    <td class="color-blue">{{$data['datos']['numero_direccion']}}</td>
                    <td class="no-border"></td>
                    <td colspan="2" class="no-border">Urbanización/PPJJ/AAHH</td>
                    <td colspan="3" class="color-blue">{{$data['datos']['urbanizacion']}}</td>
                </tr>
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="no-border"></td>
                    <td class="no-border">Distrito</td>
                    <td colspan="7" class="color-blue">{{$data['datos']['distrito']}}</td>
                </tr>
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="text-right no-border bold">14</td>
                    <td class="no-border">TELEFONO</td>
                    <td colspan="2" class="color-blue">{{$data['datos']['telefono']}}</td>
                    <td colspan="5" class="no-border"></td>
                </tr>
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="text-right no-border bold">15</td>
                    <td class="no-border">E-mail</td>
                    <td colspan="7" class="color-blue">{{$data['datos']['email']}}</td>
                </tr>
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="text-right no-border bold">16</td>
                    <td class="no-border" colspan="4">AFILIACIÓN SISTEMA DE PENSIONES ( si es AFP colocar cual es)</td>
                    <td class="no-border">AFP</td>
                    <td class="color-blue">{{$data['datos']['num_afp']}}</td>
                    <td class="no-border">SNP</td>
                    <td class="color-blue">{{$data['datos']['num_snp']}}</td>
                </tr>
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="text-right no-border bold">17</td>
                    <td class="no-border">Nº DE CUSPP</td>
                    <td colspan="3" class="color-blue">{{$data['datos']['num_cuspp']}}</td>
                    <td class="no-border" colspan="4"></td>
                </tr>
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="text-right no-border bold">18</td>
                    <td class="no-border" colspan="2">SITUACIÓN LABORAL</td>
                    <td colspan="2" class="color-blue">{{$data['datos']['situacion_laboral']}}</td>
                    <td class="no-border" colspan="4"></td>
                </tr>
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="text-right no-border bold">19</td>
                    <td class="no-border" colspan="2">CARGO QUE DESEMPEÑARÁ </td>
                    <td colspan="2" class="color-blue">{{$data['datos']['cargo']}}</td>
                    <td class="text-right no-border bold">20</td>
                    <td class="no-border">CENTRO LABORAL</td>
                    <td colspan="2" class="color-blue">{{$data['datos']['centro_laboral']}}</td>
                </tr>
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="text-right no-border bold">21</td>
                    <td class="no-border" colspan="2">NOMBRE DEL BANCO</td>
                    <td class="color-blue">{{$data['datos']['nombre_banco']}}</td>
                    <td class="no-border">N° de Cta.</td>
                    <td colspan="4" class="color-blue">{{$data['datos']['cuenta_bancaria']}}</td>
                </tr>
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="text-right no-border bold">22</td>
                    <td class="no-border" colspan="3">FECHA DE INCIO DE RELACIÓN LABORAL</td>
                    <td colspan="5" class="color-blue">{{$data['datos']['fecha_inicio']}}</td>
                </tr>
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="text-right no-border bold">23</td>
                    <td class="no-border" colspan="3">FECHA DE CESE DE RELACIÓN LABORAL</td>
                    <td colspan="5" class="color-blue">{{$data['datos']['fecha_fin']}}</td>
                </tr>
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="text-right no-border bold">24</td>
                    <td class="no-border" colspan="2">REMUNERACIÓN MENSUAL</td>
                    <td colspan="2" class="color-blue">{{$data['datos']['sueldo']}}</td>
                    <td class="no-border" colspan="4"></td>
                </tr>
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="text-right no-border bold">25</td>
                    <td class="no-border" colspan="2">Nº DE AUTOGENERADO</td>
                    <td colspan="4" class="color-blue">{{$data['datos']['num_autg']}}</td>
                    <td class="no-border" colspan="2"></td>
                </tr>
                <tr>
                    <td colspan="9" class="no-border divide"></td>
                </tr>
                <tr>
                    <td class="text-right no-border bold">26</td>
                    <td class="no-border" colspan="2">NOMBRE DEL BANCO - CTS</td>
                    <td class="color-blue">{{$data['datos']['nombre_banco_cts']}}</td>
                    <td class="no-border"></td>
                    <td class="no-border">N° de Cta. CTS</td>
                    <td colspan="3" class="color-blue">{{$data['datos']['cuenta_bancaria_cts']}}</td>
                </tr>
            </tbody>
            <tfoot>
            <tfoot>
        </table>
        <div>
        </div>
    </main>
    <footer class="footer">
        <div align="right">
        -----------------------------<br>
        <b>Firma del Trabajador</b>
        </div>
    </footer>
   </body>
</html>