<?php
$ruta='/u01/vhosts/app09.sotil.lamb-dev.upeu/httpdocs/api/lamb_financial/public/foto/foto.jpg';
$rutaImagen = $ruta;//asset('foto/'.$fotourl);
$informacionImagen = getimagesize($rutaImagen);
header("Content-type: {$informacionImagen['mime']}");
readfile($rutaImagen);
?>

