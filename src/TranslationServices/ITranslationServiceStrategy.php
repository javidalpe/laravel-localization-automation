<?php

namespace Javidalpe\LaravelLocalizationAutomation\TranslationServices;

interface ITranslationServiceStrategy
{
    /**
     * Translate a lemma from a language to another
     * @param string $lemma
     * @param string $from
     * @param string $to
     * @return string
     */
    public function translate($lemma, $from, $to);
}