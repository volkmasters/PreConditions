## PreConditions

MODx Revolution Extra which allow manipulate data with specified tags only in templates before
main parser execution (on OnParseTemplate event) by using Conditional output modifiers syntax.

### Purpose:

Allow guarantly exclude parts of template from main parser processing. Example: 
```
[[^isfolder:is=`1`:then=`[[-`]]
[[$Chunk]]
[[^isfolder:is=`1`:then=`]]`]]
```
In this example chunk Chunk will be processed only if current document is not a folder.  
Otherwise the code will be wrapped in comments tags and totally excluded from processing.

### Installation:

After installation in file core/model/modx/modtemplate.class.php change code near the 114 string:
```php
if (is_string($this->_output) && !empty($this->_output)) {
    /* turn the processed properties into placeholders */
    $this->xpdo->toPlaceholders($this->_properties, '', '.', true);
```
to
```php
if (is_string($this->_output) && !empty($this->_output)) {
    /* invoke OnParseTemplate event */
    $this->xpdo->invokeEvent('OnParseTemplate', array('content' => &$this->_output));  
    
    /* turn the processed properties into placeholders */
    $this->xpdo->toPlaceholders($this->_properties, '', '.', true);
```