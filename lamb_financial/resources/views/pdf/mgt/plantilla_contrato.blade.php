@extends('layouts.pdf')
@section('content')

<style type="text/css" media="screen">

    .font-size-10 {
      font-size: 10px !important;
    }
    
    .head-info {
        background-color: #7f264a;
        color: white;
        padding: 4px;
        text-transform: uppercase;
        font-weight: 600;
        font-size: .80rem;
        
    }
    
    .text-truncate {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    
    .text-left {
      text-align: left !important;
    }
    
    .text-right {
      text-align: right !important;
    }
    
    .text-center {
      text-align: center !important;
    }
    
    .page-break {
        page-break-after: always;
    }
    
    .izq {
          text-align: left;
          
        }
        .der {
          text-align: right;
          
        }
    .abc{
      padding: 1cm;
      font-size: 0.7em;
    }
</style>

<?php
setlocale(LC_TIME, 'es_ES');
?>

<div class="abc">
<?php
  echo html_entity_decode($data)
?>
</div>
@endsection