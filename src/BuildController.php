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

namespace DocsDeploy;

use Chevere\Controller\Controller;
use function Chevere\Filesystem\dirForPath;
use function Chevere\Filesystem\fileForPath;
use function Chevere\Parameter\parameters;
use function Chevere\Parameter\stringParameter;
use Chevere\Parameter\Parameters;
use Chevere\Parameter\StringParameter;
use function Chevere\Writer\streamFor;
use Chevere\Writer\StreamWriter;
use Chevere\Filesystem\Interfaces\DirInterface;
use Chevere\Parameter\Interfaces\ArgumentsInterface;
use Chevere\Parameter\Interfaces\ParametersInterface;
use Chevere\Response\Interfaces\ResponseInterface;
use Chevere\Writer\Interfaces\WriterInterface;

class BuildController extends Controller
{
    private WriterInterface $writer;

    private DirInterface $dir;

    private string $vuePressPath = '';

    public function getParameters(): ParametersInterface
    {
        return parameters(
            dir: stringParameter(
                description: 'Directory for VuePress-based documentation',
            ),
            stream: stringParameter(
                description: 'Stream to write log (w)',
                default: 'php://stdout',
            )
        );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $dir = $arguments->getString('dir');
        $this->dir = dirForPath($dir);
        $stream = $arguments->getString('stream');
        $this->writer = new StreamWriter(streamFor($stream, 'w'));
        $this->vuePressPath = "${dir}.vuepress/";
        $this->processModules();
        $this->processStyles();
        $this->writer->write("\n✨ Complete");

        return $this->getResponse();
    }

    private function processStyles(): void
    {
        $stylesPath = $this->vuePressPath . 'styles/';
        $stylExt = '.styl';
        $this->writer->write("\n🎨 Merging styles\n\n");
        foreach (['index', 'palette'] as $styl) {
            $stylDefaultFile = fileForPath($stylesPath . 'default-' . $styl . $stylExt);
            $stylFile = fileForPath($stylesPath . $styl . $stylExt);
            if ($stylFile->exists() && $stylDefaultFile->exists()) {
                $defaults = $stylDefaultFile->getContents();
                $customs = $stylFile->getContents();
                $stream = streamFor($stylFile->path()->__toString(), 'w');
                $stream->write($defaults . "\n\n" . $customs);
                $this->writer->write('- ' . $stylFile->path()->__toString() . "\n");
            }
        }
    }

    private function processModules(): void
    {
        $iterator = new Iterator($this->dir, $this->writer);
        $modules = new Modules($iterator);
        $modules->execute();
        $this->writer->write("\n🌈 Doing nav and sidebar modules\n\n");
        foreach ([
            'nav/en.js' => $modules->nav(),
            'sidebar/en.js' => $modules->side(),
        ] as $file => $module) {
            $file = fileForPath($this->vuePressPath . $file);
            if (! $file->exists()) {
                $file->create();
            }
            $file->put(toModuleExport($module));
            $this->writer->write('- ' . $file->path()->__toString() . "\n");
        }
    }
}
