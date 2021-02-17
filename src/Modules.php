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
    private Iterator $iterator;

    private array $links = [];

    private array $nav = [];

    private array $sidebar = [];

    public function __construct(Iterator $iterator)
    {
        $this->iterator = $iterator;
    }

    public function withAddedNavLink(string $name, string $link): self
    {
        $new = clone $this;
        $new->links[$name] = $link;

        return $new;
    }

    public function execute(): void
    {
        $mainContents = $this->iterator->contents()['/'];
        foreach ($mainContents as $node) {
            if (str_ends_with($node, '/')) {
                $this->nav[] = $this->getNav($node);
            } else {
                continue;
            }

            // $getSidebar = $this->getSidebar($path, $node);
            // $this->sidebar[$path] = empty($getSidebar) ? 'auto' : $getSidebar;
        }
        foreach ($this->links as $name => $link) {
            $this->nav[] = $this->getNavLink($name, $link);
        }
        xdd($this->nav);
        // if (isset($this->sidebar['/'])) {
        //     $rootSidebar = $this->sidebar['/'];
        //     unset($this->sidebar['/']);
        //     $this->sidebar['/'] = $rootSidebar;
        // }
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

    private function getNav(string $node): array
    {
        $title = $this->iterator->flags()['/']->naming()[$node]
            ?? $this->getTitle($node);
        $rootNode = "/${node}";
        $flags = $this->iterator->flags()[$rootNode];
        $contents = $this->iterator->contents()[$rootNode];
        if ($flags->hasReadme()) {
            return $this->getNavLink($title, $rootNode);
        }
        $return = [
            'text' => $title,
            'ariaLabel' => $title . ' Menu',
        ];

        if ($flags->hasNested()) {
            foreach ($contents as $subNode) {
                if (! str_ends_with($subNode, '/')) {
                    continue;
                }
                $subRoot = $rootNode . $subNode;
                $subFlags = $this->iterator->flags()[$subRoot] ?? null;
                $subContents = $this->iterator->contents()[$subRoot];
                $items = [];
                $items[] = $this->getItems($subRoot, $subFlags, $subContents);
                $return['items'][] = [
                    'text' => $flags->naming()[$subNode] ?? $this->getTitle($subNode),
                    'items' => $items,
                ];
            }

            return $return;
        }

        $return['items'] = $this->getItems($rootNode, $flags, $contents);

        return $return;
    }

    private function getItems(string $rootNode, Flags $flags, array $contents): array
    {
        $items = [];
        foreach ($contents as $node) {
            $items[] = $this->getNavLink(
                $flags->naming()[$node] ?? $this->getTitle($node),
                $this->getUsableNode($rootNode . $node)
            );
        }

        return $items;
    }

    private function getSidebar(string $path, array $nodes): array | string
    {
        $title = $this->getTitle($path);
        if (! $this->iterator->flags()[$path]->hasReadme()) {
            return [];
        }
        if (! $this->iterator->flags()[$path]->hasNested()) {
            return [$this->getSidebarFor(
                $title,
                $this->getNodesChildren($path, $nodes)
            )];
        }
        $sidebarPath = $this->iterator->dir()->path()->getChild(ltrim($path, '/') . 'sidebar.php');
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
        $targetPath = $this->iterator->dir()->path()->getChild(ltrim($path, '/'));
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
        return ucfirst(strtr($name, [
            '/' => '',
            '-' => ' ',
            '.md' => '',
        ]));
    }
}
