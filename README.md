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
