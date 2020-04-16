<?php
/**
 * Plugin Name: VueLab
 * Description: Vue your logic with easy
 * Plugin URI: https://github.com/vikseriq/vuelab
 * GitHub Plugin URI: https://github.com/vikseriq/vuelab
 * Author: vikseriq
 * Author URI: https://vikseriq.xyz/
 * Version: 0.1.0
 * License: MIT
 * License URI: https://tldrlegal.com/license/mit-license
 */

include_once __DIR__ . '/lib/vuer.php';

class VueLab
{
    protected static $__registry = [];
    protected static $__html = '';
    protected static $__paths = [
        __DIR__ . '/components'
    ];

    /**
     * @var bool use LESS css preprocessor
     */
    static $use_less = false;
    /**
     * @var bool use /assets/launcher.js to boot in the Vue instances
     */
    static $use_launcher = true;
    /**
     * @var bool flag: add Vue lib in wp scripts enqueue
     */
    static $wp_enqueue_vue = true;

    static function init()
    {
        // integrate into WP
        if (function_exists('add_action') && function_exists('wp_enqueue_script')) {
            add_action('wp_enqueue_scripts', function () {

                // enqueue vue
                if (static::$wp_enqueue_vue) {
                    wp_enqueue_script('vue', plugin_dir_url(__FILE__) . '/assets/vue.min.js', [], null, true);
                }

                // loader
                add_action('wp_footer', 'vuelab_inject', 228);
            });
        }
    }

    /**
     * Register Vue components repository
     *
     * @param $full_path
     */
    static function add_path($full_path)
    {
        static::$__paths[] = $full_path;
    }

    /**
     * Include Vue components
     *
     * @param $components string|string[]
     * @param null $namespace
     */
    static function req($components, $namespace = null)
    {
        if (is_array($components)) {
            foreach ($components as $ns => $map) {
                $ns = is_int($ns) ? $namespace : trim($namespace . '-' . $ns, '-');
                self::req($map, $ns);
            }
        } else {
            self::load_one($namespace, $components);
        }
    }

    /**
     * Resolve path and register Vue component
     *
     * @param $namespace
     * @param $component
     */
    static function load_one($namespace, $component)
    {
        $ext = '.vue';
        $path = '';

        // loop paths in reversed order: last added have max priority
        foreach (array_reverse(self::$__paths) as $base_path) {
            // invalid dir - skip
            if (!is_dir($base_path))
                continue;

            // simple case - component name equals file name
            $path = $base_path . '/' . $component . $ext;
            if (!is_file($path)) {
                // PascalCase: namespace + component name
                $path = $base_path . '/' . $namespace . $component . $ext;
                if (!is_file($path)) {
                    // kebab-case: namespace + '-' + component name
                    $path = $base_path . '/' . $namespace . '-' . $component . $ext;
                    if (!is_file($path)) {
                        // dir: namespace as directory
                        $path = $base_path . '/' . $namespace . '/' . $component . $ext;
                    }
                }
            }

            // file found - stop the loop
            if (is_file($path))
                break;
        }

        // nothing found - exit
        if (!is_file($path))
            return;

        // check is already registered
        if (isset(self::$__registry[$path]))
            return;

        // register component
        self::$__registry[$path] = $path;
    }

    /**
     * Append string to vuelab html output
     * @param $html string
     */
    static function append($html)
    {
        self::$__html .= $html . PHP_EOL;
    }

    /**
     * Output vuelab html with composed Vue components, styles and injection script.
     *
     * @return string precomposed vue components + launcher + styles
     */
    static function inject()
    {
        $compos = [];
        foreach (self::$__registry as $file) {
            if (is_file($file)) {
                $compos[] = vuer::load($file);
            }
        }

        // elements
        $html = '<script>document.addEventListener("vueReady", function(){'
            . implode(PHP_EOL, array_column($compos, 'script'))
            . '});</script>';

        // launcher
        if (static::$use_launcher) {
            $html .= '<script>' . file_get_contents(__DIR__ . '/assets/vue-launcher.js') . '</script>';
        }

        // styles
        $styles = implode(PHP_EOL, array_column($compos, 'css'));
        if (!empty($styles)) {
            // use css-preprocessor
            if (self::$use_less) {
                include_once __DIR__ . '/lib/lessc.php';
                if (class_exists('lessc')) {
                    $less = new lessc();
                    $styles = $less->compile($styles);
                }
            }
            $html .= '<style>' . $styles . '</style>';
        }
        // appendix
        $html .= self::$__html;

        return $html;
    }
}

/**
 * Add directory with Vue component to lookup
 *
 * @param $path string filesystem path
 */
function vuelab_add_path($path){
    VueLab::add_path($path);
}

/**
 * Register Vue component
 *
 * @param $components
 */
function vuelab_require($components)
{
    VueLab::req($components);
}

/**
 * Inject vuelab (echo-able)
 */
function vuelab_inject()
{
    echo VueLab::inject();
}

/**
 * Append text to vuelab
 * @param $html string
 */
function vuelab_append($html)
{
    VueLab::append($html);
}

// ~ let it start ~
VueLab::init();