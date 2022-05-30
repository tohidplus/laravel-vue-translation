const translations = require("./translations");
export default {
    translate(key, replacements = {}, allowKeyDotSplitting = true) {
        let lang = document.documentElement.lang;
        let word = translations[lang];
        let fallback_locale =
            document.querySelector('meta[name="fallback_locale"]') || null;

        const getAltValue = function(object, keys) {
            if (allowKeyDotSplitting === true) {
                // Avoid spliting on .dot for normal sentence case
                keys = keys.split(".");
            }

            return keys.reduce((o, k) => (o || {})[k], object);
        };

        let keys;
        if (allowKeyDotSplitting === true) {
            keys = key.split(".");
        } else {
            // Avoid spliting on .dot for normal sentence case
            keys = [key];
        }

        for (let i in keys) {
            try {
                word = word[keys[i]];
                if (word === undefined) {
                    if (fallback_locale.content) {
                        word =
                            getAltValue(
                                translations[fallback_locale.content],
                                key
                            ) || key;
                    } else {
                        word = key;
                    }
                    break;
                }
            } catch (e) {
                word = key;
                break;
            }
        }

        for (let i in replacements) {
            word = word.replace(`:${i}`, replacements[i]);
        }
        return word;
    }
};
