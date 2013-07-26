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
		$this->translateService = $di->get($options['translateService']);
		$translateService = $this->translateService;

		$_ = function($arguments, $expression) use ($di, $options, $translateService, $compiler) {
			extract($this->getView()->getParams());

			$first_argument = $compiler->expression($expression[0]['expr']);
			if (isset($expression[1])) {
				$second_argument = $compiler->expression($expression[1]['expr']);
			}

			if (isset($second_argument) || $first_argument[0] == '$') {
				$text = '$this->translateService->_(' . $arguments . ')';
			} else {
				$first_argument = trim($first_argument, '"');
				$first_argument = trim($first_argument, '\'');
				$text = "'" . addcslashes($translateService->_($first_argument), "'") . "'";
			}

			return $text;
		};

		// function "_()"
		$compiler->addFunction($this->_options['translateFunctionName'], $_);

		// filter "_"
		$compiler->addFilter($this->_options['translateFunctionName'], $_);

		// function "lang()"
		$this->_compiler->addFunction('lang', function() use($di, $options){
			extract($this->getView()->getParams());
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
