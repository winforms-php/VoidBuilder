<?php

namespace VoidBuilder;

use ConsoleArgs\{
    Manager,
    DefaultCommand,
    Param,
    Flag
};

require 'VoidBuilder.php';

if (!isset ($argv))
{
	$params = json_decode (file_get_contents (__DIR__ .'/params.json'), true);
	$oargv = [__FILE__];
	
	unlink (__DIR__ .'/params.json');
	
    foreach ($params as $name => $param)
        if ($name == '--compress')
            $oargv[] = '--compress';
        
        else
        {
            if (!is_array ($param))
                $param = [$param];
            
            foreach ($param as $arg)
                if (strlen ($arg) > 0)
                {
                    $oargv[] = $name;
                    $oargv[] = $arg;
                }
        }
    
    if (!defined ('VoidBuilder\ENGINE_DIR'))
        define ('VoidBuilder\ENGINE_DIR', $params['--engine-dir']);

    if (!defined ('VoidBuilder\CORE_DIR'))
        define ('VoidBuilder\CORE_DIR', dirname (ENGINE_DIR) .'/core');
    
	define ('VoidEngine\CORE_DIR', dirname (ENGINE_DIR) .'/core');
    
    require $params['--engine-dir'] .'/VoidEngine.php';
	
	$argv = $oargv;
}

try
{
    (new Manager ([], (new DefaultCommand (function ($args, $params)
    {
        foreach (['--app-dir', '--output-dir', '--icon-path'] as $param)
            if (is_array ($params[$param]))
                $params[$param] = end ($params[$param]);

        if (!file_exists (dirname ($params['--app-dir']) .'/qero-packages/winforms-php/VoidFramework/core/VoidCore.exe'))
            die ('Incorrect VoidFramework app path "'. $params['--app-dir'] .'"');

        if (class_exists ('VoidEngine\NetObject'))
        {
            $errors = (new Builder ($params['--app-dir']))
                ->build ($params['--output-dir'], $params['--icon-path']);

            if (sizeof ($errors) > 0)
                print_r ($errors);
        }

        else
        {
            echo PHP_EOL;
            echo ' Building ['. dirname (str_replace (dirname ($params['--app-dir'], 2) .'\\', '', $params['--app-dir'])) .']...'. PHP_EOL;

            $begin = microtime (true);

            $params['--engine-dir'] = dirname ($params['--app-dir']) .'/qero-packages/winforms-php/VoidFramework/engine';
            file_put_contents ('params.json', json_encode ($params, JSON_PRETTY_PRINT));

            echo ' > Compressing PHP files...'. PHP_EOL;

            $phps            = Builder::getPHPFiles (dirname ($params['--app-dir']));
            $originals       = [];
            $original_size   = 0;
            $compressed_size = 0;
            
            foreach ($phps as $php)
            {
                $originals[$php] = file_get_contents ($php);
                $original_size  += filesize ($php);

                file_put_contents ($php, Builder::optimizeCode (file_get_contents ($php), false));

                clearstatcache ();
                $compressed_size += filesize ($php);
            }

            echo ' > Compressed  '. round ($original_size / 1024, 2) .' kb -> '. round ($compressed_size / 1024, 2) .' kb ('. (100 - round ($compressed_size / $original_size * 100, 2)) .'%)'. PHP_EOL;
            echo ' > Packing application...'. PHP_EOL;

            $phar = new \Phar ('app.phar');
            $phar->buildFromDirectory (dirname ($params['--app-dir']), '/^(?!(.*qero\-packages\/winforms\-php\/(VoidFramework|VoidBuilder)\/core))(.*)$/i');
            $phar->setStub ($phar->createDefaultStub ('app/start.php'));

            foreach ($originals as $php => $content)
                file_put_contents ($php, $content);

            echo ' > Compiling...'. PHP_EOL . PHP_EOL;
            
            shell_exec ('"'. dirname ($params['--app-dir']) .'/qero-packages/winforms-php/VoidFramework/core/VoidCore.exe" "'. __FILE__ .'"');

            echo ' Building completed after '. round (microtime (true) - $begin, 2) .' seconds'. PHP_EOL;
            echo '   Saved at '. $params['--output-dir'] .'/build'. PHP_EOL . PHP_EOL;

            if ($params['--compress'])
            {
                $begin  = microtime (true);
                $joiner = new Joiner ($params['--output-dir'] .'/build/app.exe', $params['--output-dir'] .'/app.exe');
                
                foreach (array_diff (array_slice (scandir ($params['--output-dir'] .'/build'), 2),
                    ['php7ts.dll', 'vcruntime140.dll']) as $file)
                    $joiner->add (file_exists ($file) ? $file : $params['--output-dir'] .'/build/'. $file);

                copy ($params['--output-dir'] .'/build/php7ts.dll', $params['--output-dir'] .'/php7ts.dll');
                copy ($params['--output-dir'] .'/build/vcruntime140.dll', $params['--output-dir'] .'/vcruntime140.dll');

                echo str_replace ("\n", "\n ", $joiner->join ()) . PHP_EOL;
                echo ' Compressing completed after '. round (microtime (true) - $begin, 2) .' seconds'. PHP_EOL;
                echo '   Saved at '. $params['--output-dir'] . PHP_EOL;
            }

            elseif (isset ($params['--join']))
            {
                if (!is_array ($params['--join']))
                    $params['--join'] = [$params['--join']];

                if (($size = sizeof ($params['--join'])) > 0)
                {
                    echo ' Union '. $size .' entries...'. PHP_EOL;

                    $begin  = microtime (true);
                    $joiner = new Joiner ($params['--output-dir'] .'/build/app.exe', $params['--output-dir'] .'/app.exe');
                    
                    foreach ($params['--join'] as $file)
                        $joiner->add (file_exists ($file) ? $file : $params['--output-dir'] .'/build/'. $file);

                    echo str_replace ("\n", "\n ", $joiner->join ()) . PHP_EOL;
                    echo ' Union completed after '. round (microtime (true) - $begin, 2) .' seconds'. PHP_EOL;
                    echo '   Saved at '. $params['--output-dir'] . PHP_EOL;
                }
            }
        }
    }))->addParams ([
        (new Param ('--app-dir', null, true))->addAliase ('-d'),
        (new Param ('--output-dir', __DIR__ .'/build'))->addAliase ('-o'),
        (new Param ('--icon-path', __DIR__ .'/system/Icon.ico'))->addAliase ('-i'),
        (new Param ('--join'))->addAliase ('-j'),
        (new Flag ('--compress'))->addAliase ('-c')
    ])))->execute ($argv);
}

catch (\Exception $e)
{
    die (PHP_EOL .' '. $e->getMessage () . PHP_EOL);
}
