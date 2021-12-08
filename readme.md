# Vuelab – PHP loader for Vue Single File Components

It's tiny tool that helps to integrate Vue (v.2) with all reactivity benefits to almost
 every PHP project on any PHP hosting – without need of heavy-weight loaders like
 webpack/rollup nor gulp/require.js.
 
Also available as WordPress plugin: just drop in `/wp-content/plugins` folder.

Vue components composes with [vuer](#vuer) utility
 and injects with simple `html container + new Vue` [vueLauncher](#vuelauncher) technique.

Additionally it uses [lessphp](https://leafo.net/lessphp/) for processing styles written in `less`.

Note that Vue 3, ES6/`module exports`, template languages, loading by `src` and so on not supported 
 – loader do not process nor evaluate js on server side,
 only composing `*.vue` into valid ES5 scripts and boot instances.

Template compilation relies on Vue built-in template compiler, 
 so you **must use** full version of vue.js lib, not runtime-only.


# Usage

1. Clone this repo – or load via composer:
```shell
composer require vikseriq/vuelab
```

2. Include `vuelab.php` – or use composer autoload.

3. Provide path to dir with Vue single file components. Or drop some into `/vuelab/components`.

4. Register components – just by typing component names.

5. Place somewhere html element with class `js-v-scope` – it will indicate vuelab 
 to start Vue instance it this container.

6. And call `vuelab_inject()`. Now your PHP page become a first-class Vue app.


Assume that we have `app.vue` that loads `todo-list.vue` with `todo.vue` inside.
Drop `vuelab` and create `index.php` looking like:

```php
include '/vuelab/vuelab.php';

vuelab_setup(__DIR__, ['app', 'todo-list', 'todo']);

vuelab_append('<div class="js-v-scope"><app /></div>');

vuelab_inject();
```

That's all.

# Usage on WordPress

1. Add plugin to Wordpress: via upload or copy to plugins dir.

2. Enable plugin from `Plugins` page – it will hook automatically.

3. Just use it: register components and their placeholders on desired page areas.

For example, place `foo.vue` inside your template folder and add in `functions.php`:
```php
// register component located in current path named foo.vue
vikseriq\vuelab\vuelab_setup(__DIR__, ['foo']);
// register placement for `foo` component - it will placed where `vuelab_inject` executed, in this case - at footer
vikseriq\vuelab\vuelab_append('<div class="js-v-scope"><foo /></div>');
// optionally: enable less styles compilation
vikseriq\vuelab\VueLab::$use_less = true;
```

```vue
<template>
  <div class="foo">Hello from Vue <span>{{ createdAt }}</span></div>
</template>
<script>
Vue.component('foo', {
  template: template, // ! it allows to pass template inside component
  data: function () {
    return {
      createdAt: new Date().toLocaleTimeString()
    }
  }
})
</script>
<style lang="less">
// feel free to use less here
@justOrange: #fc0;

.foo {
  display: block;
  margin: 1rem auto;
  padding: 1rem;
  width: fit-content;
  // sample use of less variables
  border: 3px outset @justOrange;
 
  // ... and nesting
  span {
    font-style: italic;
  }
}
</style>
```

Then on the very bottom of pages will be `foo` component with greeting and current page loading time.

# Documentation

## Vuelab

### Vuelab::inject

Returns HTML string with Vue components, styles and launcher.

1. Composes script+template bundle with every component via vuer. 

2. Wrap bundle in js function and bind execution on `document.vueReady` event to prevent 
 evaluation before Vue and vueLauncher is ready.
 
3. Appends vueLauncher code with trimmed space and comments.

4. Process bundle styles.

5. If `\VueLab::$use_less` is set – load [less compiler](lib/lessc.php) and process styles.

6. Appends rest of html added via `\VueLab::append`.


## Vuer – load Vue SFC with PHP

[Vuer](lib/vuer.php) used to convert `*.vue` files to browser-executable `<script>`

Inspired by [requirejs-vue](https://github.com/vikseriq/requirejs-vue/) technique.


## VueLauncher – make a Vue instance anywhere

[VueLauncher](lib/vue-launcher.js) helps boot Vue instance on any html container, 
by default used selector `.js-v-scope`.


## WordPress plugin

Install by uploading archive with this repos or by using awesome [GitHub Updater](https://github.com/afragen/github-updater/releases/latest) plugin.

When `\VueLab::$wp_enqueue_vue` flag is set, Vue `wp_enqueue_script`-ed 
 as `vue` from path specified in `\VueLab::$wp_vuejs_path`.
 Obviously, for better loading time and use with cache/packer plugins 
 provide path to local copy of `vue.min.js`.

# Things to do

[+] Sample project.

[_] Pass variables (like string translations) to Vue component via `__v` on build time.

[+] Make Vuelab available as Composer package.


# License

MIT © 2020 - present, vikseriq