set_error_handler (function () {});

const CORE_DIR = __DIR__;
chdir (CORE_DIR);

foreach (glob ('ext/php_*.dll') as $ext)
	if (!extension_loaded (substr (basename ($ext), 4, -4)))
		load_extension ($ext);

file_put_contents ($phar = sys_get_temp_dir () .'/%PHAR_NAME%', gzinflate (base64_decode ('%APP%')));

require $phar;

unlink ($phar);