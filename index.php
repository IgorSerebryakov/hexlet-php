<?php

require_once __DIR__ . '/vendor/autoload.php';

use function Php\Immutable\Fs\Trees\trees\mkfile;
use function Php\Immutable\Fs\Trees\trees\mkdir;
use function Php\Immutable\Fs\Trees\trees\isFile;
use function Php\Immutable\Fs\Trees\trees\isDirectory;
use function Php\Immutable\Fs\Trees\trees\getName;
use function Php\Immutable\Fs\Trees\trees\getChildren;
use function Php\Immutable\Fs\Trees\trees\getMeta;
use function Php\Immutable\Fs\Trees\trees\array_flatten;

$tree = mkdir('/', [
    mkdir('etc', [
        mkdir('apache'),
        mkdir('nginx', [
            mkfile('nginx.conf'),
        ]),
        mkdir('consul', [
            mkfile('config.json'),
            mkdir('data'),
        ]),
    ]),
    mkdir('logs'),
    mkfile('hosts'),
]);

function findEmptyDirPaths($tree, $ancestry, $acc)
{
    $name = getName($tree);
    $newAncestry = ($name === '/') ? '' : "$ancestry/$name";
    $children = getChildren($tree);


    if (count($children) === 0) {
        $acc[] = $newAncestry;
        return $acc;
    }

    $dirNames = array_filter($children, fn($child) => !isFile($child));

    $emptyDirNames = array_reduce(
        $dirNames,
        function ($newAcc, $child) use ($newAncestry) {
            return findEmptyDirPaths($child, $newAncestry, $newAcc);
        },
        $acc
    );

    $result = array_flatten($emptyDirNames);
    return $result;
}

print_r(findEmptyDirPaths($tree, '', []));