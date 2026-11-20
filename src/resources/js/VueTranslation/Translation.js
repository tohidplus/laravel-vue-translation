/**
 * Laravel translations for Vue.js and any other JavaScript application.
 *
 * Generate translations.json with: php artisan VueTranslation:generate
 * Documentation: https://github.com/tohidplus/laravel-vue-translation
 */
import translations from './translations.json';
import { createTranslator, createVuePlugin } from './translator.js';

/**
 * The current locale comes from the lang attribute of the <html> tag.
 */
function currentLocale() {
    return typeof document !== 'undefined' ? document.documentElement.lang : undefined;
}

/**
 * The fallback locale comes from <meta name="fallback_locale" content="en">.
 */
function fallbackLocale() {
    if (typeof document === 'undefined') {
        return undefined;
    }

    const meta = document.querySelector('meta[name="fallback_locale"]');

    return meta ? meta.getAttribute('content') : undefined;
}

export const translate = createTranslator(translations, {
    locale: currentLocale,
    fallbackLocale,
});

export const { install } = createVuePlugin(translate);

export { createTranslator, createVuePlugin, translations };

export default { translate, install };
