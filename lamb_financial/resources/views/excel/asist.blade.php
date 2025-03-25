
<table>
    <thead>
  
    <tr class="text-center">
        <th colspan="12">Assitencia </th>
    </tr>

    <tr>
        <th>#&nbsp;</th>
        <th>Id</th>
        <th>Nombre</th>
        <th>Comentario</th>
        <th>desde</th>
        <th>Hasta</th>
        <th>Depto</th>
        <th>Estado</th>

    </tr>

</thead>
<tbody>
    <?php
    $i=1;

    foreach($data as $row){
            ?>
    <tr>

    <td><?php echo $i++?></td>
    <td><?php echo $row->id_proyecto?></td>
    <td>

        <b><?php echo $row->nombre ?></b>

    </td>
    <td><?php echo $row->comentario?></td>
    <td><?php echo $row->fdesde?></td>
    <td><?php echo $row->fhasta?></td>
    <td><?php echo $row->id_depto?></td>
    <td>
        <?php 
        if($row->estado==1){
        ?>
        &Sqrt;
        <?php
        }else{
        ?>
        &Chi;
        <?php    
        }
        ?>

    </td>

  </tr>
<?php
    }
?>
  
</tbody>
</table>


