<?php
require_once 'vendor/autoload.php';
define('BASE_ROOT',__DIR__.DIRECTORY_SEPARATOR.'controllers');
define('CONFIG_DIR',__DIR__.DIRECTORY_SEPARATOR.'config');

//防止正则匹配过长字符串时无法匹配成功
ini_set('pcre.backtrack_limit', 1000000);

date_default_timezone_set('Asia/Shanghai');

\App\System\Configure\Config::loadConfig();
\App\System\Cache\Cache::init();

$show = \App\System\Handler\Handler::Start();
echo $show;

