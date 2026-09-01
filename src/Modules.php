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

use function Chevere\Filesystem\filePhpReturnForPath;

class Modules
{
    private Iterator $iterator;

    private array $links = [];

    private array $nav = [];

    private array $side = [];

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
        $mainFlags = $this->iterator->flags()['/'];
        $mainFiles = [];
        foreach ($mainContents as $node) {
            if (! str_ends_with($node, '/')) {
                $mainFiles[] = $node;

                continue;
            }
            $rootNode = "/{$node}";
            if (! isset($this->iterator->flags()[$rootNode])) {
                continue;
            }
            $this->setNavFor($node);
            $this->setSideFor($node);
        }
        foreach ($this->links as $name => $link) {
            $this->nav[] = $this->getNavLink($name, $link);
        }
        $this->side['/'] = $this->getSide('/', $mainFlags, $mainFiles);
    }

    public function nav(): array
    {
        return $this->nav;
    }

    public function side(): array
    {
        return $this->side;
    }

    public function getUsableNode(string $node): string
    {
        return strtr($node, [
            'README.md' => '',
            '.md' => '',
        ]);
    }

    private function setSideFor(string $node): void
    {
        $side = 'auto';
        $rootNode = "/{$node}";
        $flags = $this->iterator->flags()[$rootNode] ?? null;
        if ($flags === null) {
            return;
        }
        $contents = $this->iterator->contents()[$rootNode];
        $filepath = $flags->dir()->path()->getChild('sidebar.php');
        if ($filepath->exists()) {
            $filePhp = filePhpReturnForPath($filepath->__toString());
            $side = $filePhp->get();
        } elseif ($flags->hasNested() || $flags->hasReadme()) {
            $side = $this->getSide($rootNode, $flags, $contents);
        }
        $this->side["/{$node}"] = $side;
    }

    private function setNavFor(string $node): void
    {
        $title = $this->iterator->flags()['/']->naming()[$node]
            ?? $this->getTitle($node);
        $rootNode = "/{$node}";
        $flags = $this->iterator->flags()[$rootNode];
        $contents = $this->iterator->contents()[$rootNode];
        if ($flags !== null && $flags->hasReadme()) {
            $this->nav[] = $this->getNavLink($title, $rootNode);

            return;
        }
        $navMenu = [
            'text' => $title,
            'ariaLabel' => $title . ' Menu',
        ];
        $files = [];
        foreach ($contents as $subNode) {
            if (! str_ends_with($subNode, '/')) {
                $files[] = $subNode;

                continue;
            }
        }
        if ($files === []) {
            foreach ($contents as $subNode) {
                $subRoot = $rootNode . $subNode;
                $subFlags = $this->iterator->flags()[$subRoot] ?? null;
                $subContents = $this->iterator->contents()[$subRoot];
                $title = $flags->naming()[$subNode] ?? $this->getTitle($subNode);
                $navMenu['items'][] = [
                    'text' => $title,
                    'items' => $this->getNavItems($subRoot, $subFlags, $subContents),
                ];
            }
            $this->nav[] = $navMenu;

            return;
        }
        $navMenu['items'] = $this->getNavItems($rootNode, $flags, $files);
        $this->nav[] = $navMenu;
    }

    private function getNavItems(string $rootNode, Flags $flags, array $contents): array
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

    private function getSide(string $rootNode, Flags $flags, array $contents): array
    {
        $main = [];
        $items = [];
        foreach ($contents as $node) {
            $usableNode = $this->getUsableNode($node);
            $naming = $flags->naming()[$node] ?? $this->getTitle($usableNode);
            if (str_ends_with($node, '/')) {
                $nodes = $this->iterator->contents()[$rootNode . $node];
                $children = [];
                foreach ($nodes as $subNode) {
                    $usableSubNode = $this->getUsableNode($subNode);
                    $nodeFlags = $this->iterator->flags()[$rootNode . $node];
                    $namingSubNode = $nodeFlags->naming()[$subNode] ?? $this->getTitle($usableSubNode);
                    $children[] = $this->getChild($rootNode . $node, $usableSubNode, $namingSubNode);
                }
                $items[] = [
                    'title' => $naming,
                    'collapsable' => false,
                    'children' => $children,
                ];
            } else {
                $main[] = $this->getChild($rootNode, $usableNode, $naming);
            }
        }
        $nodeInRoot = ltrim($rootNode, '/');
        array_unshift($items, [
            'title' => $this->iterator->flags()['/']->naming()[$nodeInRoot] ?? $this->getTitle($nodeInRoot),
            'collapsable' => false,
            'children' => $main,
        ]);

        return $items;
    }

    private function getChild($root, $node, $naming): array | string
    {
        $return = $root . $node;
        if ($naming === '' && $node === '') {
            return $return;
        }

        return [$return, $naming];
    }

    private function getNavLink(string $name, string $link): array
    {
        return [
            'text' => $name,
            'link' => $link,
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
