[![GitHub issues](https://img.shields.io/github/issues/tohidplus/laravel-vue-translation.svg)](https://github.com/tohidplus/laravel-vue-translation/issues)
[![GitHub stars](https://img.shields.io/github/stars/tohidplus/laravel-vue-translation.svg)](https://github.com/tohidplus/laravel-vue-translation/stargazers)
[![Total Downloads](https://img.shields.io/packagist/dt/tohidplus/laravel-vue-translation.svg)](https://packagist.org/packages/tohidplus/laravel-vue-translation)
[![GitHub license](https://img.shields.io/github/license/tohidplus/laravel-vue-translation.svg)](https://github.com/tohidplus/mellat/blob/master/LICENSE.txt)
# Laravel translation in VueJS
This package helps you to have Laravel translation functionality in your client side applications specially in Vue js 
### Get started
 install the package via composer
 ```bash
 composer require tohidplus/laravel-vue-translation
 ```
 In the **config/app.php** file add the service provider
 ```php
'providers' => [
    //
    Tohidplus\Translation\TranslationServiceProvider::class,
    //
  ];
 ```
 Publish the package assets by running the command
 ```bash
 php artisan vendor:publish --provider="Tohidplus\Translation\TranslationServiceProvider"
 ```
 > This will publish the **Translation.js** file in **resources/js/VueTranslation** directory  
 
 Run the artisan command
 ```bash
 php artisan VueTranslation:generate --watch=1
 ```
  > This will compile down all the translation files in the **resources/lang** directory in the file **resources/js/VueTranslation/translations.json** 
 
 Open the file **resources/js/app.js** and add the Translation helper
 ```js
window.Vue = require('vue');
// If you want to add to window object
window.tranlate=require('./VueTranslation/Translation').default.translate;

// If you want to use it in your vue components
Vue.prototype.translate=require('./VueTranslation/Translation').default.translate;
```  
Compile the assets by running the command
```bash
npm run development
// or watch or production
```

### How to switch the languages?
This will get the current language form the document **lang** attribute
```html
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Document</title>
    </head>
    <body>
    
    </body>
</html>
```
### How to use?
Imagine this is the directory structure of **resources/lang** 
<pre>
|--en
   |--auth.php
   |--pagination.php
   |--passwords.php
   |--validation.php
   |--messages.php
|--fr
   |--auth.php
   |--pagination.php
   |--passwords.php
   |--validation.php
   |--messages.php  
</pre>
And the **messages.php** file is something like
```php
return [
    'foo'=>[
        'bar'=>'Some message'
    ]
];
```
You can get the value by calling the **translate** method
```js
translate('messages.foo.bar')
```
Example in Vue component
```vue
<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Example Component</div>
                    <div class="card-body">
                        {{translate('messages.foo.bar')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
      
    }
</script>
```
### Uses Fallback Locale
> To interact same like **Laravel**   trans() insert in your layout 
```html
<meta name="fallback_locale" content="{{ config('app.fallback_locale') }}">
```

### Replace attributes
> It's not recommended to use this package for showing validation errors but if you want you can replace :attribute, :size
etc by passing the second argument as an object.
```js
translate('validation.required',{
    attribute:translate('validation.attributes.title')
});
```
> **Notice:** if it could not find the value for the key you passed it will return the exact key
 
