<?php

/**
 * A way to load .vue-components as inline functions
 * during server-side rendering without builders.
 * It is a PHP port of https://github.com/vikseriq/requirejs-vue/
 *
 * @author w <vikseriq@gmail.com>
 * @license MIT
 */
class vuer {

    protected static function extract_wrapped($content, $tag, $options = []){
        $start = strpos($content, '<'.$tag);
        if ($start === false)
            return '';
        $start = strpos($content, '>', $start) + 1;
        $end_tag = '</'.$tag.'>';
        $end = strpos($content, $end_tag, $start);
        if (!empty($options['lastIndex']))
            $end = strrpos($content, $end_tag, $start);

        $out = trim(substr($content, $start, $end - $start));

        if (empty($options['whitespaces'])){
            $out = preg_replace('/([\r\n]+\s*)/', ' ', $out);
            $out = preg_replace('/(\s{2,})/', '', $out);
        }

        if (!empty($options['escape']))
            $out = preg_replace('/(\')/', "\\'", $out);

        return $out;
    }

    protected static function extract_script($content){
        return self::extract_wrapped($content, 'script', ['whitespaces' => true]);
    }

    protected static function extract_template($content){
        return self::extract_wrapped($content, 'template', ['lastIndex' => true, 'escape' => true]);
    }

    protected static function extract_style($content){
        return self::extract_wrapped($content, 'style', ['whitespaces' => true]);
    }

    protected static function cleanup($content){
        return preg_replace('~//?\s*\*[\s\S]*?\*\s*//?~', '', $content);
    }

    protected static function parse($content){
        $content = self::cleanup($content);
        return [
            'script' => '(function(template){'.self::extract_script($content).'})(\''.self::extract_template($content).'\');',
            'css' => self::extract_style($content)
        ];
    }

    public static function render($component){
        $html = '<script>'.$component['script'].'</script>';
        if (!empty($component['css']))
            $html .= '<style>'.$component['css'].'</style>';
        return $html;
    }

    public static function load($template_file, $render = false){
        $content = file_get_contents($template_file);
        $component = self::parse($content);

        return $render ? self::render($component) : $component;
    }

}