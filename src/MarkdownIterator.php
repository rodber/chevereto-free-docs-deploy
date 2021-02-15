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

use Chevere\Interfaces\Filesystem\DirInterface;
use Chevere\Interfaces\Writer\WriterInterface;
use RecursiveDirectoryIterator;
use RecursiveFilterIterator;
use RecursiveIteratorIterator;
use UnexpectedValueException;

final class MarkdownIterator
{
    private DirInterface $dir;

    private WriterInterface $logWriter;

    private RecursiveDirectoryIterator $dirIterator;

    private RecursiveFilterIterator $filterIterator;

    private RecursiveIteratorIterator $recursiveIterator;

    private array $hierarchy = [];

    private string $root;

    private string $node;

    /**
     * @var Flags[]
     */
    private array $flagged = [];

    public function __construct(DirInterface $dir, WriterInterface $logWriter)
    {
        $dir->assertExists();
        $this->dir = $dir;
        $this->logWriter = $logWriter;
        $this->dirIterator = $this->getRecursiveDirectoryIterator($dir->path()->toString());
        $this->filterIterator = $this->getRecursiveFilterIterator($this->dirIterator);
        $this->recursiveIterator = new RecursiveIteratorIterator($this->filterIterator);
        $this->chop = strlen($this->dir->path()->toString());

        try {
            $this->recursiveIterator->rewind();
        } catch (UnexpectedValueException $e) {
            $this->logWriter->write(
                'Unable to rewind iterator: ' .
                $e->getMessage() . "\n\n" .
                '🤔 Maybe try with user privileges?'
            );
        }

        $this->logWriter->write('🎾 Iterating ' . $dir->path()->toString() . "\n\n");
        $this->iterate();
    }

    public function dir(): DirInterface
    {
        return $this->dir;
    }

    public function hierarchy(): array
    {
        return $this->hierarchy;
    }

    public function flagged(): array
    {
        return $this->flagged;
    }

    private function iterate(): void
    {
        while ($this->recursiveIterator->valid()) {
            $path = $this->recursiveIterator->current()->getPathName();
            $this->logWriter->write("- ${path}\n");
            $this->setRootNode($path);
            if ($this->node === false) {
                $this->recursiveIterator->next();

                continue;
            }
            $flags = $this->getFlags();
            if ($this->node === 'README.md') {
                $this->node = '';
                $flags = $flags->withReadme(true);
            }
            $this->hierarchy[$this->root][] = $this->node;
            $this->flagged[$this->root] = $flags;
            $this->recursiveIterator->next();
        }
    }

    private function setRootNode(string $path): void
    {
        $path = substr($path, $this->chop);
        $explode = explode('/', $path);
        $this->root = '/';
        $this->node = $explode[0];
        if (isset($explode[1])) {
            $this->root = '/' . $explode[0] . '/';
            $this->node = substr($path, strlen($this->root) - 1);
        }
    }

    private function getFlags(): Flags
    {
        $flags = $this->flagged[$this->root] ?? null;
        if ($flags === null) {
            $flags = new Flags($this->root);
        }
        if (strpos($this->node, '/') !== false) {
            $flags = $flags->withNested(true);
        }

        return $flags;
    }

    private function getRecursiveDirectoryIterator(string $path): RecursiveDirectoryIterator
    {
        return new RecursiveDirectoryIterator(
            $path,
            RecursiveDirectoryIterator::SKIP_DOTS
            | RecursiveDirectoryIterator::KEY_AS_PATHNAME
        );
    }

    private function getRecursiveFilterIterator(RecursiveDirectoryIterator $dirIterator): RecursiveFilterIterator
    {
        return new class($dirIterator) extends RecursiveFilterIterator {
            public function accept(): bool
            {
                if ($this->hasChildren()) {
                    return true;
                }

                return $this->current()->getExtension() === 'md';
            }
        };
    }
}
