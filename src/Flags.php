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

    public function hasNested(): bool
    {
        return $this->hasNested;
    }

    public function hasReadme(): bool
    {
        return $this->hasReadme;
    }
}
