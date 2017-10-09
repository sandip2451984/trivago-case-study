<?php

namespace Tests\AppBundle\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Service\TypoFixer;

class TypoFixerTest extends WebTestCase {

    private $TypoFixer;

    public function __construct() {
        parent::__construct();
        $this->TypoFixer = new TypoFixer();
    }

    public function testTypoFixer() {
        if (! function_exists('pspell_new')) {
            echo "\n\nphp7.1-pspell extension is not enabled. Aborting tests for TypoFixer.\n\n";
            return;
        }

        $review = 'This is a revieww with typos in it.';
        $expectedResult = 'This is a review with typos in it.';

        $this->TypoFixer->fix($review);

        $this->assertEquals($expectedResult, $review);
    }


}
