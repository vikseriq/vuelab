# Vuelab – PHP loader for Vue Single File Components

It's tiny tool that helps to integrate Vue with all reactivity benefits to almost
 every PHP project on any PHP hosting – without need of heavy-weight loaders like
 webpack/rollup nor gulp/require.js.
 
Also available as WordPress plugin: just drop in `/wp-content/plugins` folder.

Vue components composes with [vuer](#vuer) utility
 and injects with simple `html container + new Vue` [vueLauncher](#vuelauncher) technique.

Additionally it uses [lessphp](https://leafo.net/lessphp/) for processing less.

Note that ES6/`module exports` not supported – loader do not process nor evaluate js on server side,
 only composing `*.vue` into valid ES5 scripts and boot instances.

Template compilation relies on Vue built-in template compiler, 
 so you **must use** full version of Vue lib, not runtime-only.


# Usage

0. Clone this repo.

1. Include Vuelab.

2. Provide path to dir with Vue single file components. Or drop some into `/vuelab/components`.

3. Register components – just by typing component names.

4. Place somewhere html element with class `js-v-scope` – it will indicate vuelab 
 to start Vue instance it this container.

5. And call `vuelab_inject()`. Now your PHP page become a first-class Vue app.


Assume that we have `app.vue` that loads `todo-list.vue` with `todo.vue` inside.
Drop `vuelab` and create `index.php` looking like:

```php
include '/vuelab/vuelab.php';

vuelab_setup(__DIR__, ['app', 'todo-list', 'todo']);

vuelab_append('<div class="js-v-scope"><app /></div>');

vuelab_inject();

```

That's all.

# Documentation

## Vuer 

[Vuer](lib/vuer.php) used to convert `*.vue` files to browser-executable `<script>`


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

[_] Sample project.

[_] Build `vue-launcher.min.js`.

[_] Pass variables (like string translations) to Vue component via `__v` on build time.

[_] Make Vuelab available as Composer package.


# License

MIT © 2020 vikseriq