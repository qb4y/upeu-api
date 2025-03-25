<!DOCTYPE HTML>
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
      <title>Title of your Website</title>
   </head>
   <body>
<?php
if($response['success']){
?>
        <table border='1' cellpadding='0' cellspacing='0' > 
           <tr>
<?php
            foreach ($data[0] as $key => $row) {
?>
               <th><?php echo $key ?></th>
<?php
            }
?>
           </tr>
<?php
            foreach ($data as $items){
                $item=(array) $items;
?>
           <tr>
 <?php
                foreach ($data[0] as $key => $row) {
                                   
 ?>
               <td><?php echo $item[$key]?></td>
<?php
                }
?> 
           </tr>
<?php
            }
?>           
       </table>
<?php
}else{
    echo $response['message'];
}
?>
   </body>
</html>
