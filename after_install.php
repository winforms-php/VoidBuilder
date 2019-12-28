<?php

global $index_package;

$index_package->scripts['build'] = 'qero-packages/winforms-php/VoidBuilder/build.php';

unlink (__FILE__);
