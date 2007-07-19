<?php

include_once("Stegano.php");

$stegano = new Stegano();
echo '<pre>'.$stegano -> decodeImageFile("encoded.png").'</pre>';
