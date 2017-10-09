<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

interface IAnalyzer {

    public function __construct(EntityManagerInterface $em, ContainerInterface $container, AnalyzerResponse $ar, ?ITypoFixer $tf = NULL);

    public function analyze(string $review) : AnalyzerResponse;

}