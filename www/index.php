<?php 
require_once "../conf/config.php";
require_once "FeedMagick2.php";
$f = new FeedMagick2($config);
$f->dispatch();
?>
