@extends('layouts.asist')
@section('content')

<?php
   $letras=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','W','X','Y','Z');
   ?>
<form  method="POST">


    <table cellpadding= "0"  cellspacing= "0">
    <?php
    $i=1;
    foreach($letras as $letra){
    ?>
    <tr class="tds">
            <?php
            for($j=1;$j<=26;$j++){
            $clase='tds';
            $nom = $j;



            if($j==5 or $j==13 or $j==20){
                    $clase='tdsl';
            }else{
                    if($j==1 ){
                            $clase='tds1';
                    }

            }

            if($j==1 or $j==6 or $j==14 or $j==21){
                    $clasel = $clase;
                    if($j==1 ){
                            $clasel = 'tdslet';
                    }
            ?>
                    <td class="<?php echo $clasel ?>">
            <?php echo $letra?><br/>
            </td>
            <?php
            }
            ?>
            <td class="<?php echo $clase ?>">
            <?php echo $j ?><br/>
           
                <input type="checkbox" value="0">
         
            </td>
            <?php
            }
            ?>
    </tr>
    <?php
            $i++;
    }
    ?>
    </table>

</form>

@endsection