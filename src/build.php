<?php

require 'MarkdownIterator.php';

$iterator = (new MarkdownIterator(getcwd() . '/docs/'))
    ->withAddedLink('Examples', 'https://github.com/chevere/examples/');
    
$iterator->execute();
