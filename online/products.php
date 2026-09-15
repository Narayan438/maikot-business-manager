<?php
// MeroKhata Products page: preserve current module and inject shared navigation.
ob_start();
require __DIR__.'/products-online.php';
$html=ob_get_clean();
// products-online.php already injects online-nav.js. Output unchanged.
echo $html;
