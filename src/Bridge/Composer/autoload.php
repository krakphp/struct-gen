<?php

namespace Krak\StructGen\Bridge\Composer;

// Work around to prevent the php functions from being redeclared due to composer plugin semantics.
if (defined('Krak\\StructGen\\Bridge\\Composer\\_INCLUDED')) {
    return;
}

const _INCLUDED = 1;

require_once __DIR__ . '/../../struct-gen.php';
require_once __DIR__ . '/../../../../lex/src/lex.php'; // nasty hack since composer plugins don't autoload files
