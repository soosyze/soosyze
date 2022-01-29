<?php

$finder = PhpCsFixer\Finder::create()
    ->notPath('bootstrap/requirements.php')
    ->notPath('build')
    ->in(__DIR__);

$config = new Soosyze\PhpCsFixer\Config();
$config->setFinder($finder);

return $config;