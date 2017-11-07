<?php

namespace Javidalpe\LaravelLocalizationAutomation\Commands;

use Exception;
use Guzzle\Service\Command\Factory\ServiceDescriptionFactory;
use Illuminate\Console\Command;
use Javidalpe\LaravelLocalizationAutomation\TranslationServices\TranslationServiceStrategyFactory;

class AutoTranslateCommand extends Command
{
	const VARIABLE_REGEX = '/(:\w+)/';
	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'localization:translate 
        {from : The original language to translate} 
        {to : The destination language to translate} 
        {--provider=' . TranslationServiceStrategyFactory::DEEPL .' : The third party service used to translate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uses a third party service to translate your localization files.';

    private $translatorService;
    private $serviceStrategyFactory;

    /**
     * AutoTranslateCommand constructor.
     * @param TranslationServiceStrategyFactory $serviceStrategyFactory
     */
    public function __construct(TranslationServiceStrategyFactory $serviceStrategyFactory)
    {
        parent::__construct();
        $this->serviceStrategyFactory = $serviceStrategyFactory;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setTranslatorService();

    	$from = $this->argument('from');
	    $to = $this->argument('to');

        $files = $this->getLocaleFile($from);
        $count = count($files, COUNT_RECURSIVE);
        $this->bar = $this->output->createProgressBar($count);
        $this->info(sprintf('%s lemmas found', $count));
        foreach ($files as $fileName => $dictionary) {
            $this->translateDictionary($dictionary, $from, $to);
            $this->writeDictionary($fileName, $dictionary, $to);
        }
        $this->bar->finish();
        $this->info('Finished');
    }

    /**
     * @param $lang
     * @return array
     */
    private function getLocaleFile($lang)
    {
        $langPath = $this->getLangPath($lang);
        $filesNames = $this->getFileNames($langPath);
        $lemmas = [];
        foreach ($filesNames as $fileName) {
            $fullPath = $langPath . '/' . $fileName;
            $lemmas[$fileName] = include $fullPath;
        }
        return $lemmas;
    }

    /**
     * @param $lang
     * @return mixed
     */
    private function getLangPath($lang)
    {
        return resource_path('lang/' . $lang);
    }

    /**
     * @param $langPath
     * @return array
     */
    private function getFileNames($langPath)
    {
        $allFiles = scandir($langPath);
        $filterFiles = [];
        foreach ($allFiles as $file) {
            $fullPath = $langPath . '/' . $file;
            if (is_file($fullPath)) {
                array_push($filterFiles, $file);
            }
        }

        return $filterFiles;
    }

    /**
     * @param $dictionary
     * @param $from
     * @param $to
     */
    private function translateDictionary(&$dictionary, $from, $to)
    {
        foreach ($dictionary as $key => $value) {
            if (is_array($value)) {
                $this->translateDictionary($value, $from, $to);
                $dictionary[$key] = $value;
            } else {
                $this->translateLemma($dictionary, $value, $key, $from, $to);
            }
            $this->bar->advance();
        }

    }

    /**
     * @param $value
     * @param $from
     * @param $to
     * @return null
     */
    private function getTranslated($value, $from, $to)
    {
        preg_match_all(self::VARIABLE_REGEX, $value, $matches, PREG_SET_ORDER, 0);
        if (count($matches) > 0) {
            $translatedText = $this->translateLemmaWithVariables($value, $from, $to, $matches);
        } else {
            $translatedText = $this->trans($from, $to, $value);
        }

        return $translatedText;
    }

    /**
     * @param $dictionary
     * @param $value
     * @param $key
     * @param $from
     * @param $to
     */
    private function translateLemma(&$dictionary, $value, $key, $from, $to)
    {
        if (strpos($value, "|")) {
            $this->translateChoiceLemma($dictionary, $value, $key, $from, $to);
        } else {
            $lemma = $this->getTranslated($value, $from, $to);
            $dictionary[$key] = $lemma;
        }
    }

    /**
     * @param $dictionary
     * @param $value
     * @param $key
     * @param $from
     * @param $to
     */
    private function translateChoiceLemma(&$dictionary, $value, $key, $from, $to)
    {
        $lemmas = explode("|", $value);
        $lemmaSingular = $this->getTranslated($lemmas[0], $from, $to);
        $lemmaPlural = $this->getTranslated($lemmas[1], $from, $to);
        $dictionary[$key] = $lemmaSingular . "|" . $lemmaPlural;
    }

    /**
     * @param $fileName
     * @param $dictionary
     * @param $to
     */
    private function writeDictionary($fileName, $dictionary, $to)
    {
        $content = $this->getFileContent($dictionary);
        $this->writeFile($fileName, $to, $content);
    }


    private function getFileContent($dictionary)
    {
        $content = var_export($dictionary, true);
        return sprintf("<?php
return %s;", $content);
    }


    private function createLangDirectoryIfNotExists($to)
    {
        $langDirectory = $this->getLangPath($to);
        if (!file_exists($langDirectory)) {
            mkdir($langDirectory, 0777, true);
        }
        return $langDirectory;
    }

    /**
     * @param $fileName
     * @param $to
     * @param $content
     */
    private function writeFile($fileName, $to, $content)
    {
        $langDirectory = $this->createLangDirectoryIfNotExists($to);
        $fullPath = $langDirectory . '/' . $fileName;
        file_put_contents($fullPath, $content);
    }

	/**
	 * @param $value
	 * @param $from
	 * @param $to
	 * @param $matches
	 *
	 * @return mixed
	 */
	private function translateLemmaWithVariables($value, $from, $to, $matches)
	{
		$replaces = [];
		$replaced = $value;
		foreach ($matches as $index => $match) {
			$replace = 'X' . ($index + 1);
			$replaced = str_replace($match[0], $replace, $replaced);
			$replaces[$replace] = $match[0];
		}
		$translatedText = $this->trans($from, $to, $replaced);
		foreach ($replaces as $replace => $match) {
			$translatedText = str_replace($replace, $match, $translatedText);
		}

		return $translatedText;
	}

    private function setTranslatorService()
    {
        $provider = $this->option('provider');
        $this->translatorService = $this->serviceStrategyFactory->getStrategy($provider);
    }

	/**
	 * @param $from
	 * @param $to
	 * @param $value
	 *
	 * @return mixed
	 */
	private function trans($from, $to, $value)
	{
		if (empty($value)) return $value;

		$try = 3;
		while ($try > 0) {
            try {
                $translatedText = $this->translatorService->translate($value, $from, $to);
                return $translatedText;
            } catch (Exception $e) {
                $try--;
            }
        }

	}
}
