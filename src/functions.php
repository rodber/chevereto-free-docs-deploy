<?php

declare(strict_types=1);

namespace DocsDeploy;

function toModuleExport(array $array): string
{
    return 'module.exports = ' . json_encode($array, JSON_PRETTY_PRINT);
}
