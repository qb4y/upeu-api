<!DOCTYPE html>
<html lang="en">
  <head>
    <meta name="description" content="UPeU Visa">
    <!-- Open Graph Meta-->
    <title>UPEU-INTEROCONEXIÓN</title>
    <link rel="shortcut icon" href="{{ url('img/upeu.ico') }}">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" type="text/css" href="{{ url('bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css"  href="{{ url('css/upeu.css') }}" rel="stylesheet">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
   
  </head>
  <body>
   
    <div class="container">

        <div class="row">

            <div class="col-md-12">
                <div class="row">
                     @include('partial.header')
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
    <script src="{{ url('jquery/jquery.min.js') }}"></script>
    <script src="{{ url('bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ url('bootstrap/js/bootstrap.bundle.js') }}"></script>
    <script>

 


</script>
   
  </body>
</html>
