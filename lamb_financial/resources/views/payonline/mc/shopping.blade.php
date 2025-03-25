@extends('layouts.mc')
@section('content')
<div class="col-md-12">
    
    
   <div class="card" id="PrintArea">
    <div class="card-body">
      <form class="login-form" method="POST" action="{{route('mc/tokens')}}">
          {{ csrf_field() }}
          <h4 class="login-head"><i class="fa fa-lg  fa-cc-mastercard"></i> PAGOS PRUEBA</h4>
          <hr/>
          
          <?php
          
          $prod="";
          $pru="";
          foreach($dataPayonline as $row){
              $prod=$row->url_mc;
              $pru=$row->url_mc_pr;
          }
          ?>
          <div class="row form-group">
            <label class="control-label col-md-2">*URL(POST) PROD</label>
            <div class="col-md-10">
                {{$prod}}
            </div>
          </div>
          <div class="row form-group">
            <label class="control-label col-md-2">*URL(POST) PRU</label>
            <div class="col-md-10">
                {{$pru}}
            </div>
          </div>
          
          <div class="row form-group">
            <label class="control-label col-md-2">nombre</label>
            <div class="col-md-4">
                <input class="form-control form-control-sm" type="text" name="nombre"  value="">
               
              </div>
            <label class="control-label col-md-2">paterno</label>
             <div class="col-md-4">
            <input class="form-control form-control-sm" type="text" name="paterno"  value="">
            
            </div>
          </div>
          <div class="row form-group">
            <label class="control-label col-md-2">materno</label>
            <div class="col-md-4">
                <input class="form-control form-control-sm" type="text" name="materno"  value="">
               
              </div>
            <label class="control-label col-md-2">sexo</label>
             <div class="col-md-4">
                <select class="form-control form-control-sm" name="sexo">
                      <option value=""></option>
                      <option value="M">M-Masculino</option>
                      <option value="F">F-Femenino</option>
                </select>
            
            </div>
          </div>
          <div class="row form-group">
            <label class="control-label col-md-2">correo</label>
            <div class="col-md-10">
                <input class="form-control form-control-sm" type="text" name="correo"  value="">
               
              </div>
            
          </div>
          
          <div class="row form-group">
            <label class="control-label col-md-2">tipodoc</label>
            <div class="col-md-4">
                <select class="form-control form-control-sm" name="tipodoc">
                    
                      <option value="1">1-DNI</option>
                      <!--<option value="4">C.E</option>-->
                </select>
               
              </div>
            <label class="control-label col-md-2">numdoc</label>
             <div class="col-md-4">
            <input class="form-control form-control-sm" type="text" name="numdoc"  value="78342405">
            
            </div>
          </div>
          <div class="row form-group">
            <label class="control-label col-md-2">*id_operacion(Concepto)</label>
            <div class="col-md-4">
            <input class="form-control form-control-sm" type="text" name="id_operacion"  value="002" aria-describedby="helpper">
            <span id="helpper" class="help-block">Por cada nivel</span>
            </div>
          </div>
          <div class="row form-group">
            <label class="control-label col-md-2">id_persona</label>
            <div class="col-md-4">
            <input class="form-control form-control-sm" type="text" name="id_persona"  value="20170112100153" aria-describedby="helpper">
            <span id="helpper" class="help-block">Id  de la persona</span>
            </div>
            <label class="control-label col-md-2">id_origen</label>
            <div class="col-md-4">
            <input class="form-control form-control-sm" type="text" name="id_origen"  value=""  aria-describedby="helpOri">
            <span id="helpOri" class="help-block">Id  para actualizar en el sistema de origin </span>
            </div>
          </div>
          <div class="row form-group">
            <label class="control-label col-md-2">*tokens</label>
            <div class="col-md-10">
            <input class="form-control form-control-sm" type="text" name="tokens"  value="$2y$10$jfeV4ewDnkHjilscOvy5h.sAYBwEzfJgFbnl3Asd0FvUcTOs6EZxu" 
                   required  aria-describedby="helpTok" readonly="yes">
            <span id="helpTok" class="help-block">Tokens para validar creación de boton Visa</span>
            
            </div>
          </div>
          <div class="row form-group">
            <label class="control-label col-md-2">*id_negocio</label>
            <div class="col-md-4">
                <select class="form-control form-control-sm" name="id_negocio" required aria-describedby="helpNeg">
                  @foreach($dataMc as $row)
                      <option value="{{$row->id_mc}}">{{$row->id_mc}} - {{$row->descripcion}}</option>
                  @endforeach
                </select>
            
                <span id="helpNeg" class="help-block">Id de mearchatID</span>
            </div>
            <label class="control-label col-md-2">*id_aplicacion</label>
            <div class="col-md-4">
                <select class="form-control form-control-sm" name="id_aplicacion" required aria-describedby="helpApli">
                @foreach($dataAplicacion as $row)
                      <option value="{{$row->id_aplicacion}}">{{$row->id_aplicacion}} - {{$row->nombre}}</option>
                @endforeach
                </select>
            <span id="helpApli" class="help-block">Para actualizar que servicio</span>
            </div>
          </div>
          
          <div class="row form-group">
            <label class="control-label col-md-2">*moneda</label>
            <div class="col-md-4">
            <select class="form-control form-control-sm" name="moneda">
                      <option value="0">0-Soles</option>
                      <option value="1">1-Dolares</option>
                </select>
            </div>
            <label class="control-label col-md-2">*importe</label>
            <div class="col-md-4">
            <input class="form-control form-control-sm" type="importe" name="importe"  value="1.00" >
           
            </div>
          </div>
           
        
          <div class="form-group btn-container text-center">

            <button type="submit" class="btn btn-primary"><i class="fa fa-sign-in fa-lg fa-fw"></i>Crear Boton Mc</button>
           <a href="#" onclick="imprimir()">Imprimir</a>
              
          </div>
          
        </form>  
      </div>
      
    </div>
  </div>


@endsection