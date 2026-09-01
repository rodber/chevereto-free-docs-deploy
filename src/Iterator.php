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

use function Chevere\Filesystem\directoryForPath;
use function Chevere\Filesystem\dirForPath;
use function Chevere\Filesystem\filePhpReturnForPath;

use Chevere\Filesystem\Interfaces\DirectoryInterface;
use Chevere\Filesystem\Interfaces\DirInterface;
use Chevere\Writer\Interfaces\WriterInterface;
use RecursiveDirectoryIterator;
use RecursiveFilterIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use UnexpectedValueException;

final class Iterator
{
    public const NAMING = [];

    public const SORTING = ['README.md'];

    private DirectoryInterface $dir;

    private WriterInterface $writer;

    private RecursiveDirectoryIterator $dirIterator;

    private RecursiveFilterIterator $filterIterator;

    private RecursiveIteratorIterator $recursiveIterator;

    private array $contents = [];

    private string $root;

    private string $node;

    private SplFileinfo $current;

    private int $chop;

    /**
     * @var Flags[]
     */
    private array $flags = [];

    public function __construct(DirectoryInterface $dir, WriterInterface $writer)
    {
        $dir->assertExists();
        $this->dir = $dir;
        $this->writer = $writer;
        $this->dirIterator = $this->getRecursiveDirectoryIterator($dir->path()->__toString());
        $this->filterIterator = $this->getRecursiveFilterIterator($this->dirIterator);
        $this->recursiveIterator = new RecursiveIteratorIterator($this->filterIterator, RecursiveIteratorIterator::SELF_FIRST);
        $this->chop = strlen($this->dir->path()->__toString());

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

        $this->writer->write('👀 Iterating ' . $dir->path()->__toString() . "\n\n");
        $this->iterate();
    }

    public function dir(): DirectoryInterface
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
        if (isset($this->contents['/'])) {
            $this->flags['/'] = $this->flags['/']
                ->withNested(count($this->contents) > 1);
        }
        $this->stripEmpty();
        $this->fixContents();
    }

    private function stripEmpty(): void
    {
        foreach ($this->contents as $root => &$nodes) {
            foreach ($nodes as &$node) {
                $flags = $this->flags['/' . $node] ?? null;
                if (isset($flags) && count($flags) === 0) {
                    $search = array_search($node, $nodes, true);
                    unset($nodes[$search], $this->contents['/' . $node]);
                }
            }
        }
    }

    private function fixContents(): void
    {
        foreach ($this->contents as $key => $nodes) {
            $flags = $this->flags[$key];
            $this->flags[$key] = $this->getDirFlags($flags);
            asort($nodes);
            $this->contents[$key] = sortArray($nodes, $this->flags[$key]->sorting());
        }
    }

    private function setFlags(): void
    {
        $flags = $this->getFlags();
        if ($this->node === 'README.md') {
            $flags = $flags->withReadme(true);
        }
        if ($this->current->isFile()) {
            $flags = $flags->withAddedMarkdown();
        }
        $this->flags[$this->root] = $flags;
    }

    private function getDirFlags(Flags $flags): Flags
    {
        foreach (['naming', 'sorting'] as $flagger) {
            $filepath = $flags->dir()->path()->getChild($flagger . '.php');
            $return = null;
            if ($filepath->exists()) {
                $filePhp = filePhpReturnForPath($filepath->__toString());
                $return = $filePhp->get();
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
        $parent = rtrim(dirname($this->root), '/') . '/';
        if (str_contains(trim($this->root, '/'), '/')) {
            $this->flags[$parent] = $this->flags[$parent]
                ->withNested(isset($this->flags[$parent]));
        }
        if ($this->current->isFile()) {
            if (! isset($this->flags[$parent])) {
                $this->flags[$parent] = new Flags(directoryForPath($parent));
            }
            $this->flags[$parent] = $this->flags[$parent]
                ->withAddedMarkdown();
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
                if (str_starts_with($this->current()->getBasename(), '.')) {
                    return false;
                }
                if ($this->hasChildren()) {
                    return true;
                }

                return $this->current()->getExtension() === 'md';
            }
        };
    }
}
