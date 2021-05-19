const translations = require('./translations');
export default {
    translate(key,replacements={}) {
        let lang = document.documentElement.lang;
        let word = translations[lang];
        let fallback_locale = document.querySelector('meta[name="fallback_locale"]') || null;
        
        const keys = key.split('.');
        for (let i in keys) {
            try {
                word = word[keys[i]];
                if (word === undefined) {
                    if (fallback_locale.content){
                        word = translations[fallback_locale.content][keys[i]];
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
         for (let i in replacements){
             word=word.replace(`:${i}`,replacements[i]);
         }
         return word;
    }
}
