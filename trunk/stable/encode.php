<?php

include_once("Stegano.php");

$data = "f ljdhsgjkhjsdghjkshigfvhb fhjks hgih ihgfgf hih (ho i(iho(o (_(_y_g _ç_yç _t _y _ t _hg_hg hg )))))";

$stegano = new Stegano();
$stegano -> setData($data);
$stegano -> setOrigin("origin.jpg");
$stegano -> keepSize();
$stegano -> draw("encoded.png");
$stegano -> draw(PNG);
