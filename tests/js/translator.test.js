import { describe, it } from 'node:test';
import assert from 'node:assert/strict';

import {
    createTranslator,
    createVuePlugin,
    makeReplacements,
} from '../../src/resources/js/VueTranslation/translator.js';

const translations = {
    en: {
        'I love programming.': 'I love programming.',
        'Welcome, :name!': 'Welcome, :name!',
        'Untranslated JSON line': '',
        404: 'Not found',
        messages: {
            foo: { bar: 'Some message' },
            hello: 'Hello :name',
            empty: '',
            only_in_english: 'Only in English',
            count: 3,
        },
        validation: {
            required: 'The :attribute field is required.',
            attributes: { title: 'title' },
        },
    },
    fr: {
        'I love programming.': "J'adore programmer.",
        messages: {
            foo: { bar: 'Un message' },
            empty: '',
        },
    },
    pt_BR: {
        messages: { foo: { bar: 'Uma mensagem' } },
    },
};

const translatorFor = (locale, fallbackLocale) => createTranslator(translations, { locale, fallbackLocale });

describe('createTranslator', () => {
    it('translates dot notation keys from PHP files', () => {
        assert.equal(translatorFor('en')('messages.foo.bar'), 'Some message');
        assert.equal(translatorFor('fr')('messages.foo.bar'), 'Un message');
    });

    it('translates JSON keys that contain dots (issue #12)', () => {
        assert.equal(translatorFor('fr')('I love programming.'), "J'adore programmer.");
    });

    it('keeps the old third argument to turn off dot splitting', () => {
        assert.equal(translatorFor('fr')('I love programming.', {}, false), "J'adore programmer.");
        assert.equal(translatorFor('en')('messages.foo.bar', {}, false), 'messages.foo.bar');
    });

    it('returns the key when no translation exists', () => {
        assert.equal(translatorFor('en')('messages.unknown'), 'messages.unknown');
        assert.equal(translatorFor('en')('Not translated.'), 'Not translated.');
        assert.equal(translatorFor('de')('messages.foo.bar'), 'messages.foo.bar');
    });

    it('uses the fallback locale when the current locale has no value', () => {
        assert.equal(translatorFor('fr', 'en')('messages.only_in_english'), 'Only in English');
        assert.equal(translatorFor('de', 'en')('messages.foo.bar'), 'Some message');
    });

    it('treats an empty string as a missing translation (issue #23)', () => {
        assert.equal(translatorFor('en')('messages.empty'), 'messages.empty');
        assert.equal(translatorFor('en')('Untranslated JSON line'), 'Untranslated JSON line');
        assert.equal(translatorFor('fr', 'en')('messages.empty'), 'messages.empty');
    });

    it('accepts the locale with a dash or an underscore', () => {
        assert.equal(translatorFor('pt-BR')('messages.foo.bar'), 'Uma mensagem');
        assert.equal(translatorFor('pt_BR')('messages.foo.bar'), 'Uma mensagem');
    });

    it('reads the locale from a function each time', () => {
        let locale = 'en';
        const translate = createTranslator(translations, { locale: () => locale });

        assert.equal(translate('messages.foo.bar'), 'Some message');
        locale = 'fr';
        assert.equal(translate('messages.foo.bar'), 'Un message');
    });

    it('returns groups and non-string values as they are', () => {
        assert.deepEqual(translatorFor('en')('messages.foo'), { bar: 'Some message' });
        assert.equal(translatorFor('en')('messages.count'), 3);
        assert.equal(translatorFor('en')('404'), 'Not found');
    });

    it('replaces placeholders', () => {
        const translate = translatorFor('en');

        assert.equal(translate('messages.hello', { name: 'Taylor' }), 'Hello Taylor');
        assert.equal(translate('Welcome, :name!', { name: 'Taylor' }), 'Welcome, Taylor!');
        assert.equal(
            translate('validation.required', { attribute: translate('validation.attributes.title') }),
            'The title field is required.'
        );
    });

    it('replaces placeholders in the key when no translation exists', () => {
        assert.equal(translatorFor('en')('Hi :name', { name: 'Taylor' }), 'Hi Taylor');
    });

    it('does not use properties of Object.prototype as translations', () => {
        const translate = translatorFor('en');

        assert.equal(translate('constructor'), 'constructor');
        assert.equal(translate('messages.__proto__'), 'messages.__proto__');
        assert.equal(createTranslator(translations, { locale: 'toString' })('messages.foo.bar'), 'messages.foo.bar');
    });

    it('works without translations or locales', () => {
        assert.equal(createTranslator(undefined)('messages.foo.bar'), 'messages.foo.bar');
        assert.equal(createTranslator(translations)('messages.foo.bar'), 'messages.foo.bar');
    });
});

describe('makeReplacements', () => {
    it('replaces every occurrence of a placeholder', () => {
        assert.equal(makeReplacements(':name and :name', { name: 'a' }), 'a and a');
    });

    it('changes the case of the value like Laravel', () => {
        assert.equal(
            makeReplacements(':name, :Name, :NAME', { name: 'taylor' }),
            'taylor, Taylor, TAYLOR'
        );
    });

    it('replaces the longest placeholder first', () => {
        assert.equal(makeReplacements(':name :names', { name: 'a', names: 'b' }), 'a b');
    });

    it('does not replace placeholders inside the replaced values', () => {
        assert.equal(makeReplacements(':a :b', { a: ':b', b: 'x' }), ':b x');
    });

    it('converts values to strings', () => {
        assert.equal(makeReplacements(':count items', { count: 5 }), '5 items');
        assert.equal(makeReplacements('[:value]', { value: null }), '[]');
    });

    it('returns non-string lines as they are', () => {
        const group = { a: ':name' };

        assert.equal(makeReplacements(group, { name: 'x' }), group);
    });
});

describe('createVuePlugin', () => {
    const translate = () => 'translated';

    it('adds translate() to the global properties of a Vue 3 app', () => {
        const provided = {};
        const app = {
            config: { globalProperties: {} },
            provide: (key, value) => {
                provided[key] = value;
            },
        };

        createVuePlugin(translate).install(app);

        assert.equal(app.config.globalProperties.translate, translate);
        assert.equal(provided.translate, translate);
    });

    it('adds translate() to the prototype of Vue 2', () => {
        function Vue() {}

        createVuePlugin(translate).install(Vue);

        assert.equal(Vue.prototype.translate, translate);
    });

    it('accepts other global names', () => {
        const app = { config: { globalProperties: {} } };

        createVuePlugin(translate).install(app, { name: ['translate', '__'] });

        assert.equal(app.config.globalProperties.translate, translate);
        assert.equal(app.config.globalProperties.__, translate);
    });
});
