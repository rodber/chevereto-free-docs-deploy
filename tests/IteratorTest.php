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

namespace Chevere\Tests;

use function Chevere\Components\Filesystem\dirForPath;
use function Chevere\Components\Writer\streamTemp;
use Chevere\Components\Writer\StreamWriter;
use DocsDeploy\Iterator;
use PHPUnit\Framework\TestCase;

final class IteratorTest extends TestCase
{
    public function testFiles(): void
    {
        $dir = dirForPath(__DIR__ . '/_resources/docs/');
        $writer = new StreamWriter(streamTemp(''));
        $iterator = new Iterator($dir, $writer);
        xdd($iterator->hierarchy(), $iterator->flagged());
    }
}
