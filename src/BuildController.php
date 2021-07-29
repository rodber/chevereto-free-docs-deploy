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

use Chevere\Components\Controller\Controller;
use function Chevere\Components\Filesystem\dirForPath;
use function Chevere\Components\Filesystem\fileForPath;
use function Chevere\Components\Parameter\parameters;
use function Chevere\Components\Parameter\stringParameter;

use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use function Chevere\Components\Writer\streamFor;
use Chevere\Components\Writer\StreamWriter;
use Chevere\Interfaces\Filesystem\DirInterface;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Writer\WriterInterface;

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
                $defaults = $stylDefaultFile->contents();
                $customs = $stylFile->contents();
                $stream = streamFor($stylFile->path()->toString(), 'w');
                $stream->write($defaults . "\n\n" . $customs);
                $this->writer->write('- ' . $stylFile->path()->toString() . "\n");
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
            $this->writer->write('- ' . $file->path()->toString() . "\n");
        }
    }
}
