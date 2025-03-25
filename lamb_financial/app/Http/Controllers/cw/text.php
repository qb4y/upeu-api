<?php
$img = Image::make(file_get_contents('https://www.carrerasadistancia.com.pe/logos/original/logo-universidad-peruana-union.png' ));
$img->encode('png');
$type = 'png';
$base64 = 'data:image/' . $type . ';base64,' . base64_encode($img);
?>
<img src="<?php echo $base64 ?>">