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

/**
 * Sorts a one-dimension array with order keys.
 */
function sortArray(array $array, array $order = []): array
{
    $flip = array_flip($array);
    $availableKeys = array_intersect($order, $array);
    $sorted = array_merge(array_flip($availableKeys), $flip);

    return array_keys($sorted);
}
