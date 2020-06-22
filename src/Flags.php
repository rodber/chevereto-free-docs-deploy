<?php

declare(strict_types=1);

class Flags
{
    private bool $hasNested = false;

    private bool $hasReadme = false;

    public function withNested(): Flags
    {
        $new = clone $this;
        $new->hasNested = true;

        return $new;
    }

    public function withReadme(): Flags
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
