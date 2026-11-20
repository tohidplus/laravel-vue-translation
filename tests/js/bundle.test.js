/**
 * Bundle the published Translation.js the same way Vite does (ES modules and
 * a JSON import, no require()), then run the bundle with a fake document.
 * This test makes sure that issue #22 ("require is not defined") stays fixed.
 */
import { after, before, describe, it } from 'node:test';
import assert from 'node:assert/strict';
import { mkdtemp, rm, writeFile, cp } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join, dirname } from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';
import { build } from 'esbuild';

const source = join(dirname(fileURLToPath(import.meta.url)), '../../src/resources/js/VueTranslation');

function fakeDocument(lang, fallback) {
    return {
        documentElement: { lang },
        querySelector: (selector) =>
            selector === 'meta[name="fallback_locale"]' && fallback
                ? { getAttribute: (name) => (name === 'content' ? fallback : null) }
                : null,
    };
}

describe('Translation.js bundle', () => {
    let directory;
    let module;

    before(async () => {
        directory = await mkdtemp(join(tmpdir(), 'vue-translation-'));

        // Copy the files as "php artisan vendor:publish" does, then add compiled translations.
        await cp(source, join(directory, 'VueTranslation'), { recursive: true });
        await writeFile(
            join(directory, 'VueTranslation', 'translations.json'),
            JSON.stringify({
                en: { messages: { welcome: 'Welcome :name' }, 'I love programming.': 'I love programming.' },
                fr: { messages: { welcome: 'Bienvenue :name' } },
            })
        );

        const result = await build({
            entryPoints: [join(directory, 'VueTranslation', 'Translation.js')],
            bundle: true,
            format: 'esm',
            platform: 'browser',
            write: false,
            logLevel: 'silent',
        });

        assert.equal(result.errors.length, 0);
        assert.doesNotMatch(result.outputFiles[0].text, /\brequire\(/);

        const file = join(directory, 'bundle.mjs');
        await writeFile(file, result.outputFiles[0].text);
        module = await import(pathToFileURL(file).href);
    });

    after(async () => {
        delete globalThis.document;
        await rm(directory, { recursive: true, force: true });
    });

    it('reads the locale from the <html lang> attribute', () => {
        globalThis.document = fakeDocument('fr');

        assert.equal(module.translate('messages.welcome', { name: 'Tohid' }), 'Bienvenue Tohid');
    });

    it('reads the fallback locale from the meta tag', () => {
        globalThis.document = fakeDocument('fr', 'en');

        assert.equal(module.translate('I love programming.'), 'I love programming.');
    });

    it('keeps the default export of version 3', () => {
        globalThis.document = fakeDocument('en');

        assert.equal(module.default.translate('messages.welcome', { name: 'Tohid' }), 'Welcome Tohid');
        assert.equal(typeof module.default.install, 'function');
    });

    it('works without a document (server-side rendering)', () => {
        delete globalThis.document;

        assert.equal(module.translate('messages.welcome'), 'messages.welcome');
    });
});
