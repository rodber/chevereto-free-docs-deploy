<?php

declare(strict_types=1);

use Chevere\Components\Filesystem\FileFromString;
use Chevere\Components\Filesystem\FilesystemFactory;
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
$filesystemFactory = new FilesystemFactory;
foreach ([
    'nav/en.js' => $modules->nav(),
    'sidebar/en.js' => $modules->sidebar(),
] as $file => $module) {
    $file = $filesystemFactory->getFileFromString($vuePressPath . $file);
    if (!$file->exists()) {
        $file->create();
    }
    $file->put(toModuleExport($module));
}
$stylesPath = $vuePressPath . 'styles/';
$indexProjectStyl = $filesystemFactory->getFileFromString($stylesPath . 'index-project.styl');
if ($indexProjectStyl->exists()) {
    $indexStyl = new StreamWriterFromString($stylesPath . 'index.styl', 'a');
    $indexStyl->write("\n\n" . $indexProjectStyl->contents());
}
