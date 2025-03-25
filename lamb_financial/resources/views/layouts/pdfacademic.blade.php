
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
    <link rel="shortcut icon" href="{{public_path('favicon.ico')}}" type="image/x-icon"/>
    <link rel="stylesheet" href="{{public_path('css/printlamb.css')}}">
    

    <style>
    
    @page {
      margin: 50px 0px;
    }
    body{
        font-family: sans-serif;
        margin-top: 50px;
    }
    header { position: fixed;
      left: 0px;
      top: -20px;
      right: 0px;
      height: 50px;
      /*background-color: #ddd;*/
      text-align: center;
      
      
      
    }
    header table {
      width: 100%;
      margin-top: -12px;
      margin-left: 20px;
      margin-right: 20px;
      border-bottom: 1px solid #ccc;
    }
    footer {
      position: fixed;
      left: 0px;
      bottom: -5px;
      right: 0px;
      height: 40px;
      font-size: 12px;
      border-bottom: 1px solid #ccc;
    }
    footer .page:after {
      content: counter(page);

    }
    footer table {
      width: 100%;
      margin-left: 20px;
      margin-right: 30px;
    }
    footer p {
      text-align: right;
    }
    footer .izq {
      text-align: left;
      
    }
    .content {
        margin-left: 40px;
        margin-right: 40px;
        margin-bottom: 40px;
    }
    </style>

</head>
<body>
    <?php
    $datos = Session::get('datosPrint', []);
//    dd($datos);
    ?>
    <header>
        <table>
            <tr>
                <td>
                  @if ($datos['id_empresa'] === '201') {{-- solucion momentaria para la entidad de la UPEU --}}
                    <img src="{{public_path('img/upeu.png')}}" height="60" />
                  @else
                    <img src="{{public_path('img/logo_5.png')}}" height="60" />
                  @endif
                  <?php
                    // $foto = asset('img/'.$datos['id_empresa'].'.png'); // Propuesta para hacerlo dinamico => nopmbrar los logotipos con el id de la empresa y listo
                  ?>
                  {{-- <img src="{{$foto}}" height="60" /> --}} {{-- descomentar --}}
                </td>
                <td style="width:100%; text-align: center"><h3>{{$datos['empresa']}}<br>
{{--                        <span style="font-size: 10px;">{{$datos['depto']}}</span>--}}
                        
                        @if ($sedeParam['id_depto'] == '8')  
                        <span style="width:75%; text-align: center; font-size: 20px;"> IEST Privado “Adventistas del Titicaca” </span>
                        @else  
                          <span style="font-size: 10px;">{{$datos['depto']}}</span>    
                        @endif
                    </h3></td>
                <td><img src="{{public_path('img/lamb-academic.png')}}" class="img-rounded" height="44" width="91" /></td>
            </tr>
        </table>    
    </header>
    <footer>
        <table>
          <tr>
            <td>
                <p class="izq">
                  Fecha: <?php echo date('d/m/Y H:i:s')?>
                </p>
            </td>
            <td>
                Usuario: 
                <?php
                if(isset($datos['user'])){
                    echo $datos['user'];
                }
                ?> 
            </td>
            <td>
              <p class="page">
                Página
              </p>
            </td>
          </tr>
        </table>
    </footer>
    <div class="content">
        
        @yield('content')  
        
    </div>
    
    
</body>
</html>