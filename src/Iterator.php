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
use SplFileInfo;
use UnexpectedValueException;

final class Iterator
{
    private DirInterface $dir;

    private WriterInterface $writer;

    private RecursiveDirectoryIterator $dirIterator;

    private RecursiveFilterIterator $filterIterator;

    private RecursiveIteratorIterator $recursiveIterator;

    private array $contents = [];

    private string $root;

    private string $node;

    /**
     * @var Flags[]
     */
    private array $flags = [];

    public function __construct(DirInterface $dir, WriterInterface $writer)
    {
        $dir->assertExists();
        $this->dir = $dir;
        $this->writer = $writer;
        $this->dirIterator = $this->getRecursiveDirectoryIterator($dir->path()->toString());
        $this->filterIterator = $this->getRecursiveFilterIterator($this->dirIterator);
        $this->recursiveIterator = new RecursiveIteratorIterator($this->filterIterator, RecursiveIteratorIterator::SELF_FIRST);
        $this->chop = strlen($this->dir->path()->toString());

        try {
            $this->recursiveIterator->rewind();
        }
        // @codeCoverageIgnoreStart
        catch (UnexpectedValueException $e) {
            $this->writer->write(
                'Unable to rewind iterator: ' .
                $e->getMessage() . "\n\n" .
                '🤔 Maybe try with user privileges?'
            );
        }
        // @codeCoverageIgnoreEnd

        $this->writer->write('🎾 Iterating ' . $dir->path()->toString() . "\n\n");
        $this->iterate();
    }

    public function dir(): DirInterface
    {
        return $this->dir;
    }

    public function contents(): array
    {
        return $this->contents;
    }

    /**
     * @return Flags[]
     */
    public function flags(): array
    {
        return $this->flags;
    }

    private function iterate(): void
    {
        while ($this->recursiveIterator->valid()) {
            unset($this->path, $this->root, $this->node);
            /** @var SplFileinfo $file */
            $file = $this->recursiveIterator->current();
            $this->writer->write('- ' . $file->getPathname() . "\n");
            $this->setRootNode($file);
            $flags = $this->getFlags();
            if ($this->node === 'README.md') {
                $flags = $flags->withReadme(true);
            }
            $this->contents[$this->root][] = $this->node;
            $this->flags[$this->root] = $flags;
            $this->recursiveIterator->next();
        }
        if (isset($this->contents['/']) && count($this->contents) > 1) {
            $this->flags['/'] = $this->flags['/']
                ->withNested(true);
        }
    }

    private function setRootNode(SplFileInfo $node): void
    {
        $path = $node->getPathname();
        $this->path = substr($path, $this->chop);
        $this->root = '/';
        $this->node = basename($this->path);
        if ($node->isDir()) {
            $this->node = $this->node . '/';
        }
        if (str_contains($this->path, '/')) {
            $this->root = '/' . dirname($this->path) . '/';
        }
    }

    private function getFlags(): Flags
    {
        $flags = $this->flags[$this->root] ?? new Flags($this->root);
        if (str_contains(trim($this->root, '/'), '/')) {
            $parent = dirname($this->root) . '/';
            $this->flags[$parent] = $this->flags[$parent]
                ->withNested(isset($this->flags[$parent]));
        }

        return $flags;
    }

    private function getRecursiveDirectoryIterator(string $path): RecursiveDirectoryIterator
    {
        return new RecursiveDirectoryIterator(
            $path,
            RecursiveDirectoryIterator::SKIP_DOTS |
            RecursiveDirectoryIterator::KEY_AS_PATHNAME
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
