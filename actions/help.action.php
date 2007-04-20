<?php
/**
 * Markdown-formatted help page renderer.
 */
require_once "smartypants.php";
require_once "markdown.php";

$help_id   = $tpl->help_id = $this->route_vars['path'];
$help_base = $this->getConfig('paths/help', './docs/help');

if ($help_id == 'README' || $help_id == 'TODO') {
    // HACK: README and TODO aren't in docs path.
    $help_path = realpath("./$help_id");
} else {
    $help_path = realpath("$help_base/$help_id");
    if (!$help_path) 
        $help_path = realpath("$help_base/index");
}
$tpl->help_id   = $help_id;
$tpl->help_path = $help_path;

$help_src = file_get_contents($help_path);

$tpl->toc = array(
    'Index'  => 'index',
    'README' => 'README',
    'TODO'   => 'TODO'
);

$tpl->content = SmartyPants(Markdown($help_src));
$tpl->display('help.tmpl.php');
