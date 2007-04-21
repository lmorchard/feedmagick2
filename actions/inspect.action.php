<?php
/**
 *
 */
$tpl->modules  = $this->getMetaForModules();
$tpl->pipelines = $this->getMetaForPipelines();
$tpl->pipeline_name = $pipeline_name = $this->getRouteVar('path');
$tpl->pipeline = $this->getMetaForPipeline($pipeline_name);

$tpl->display('inspect.tmpl.php');
