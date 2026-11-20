# Changelog

All notable changes to this package are in this file. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the package uses [Semantic Versioning](https://semver.org/).

## [4.0.0] - 2026-11-20

Read [UPGRADE.md](UPGRADE.md) before you upgrade.

### Added

- Support for Laravel 11, 12 and 13, and for PHP 8.2 to 8.6.
- A Vue plugin for Vue 3 (`app.use(VueTranslation)`) and Vue 2 (`Vue.use(VueTranslation)`), with a `name` option for other global names such as `__` ([#2](https://github.com/tohidplus/laravel-vue-translation/issues/2)).
- A named `translate` export for the Composition API and `<script setup>`.
- `createTranslator()` in the new `translator.js` file, to use your own locale source.
- Laravel-style placeholders: all occurrences are replaced, and `:Name` and `:NAME` change the case of the value.
- `<html lang="pt-BR">` also finds the translations of `lang/pt_BR`.
- The `vue-translation:generate` alias, the `--interval` option and `-v` output for the command.
- The `vue-translation` publish tag.
- Clear error messages when the `lang` directory does not exist, a JSON file is not valid, or a PHP file does not return an array.
- PHPUnit tests, JavaScript tests and a GitHub Actions workflow.

### Changed

- `Translation.js` is an ES module and imports `translations.json` with `import`. It works with Vite ([#22](https://github.com/tohidplus/laravel-vue-translation/issues/22)).
- `translate()` looks for the exact key first, then splits the key on dots. JSON strings that contain dots work without the third argument ([#12](https://github.com/tohidplus/laravel-vue-translation/issues/12)).
- An empty string counts as a missing translation. `translate()` gives the fallback locale value or the key ([#23](https://github.com/tohidplus/laravel-vue-translation/issues/23)).
- `--watch` without a value starts watch mode.
- The command no longer clears the screen and no longer waits between files. It returns exit code 1 when it cannot compile.
- `translations.json` keeps Unicode characters as they are, so the file is smaller.
- The `TranslationFileHelper` interface has return types.

### Fixed

- Numeric keys, such as `"404"`, keep their value. Before, the merge of the files changed them to `0`, `1`, ...
- Files that are not `.php` or `.json` files, such as `.DS_Store`, are ignored.
- An empty `lang` directory gives `{}` instead of `[]`.
- In watch mode, an error in a translation file does not stop the watcher.

### Removed

- Support for PHP 8.0 and 8.1, and for Laravel 9 and 10. Use version 3 for these versions.
- The `elementaryframework/fire-fs` dependency, the `watcher` container binding, and the `CLIPrinter` and `FileWatcher\Listener` classes.

## Older versions

See the [GitHub releases](https://github.com/tohidplus/laravel-vue-translation/releases) for version 3.0.0 and earlier.

[4.0.0]: https://github.com/tohidplus/laravel-vue-translation/compare/3.0.0...4.0.0
