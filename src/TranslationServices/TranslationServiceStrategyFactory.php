<?php


namespace Javidalpe\LaravelLocalizationAutomation\TranslationServices;


class TranslationServiceStrategyFactory
{

    const DEEPL = "deepl";

    public function getStrategy($provider)
    {
        switch ($provider) {
            case self::DEEPL:
                return new DeeplTranslationServiceStrategy();
            default:
                throw new \Exception("Provider not found.");
        }

    }
}