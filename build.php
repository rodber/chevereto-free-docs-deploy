<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use function Chevere\Components\Filesystem\dirForPath;
use function Chevere\Components\Filesystem\fileForPath;
use function Chevere\Components\Writer\streamFor;
use Chevere\Components\Writer\StreamWriter;
use DocsDeploy\Iterator;
use DocsDeploy\Modules;
use function DocsDeploy\toModuleExport;

require 'vendor/autoload.php';

$docs = getcwd() . '/docs/';
$docsDir = dirForPath($docs);
$logger = new StreamWriter(streamFor('php://stdout', 'w'));
$iterator = new Iterator($docsDir, $logger);
$modules = new Modules($iterator);
$modules->execute();
$vuePressPath = "${docs}.vuepress/";
foreach ([
    'nav/en.js' => $modules->nav(),
    'sidebar/en.js' => $modules->side(),
] as $file => $module) {
    $file = fileForPath($vuePressPath . $file);
    if (! $file->exists()) {
        $file->create();
    }
    $file->put(toModuleExport($module));
}
$stylesPath = $vuePressPath . 'styles/';
$stylExt = '.styl';
foreach (['index', 'palette'] as $styl) {
    $stylDefaultFile = fileForPath($stylesPath . 'default-' . $styl . $stylExt);
    $stylFile = fileForPath($stylesPath . $styl . $stylExt);
    if ($stylFile->exists() && $stylDefaultFile->exists()) {
        $defaults = $stylDefaultFile->contents();
        $customs = $stylFile->contents();
        $stream = streamFor($stylFile->path()->toString(), 'w');
        $stream->write($defaults . "\n\n" . $customs);
    }
}
