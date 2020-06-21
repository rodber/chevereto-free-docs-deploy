<?php

declare(strict_types=1);

require 'Flags.php';

/**
 * Cases
 *
 *  Defaults: Top level folder = nav.js as link
 *
 * - No README.md: nav.js as menu, sidebar.js as auto (components)
 * - README.md + only top level pages: sidebar.js children ['', <etc>] (get-stared, application)
 * - README.md + levels: sidebar.js multiple sidebars, arch
 */
class MarkdownIterator
{
    const NAV_ORDER = ['get-started', 'architecture', 'application', 'components'];
    
    private string $path;

    private RecursiveDirectoryIterator $dirIterator;

    private RecursiveFilterIterator $filterIterator;

    private RecursiveIteratorIterator $recursiveIterator;

    private array $hierarchy = [];

    /**
     * @var Flags[]
     */
    private array $flagged = [];

    private array $links = [];

    private array $nav = [];

    private array $sidebar = [];

    public function __construct(string $path)
    {
        $this->path = rtrim(realpath($path), '/') . '/';
        $this->dirIterator = $this->getRecursiveDirectoryIterator($path);
        $this->filterIterator = $this->getRecursiveFilterIterator($this->dirIterator);
        $this->recursiveIterator = new RecursiveIteratorIterator($this->filterIterator);
        try {
            $this->recursiveIterator->rewind();
        } catch (UnexpectedValueException $e) {
            echo 'Unable to rewind iterator: '
                . $e->getMessage() . "\n\n"
                . '🤔 Maybe try with user privileges?';
        }
    }

    public function withAddedLink(string $name, string $link): MarkdownIterator
    {
        $new = clone $this;
        $new->links[$name] = $link;

        return $new;
    }

    public function execute(): void
    {
        $this->iterate();
        $ordered = array_merge(array_flip(self::NAV_ORDER), $this->hierarchy);
        foreach ($ordered as $name => $nodes) {
            asort($nodes);
            $this->nav[] = $this->getNav($name, $nodes);
            $getSidebar = $this->getSidebar($name, $nodes);
            $this->sidebar["/$name/"] = empty($getSidebar) ? 'auto' : $getSidebar;
        }
        foreach ($this->links as $name => $link) {
            $this->nav[] = $this->getNavLink($name, $link);
        }
        $this->exportToFile(
            $this->getModuleExports($this->nav),
            $this->path . '.vuepress/nav/en.js'
        );
        $this->exportToFile(
            $this->getModuleExports($this->sidebar),
            $this->path . '.vuepress/sidebar/en.js'
        );
    }

    private function iterate(): void
    {
        $chop = strlen($this->path);
        while ($this->recursiveIterator->valid()) {
            $path = $this->recursiveIterator->current()->getPathName();
            $path = substr($path, $chop);
            $explode = explode('/', $path);
            $root = $explode[0];
            $node = substr($path, strlen($root . '/'));
            if ($node === false) {
                $this->recursiveIterator->next();
                continue;
            }
            if (!isset($this->flagged[$root])) {
                $flags = new Flags($root);
            } else {
                $flags = $this->flagged[$root];
            }
            if (strpos($node, '/') !== false) {
                $flags = $flags->withNested(true);
            }
            if ($node === 'README.md') {
                $node = '';
                $flags = $flags->withReadme(true);
            }
            $this->hierarchy[$root][] = $node;
            $this->flagged[$root] = $flags;
            $this->recursiveIterator->next();
        }
    }

    private function getNavLink(string $name, string $link): array
    {
        return [
            'text' => $name,
            'link' => $link
        ];
    }

    private function getNav(string $name, array $nodes): array
    {
        $title = $this->getTitle($name);
        $link = "/$name/";
        if ($this->flagged[$name]->hasReadme()) {
            return $this->getNavLink($title, $link);
        }
        $array = [
            'text' => $title,
            'ariaLabel' => $title . ' Menu'
        ];
        foreach ($nodes as $nodeName) {
            $array['items'][] = $this->getNavLink(
                $this->getTitle($nodeName),
                $this->getUsableNode($link . $nodeName)
            );
        }

        return $array;
    }

    public function getSidebar(string $name, array $nodes): array
    {
        $title = $this->getTitle($name);
        if (!$this->flagged[$name]->hasReadme()) {
            return [];
        }
        if (!$this->flagged[$name]->hasNested()) {
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

    public function getSidebarFor(string $title, array $children): array
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

    private function getModuleExports(array $array): string
    {
        return 'module.exports = ' . json_encode($array, JSON_PRETTY_PRINT);
    }

    private function exportToFile(string $contents, string $filename): void
    {
        file_put_contents($filename, $contents);
    }

    private function getRecursiveDirectoryIterator(string $path): RecursiveDirectoryIterator
    {
        return new RecursiveDirectoryIterator(
            $path,
            RecursiveDirectoryIterator::SKIP_DOTS
            | RecursiveDirectoryIterator::KEY_AS_PATHNAME
        );
    }

    private function getRecursiveFilterIterator(RecursiveDirectoryIterator $dirIterator): RecursiveFilterIterator
    {
        return new class($dirIterator) extends RecursiveFilterIterator {
            public function accept(): bool
            {
                if ($this->hasChildren()) {
                    return true;
                }

                return $this->current()->getExtension() === 'md';
            }
        };
    }
}
