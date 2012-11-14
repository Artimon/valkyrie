<?php

class Valkyrie_Exception extends Exception {  }

/**
 * Handles file loading and code building.
 */
class Valkyrie_Autoloader {
	/**
	 * @var string
	 */
	private $scriptGroup = 'default';

	/**
	 * @var string
	 */
	private $buildPath;

	/**
	 * @var array
	 */
	private $sourcePaths = array();

	/**
	 * Defines whether paths shall be treated lower case or not.
	 *
	 * @var bool
	 */
	private $useLowerCase = false;

	/**
	 * Perform build or not.
	 *
	 * @var bool
	 */
	private $build = true;

	/**
	 * Build currently blocked or not.
	 *
	 * @var bool
	 */
	private $isBlocked;

	/**
	 * Triggered blocking or not.
	 *
	 * @var bool
	 */
	private $isBlocker = false;

	/**
	 * @var string
	 */
	private $destination;


	public function __destruct() {
		if ($this->isBlocker) {
			$this->release();
		}
	}

	/**
	 * @static
	 * @return Valkyrie_Autoloader
	 */
	public static function create() {
		return new self();
	}

	/**
	 * @param string $scriptGroup
	 * @return Valkyrie_Autoloader
	 */
	public function setScriptGroup($scriptGroup) {
		$this->scriptGroup = $scriptGroup;

		return $this;
	}

	/**
	 * @param string $buildPath
	 * @return Valkyrie_Autoloader
	 */
	public function setBuildPath($buildPath) {
		$this->buildPath = $this->sanitizePath($buildPath);

		return $this;
	}

	/**
	 * @param string $sourcePath
	 * @return Valkyrie_Autoloader
	 */
	public function addSourcePath($sourcePath) {
		$this->sourcePaths[] = $this->sanitizePath($sourcePath);

		return $this;
	}

	/**
	 * Call this to activate lower case paths.
	 *
	 * Example:
	 * class MyFolder_MyClass { ... }
	 * Default:		MyFolder/MyClass.php
	 * Lower Case:	myfolder/myclass.php
	 *
	 * @return Valkyrie_Autoloader
	 */
	public function lowerCasePaths() {
		$this->useLowerCase = true;

		return $this;
	}

	/**
	 * @param string $path
	 * @return string
	 */
	protected function sanitizePath($path) {
		return str_replace(array('/', "\\"), DIRECTORY_SEPARATOR, $path);
	}

	/**
	 * @return string
	 */
	protected function blockFilePath() {
		return $this->buildPath . DIRECTORY_SEPARATOR . 'valkyrieAutoloaderBusy';
	}

	/**
	 * @return bool
	 */
	protected function isBlocked() {
		if ($this->isBlocked === null) {
			$filename = $this->blockFilePath();
			$fileExists = $this->fileExists($filename);

			$this->isBlocked = false;
			if ($fileExists) {
				$lastModified = filemtime($filename);
				$lastModified = time() - $lastModified;

				$this->isBlocked = ($lastModified < 5);
			}
		}

		return $this->isBlocked;
	}

	/**
	 * @return void
	 */
	protected function block() {
		$this->isBlocker = true;

		touch($this->blockFilePath());
	}

	/**
	 * @return void
	 */
	protected function release() {
		unlink($this->blockFilePath());
	}

	/**
	 * @param string $filename
	 * @return bool
	 */
	protected function fileExists($filename) {
		return file_exists($filename);
	}

	/**
	 * Call Valkyrie_Autoloader::start(false); in your development
	 * environment for example.
	 *
	 * @param bool $build
	 * @throws Valkyrie_Exception
	 */
	public function start($build = true) {
		if ($this->buildPath === null) {
			$message = 'Use Valkyrie_Autoloader::setBuildPath(...) to set your build path.';
			throw new Valkyrie_Exception($message);
		}

		if (empty($this->sourcePaths)) {
			$message = 'Use Valkyrie_Autoloader::addSourcePath(...) to add source paths.';
			throw new Valkyrie_Exception($message);
		}

		$this->build = (bool)$build;

		spl_autoload_register(array(
			$this,
			'autoload'
		));

		$this->destination = $this->buildPath
			. DIRECTORY_SEPARATOR
			. $this->scriptGroup
			. '.php';

		if ($build && $this->destinationExists()) {
			require_once $this->destination;
		}
	}

	/**
	 * @param $className
	 */
	protected function autoload($className) {
		$fileName = $this->determineFileName($className);

		$this->addCode($fileName, $className);

		require_once $fileName;
	}

	/**
	 * @param string $className
	 * @return string
	 * @throws Valkyrie_Exception
	 */
	protected function determineFileName($className) {
		$classFile = str_replace('_', DIRECTORY_SEPARATOR, $className);
		if ($this->useLowerCase) {
			$classFile = strtolower($classFile);
		}

		foreach ($this->sourcePaths as $path) {
			$filename = $path . DIRECTORY_SEPARATOR . $classFile . '.php';

			if ($this->fileExists($filename)) {
				return $filename;
			}
		}

		$message = "Class \"{$className}\" could not be found.";
		throw new Valkyrie_Exception($message);
	}

	/**
	 * @param string $fileName
	 * @param string $className
	 */
	protected function addCode($fileName, $className) {
		if (!$this->build) {
			return;
		}

		if ($this->isBlocked()) {
			return;
		}

		$this->block();

		$code = file_get_contents($fileName);
		$isAbstract = (false !== strpos($code, 'abstract class ' . $className));

		if ($isAbstract) {
			/*
			 * Prepend abstract classes to avoid loading problems.
			 * Interfaces could be added, too.
			 */
			if ($this->destinationExists()) {
				$codeCollection = file_get_contents($this->destination);
				$codeCollection = substr($codeCollection, 5);

				$code .= $codeCollection;
			}

			file_put_contents($this->destination, $code);
		}
		else {
			if ($this->destinationExists()) {
				// Strip php tag to not duplicate it.
				$code = substr($code, 5);
			}

			file_put_contents($this->destination, $code, FILE_APPEND);
		}
	}

	/**
	 * @return bool
	 */
	protected function destinationExists() {
		return $this->fileExists($this->destination);
	}
}