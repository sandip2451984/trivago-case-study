<?php

namespace AppBundle\Service;

class DefaultAnalyzerConstants {

    const NEGATORS = [
        "not",
        "isn't",
        "aren't",
        "wasn't",
        "weren't",
        "doesn't",
        "didn't",
        "won't",
        "wouldn't",
        "shouldn't",
        "don't",
        "hasn't",
        "haven't",
        "isnt",
        "arent",
        "wasnt",
        "werent",
        "doesnt",
        "didnt",
        "wont",
        "wouldnt",
        "shouldnt",
        "dont",
        "hasnt",
        "havent",
        "no"
    ];

    const NEGATED_NEGATIVE_CRITERIA_SCORE_MODIFIER = -0.1;

    const NEGATED_POSITIVE_CRITERIA_SCORE_MODIFIER = -1;

}