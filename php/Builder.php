<?php

namespace VoidBuilder;

if (!defined ('VoidBuilder\ENGINE_DIR') && defined ('VoidEngine\ENGINE_DIR'))
    define ('VoidBuilder\ENGINE_DIR', \VoidEngine\ENGINE_DIR);

class Builder
{
    public $appDir;

    public function __construct (string $appDir)
    {
        if (!file_exists ($appDir))
            throw new \Exception ('Wrong $appDir param');

        $this->appDir = $appDir;
    }

    public function build (string $outputDir, string $iconPath = null): array
    {
        \VoidEngine\dir_clean ($outputDir .'/build');
        \VoidEngine\dir_copy (CORE_DIR, $outputDir .'/build');

        unlink ($outputDir .'/build/script.php');
        unlink ($outputDir .'/build/VoidCore.exe');

        $output = \VoidEngine\EngineAdditions::compile ($outputDir .'/build/app.exe', $iconPath ?? dirname (__DIR__) .'/system/Icon.ico', self::optimizeCode (str_replace ([
            '%APP%',
            '%PHAR_NAME%'
        ], [
            base64_encode (gzdeflate (file_get_contents (BUILDER_DIR .'/app.phar'), 9)),
            uniqid (rand(), true)
        ], file_get_contents (dirname (__DIR__) .'/system/preset.php')))/*, null, null, null, null, null, '', '', null, null*/);

        unlink (BUILDER_DIR .'/app.phar');

        return $output;
    }

    public static function generateCode (array $references, bool $removeNamespaces = true): string
    {
        $code = "/*\n\n\t". join ("\n\t", explode ("\n", file_get_contents (dirname (ENGINE_DIR) .'/license.txt'))) ."\n\n*/\n\n";

        foreach ($references as $path)
            $code .= join (array_slice (array_map (function ($line)
            {
                return substr ($line, 0, 7) != 'require' ? $line : '';
            }, file ($path)), 1));

        return $removeNamespaces ?
            preg_replace ('/'. "\n" .'namespace [a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*;'. "\n" .'/', "\n\n", $code) : $code;
    }

    public static function getReferences (string $file, bool $parseExtensions = true): array
    {
        $references = [];

        foreach (file ($file) as $line)
            if (substr ($line, 0, 7) == 'require')
                try
                {
                    $begin = strpos ($line, "'");
                    $end   = strrpos ($line, "'") - $begin + 1;

                    $references = array_merge ($references, self::getReferences (dirname ($file) .'/'. eval ('namespace VoidEngine; return '. substr ($line, $begin, $end) .';'), false));
                }

                catch (\Throwable $e)
                {
                    continue;
                }

        if ($parseExtensions)
            if (is_dir (ENGINE_DIR .'/extensions') && is_array ($exts = scandir (ENGINE_DIR .'/extensions')))
                foreach ($exts as $ext)
                    if (is_dir (ENGINE_DIR .'/extensions/'. $ext) && file_exists ($ext = ENGINE_DIR .'/extensions/'. $ext .'/main.php'))
                        $references = array_merge ($references, self::getReferences ($ext, false));

        $references[] = $file;

        return $references;
    }

    public static function getFiles (string $path, string $prefixBlacklist = null, array $files = [], int $originalPathLength = -1): array
    {
        if ($originalPathLength == -1)
            $originalPathLength = strlen (dirname ($path)) + 1;

        $len = strlen ($prefixBlacklist);
        
        foreach (array_slice (scandir ($path), 2) as $name)
            if ($prefixBlacklist === null || substr ($path .'/'. $name, $originalPathLength, $len) != $prefixBlacklist)
            {
                if (is_dir ($file = $path .'/'. $name))
                    $files = self::getFiles ($file, $prefixBlacklist, $files, $originalPathLength);

                else $files[substr ($file, $originalPathLength)] = file_get_contents ($file);
            }

        return $files;
    }

    public static function getPHPFiles (string $path, array $files = []): array
    {
        foreach (array_slice (scandir ($path), 2) as $file)
            $files = is_dir ($path .'/'. $file) ?
                self::getPHPFiles ($path .'/'. $file, $files) : (
                    strtolower (substr ($file, strrpos ($file, '.') + 1)) == 'php' ?
                    array_merge ($files, [$path .'/'. $file]) : $files);

        return $files;
    }

    public static function optimizeCode (string $code, bool $addPrefix = true): string
    {
        $tokens = token_get_all (($addPrefix ? '<?php ' : '') . $code);
        $return = '';

        foreach ($tokens as $id => $token)
            if (is_string ($token))
                $return .= $token;

            else
            {
                list ($token_id, $text) = $token;

                switch ($token_id)
                {
                    case T_COMMENT:
                    case T_DOC_COMMENT:
                        break;

                    case T_WHITESPACE:
                        if (!isset ($tokens[$id + 1]) || !is_array ($tokens[$id + 1]) || $tokens[$id + 1][0] != T_WHITESPACE)
                            $return .= ' ';

                        break;

                    default:
                        $return .= $text;

                        break;
                }
            }

        return $addPrefix ?
            substr ($return, 6) : $return;
    }
}
