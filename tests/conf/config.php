<?php
/**
 * Configuration for tests.
 */
$base_dir = realpath(dirname(__FILE__).'/..');
ini_set("include_path", ini_get("include_path").":$base_dir/lib:$base_dir/../lib:$base_dir/../extlib:$base_dir/../modules");

$config = array(

    'base_dir' => $base_dir,

    'paths' => array(
        'modules'   => "$base_dir/../modules",
        'web'       => "$base_dir/../www",
        'pipelines' => "$base_dir/pipelines",
        'xsl'       => "$base_dir/../www/xsl"
    ),

    'log' => array(
        'path'  => "$base_dir/logs/feedmagick2-tests-".date('Ymd', time()).".log",
        'level' => 7 // PEAR_LOG_DEBUG
    ),
    
    'cache' => array(
        'caching' => TRUE,
        'lifeTime' => '10',
        'cacheDir' => "$base_dir/data/cache/",
        'automaticCleaningFactor' => 20,
        'hashedDirectoryLevel' => 2
    )

);
