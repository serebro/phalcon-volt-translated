<?php

namespace Phalcon\Mvc\View\Engine\VoltTranslated;

use Phalcon\Mvc\View\Engine\Volt\Compiler as VoltCompiler;

class Compiler extends VoltCompiler {

	/**
	 * @param array $statement
	 * @return string
	 */
	public function compileEcho($statement) {
		$result = parent::compileEcho($statement);

		if (
			isset($statement['expr']['name']['value']) &&
			$statement['expr']['name']['value'] == $this->_options['translateFunctionName'] &&
			preg_match('<\?php echo \'(.*)\'; \?>', $result, $matches)
		) {
			$result = $matches[1];
		}

		return $result;
	}
}
