# Laravel translation in Vue.js

[![Tests](https://github.com/tohidplus/laravel-vue-translation/actions/workflows/tests.yml/badge.svg)](https://github.com/tohidplus/laravel-vue-translation/actions/workflows/tests.yml)
[![Latest version](https://img.shields.io/packagist/v/tohidplus/laravel-vue-translation.svg)](https://packagist.org/packages/tohidplus/laravel-vue-translation)
[![Total downloads](https://img.shields.io/packagist/dt/tohidplus/laravel-vue-translation.svg)](https://packagist.org/packages/tohidplus/laravel-vue-translation)
[![License](https://img.shields.io/github/license/tohidplus/laravel-vue-translation.svg)](LICENCE.txt)

Use the translation files of your Laravel application in Vue.js, or in any other JavaScript code.

The package compiles the files in your `lang` directory into one `translations.json` file. A small JavaScript helper reads that file and gives you a `translate()` function that works like the Laravel `__()` helper.

```vue
<template>
    <h1>{{ translate('messages.welcome', { name: user.name }) }}</h1>
</template>
```

## Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Set the locale](#set-the-locale)
- [Add translate() to your application](#add-translate-to-your-application)
- [Translate](#translate)
- [The generate command](#the-generate-command)
- [Troubleshooting](#troubleshooting)
- [Upgrade from version 3](#upgrade-from-version-3)
- [Contributing](#contributing)

## Requirements

Version 4 supports these versions. The tests run on each combination.

| Laravel | PHP |
| --- | --- |
| 13 | 8.3 – 8.6 |
| 12 | 8.2 – 8.6 |
| 11 | 8.2 – 8.4 |

For older Laravel versions, use version 3 of the package.

The JavaScript helper works with Vite (the Laravel default), Laravel Mix and webpack, and with Vue 3, Vue 2 or no framework.

## Installation

1. Install the package with Composer. Laravel finds the service provider automatically.

    ```bash
    composer require tohidplus/laravel-vue-translation
    ```

2. Publish the JavaScript files. This command copies `Translation.js`, `translator.js` and an empty `translations.json` to `resources/js/VueTranslation`.

    ```bash
    php artisan vendor:publish --tag=vue-translation
    ```

3. Make sure that your application has a `lang` directory. New Laravel 11+ applications do not have one. Create it with this command:

    ```bash
    php artisan lang:publish
    ```

4. Compile the translation files into `resources/js/VueTranslation/translations.json`:

    ```bash
    php artisan VueTranslation:generate
    ```

    Run the command again each time that you change a translation file, or use [watch mode](#watch-mode).

## Set the locale

`translate()` reads the current locale from the `lang` attribute of the `<html>` tag. You can also give a fallback locale in a `<meta>` tag. Add both to your layout, for example `resources/views/app.blade.php`:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta name="fallback_locale" content="{{ config('app.fallback_locale') }}">
    @vite(['resources/js/app.js'])
</head>
```

A locale such as `pt-BR` in the `lang` attribute also finds the `lang/pt_BR` directory.

`translate()` reads the locale each time you call it. If you change `document.documentElement.lang` without a page reload, Vue does not render the page again by itself.

## Add translate() to your application

### Vue 3 with Vite

Install the plugin in `resources/js/app.js`. Then you can use `translate()` in all component templates.

```js
import { createApp } from 'vue';
import VueTranslation from './VueTranslation/Translation';
import App from './App.vue';

createApp(App)
    .use(VueTranslation)
    .mount('#app');
```

With Inertia.js, install the plugin in the `setup` callback:

```js
createInertiaApp({
    // ...
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(VueTranslation)
            .mount(el);
    },
});
```

To use another name in the templates, for example `__` as in Laravel, give the `name` option. The value can be one name or a list of names:

```js
app.use(VueTranslation, { name: ['translate', '__'] });
```

### The Composition API and `<script setup>`

Import the function where you need it:

```vue
<script setup>
import { translate } from '../VueTranslation/Translation';

const title = translate('messages.title');
</script>
```

The plugin also provides the function with the key `translate`, so `inject('translate')` works in components.

### Vue 2

```js
import Vue from 'vue';
import VueTranslation from './VueTranslation/Translation';

Vue.use(VueTranslation);
```

### Laravel Mix, webpack and `require()`

Version 4 uses ES modules. Vite needs `import`, but webpack also accepts the `require()` syntax of version 3:

```js
Vue.prototype.translate = require('./VueTranslation/Translation').default.translate;
```

### Without Vue

The function does not need Vue. Use it in plain JavaScript, React, Svelte or Alpine.js:

```js
import { translate } from './VueTranslation/Translation';

window.translate = translate;
```

To use your own locale source, for example a store, make a translator with `createTranslator()`. The `locale` and `fallbackLocale` options accept a string or a function:

```js
import translations from './VueTranslation/translations.json';
import { createTranslator } from './VueTranslation/translator';

const translate = createTranslator(translations, {
    locale: () => store.locale,
    fallbackLocale: 'en',
});
```

## Translate

Assume that your `lang` directory has these files:

```text
lang/
├── en.json
├── en/
│   ├── messages.php
│   └── validation.php
├── fr.json
└── fr/
    ├── messages.php
    └── validation.php
```

And `lang/en/messages.php` contains:

```php
return [
    'welcome' => 'Welcome, :name!',
    'foo' => [
        'bar' => 'Some message',
    ],
];
```

### Keys from PHP files

Use the file name and the keys, separated by dots, the same as in Laravel:

```js
translate('messages.foo.bar'); // "Some message"
```

Files in sub-directories also work. The key for `lang/en/admin/users.php` starts with `admin.users`.

### Translation strings from JSON files

Use the full string as the key. Strings that contain dots work too:

```json
{
    "I love programming.": "J'adore programmer."
}
```

```js
translate('I love programming.'); // "J'adore programmer."
```

`translate()` looks for the exact key first. If it does not find the key, it splits the key on the dots. To look for the exact key only, give `false` as the third argument:

```js
translate('Some full sentence. Like this one.', {}, false);
```

### Replace placeholders

Give the values for the `:placeholder` parts in the second argument. As in Laravel, `:Name` gives the value with an upper case first letter, and `:NAME` gives the value in upper case.

```js
translate('messages.welcome', { name: 'Taylor' }); // "Welcome, Taylor!"

translate('validation.required', {
    attribute: translate('validation.attributes.title'),
});
```

### Missing translations

When a key has no value in the current locale, `translate()` uses the fallback locale. When the fallback locale has no value too, `translate()` returns the key. An empty string counts as a missing value.

```js
translate('messages.does_not_exist'); // "messages.does_not_exist"
```

### Groups

A key that points to a group returns the group as an object:

```js
translate('messages.foo'); // { bar: "Some message" }
```

## The generate command

```bash
php artisan VueTranslation:generate
```

| Option | Description |
| --- | --- |
| `--watch` | Watch the `lang` directory and compile again when a translation file changes. `--watch=1` also works. |
| `--interval=250` | The time in milliseconds between two checks in watch mode. |
| `-v` | Show each compiled file. |

The command also has the alias `vue-translation:generate`.

The command reads only `.php` and `.json` files. It stops with an error message if a JSON file is not valid or a PHP file does not return an array.

### Watch mode

Run watch mode next to the Vite development server:

```bash
php artisan VueTranslation:generate --watch
```

Laravel 11+ applications start the development servers with `composer run dev`. To start watch mode with them, add a command to the `dev` script in your `composer.json`:

```json
"dev": [
    "Composer\\Config::disableProcessTimeout",
    "npx concurrently -c \"#93c5fd,#c4b5fd,#fdba74,#86efac\" \"php artisan serve\" \"php artisan queue:listen --tries=1\" \"npm run dev\" \"php artisan VueTranslation:generate --watch\" --names=server,queue,vite,lang"
]
```

### Production builds

Vite puts the content of `translations.json` in your JavaScript bundle. Run the command before `npm run build`, for example in your deploy script:

```bash
php artisan VueTranslation:generate
npm run build
```

You can also commit `translations.json` to your repository.

## Troubleshooting

### "require is not defined"

Vite uses ES modules and does not support `require()`. Publish the version 4 files and use `import`:

```bash
php artisan vendor:publish --tag=vue-translation --force
php artisan VueTranslation:generate
```

```js
import VueTranslation from './VueTranslation/Translation';
```

### "Property or method translate is not defined on the instance"

Vue does not know the function. Install the plugin with `app.use(VueTranslation)` (Vue 3) or `Vue.use(VueTranslation)` (Vue 2) before you mount the application. In Vue 3, `Vue.prototype` does not exist. In `<script setup>`, import `translate` from the file.

### "The lang directory does not exist"

Run `php artisan lang:publish`, or create the `lang` directory and add your translation files.

### translate() returns the key

- Run `php artisan VueTranslation:generate` again, then build your assets again.
- Make sure that the `lang` attribute of the `<html>` tag has a locale that exists in your `lang` directory.
- Make sure that the translation is not an empty string.

## Upgrade from version 3

Version 4 has breaking changes. Read [UPGRADE.md](UPGRADE.md) for the steps and [CHANGELOG.md](CHANGELOG.md) for all changes.

## Contributing

Run the PHP tests and the JavaScript tests:

```bash
composer install
vendor/bin/phpunit
```

```bash
npm install
npm test
```

GitHub Actions runs both test suites on all supported PHP, Laravel and Node.js versions for each pull request.

## License

The MIT License. See [LICENCE.txt](LICENCE.txt).
