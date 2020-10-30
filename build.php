<?php

declare(strict_types=1);

use DocsDeploy\MarkdownIterator;
use DocsDeploy\Modules;

use function Chevere\Components\Filesystem\dirForPath;
use function Chevere\Components\Filesystem\fileForPath;
use function Chevere\Components\Writer\streamFor;
use function DocsDeploy\toModuleExport;

require 'vendor/autoload.php';

$docs = getcwd() . '/docs/';
$sortNavFile = fileForPath($docs . 'sortNav.php');
$docsDir = dirForPath($docs);
$iterator = new MarkdownIterator($docsDir);
$sortNav = $sortNavFile->exists()
    ? include $sortNavFile->path()->absolute()
    : [];
$modules = new Modules($iterator, $sortNav);
$modules->execute();
$vuePressPath = "$docs.vuepress/";
foreach ([
    'nav/en.js' => $modules->nav(),
    'sidebar/en.js' => $modules->sidebar(),
] as $file => $module) {
    $file = fileForPath($vuePressPath . $file);
    if (!$file->exists()) {
        $file->create();
    }
    $file->put(toModuleExport($module));
}
$stylesPath = $vuePressPath . 'styles/';
$indexProjectStyl = fileForPath($stylesPath . 'index-project.styl');
if ($indexProjectStyl->exists()) {
    $indexStyl = streamFor($stylesPath . 'index.styl', 'a');
    $indexStyl->write("\n\n" . $indexProjectStyl->contents());
}
