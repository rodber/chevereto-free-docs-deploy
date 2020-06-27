<?php

declare(strict_types=1);

namespace DocsDeploy;

class Modules
{
    private array $hierarchy;

    private array $flagged;

    private array $sortNav;

    private array $links = [];

    private array $nav = [];

    private array $sidebar = [];

    public function __construct(MarkdownIterator $markdownIterator, array $sortNav)
    {
        $this->hierarchy = $markdownIterator->hierarchy();
        $this->flagged = $markdownIterator->flagged();
        $this->sortNav = $sortNav;
    }

    public function withAddedNavLink(string $name, string $link): Modules
    {
        $new = clone $this;
        $new->links[$name] = $link;

        return $new;
    }

    public function execute(): void
    {
        $sortedNav = (new SortArray($this->hierarchy, $this->sortNav))->toArray();
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

    private function getNav(string $path, array $nodes): array
    {
        $title = $this->getTitle($path);
        if ($this->flagged[$path]->hasReadme()) {
            return $this->getNavLink($title, $path);
        }
        $array = [
            'text' => $title,
            'ariaLabel' => $title . ' Menu'
        ];
        foreach ($nodes as $nodeName) {
            $array['items'][] = $this->getNavLink(
                $this->getTitle($nodeName),
                $this->getUsableNode($path . $nodeName)
            );
        }

        return $array;
    }

    private function getSidebar(string $path, array $nodes): array
    {
        $title = $this->getTitle($path);
        if (!$this->flagged[$path]->hasReadme()) {
            return [];
        }
        if (!$this->flagged[$path]->hasNested()) {
            return [$this->getSidebarFor(
                $title,
                $this->getNodesChildren($nodes)
            )];
        }
        $sidebar = [];
        $nested = $this->getNestedHierarchy($nodes);
        foreach ($nested as $nestedName => $nestedNodes) {
            $getSidebar = $this->getSidebarFor(
                $this->getTitle($nestedName),
                $this->getNodesChildren($nestedNodes)
            );
            $sidebar[] = empty($getSidebar) ? 'auto'.$nestedName : $getSidebar;
        };

        return $sidebar;
    }

    private function getNavLink(string $name, string $link): array
    {
        return [
            'text' => $name,
            'link' => $link
        ];
    }

    private function getNodesChildren(array $nodes): array
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
            'children' => $children
        ];
    }

    private function getTitle(string $name): string
    {
        return ucwords(strtr($name, [
            '/' => '',
            '-' => ' ',
            '.md' => ''
        ]));
    }

    public function getUsableNode(string $node): string
    {
        return strtr($node, [
            'README.md' => '',
            '.md' => ''
        ]);
    }
}
