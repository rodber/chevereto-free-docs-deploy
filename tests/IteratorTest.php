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

use function Chevere\Filesystem\directoryForPath;
use function Chevere\Filesystem\dirForPath;
use function Chevere\Writer\streamTemp;
use Chevere\Writer\StreamWriter;
use Chevere\Writer\Interfaces\WriterInterface;
use DocsDeploy\Iterator;
use PHPUnit\Framework\TestCase;

final class IteratorTest extends TestCase
{
    public function testConstruct(): void
    {
        $dir = directoryForPath(__DIR__ . '/_resources/docs/');
        $writer = new StreamWriter(streamTemp(''));
        $iterator = new Iterator($dir, $writer);
        $this->assertSame($dir, $iterator->dir());
    }

    public function testDocs(): void
    {
        $iterator = $this->getIterator('');
        $this->assertSame(
            [
                'README.md',
                'files/',
                'files-readme/',
                'files-readme-sub-folders/',
                'sub-folders/',
            ],
            $iterator->contents()['/']
        );
        $flags = $iterator->flags()['/'];
        $this->assertTrue($flags->hasNested());
        $this->assertTrue($flags->hasReadme());
        $this->assertSame(
            [
                'files/' => 'Archivos',
                'files-readme/' => 'Archivos Léame',
            ],
            $flags->naming()
        );
        $this->assertSame(
            [
                'README.md',
                'files/',
                'files-readme/',
                'files-readme-sub-folders/',
                'sub-folders/',
            ],
            $flags->sorting()
        );
    }

    public function testFiles(): void
    {
        $iterator = $this->getIterator('files/');
        $this->assertSame(
            [
                'file-1.md',
                'file-2.md',
            ],
            $iterator->contents()['/']
        );
        $flags = $iterator->flags()['/'];
        $this->assertFalse($flags->hasNested());
        $this->assertFalse($flags->hasReadme());
    }

    public function testFilesReadme(): void
    {
        $iterator = $this->getIterator('files-readme/');
        $this->assertSame(
            [
                'README.md',
                'file-1.md',
                'file-2.md',
            ],
            $iterator->contents()['/']
        );
        $flags = $iterator->flags()['/'];

        $this->assertFalse($flags->hasNested());
        $this->assertTrue($flags->hasReadme());
    }

    public function testFilesReadmeSubFolders(): void
    {
        $iterator = $this->getIterator('files-readme-sub-folders/');
        $this->assertSame(
            [
                'README.md',
                'file-1.md',
                'folder-1/',
                'folder-2/',
            ],
            $iterator->contents()['/']
        );
        $flags = $iterator->flags()['/'];
        $this->assertTrue($flags->hasNested());
        $this->assertTrue($flags->hasReadme());
    }

    public function testSubFolders(): void
    {
        $iterator = $this->getIterator('sub-folders/');
        $this->assertSame(
            [
                'folder-1/',
                'folder-2/',
            ],
            $iterator->contents()['/']
        );
        $flags = $iterator->flags()['/'];
        $this->assertTrue($flags->hasNested());
        $this->assertFalse($flags->hasReadme());
    }

    // public function testEmptySubFolders(): void
    // {
    //     $iterator = $this->getIterator('sub-folders/');
    //     // xdd($iterator->contents());
    //     $this->assertSame(
    //         [],
    //         $iterator->contents()['/']
    //     );
    //     $flags = $iterator->flags()['/'];
    //     $this->assertTrue($flags->hasNested());
    //     $this->assertFalse($flags->hasReadme());
    // }

    private function getIterator(string $path, WriterInterface $writer = null): Iterator
    {
        return new Iterator(
            directoryForPath(__DIR__ . '/_resources/docs/' . $path),
            $writer ?? new StreamWriter(streamTemp(''))
        );
    }
}
