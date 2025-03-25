<!DOCTYPE html>
<!--[if IE 8]>          <html class="ie ie8"> <![endif]-->
<!--[if IE 9]>          <html class="ie ie9"> <![endif]-->
<!--[if gt IE 9]><!-->
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"><!-- End Required meta tags -->
  <title>Print-Lamb</title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Impresión">
  <!-- FAVICONS -->
  <link rel="shortcut icon" href="{{public_path('favicon.ico')}}" type="image/x-icon" />

  <style>
    @page {
      margin: 50px 12px;
    }

    body {
      font-family: sans-serif;
      margin-top: 50px;
    }

    header {
      position: fixed;
      left: 0px;
      top: -20px;
      right: 0px;
      height: 50px;
      text-align: center;
    }

    .hr-table {
      width: 100%;
      margin-top: -12px;
      margin-left: 20px;
      margin-right: 20px;
    }

    footer {
      position: fixed;
      left: 0px;
      bottom: -45px;
      right: 0px;
      height: 40px;
      font-size: 12px;
    }

    footer .page:after {
      content: counter(page);
    }

    footer table {
      width: 100%;
      margin-left: 20px;
      margin-right: 20px;
    }

    footer p {
      text-align: right;
    }

    footer .izq {
      text-align: left;

    }

    .content {
      margin-left: 24px;
      margin-right: 24px;
      margin-bottom: 30px;
    }
  </style>

</head>

<body>
  <?php
  $datos = Session::get('datosPrint', []);
  //    dd($datos);
  ?>
  <header id="ao">
    <table class="hr-table">
      <tr>
        <td>
          <img src="{{public_path('img/upeu_new.svg')}}" width="90" />
        </td>
        <td style="width:100%; text-align: center;color:#003264">
          <h3>{{$datos['empresa']}}<br>
            <span style="font-size: 10px;">{{$sedeParam['depto']}}</span>
          </h3>
        </td>
        <td>
          <img src="{{public_path('img/lamb_new.png')}}" width="90" />
        </td>
      </tr>
    </table>
  </header>
  <footer>
    <table>
      <tr>
        <td style="width: 30%;color:#003264">
          <strong>
            Fecha: {{$info['fecha_matricula']??$fecha_actual}}
          </strong>
        </td>
        <td style="width:40%; text-align: center;color:#003264">
          <strong>Usuario:
            <?php
            if (isset($datos['matriculador'])) {
              echo $datos['matriculador'];
            } else {
              if (isset($datos['user'])) {
                echo $datos['user'];
              }
            }
            ?></strong>
        </td>
        <td style="width: 30%;">
          <!--p class="page">
            Página
          </p-->
          <span style="width: 20px;
            height: 20px;
            background-color: #F8A900;
            border-radius: 100%;
            font-size: 10px;
            text-align: center;
            color: #FFF;
            float:right;
            padding:5px 2px 0 2px;">
            <strong><span class="page"></span></strong>
          </span>
        </td>
      </tr>
    </table>
  </footer>
  <div class="content">

    @yield('content')

  </div>


</body>

</html>