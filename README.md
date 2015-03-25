Phalcon Volt Translated
=======================
[![Phalconist](http://phalconist.com/serebro/phalcon-volt-translated/default.svg)](http://phalconist.com/serebro/phalcon-volt-translated)

This labrary converts volt templates in php files with translated phrases and stores them in a folder for each language. 
This method reduces the load on the system in productions.


### Example

**test.volt**
~~~html
<label>{{ _('first_name') }}:</label>
<label>{{ _('last_name') }}:</label>
<div>{{ _('current_language') }} - {{ lang() }}</div>
~~~

**This volt template will translate to the view**

For english

*/cache/volt/en/test.volt.php*
```html
<label>First name:</label>
<label>Last name:</label>
<div>Current language - <?= $this->lang() ?></div>
```

For russian

*/cache/volt/ru/test.volt.php*
```html
<label>Имя:</label>
<label>Фамилия:</label>
<div>Текущий язык - <?= $this->lang() ?></div>
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
