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

class Flags
{
    private bool $hasNested = false;

    private bool $hasReadme = false;

    public function withNested(): self
    {
        $new = clone $this;
        $new->hasNested = true;

        return $new;
    }

    public function withReadme(): self
    {
        $new = clone $this;
        $new->hasReadme = true;

        return $new;
    }

    public function hasNested(): bool
    {
        return $this->hasNested;
    }

    public function hasReadme(): bool
    {
        return $this->hasReadme;
    }
}
