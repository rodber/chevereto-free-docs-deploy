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

function toModuleExport(array $array): string
{
    return 'module.exports = ' . json_encode($array, JSON_PRETTY_PRINT);
}

function sortArray(array $module, array $order = []): array
{
    $availableKeys = array_intersect($order, array_keys($module));

    return array_merge(array_flip($availableKeys), $module);
}
