<!DOCTYPE html>
<html lang="en">
  <head>
    <meta name="description" content="UPeU Visa">
    <!-- Open Graph Meta-->
    <title>UPEU-MC</title>
    <link rel="shortcut icon" href="{{ asset('img/upeu.ico') }}">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css"  href="{{ asset('css/upeu.css') }}" rel="stylesheet">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
   
  </head>
  <body>
   
    <div class="container">

        <div class="row">

            <div class="col-md-12">
                <div class="row">
                     @include('partial.headermc')
                </div>
                <div class="row">

                    @yield('content')

                </div>
                <br/>
                <div class="row">

                    @include('partial.footer')

                </div>
            </div>


        </div>
      <!-- /.row -->

    </div>
    <!-- /.container -->

    <!-- Footer -->


    <!-- Essential javascripts for application to work-->
    <script src="{{ asset('jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('bootstrap/js/bootstrap.bundle.js') }}"></script>
    <script>
 $(document).ready(function(){   
   $('#pagovisa').hide();
    //$('.start-js-btn').hide();
   
   $("#terminos").on( 'change', function() {
        if( $(this).is(':checked') ) {
           $('#pagovisa').show();
        } else {
            $('#pagovisa').hide();
           
        }
    });
  });
  
  function fnterminos(){

     var ruta="{{route('mc/terminos')}}";
     
     $('#modalterminos .modal-content').load(ruta, function (result) {
        $('#modalterminos').modal({ show: true,backdrop: "static" });                    
    });
 }
 
 /*$("#imprime").click(function (){
    $("div#myPrintArea").printArea();
});*/

function imprimir(){
    var div = document.querySelector("#PrintArea");
    imprimirElemento(div);
}

function imprimirElemento(elemento){
  var ventana = window.open('', 'PRINT', 'height=400,width=600');
  ventana.document.write('<html><head><title>' + document.title + '</title>');
  ventana.document.write('<link rel="stylesheet" type="text/css"  href="<?php echo asset('css/print.css')?>" rel="stylesheet">');
  ventana.document.write('</head><body >');
  ventana.document.write(elemento.innerHTML);
  ventana.document.write('</body></html>');
  ventana.document.close();
  ventana.focus();
  ventana.onload = function() {
    ventana.print();
    ventana.close();
  };
  return true;
  
}

</script>
   
  </body>
</html>