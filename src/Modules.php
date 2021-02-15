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

use Chevere\Components\Filesystem\File;
use Chevere\Components\Filesystem\FilePhp;
use Chevere\Components\Filesystem\FilePhpReturn;
use Chevere\Components\Message\Message;
use Chevere\Exceptions\Core\TypeException;

class Modules
{
    private MarkdownIterator $markdownIterator;

    private array $sortNav;

    private array $links = [];

    private array $nav = [];

    private array $sidebar = [];

    public function __construct(MarkdownIterator $markdownIterator, array $sortNav)
    {
        $this->markdownIterator = $markdownIterator;
        $this->sortNav = $sortNav;
    }

    public function withAddedNavLink(string $name, string $link): self
    {
        $new = clone $this;
        $new->links[$name] = $link;

        return $new;
    }

    public function execute(): void
    {
        $sortedNav = sortArray($this->markdownIterator->hierarchy(), $this->sortNav);
        foreach ($sortedNav as $path => $nodes) {
            asort($nodes);
            if ($path !== '/') {
                $this->nav[] = $this->getNav($path, $nodes);
            }
            $getSidebar = $this->getSidebar($path, $nodes);
            $this->sidebar[$path] = empty($getSidebar) ? 'auto' : $getSidebar;
        }
        if (isset($this->sidebar['/'])) {
            $rootSidebar = $this->sidebar['/'];
            unset($this->sidebar['/']);
            $this->sidebar['/'] = $rootSidebar;
        }
        foreach ($this->links as $name => $link) {
            $this->nav[] = $this->getNavLink($name, $link);
        }
    }

    public function nav(): array
    {
        return $this->nav;
    }

    public function sidebar(): array
    {
        return $this->sidebar;
    }

    public function getUsableNode(string $node): string
    {
        return strtr($node, [
            'README.md' => '',
            '.md' => '',
        ]);
    }

    private function getNav(string $path, array $nodes): array
    {
        $title = $this->getTitle($path);
        if ($this->markdownIterator->flagged()[$path]->hasReadme()) {
            return $this->getNavLink($title, $path);
        }
        $array = [
            'text' => $title,
            'ariaLabel' => $title . ' Menu',
        ];
        foreach ($nodes as $nodeName) {
            $link = $this->getUsableNode($path . $nodeName);
            if (count(explode('/', $link)) > 3) {
                continue;
            }
            $array['items'][] = $this->getNavLink(
                $this->getTitle($nodeName),
                $link
            );
        }

        return $array;
    }

    private function getSidebar(string $path, array $nodes): array | string
    {
        $title = $this->getTitle($path);
        if (! $this->markdownIterator->flagged()[$path]->hasReadme()) {
            return [];
        }
        if (! $this->markdownIterator->flagged()[$path]->hasNested()) {
            return [$this->getSidebarFor(
                $title,
                $this->getNodesChildren($path, $nodes)
            )];
        }
        $sidebarPath = $this->markdownIterator->dir()->path()->getChild(ltrim($path, '/') . 'sidebar.php');
        if ($sidebarPath->exists()) {
            return include $sidebarPath->toString();
        }
        $sidebar = [];
        $nested = $this->getNestedHierarchy($nodes);
        foreach ($nested as $nestedName => $nestedNodes) {
            if (count(explode('/', $nestedNodes[0])) > 2) {
                continue;
            }
            $getSidebar = $this->getSidebarFor(
                $this->getTitle($nestedName),
                $this->getNodesChildren($path, $nestedNodes)
            );
            $sidebar[] = empty($getSidebar) ? 'auto' : $getSidebar;
        }

        return $sidebar;
    }

    private function getNavLink(string $name, string $link): array
    {
        return [
            'text' => $name,
            'link' => $link,
        ];
    }

    private function getNodesChildren(string $path, array $nodes): array
    {
        $hasReadme = false;
        $children = [];
        foreach ($nodes as $node) {
            if ($node === '') {
                $hasReadme = true;

                continue;
            }
            $children[] = $this->getUsableNode($node);
        }
        if ($hasReadme) {
            $children = array_merge([''], $children);
        }
        $targetPath = $this->markdownIterator->dir()->path()->getChild(ltrim($path, '/'));
        $childrenFile = new File($targetPath->getChild('children.php'));
        if ($childrenFile->exists()) {
            $declaredChildren = (new FilePhpReturn(new FilePhp($childrenFile)))->var();
            if (! is_array($declaredChildren)) {
                throw new TypeException(
                    (new Message('Expecting a file-return array file, %type% provided'))
                        ->code('%type%', get_debug_type($declaredChildren))
                );
            }
            foreach ($declaredChildren as $k => $v) {
                if (! in_array($v, $children, true)) {
                    unset($declaredChildren[$k]);
                }
            }
            $ordered = array_flip(array_replace(array_flip($declaredChildren), array_flip($children)));
            $children = array_values($ordered);
        }

        return $children;
    }

    private function getNestedHierarchy(array $nodes): array
    {
        $hierarchy = [];
        foreach ($nodes as $node) {
            $explode = explode('/', $node);
            $root = $explode[0];
            if ($root === '') {
                continue;
            }
            $node = $this->getUsableNode($node);
            $hierarchy[$root][] = $node;
        }

        return $hierarchy;
    }

    private function getSidebarFor(string $title, array $children): array
    {
        return [
            'title' => $title,
            'collapsable' => false,
            'children' => $children,
        ];
    }

    private function getTitle(string $name): string
    {
        return ucwords(strtr($name, [
            '/' => '',
            '-' => ' ',
            '.md' => '',
        ]));
    }
}
