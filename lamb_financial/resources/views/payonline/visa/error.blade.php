@extends('layouts.visa')
@section('content')
<div class="col-md-12 py-10">

    <div class="alert alert-danger" role="alert">
        {{$nerror}}   <?php echo $mensaje ?>
    </div>


    <?php
    dd($data); 
    ?>

</div>

@endsection



