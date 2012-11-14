<?php

require_once 'PHPUnit2/Framework/TestCase.php';
require_once '../valkyrie/Autoloader.php';

Valkyrie_Autoloader::create()
	->addSourcePath('fixtures')
	->setBuildPath('tmp')
	->lowerCasePaths()
	->start(false);