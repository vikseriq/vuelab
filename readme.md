# Vuelab – PHP loader for Vue Single File Components applications

It's tiny tool that helps to integrate Vue with all reactivity benefits to almost
 every PHP project on any PHP hosting – without need of heavy-weight loaders like
 webpack/rollup nor gulp/require.js.
 
Also available as WordPress plugin: just drop in `/wp-content/plugins` folder.

Vue components composes with [vikseriq/vuer](https://github.com/vikseriq/vuer) utility
 and injects with simple `html container + new Vue` technique.

Additionally it uses [lessphp](https://leafo.net/lessphp/) for processing less.

Note that ES6/`module exports` not supported – we do not process nor evaluate js on server side,
only composing `*.vue` into valid ES5 scripts and boot instances.

# Usage

0. Clone this repo.

1. Include Vuelab.

2. Provide path to dir with Vue single file components. Or drop some into '/vuelab/components'.

3. Register components – just by typing component names.

4. Place somewhere html element with class `js-v-scope` – it will indicate vuelab 
 to start Vue instance it this container.

5. And call `vuelab_inject()`. Now your PHP page become a first-class Vue app.


Assume that we have `app.vue` that loads `todo-list.vue` with `todo.vue` inside.
Drop `vuelab` and create `index.php` looking like:

```php
include '/vuelab/vuelab.php';

vuelab_add_path(__DIR__);

vuelab_require(['app', 'todo-list', 'todo']);

vuelab_append('<div class="js-v-scope"><app /></div>');

vuelab_inject();

```

That's all.


# Things to do

[_] Remove jQuery dependency in `assets/launcher.js`

[_] Sample project


# License

MIT © 2020 vikseriq