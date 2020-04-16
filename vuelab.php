<?php
/**
 * Plugin Name: Vuelab
 * Description: Vue your logic with easy
 * Plugin URI: https://github.com/vikseriq/vuelab
 * GitHub Plugin URI: https://github.com/vikseriq/vuelab
 * Author: vikseriq
 * Author URI: https://vikseriq.xyz/
 * Version: 0.4.0
 * License: MIT
 * License URI: https://tldrlegal.com/license/mit-license
 */

include_once __DIR__ . '/lib/vuer.php';

class VueLab
{
    protected static $__registry = [];
    protected static $__html = '';
    protected static $__html_pre = '';
    protected static $__paths = [
        __DIR__ . '/components'
    ];

    /**
     * @var bool use LESS css preprocessor
     */
    static $use_less = false;
    /**
     * @var bool use /assets/vue-launcher.js to boot in the Vue instances
     */
    static $use_launcher = true;
    /**
     * @var bool flag: add Vue lib in wp scripts enqueue
     */
    static $wp_enqueue_vue = true;
    /**
     * @var string path to vue.min.js file
     */
    static $wp_vuejs_path = 'https://cdn.jsdelivr.net/npm/vue/dist/vue.min.js';

    static function init()
    {
        // integrate into WP
        if (function_exists('add_action') && function_exists('wp_enqueue_script')) {
            add_action('wp_enqueue_scripts', function () {

                // enqueue vue
                if (static::$wp_enqueue_vue) {
                    wp_enqueue_script('vue', static::$wp_vuejs_path, [], null, true);
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
        $path = realpath($full_path);
        if (!in_array($path, static::$__paths)) {
            static::$__paths[] = $path;
        }
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
        self::$__registry[$path] = [
            'path' => $path,
            'namespace' => $namespace,
            'component' => $component,
            'tagname' => trim($namespace . '-' . $component, '-'),
        ];
    }

    /**
     * Append string to vuelab html output
     * @param $html string
     */
    static function append($html)
    {
        self::$__html .= $html . PHP_EOL;
    }

    static function prepend($html)
    {
        self::$__html_pre .= $html;
    }

    /**
     * Output vuelab html with composed Vue components, styles and injection script.
     *
     * @return string precomposed vue components + launcher + styles
     */
    static function inject()
    {
        $compos = [];
        foreach (self::$__registry as $file_info) {
            $compos[] = vuer::load($file_info['path'], false, $file_info);
        }

        $html = self::$__html_pre;

        // elements
        $html .= '<script>document.addEventListener("vueReady", function(){'
            . implode(PHP_EOL, array_column($compos, 'script'))
            . '});</script>' . PHP_EOL;

        // launcher
        if (static::$use_launcher) {
            $launcher_js = file_get_contents(__DIR__ . '/lib/vue-launcher.js');
            $html .= '<script>' . preg_replace('!(//.*?\n)|(\s{2,})!', '', $launcher_js)
                . '</script>' . PHP_EOL;
        }

        // styles
        $styles = trim(implode(PHP_EOL, array_column($compos, 'css')));
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
function vuelab_add_path($path)
{
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

/**
 * Setup vue oneliner. Shorthand for ::add_path + ::req
 * @param string|string[] $paths Path to component repositories
 * @param array $components Components to include
 * @param array $launcher_options Options for vueLauncher
 */
function vuelab_setup($paths = '', $components = [], $launcher_options = [])
{

    // setup paths
    if (!is_array($paths)) {
        $paths = [$paths];
    }
    foreach ($paths as $path) {
        VueLab::add_path($path);
    }

    // include components
    VueLab::req($components);

    if (!empty($launcher_options)) {
        VueLab::prepend('<script>window.vueLauncherOptions = ' . json_encode($launcher_options) . '</script>');
    }
}

// ~ let it start ~
VueLab::init();