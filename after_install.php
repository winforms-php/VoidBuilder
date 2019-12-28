<?php

global $index_package;

$index_package->scripts['build-app'] = 'php "%QERO%/qero-packages/winforms-php/VoidBuilder/build.php" --app-dir "%QERO%/app" --output-dir "%QERO%/build"';

unlink (__FILE__);
