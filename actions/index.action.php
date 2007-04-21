<?php
/**
 *
 */
$tpl->modules   = $this->getMetaForModules();
$tpl->pipelines = $this->getMetaForPipelines();

$tpl->display('index.tmpl.php');
