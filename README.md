Phalcon Volt Translated
=======================

### Example

**test.volt**
~~~html
<div>Current language - {{ lang() }}</div>
<label>{{ _('name') }}:</label><input type="text" />
~~~

**This volt template will translate to the view**

(For English)
```html
<div>Current language - <?= $this->lang() ?></div>
<label>Name:</label><input type="text" />
```

(For Russian)
```html
<div>Current language - <?= $this->lang() ?></div>
<label>Имя:</label><input type="text" />
```

### Configuration
```php
define('LANGUAGE_PARAMETER_NAME', 'lang');
define('TRANSLATE_SERVICE_NAME', 'translate');
define('VOLT_SERVICE_NAME', 'voltService');

// Step #1 Registrating volt (translated) service
$di->set(VOLT_SERVICE_NAME, function($view, $di) {
    $volt = new \Phalcon\Mvc\View\Engine\VoltTranslated($view, $di);
    $volt->setOptions(array(
	    // common options
	    'compiledPath'          => '../cache/tmpl/',
	    'compiledSeparator'     => '_',
	    'compiledExtension'     => '.php',
	    
	    // additional (translated) options
	    'translateFunctionName' => '_', // {{ _('key') }}
	    'translateService'      => TRANSLATE_SERVICE_NAME,
	    'mkdirMode'             => 0777,
	    'paramLang'             => LANGUAGE_PARAMETER_NAME,
    ));
    
    return $volt;
});

// Step #2 Registrating translation storage service
$di->set(TRANSLATE_SERVICE_NAME, function() use($di) {
  // Now we're getting the best language for the user
	$current_lang = $di->get('dispatcher')->getParam(LANGUAGE_PARAMETER_NAME); // or $di->get('request')->getBestLanguage();

  // MongoDB
	$mongo = new Mongo('mongodb://localhost:27017');
	$db = $mongo->selectDb('testdb');
	return new \Phalcon\Translate\Adapter\Mongo([
	  'db'         => $db,
	  'collection' => 'translate',
	  'lang'       => $current_lang,
	]);
	
	// MySQL
  // return new \Phalcon\Translate\Adapter\Database([
  //  'db'       => $this->di->get('db'), // Here we're getting the database from DI
  //  'table'    => 'translations', // The table that is storing the translations
  //  'language' => $current_lang,
  // ]);
});

// Step #3 Registrating view
$di->set('view', function() {
    $view = new \Phalcon\Mvc\View();
    $view->setViewsDir('../app/views/');
    $view->registerEngines(array(
        '.volt' => VOLT_SERVICE_NAME
    ));
    return $view;
});
```
