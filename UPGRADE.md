# Upgrade guide

## From 3.x to 4.0

### 1. Check the requirements

Version 4 needs PHP 8.2 or newer and Laravel 11, 12 or 13. For Laravel 9 and 10, continue to use version 3.

```bash
composer require tohidplus/laravel-vue-translation:^4.0
```

### 2. Publish the JavaScript files again

The JavaScript files changed. `vendor:publish` does not replace files that exist, so use `--force`. This also replaces `translations.json`, so compile the translations again after.

```bash
php artisan vendor:publish --tag=vue-translation --force
php artisan VueTranslation:generate
```

If you changed the published `Translation.js`, save your changes before you run the command.

### 3. Use `import` instead of `require()`

`Translation.js` is now an ES module. Vite needs `import`:

```js
// Before
window.translate = require('./VueTranslation/Translation').default.translate;
Vue.prototype.translate = require('./VueTranslation/Translation').default.translate;

// After (Vue 3)
import VueTranslation from './VueTranslation/Translation';
app.use(VueTranslation);

// After (Vue 2)
import VueTranslation from './VueTranslation/Translation';
Vue.use(VueTranslation);

// After (no Vue)
import { translate } from './VueTranslation/Translation';
window.translate = translate;
```

Webpack and Laravel Mix still accept the `require()` lines of version 3.

### 4. Check the changes in translate()

- **Empty strings are missing values.** A key with the value `""` now gives the fallback locale value or the key. Before, it gave an empty string.
- **Exact keys come first.** `translate('I love programming.')` now finds the JSON string without the third argument. The third argument still works.
- **All placeholders are replaced.** Before, only the first `:name` in a line was replaced. Now all of them are replaced, and `:Name` and `:NAME` change the case of the value, as in Laravel.
- **Regional locales.** `<html lang="pt-BR">` now also finds the translations of `lang/pt_BR`.

### 5. Check the command

- `--watch` without a value now starts watch mode. `--watch=1` still works.
- The command no longer clears the terminal screen. Use `-v` to see each compiled file.
- The command now returns exit code 1 when it cannot compile, so a deploy script stops on errors.
- The command ignores files that are not `.php` or `.json` files, for example `.DS_Store`.

### 6. Check your PHP code (only if you extend the package)

- The `elementaryframework/fire-fs` dependency is removed. The package now has its own watcher: `Tohidplus\Translation\Watcher\TranslationWatcher`.
- The `watcher` container binding, the `CLIPrinter` class and the `FileWatcher\Listener` class are removed.
- The methods of the `Tohidplus\Translation\Contract\TranslationFileHelper` interface now have return types. Add the same return types to your own implementations.
- `LaravelVueTranslation::compile()` now returns the compiled translations and accepts an optional callback for each compiled file.
- The service provider now binds `TranslationFileHelper` only if your application did not bind it before.
