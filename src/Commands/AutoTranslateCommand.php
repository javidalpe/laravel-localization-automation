<?php

namespace Javidalpe\LaravelLocalizationAutomation\Commands;

use ChrisKonnertz\DeepLy\DeepLy;
use Illuminate\Console\Command;

class AutoTranslateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uses a third party service to translate your localization files.';

    private $deeply;

    /**
     * AutoTranslateCommand constructor.
     * @param DeepLy $deeply
     */
    public function __construct(DeepLy $deeply)
    {
        parent::__construct();
        $this->deeply = $deeply;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $files = $this->getLocaleFile('es');
        foreach ($files as $fileName => $dictionary) {
            $this->translateDictionary($dictionary, $from = 'es', $to = 'it');
            $this->writeDictionary($fileName, $dictionary, $to);
            echo "Fin {$fileName} -------------------";
        }
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
            } else {
                $this->translateLemma($dictionary, $value, $key, $from, $to);
            }
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
        try {
            $valueWithoutVariable = preg_replace('/:\w+/', 'X', $value);
            $translatedText = $this->deeply->translate($valueWithoutVariable, strtoupper($to), strtoupper($from));
            echo "[{$value} -> {$translatedText}]\n";
            return $translatedText;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return null;
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
        if (str_contains("|", $value)) {
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
    private function translateChoiceLemma($dictionary, $value, $key, $from, $to)
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
        $content = $this->arrayEncode($dictionary, 1);
        return sprintf("<?php
return %s;", $content);
    }

    private function arrayEncode($dictionary, $int)
    {
        $content = '';
        foreach ($dictionary as $key => $value) {
            if (is_array($value)) {
                $value = $this->arrayEncode($value, $int + 1);
            } else {
                $value = "\"{$value}\"";
            }
            $pad = str_repeat("\t", $int * 2);
            $line = sprintf("%s'%s' => %s,\n", $pad, $key, $value);;
            $content = $content . $line;
        }
        return sprintf("array(
%s
)", $content);
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
}
