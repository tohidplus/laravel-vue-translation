/**
 * Framework-free translation logic for the compiled Laravel translations.
 *
 * This file has no imports and no access to the DOM, so you can use it in
 * any JavaScript environment (browser, Node.js, SSR, tests).
 */

const hasOwn = (object, key) =>
    object !== null && typeof object === 'object' && Object.prototype.hasOwnProperty.call(object, key);

const resolve = (value) => (typeof value === 'function' ? value() : value);

const isMissing = (value) => value === undefined || value === null || value === '';

const ucfirst = (value) => value.charAt(0).toUpperCase() + value.slice(1);

const escapeRegExp = (value) => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

/**
 * Get the locale names to try for one locale. The <html lang> attribute uses
 * "pt-BR", but the Laravel lang directory uses "pt_BR", so try both.
 *
 * @param {string|undefined|null} locale
 * @returns {string[]}
 */
function localeCandidates(locale) {
    if (!locale) {
        return [];
    }

    return [...new Set([locale, locale.replace(/-/g, '_')])];
}

/**
 * Find the line for a key in the translations of one locale.
 *
 * 1. Look for the exact key. This finds JSON translation strings such as
 *    "I love programming.", which can contain dots.
 * 2. Split the key on dots and walk down the tree. This finds lines from PHP
 *    files, such as "messages.foo.bar".
 */
function findLine(lines, key, allowKeyDotSplitting) {
    if (hasOwn(lines, key) && !isMissing(lines[key])) {
        return lines[key];
    }

    if (!allowKeyDotSplitting) {
        return undefined;
    }

    let value = lines;

    for (const segment of key.split('.')) {
        if (!hasOwn(value, segment)) {
            return undefined;
        }

        value = value[segment];
    }

    return value;
}

/**
 * Replace the ":placeholder" parts of a line, the same as Laravel does.
 *
 * ":name" gets the value, ":Name" gets the value with an upper case first
 * letter, and ":NAME" gets the value in upper case.
 *
 * @param {*} line
 * @param {Object<string, *>} replacements
 * @returns {*}
 */
export function makeReplacements(line, replacements = {}) {
    if (typeof line !== 'string' || !replacements || typeof replacements !== 'object') {
        return line;
    }

    const map = {};

    for (const [name, raw] of Object.entries(replacements)) {
        if (name === '') {
            continue;
        }

        const value = raw === undefined || raw === null ? '' : String(raw);

        map[`:${ucfirst(name)}`] = ucfirst(value);
        map[`:${name.toUpperCase()}`] = value.toUpperCase();
        map[`:${name}`] = value;
    }

    const placeholders = Object.keys(map).sort((a, b) => b.length - a.length);

    if (placeholders.length === 0) {
        return line;
    }

    const pattern = new RegExp(placeholders.map(escapeRegExp).join('|'), 'g');

    return line.replace(pattern, (placeholder) => map[placeholder]);
}

/**
 * Create a translate() function for a set of compiled translations.
 *
 * @param {Object<string, Object>} translations The content of translations.json, keyed by locale.
 * @param {Object} [options]
 * @param {string|function(): string} [options.locale] The current locale, or a function that returns it.
 * @param {string|function(): string} [options.fallbackLocale] The fallback locale, or a function that returns it.
 * @returns {function(string, Object=, boolean=): *}
 */
export function createTranslator(translations, options = {}) {
    const all = translations && typeof translations === 'object' ? translations : {};

    /**
     * Translate a key.
     *
     * If the key has no value (or an empty string) in the current locale,
     * the fallback locale is used. If the fallback locale has no value too,
     * the key is returned.
     *
     * @param {string} key A key such as "messages.welcome", or a JSON translation string.
     * @param {Object<string, *>} [replacements] Values for the ":placeholder" parts.
     * @param {boolean} [allowKeyDotSplitting=true] Set to false to look up the exact key only.
     * @returns {*} The translated line. Groups (objects) are returned as they are.
     */
    return function translate(key, replacements = {}, allowKeyDotSplitting = true) {
        const text = String(key);
        const locales = [
            ...new Set([
                ...localeCandidates(resolve(options.locale)),
                ...localeCandidates(resolve(options.fallbackLocale)),
            ]),
        ];

        for (const locale of locales) {
            if (!hasOwn(all, locale)) {
                continue;
            }

            const line = findLine(all[locale], text, allowKeyDotSplitting);

            if (!isMissing(line)) {
                return makeReplacements(line, replacements);
            }
        }

        return makeReplacements(text, replacements);
    };
}

/**
 * Create a Vue plugin that adds translate() to all components.
 * The plugin works with Vue 3 (app.use) and Vue 2 (Vue.use).
 *
 * @param {function} translate
 * @returns {{install: function(Object, Object=): void}}
 */
export function createVuePlugin(translate) {
    return {
        install(app, options = {}) {
            const names = [].concat(options.name || 'translate');

            if (app && app.config && app.config.globalProperties) {
                // Vue 3
                for (const name of names) {
                    app.config.globalProperties[name] = translate;
                }

                if (typeof app.provide === 'function') {
                    app.provide('translate', translate);
                }
            } else if (app && app.prototype) {
                // Vue 2
                for (const name of names) {
                    app.prototype[name] = translate;
                }
            }
        },
    };
}
