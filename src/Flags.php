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

use Chevere\Filesystem\Interfaces\DirectoryInterface;
use Chevere\Filesystem\Interfaces\DirInterface;
use Countable;

class Flags implements Countable
{
    private DirectoryInterface $dir;

    private bool $hasNested = false;

    private bool $hasReadme = false;

    private array $naming = [];

    private array $sorting = [];

    private int $count = 0;

    public function __construct(DirectoryInterface $dir)
    {
        $this->dir = $dir;
    }

    public function dir(): DirectoryInterface
    {
        return $this->dir;
    }

    public function withAddedMarkdown(): self
    {
        $new = clone $this;
        ++$new->count;

        return $new;
    }

    public function withNested(bool $flag): self
    {
        $new = clone $this;
        $new->hasNested = $flag;

        return $new;
    }

    public function withReadme(bool $flag): self
    {
        $new = clone $this;
        $new->hasReadme = $flag;

        return $new;
    }

    public function withNaming(array $naming): self
    {
        $new = clone $this;
        $new->naming = $naming;

        return $new;
    }

    public function withSorting(array $sorting): self
    {
        $new = clone $this;
        $new->sorting = $sorting;

        return $new;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function hasNested(): bool
    {
        return $this->hasNested;
    }

    public function hasReadme(): bool
    {
        return $this->hasReadme;
    }

    public function sorting(): array
    {
        return $this->sorting;
    }

    public function naming(): array
    {
        return $this->naming;
    }
}
