# Translate your Laravel locale files
This package translates your Laravel localization files using a third party service. Currently only supports [DeepL](https://www.deepl.com).

## Installation
You can install the package via composer:
```
composer require --dev javidalpe/laravel-localization-automation
```  

If you're on Laravel 5.4 or earlier, you'll need to add the following to your config/app.php:
```php
'providers' => [
    ...
    Javidalpe\LaravelLocalizationAutomation\LaravelLocalizationAutomationServiceProvider::class,
];
```

## Usage 
This command translates all your files in `/lang/{from}/` directory and create the new ones in `/lang/{to}/` directory. 
```
php artisan localization:translate {from} {to} {--provider=deepl}
```

**Example using default provider:**
```
php artisan localization:translate es fr
```

**Example using custom provider:**
```
php artisan localization:translate es fr --provider=deepl
```

### Tips
Avoid using HTML tags inside your lemmas.
Wrong
```php
'welcome.greetings' => '<strong>Hey!</strong>',
```
Good
```php
'welcome.greetings' => 'Hey!',
```
## Supported Languages
### Deeply
DeepL(y) supports these languages:

| Code | Language      |
|------|---------------|
| DE   | German        |
| EN   | English       |
| FR   | French        |
| ES   | Spanish       |
| IT   | Italian       |
| NL   | Dutch         |
| PL   | Polish        |


DeepL says they will [add more languages](https://www.heise.de/newsticker/meldung/Maschinelles-Uebersetzen-Deutsches-Start-up-DeepL-will-230-Sprachkombinationen-unterstuetzen-3836533.html) 
in the future, such as Chinese and Russian.

## How it works
1. Foreach file in `/lang/{from}/` directory.
2. Foreach lemma in file.
3. Split pluralization lemmas.
4. Replace each place-holder with temp string.
5. Translate the lemma using the provider.
6. Undo replacement.
7. Assembly all lemmas in new files at `/lang/{to}/` directory.

## Contributing
1. Fork it
2. Create your feature branch (git checkout -b my-new-translation-service)
3. Commit your changes (git commit -am 'Added translation service')
4. Push to the branch (git push origin my-new-translation-service)
5. Create new Pull Request

### Add a new translation service
1. Create a new class inside `/src/TranslationServices`. Implements `TranslationServiceStrategy`.
2. Add this class to `TranslationServiceStrategyFactory`.
3. Update this `README.md file.

## Credits
- DeepL GmbH: https://www.deepl.com/
- Chris Konnertz: https://github.com/chriskonnertz/DeepLy

## License
The MIT License (MIT). Please see License File for more information.