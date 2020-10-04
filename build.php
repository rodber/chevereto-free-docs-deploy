<?php

declare(strict_types=1);

use DocsDeploy\MarkdownIterator;
use DocsDeploy\Modules;

use function Chevere\Components\Filesystem\dirForString;
use function Chevere\Components\Filesystem\fileForString;
use function Chevere\Components\Writer\streamFor;
use function DocsDeploy\toModuleExport;

require 'vendor/autoload.php';

$docs = getcwd() . '/docs/';
$sortNavFile = fileForString($docs . 'sortNav.php');
$docsDir = dirForString($docs);
$iterator = new MarkdownIterator($docsDir);
$sortNav = $sortNavFile->exists()
    ? include $sortNavFile->path()->absolute()
    : [];
$modules = new Modules($iterator, $sortNav);
$modules->execute();
$vuePressPath = "$docs.vuepress/";
foreach ([
    // 'nav/en.js' => $modules->nav(),
    'sidebar/en.js' => $modules->sidebar(),
] as $file => $module) {
    $file = fileForString($vuePressPath . $file);
    if (!$file->exists()) {
        $file->create();
    }
    $file->put(toModuleExport($module));
    // xdd(toModuleExport($module));
}
$stylesPath = $vuePressPath . 'styles/';
$indexProjectStyl = fileForString($stylesPath . 'index-project.styl');
if ($indexProjectStyl->exists()) {
    $indexStyl = streamFor($stylesPath . 'index.styl', 'a');
    $indexStyl->write("\n\n" . $indexProjectStyl->contents());
}
