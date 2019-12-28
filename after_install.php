<?php

global $index_package;

$index_package->scripts['build'] = 'php qero-packages/winforms-php/VoidBuilder/build.php --app-dir app --output-dir build';

unlink (__FILE__);
