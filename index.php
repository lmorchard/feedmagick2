<?php 
ini_set("include_path", ini_get("include_path").":./lib:./extlib:./modules");
require_once "FeedMagick2.php";
require_once "conf/config.php";
$f = new FeedMagick2($config);
$f->dispatch();
?>
