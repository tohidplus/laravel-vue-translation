const translations = require('./translations');
export default {
    translate(key,replacements={}) {
        let lang = document.documentElement.lang;
        let word = translations[lang];
        const keys = key.split('.');
        for (let i in keys) {
            try {
                word = word[keys[i]];
                if (word === undefined) {
                    word = key;
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
