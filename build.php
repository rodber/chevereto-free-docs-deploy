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

use DocsDeploy\BuildController;

use function Chevere\Parameter\arguments;

require 'vendor/autoload.php';

(new BuildController())
    ->run(
        dir: getcwd() . '/docs/',
        stream: 'php://stdout',
    );
