<?php 
$base_dir = realpath(dirname(__FILE__));
set_include_path("$base_dir/lib:$base_dir/extlib:$base_dir/modules:".get_include_path());
require_once "./conf/config.php";

require_once "FeedMagick2.php";
$f = new FeedMagick2($config);
$f->webdispatch();
