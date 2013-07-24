<?php

namespace Phalcon\Mvc\View\Engine;

use Phalcon\Mvc\View\Engine;

/**
 * Class VoltTranslated
 * Additional options:
 *  - translateFunctionName - Default "_"
 *  - translateService      - Default "translate"
 *  - mkdirMode             - Default 0777
 *  - paramLang             - Default "lang"
 *
 * @package Phalcon\Mvc\View\Engine
 */
class VoltTranslated extends Volt {

	/**
	 * Adapter constructor
	 * @param \Phalcon\Mvc\View $view
	 * @param \Phalcon\DI       $di
	 */
	public function __construct($view, $di = null) {
		parent::__construct($view, $di);
	}

	public function getCompiler() {
		if ($this->_compiler) {
			return $this->_compiler;
		}

		$this->_compiler = new VoltTranslated\Compiler($this->_view);
		$this->_compiler->setDI($this->getDI());

		return $this->_compiler;
	}

	/**
	 * Renders a view using the template engine
	 * @param string $templatePath
	 * @param array  $params
	 * @param null   $mustClean
	 */
	public function render($templatePath, $params, $mustClean = null) {
		$this->_registryFunctions();

		$this->_options['compiledPath'] = $this->_getCompiledTemplatePath();
		$this->_options['mkdirMode'] = empty($this->_options['mkdirMode']) ? 0777 : $this->_options['mkdirMode'];
		$this->_options['paramLang'] = empty($this->_options['paramLang']) ? 'lang' : $this->_options['paramLang'];
		$this->_options['translateService'] = empty($this->_options['translateService']) ? 'translate' : $this->_options['translateService'];
		$this->_options['translateFunctionName'] = empty($this->_options['translateFunctionName']) ? '_' : $this->_options['translateFunctionName'];
		$this->_compiler->setOptions($this->_options);

		parent::render($templatePath, $params, $mustClean);
	}

	private function _registryFunctions() {
		$di = $this->getDI();
		$compiler = $this->getCompiler();
		$options = $this->getOptions();

		// function "_()"
		$compiler->addFunction($this->_options['translateFunctionName'], function($arguments) use($di, $options){
			$translateService = $di->get($options['translateService']);
			$text = eval('return $translateService->_(' . $arguments . ');');
			return "'" . addcslashes($text, "'") . "'";
		});

		// function "lang()"
		$this->_compiler->addFunction('lang', function() use($di, $options){
			$text = $di->get('dispatcher')->getParam($options['paramLang']);
			return "'" . addcslashes($text, "'") . "'";
		});
	}

	public function _getCompiledTemplatePath(){
		$di = $this->getDI();
		$lang = DIRECTORY_SEPARATOR . $di->get('dispatcher')->getParam($this->_options['paramLang']) . DIRECTORY_SEPARATOR;
		if (strpos($this->_options['compiledPath'], $lang) !== false) {
			return $this->_options['compiledPath'];
		}

		$path = realpath($this->_options['compiledPath']) . $lang;

		if (!is_dir($path)) {
			mkdir($path, $this->_options['mkdirMode'], true);
		}

		return $path;
	}
}
