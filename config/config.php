<?php

define('LANGUAGE_PARAMETER_NAME', 'lang');
define('TRANSLATE_SERVICE_NAME', 'translate');
define('VOLT_SERVICE_NAME', 'voltService');

$di = \Phalcon\DI::getDefault();

//Register Mongo translate adapter
$di->set(TRANSLATE_SERVICE_NAME, function() use($di) {
	$mongo = new Mongo('mongodb://localhost:27017');
	$mongoDb = $mongo->selectDb('testdb');
	$current_lang = $di->get('dispatcher')->getParam(LANGUAGE_PARAMETER_NAME);

	return new \Phalcon\Translate\Adapter\Mongo(array(
		'db'         => $mongoDb,
		'collection' => 'translate',
		'lang'       => $current_lang,
	));
});



//Register Volt as a service
$di->set(VOLT_SERVICE_NAME, function($view, $di) {

    $volt = new \Phalcon\Mvc\View\Engine\VoltTranslated($view, $di);

    $volt->setOptions(array(
	    // common options
	    'compiledPath'          => '../cache/tmpl/',
	    'compiledSeparator'     => '_',
	    'compiledExtension'     => '.php',

	    // Additional options
	    'translateFunctionName' => '_',
	    'translateService'      => TRANSLATE_SERVICE_NAME,
	    'mkdirMode'             => 0777,
	    'paramLang'             => LANGUAGE_PARAMETER_NAME,
    ));

    return $volt;
});


//Register Volt as template engine
$di->set('view', function() {

    $view = new \Phalcon\Mvc\View();

    $view->setViewsDir('../app/views/');

    $view->registerEngines(array(
        '.volt' => VOLT_SERVICE_NAME
    ));

    return $view;
});

