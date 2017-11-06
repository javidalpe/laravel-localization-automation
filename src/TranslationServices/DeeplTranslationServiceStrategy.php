<?php


namespace Javidalpe\LaravelLocalizationAutomation\TranslationServices;


use ChrisKonnertz\DeepLy\DeepLy;

class DeeplTranslationServiceStrategy implements ITranslationServiceStrategy
{

    private $deeply;

	/**
	 * DeeplTranslationServiceStrategy constructor.
	 */
    public function __construct()
    {
        $this->deeply = new DeepLy();
    }

    public function translate($lemma, $from, $to)
    {
        return $this->deeply->translate($lemma, strtoupper($to), strtoupper($from));
    }
}