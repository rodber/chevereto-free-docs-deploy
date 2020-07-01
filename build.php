<?php

declare(strict_types=1);

use Chevere\Components\Filesystem\FileFromString;
use Chevere\Components\Writer\StreamWriterFromString;
use DocsDeploy\MarkdownIterator;
use DocsDeploy\Modules;

use function DocsDeploy\toModuleExport;

require 'vendor/autoload.php';

$docs = getcwd() . '/docs/';
$sortNavFile = $docs . 'sortNav.php';
$iterator = new MarkdownIterator($docs);
if (stream_resolve_include_path($sortNavFile)) {
    $sortNav = include $docs . 'sortNav.php';
}
$modules = new Modules($iterator, $sortNav ?? []);
$modules->execute();
$vuePressPath = "$docs.vuepress/";
foreach ([
    'nav/en.js' => $modules->nav(),
    'sidebar/en.js' => $modules->sidebar(),
] as $file => $module) {
    $file = new FileFromString($vuePressPath . $file);
    if (!$file->exists()) {
        $file->create();
    }
    $file->put(toModuleExport($module));
}
$stylesPath = $vuePressPath . 'styles/';
$indexProjectStyl = new FileFromString($stylesPath . 'index-project.styl');
if ($indexProjectStyl->exists()) {
    $indexStyl = new StreamWriterFromString($stylesPath . 'index.styl', 'a');
    $indexStyl->write("\n\n" . $indexProjectStyl->contents());
}
