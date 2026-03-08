<?php
namespace SJ_Reviews\Core;

defined('ABSPATH') || exit;

/**
 * Autoloader PSR-4 simplifié pour SJ_Reviews\*.
 */
class Loader {

    public static function register(): void {
        spl_autoload_register([self::class, 'autoload']);
    }

    public static function autoload(string $class): void {
        $prefix = 'SJ_Reviews\\';
        if (strpos($class, $prefix) !== 0) return;

        $relative = substr($class, strlen($prefix));
        $path     = SJ_REVIEWS_DIR . str_replace('\\', '/', strtolower(
            preg_replace('/([A-Z])/', '-$1', lcfirst($relative))
        )) . '.php';

        // Convention : SJ_Reviews\Admin\Backoffice\Backoffice → admin/backoffice/class-backoffice.php
        // Reconstruit manuellement la convention class-*.php
        $parts    = explode('\\', $relative);
        $class_n  = array_pop($parts);
        $dir      = implode('/', array_map('strtolower', $parts));
        $file     = SJ_REVIEWS_DIR . ($dir ? $dir . '/' : '') . 'class-' . strtolower(
            preg_replace('/([A-Z])/', '-$1', lcfirst($class_n))
        ) . '.php';

        if (is_readable($file)) {
            require_once $file;
        }
    }
}

Loader::register();
