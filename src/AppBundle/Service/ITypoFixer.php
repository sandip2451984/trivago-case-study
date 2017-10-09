<?php

namespace AppBundle\Service;

interface ITypoFixer {

    public function fix(string &$stringToFix) : void;

}