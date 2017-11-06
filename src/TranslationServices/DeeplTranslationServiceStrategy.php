<?php


namespace Javidalpe\LaravelLocalizationAutomation\TranslationServices;


use ChrisKonnertz\DeepLy\DeepLy;

class DeeplTranslationServiceStrategy implements ITranslationServiceStrategy
{

    private $deeply;

    /**
     * AutoTranslateCommand constructor.
     * @param DeepLy $deeply
     */
    public function __construct(DeepLy $deeply = null)
    {
        $this->deeply = $deeply;
    }

    public function translate($lemma, $from, $to)
    {
        return $this->deeply->translate($lemma, strtoupper($to), strtoupper($from));
    }
}