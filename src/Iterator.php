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

use function Chevere\Components\Filesystem\filePhpReturnForPath;
use Chevere\Interfaces\Filesystem\DirInterface;
use Chevere\Interfaces\Writer\WriterInterface;
use RecursiveDirectoryIterator;
use RecursiveFilterIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use UnexpectedValueException;

final class Iterator
{
    public const NAMING = [];

    public const SORTING = ['README.md'];

    private DirInterface $dir;

    private WriterInterface $writer;

    private RecursiveDirectoryIterator $dirIterator;

    private RecursiveFilterIterator $filterIterator;

    private RecursiveIteratorIterator $recursiveIterator;

    private array $contents = [];

    private string $root;

    private string $node;

    private SplFileinfo $current;

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
            $this->current = $this->recursiveIterator->current();
            $this->writer->write('- ' . $this->current->getPathname() . "\n");
            $this->setRootNode();
            $this->setFlags();
            $this->contents[$this->root][] = $this->node;
            $this->recursiveIterator->next();
        }
        $this->setRootFlags();
        $this->processFileFlags();
    }

    private function processFileFlags(): void
    {
        // xdd(array_keys($this->contents));
        foreach ($this->contents as $key => $nodes) {
            $this->flags[$key] = $this->getDirFlags(
                $this->flags[$key],
            );
            $this->contents[$key] = sortArray($nodes, $this->flags[$key]->sorting());
        }
    }

    private function setRootFlags(): void
    {
        if (isset($this->contents['/'])) {
            $this->flags['/'] = $this->flags['/']
                ->withNested(count($this->contents) > 1);
        }
    }

    private function setFlags(): void
    {
        $flags = $this->getFlags();
        if ($this->node === 'README.md') {
            $flags = $flags->withReadme(true);
        }
        $this->flags[$this->root] = $flags;
    }

    private function getDirFlags(Flags $flags): Flags
    {
        foreach (['naming', 'sorting'] as $flagger) {
            $filepath = $flags->dir()->path()->getChild($flagger . '.php');
            $return = null;
            if ($filepath->exists()) {
                $filePhp = filePhpReturnForPath($filepath->toString())->withStrict(false);
                $return = $filePhp->var();
            }
            $values[$flagger] = $return;
        }

        return $flags
            ->withNaming($values['naming'] ?? self::NAMING)
            ->withSorting($values['sorting'] ?? self::SORTING);
    }

    private function setRootNode(): void
    {
        $this->root = '/';
        $path = substr($this->current->getPathname(), $this->chop);
        $this->node = basename($path);
        if ($this->current->isDir()) {
            $this->node .= '/';
        }
        if (str_contains($path, '/')) {
            $this->root = '/' . dirname($path) . '/';
        }
    }

    private function getFlags(): Flags
    {
        $flags = $this->flags[$this->root]
            ?? new Flags($this->dir()->getChild(ltrim($this->root, '/')));
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
