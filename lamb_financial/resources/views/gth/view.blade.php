@extends('layouts.gth')
@section('js')
<script>
    var tipo ='N';
$(document).ready(function(){
    
  $("#formAccion").on('submit', function(e){

        e.preventDefault();

        var ruta=$('#routeurl').val();
        var urlimg = $('#gcarga').val(); 
        var archivo = $('#file').val(); 
        if(comprueba_extension(archivo)==0){
            return ;
        } 

        $.ajax({
            type: 'POST',
            url: ruta,
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData:false,
            beforeSend: function(){
                $('#formAccion').css("opacity",".5");
                $('#listview').html('<img src="'+urlimg+'" class="img-circle"/>');
                $("#btngrabar").prop( "disabled", true );
                $("#liscert").html('');
            },
            success: function(data){
                var request = $.parseJSON(data);
                var html='';
                if(request.nerror==0){
                    html+='<div class="alert alert-primary" role="alert">';
                    html+=request.mensaje+' [<a href="javascript:fnvercertificado('+request.cert+');" >Ver Certificado</a>]<br>'+' Fecha Firmado: '+request.fecha;
                    html+='</div>';
                }else{
                    html+='<div class="alert alert-danger" role="alert">';
                    html+=request.mensaje;
                    html+='</div>';
                }
                 $('#listview').html(html);
                 $('#formAccion').css("opacity","");
                 $("#btngrabar").prop( "disabled", false );
                 
                 refrescar();
                 $("#tcapcha").val('');
                 
            },

           error: function(jqXHR, textStatus, errorThrown)
            {
                $('#formAccion').css("opacity","");
                $("#btngrabar").prop( "disabled", false );

                if(jqXHR)
                {
                    
 
                    var errors = jqXHR.responseJSON;
 
                    var html = "";
 
                    for(error in errors)
                    {
                        if(error=='message' || error=='exception' || error=='file' || error=='line'){
                             html+= errors[error] + "<br/>";
                        }
                       
                    }
                    
                    var dato='<div class="alert alert-danger" role="alert">';
                    dato+=html;
                    dato+='</div>';
                    $('#listview').html(dato);
         
                }
                refrescar();
                $("#tcapcha").val('');
            }
        });
    });
});

function comprueba_extension(archivo) { 
   var extensiones_permitidas = new Array(".pdf"); 
   var mierror = ""; 
   if (!archivo) { 
      //Si no tengo archivo, es que no se ha seleccionado un archivo en el formulario 
      	mierror = "No has seleccionado ningún archivo"; 
   }else{ 
      //recupero la extensión de este nombre de archivo 
      var extension = (archivo.substring(archivo.lastIndexOf("."))).toLowerCase(); 
      //alert (extension); 
      //compruebo si la extensión está entre las permitidas 
      var permitida = false; 
      for (var i = 0; i < extensiones_permitidas.length; i++) { 
         if (extensiones_permitidas[i] == extension) { 
         permitida = true; 
         break; 
         } 
      } 
      if (!permitida) { 
          mierror = "Comprueba la extensión de los archivos a subir. \nSólo se pueden subir archivos con extensiones: " + extensiones_permitidas.join(); 
      	}else{ 
         return 1; 
      	} 
   } 
   //si estoy aqui es que no se ha podido submitir 
   alert (mierror); 
   return 0; 
}
function refrescar(){
    var num = getRandomArbitrary(1,99);
    var ruta= $("#rutacapcha1").val();
    if(tipo=="N"){
        
        tipo='A';
    }else{
         ruta= $("#rutacapcha2").val();
         tipo='N';
     }
     $('#imgCapcha').attr('src',ruta+'?num='+num);

 }
 
 function getRandomArbitrary(min, max) {
    return Math.random() * (max - min) + min;
}



function fnvercertificado(id_certificado){

    var str     = 'id_certificado='+id_certificado;
    var ruta =$('#rutacert').val();
    $.ajax({
        url: ruta,
        type: 'get',
        data: str,
        cache: false,
        beforeSend: function(){
            $('#liscert').html('Espere por favor.....');
        },
        success: function(data){
            $('#liscert').html(data);
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
            if(jqXHR)
            {
                var errors = jqXHR.responseJSON;
                var html = '';
                for(error in errors)
                {
                    if(error === 'message' || error === 'exception' || error === 'file' || error === 'line'){
                       html+= errors[error] + '<br/>';
                    }
               }
               $('#liscert').html(html);
            }
        }
    }); 
}

function fnbuscar(){
}
</script>    
@endsection
@section('content')
<div class="col-md-12">
    
    
   <div class="card" id="PrintArea">
    <div class="card-body">
      <form class="login-form" method="POST" action="javascript:fnbuscar();" id="formAccion" target="formAccion" enctype="multipart/form-data">
          {{ csrf_field() }}
          <h4 class="login-head"><i class="fa fa-check-square" aria-hidden="true"></i> VERIFICAR DOCUMENTO</h4>
          <hr/>
          
         <div class="row form-group">
         <div class="col-md-6">
             <div class="input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text">Documento</div>
                </div>
                <input class="form-control form-control-sm" type="file" name="file" id="file"  value="" required>
              </div>
         </div>
         <div class="col-md-2">
             <div class="input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text">Doc Interno</div>
                </div>
                <input class="form-control form-control-sm" type="text" name="docinterno"  value="" required>
              </div>
         </div>
         <div class="col-md-4"> 
             <div class="input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text">Capcha</div>
                </div>
                 <input class="form-control form-control-sm" type="text" name="tcapcha"  id="tcapcha"  value="" required>
                <span>
                <img src="{{secure_url('capcha')}}?num=1" id="imgCapcha"/> 
                </span> 
                <a href="javascript:refrescar();">&nbsp; <i class="fa fa-refresh" aria-hidden="true"></i> &nbsp; </a>
                
              </div>
         </div>
         
         </div>

          <input type="hidden" name="tokens"  value="$2y$10$jfeV4ewDnkHjilscOvy5h.sAYBwEzfJgFbnl3Asd0FvUcTOs6EZxu"/> 
          <input type="hidden" name="routeurl" id="routeurl" value="{{secure_url('gth/validardocumento')}}"/> 
          <input type="hidden" name="gcarga" id="gcarga" value="{{secure_url('img/xloading.gif')}}"/> 
          <input type="hidden" name="rutacapcha1" id="rutacapcha1" value="{{secure_url('capcha')}}"/> 
          <input type="hidden" name="rutacapcha2" id="rutacapcha2" value="{{secure_url('capchaajax')}}"/>
          <input type="hidden" name="rutacert" id="rutacert" value="{{secure_url('vercertificado')}}"/>
          
          <div class="form-group btn-container text-center">

              <button type="submit" class="btn btn-primary" id="btngrabar"><i class="fa fa-check-square" aria-hidden="true"></i> Validar</button>
        
              
          </div>
          <div class="form-group btn-container">

              <div id="listview" class="text-center"></div>  
              
              <div id="liscert"></div>  
              
          </div>
        </form>

 
      </div>
      
    
    </div>
  </div>


@endsection