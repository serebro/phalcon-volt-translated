<?php

namespace Phalcon\Mvc\View\Engine\VoltTranslated;

use Phalcon\Mvc\View\Engine\Volt\Compiler as VoltCompiler;

class Compiler extends VoltCompiler {

	/**
	 * @param array $statement
	 * @return string
	 */
	public function compileEcho($statement) {
		$c1 = isset($statement['expr']['name']['value']) && $statement['expr']['name']['value'] == $this->_options['translateFunctionName'];
		$c2 = isset($statement['expr']['left']['name']['value']) && $statement['expr']['left']['name']['value'] == $this->_options['translateFunctionName'];
		if ($c1 || $c2) {
			$exp = $this->expression($statement['expr']);
			$result = trim(trim($exp, '"'), '\'');
			if ($result !== $exp) {
				return $result;
			}
		}

		return parent::compileEcho($statement);
	}
}
