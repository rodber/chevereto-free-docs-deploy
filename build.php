<?php

declare(strict_types=1);

use DocsDeploy\MarkdownIterator;
use DocsDeploy\Modules;

use function Chevere\Components\Filesystem\getDirFromString;
use function Chevere\Components\Filesystem\getFileFromString;
use function Chevere\Components\Writer\writerForFile;
use function DocsDeploy\toModuleExport;

require 'vendor/autoload.php';

$docs = getcwd() . '/docs/';
$sortNavFile = $docs . 'sortNav.php';
$docsDir = getDirFromString($docs);
$iterator = new MarkdownIterator($docsDir);
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
    $file = getFileFromString($vuePressPath . $file);
    if (!$file->exists()) {
        $file->create();
    }
    $file->put(toModuleExport($module));
}
$stylesPath = $vuePressPath . 'styles/';
$indexProjectStyl = getFileFromString($stylesPath . 'index-project.styl');
if ($indexProjectStyl->exists()) {
    $indexStyl = writerForFile(getFileFromString($stylesPath . 'index.styl'), 'a');
    $indexStyl->write("\n\n" . $indexProjectStyl->contents());
}
