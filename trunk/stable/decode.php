<?php

include_once("class.Stegano.php");

$stegano = new Stegano();
echo '<pre>'.$stegano -> decodeImageFile("encoded.png").'</pre>';

?>