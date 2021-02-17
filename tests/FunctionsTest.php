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

use function DocsDeploy\sortArray;
use PHPUnit\Framework\TestCase;

final class FunctionsTest extends TestCase
{
    public function testSortArray(): void
    {
        $unordered = ['two', 'one', 'three'];
        $order = ['one', 'two'];
        $sorted = sortArray($unordered, $order);
        $this->assertSame(
            [
                'one',
                'two',
                'three',
            ],
            $sorted
        );
    }
}
