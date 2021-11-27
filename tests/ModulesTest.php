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
use DocsDeploy\Modules;
use PHPUnit\Framework\TestCase;

final class ModulesTest extends TestCase
{
    public function testConstruct(): void
    {
        // $dir = dirForPath(__DIR__ . '/_resources/docs/');
        $dir = dirForPath('/home/rodolfo/git/chevere/docs-deploy/docs/');
        $writer = new StreamWriter(streamTemp(''));
        $iterator = new Iterator($dir, $writer);
        $modules = new Modules($iterator);
        $modules->execute();
    }
}
