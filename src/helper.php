<?php
declare (strict_types = 1);
spl_autoload_register('loader');

!defined('__LIM__') && define('__LIM__', strstr(__DIR__, '/vendor', true));

if (!function_exists('loger')) {
    function loger($v = '', $type = 'debug', $file = null)
    {

        $color = ['debug' => '\\e[33m', 'info' => '\\e[32m', 'err' => '\\e[31m'];

        if (is_array($v) || is_object($v)) {
            $v = print_r($v, true);

        }
        
        if ($file) {
            $file = $file==true?date('Y-m-d').'log':$file;
            file_put_contents(__LIM__ . '/runtime/logs/' . $file, date('Y-m-d H:i:s') . ' ' . $v . PHP_EOL, FILE_APPEND);
        }

        $str = '\\033[36m[' . date('H:i:s') . '] ' . $color[$type];
        if (PHP_SAPI == 'cli') {
            echo shell_exec('echo -e -n "' . $str . '"') . $v . PHP_EOL;
        }
    }
}

if (!function_exists('echox')) {
    function echox($v = '', $type = 'debug')
    {

        $color = ['debug' => '\\e[33m', 'info' => '\\e[32m', 'err' => '\\e[31m'];

        if (is_array($v) || is_object($v)) {
            $v = print_r($v, true);

        }
        
        $str = '\\033[' . $color[$type];
        echo shell_exec('echo -e -n "' . $str . '"') . $v . PHP_EOL;
    }
}


// loadHelper();

function loader($class)
{
    $arr  = explode('\\', $class);
    $file = __LIM__ . '/' . implode('/', $arr) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
}

function hfConfiger($configer)
{
    $dir = __LIM__ . "/app/Service";
    if (is_dir($dir) && $handle = opendir($dir)) {
        while (($file = readdir($handle)) !== false) {
            if (($file == ".") || ($file == "..")) {
                continue;
            }

            $configDir   = $dir . '/' . $file . '/config/config.php';
            $ruleDir     = $dir . '/' . $file . '/config/rule.php';
            $databaseDir = $dir . '/' . $file . '/config/database.php';
            if (is_file($configDir)) {
                $config = include $configDir;
                $configer->set(strtolower($file), $config);
            }

            if (is_file($ruleDir)) {
                $rule = include $ruleDir;
                $configer->set('rule.' . strtolower($file), $rule);
            }

            if (is_file($databaseDir)) {
                $database = include $databaseDir;
                $key      = 'databases.' . strtolower($file);
                if (!$configer->has($key)) {
                    $configer->set($key, $database);
                }
            }
        }
        closedir($handle);
    }

    // loger($this->config->get('databases'));
    // loger('????????????');
}

// if (!function_exists('message')) {
//     /**
//      * @return bool|int
//      */
//     function message($contents, $event = [])
//     {
//         return new lim\Helper\MessageClient($contents, $event);
//     }
// }

if (!function_exists('config')) {
    /**
     * @return bool|int
     */
    function config($key = '')
    {

        $c = \lim\Helper\Env::$config;

        if (!$key) {
            return $c;
        }

        $arr = explode('.', $key);

        foreach ($arr as $k => $v) {
            if (!isset($c[$v])) {
                return null;
            }
            $c = $c[$v];
        }

        return $c;

    }
}

if (!function_exists('go')) {
    /**
     * @return bool|int
     */
    function go(callable $callable)
    {
        $id = \Swoole\Coroutine::create($callable);
        return $id > 0 ? $id : false;
    }
}

if (!function_exists('wlog')) {
    function wlog($v = '', $type = 'debug')
    {
        loger($v, $type);
    }
}

if (!function_exists('tu')) {
    function tu($fn, $value = '')
    {
        $s = microtime(true);
        $fn();
        $u = intval((microtime(true) - $s) * 1000);
        loger($value . '??????:' . $u . '??????', 'info');
    }
}

if (!function_exists('env')) {
    function env($key = null, $value = 'SSS')
    {
        if (!is_file(__LIM__ . '/.env')) {
            return $value;
        }

        // print_r(parse_ini_file(__LIM__ . '/.env', true));
        return parse_ini_file(__LIM__ . '/.env', true)[$key] ?? $value;
    }
}

if (!function_exists('proc')) {
    function proc($fn = null, $name = null)
    {
        if (!is_object($fn)) {
            return;
        }

        $proc = new \Swoole\Process($fn);
        if ($name) {
            cli_set_process_title($name);
        }
        $proc->daemon();
        $proc->start();

    }
}

// if (!function_exists('rpc')) {
//     function rpc($service = null, $onlyData = true)
//     {
//         return new lim\Helper\Rpclient($service, $onlyData);
//     }
// }

if (!function_exists('kv')) {
    function kv($service = null, $onlyData = true)
    {
        return 1;
    }
}

if (!function_exists('objRun')) {
//???????????????????????????
    function objRun($obj = '', ...$opt)
    {
        $obj = explode(':', $obj);

        if (!$class = $obj[0] ?? null) {
            exit('obj ???');
        }

        $class = '\\' . str_replace('.', '\\', $class);
        // wlog([$class,$opt,$obj[1]]);
        try {
            //?????????????????????
            if (!$method = $obj[1] ?? null) {
                new $class(...$opt);
                return;
            }

            //????????????????????????
            if (!method_exists($class, $method)) {
                loger($class . ' ' . $method . ' ???????????????', 'err');
                return;
            }

            //??????????????????
            if ((new \ReflectionMethod($class, $method))->isStatic()) {
                $class::$method(...$opt);
            } else {
                (new $class)->$method(...$opt);
            }

        } catch (\Swoole\ExitException $e) {
            loger($e->getStatus(), 'err');
        }
    }
}

function loadHelper($dir = null)
{
    $dir = $dir ?? __LIM__ . "/app";
    if (is_dir($dir) && $handle = opendir($dir)) {
        while (($file = readdir($handle)) !== false) {
            if (($file == ".") || ($file == "..")) {
                continue;
            }
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                // wlog($path);
                loadHelper($path);

                continue;
            }

            if ($file == 'helper.php') {
                // wlog($path);
                require_once $path;
            }
        }
        closedir($handle);
    }
}

lim\Helper\Env::initConfig();
