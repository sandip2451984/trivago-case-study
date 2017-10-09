<?php

namespace AppBundle\Service;

class TypoFixer implements ITypoFixer {

    public function fix(string &$stringToFix) : void {
        if (! $this->isPspellInstalled()) return;

        $words = str_word_count($stringToFix, 1);

        foreach ($words as $word) {
            $suggestions = $this->getSuggestions($word);
            if (! count($suggestions)) continue;

            $bestSuggestion = $this->getBestSuggestion($word, $suggestions);
            $stringToFix = str_replace($word, $bestSuggestion, $stringToFix);
        }
    }

    private function getSuggestions(string $word) : array {
        $dictionary = pspell_new("en");

        // This returns TRUE if the word does not have any typos.
        if (pspell_check($dictionary, $word)) return [];

        return pspell_suggest($dictionary, $word);
    }

    private function getBestSuggestion(string $word, array $suggestions) : string {
        $bestSuggestion = [
            'similarityPercentage' => 0,
            'suggestion' => $word
        ];
        foreach ($suggestions as $suggestion) {
            $suggestion = strtolower($suggestion);
            $similarityPercentage = 0;
            similar_text($word, $suggestion, $similarityPercentage);

            if ($similarityPercentage > $bestSuggestion['similarityPercentage']) {
                $bestSuggestion['similarityPercentage'] = $similarityPercentage;
                $bestSuggestion['suggestion'] = $suggestion;
            }
        }

        return $bestSuggestion['suggestion'];
    }

    private function isPspellInstalled() {
         return function_exists('pspell_new');
    }

}