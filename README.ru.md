## PreConditions

MODX Revolution компонент который позволяет манипулировать данными находящимися в специальных тегах до обработки шаблонов
основным парсером (на событие OnParseTemplate) используя синтаксис условных модификаторов (Conditional output modifiers).

### Назначение:

Позволяет гарантированно избежать обработки части шаблона основным парсером. Пример:
```
[[^isfolder:is=`1`:then=`[[-`]]
[[$Chunk]]
[[^isfolder:is=`1`:then=`]]`]]
```
В данном примере чанк Chunk будет обработан только при условии, что текущий документ не папка.  
В противном случае код будет обернут в комментарии, что полностью исключит его из обработки.

### Установка:

После установки в файле core/model/modx/modtemplate.class.php поменяйте код рядом с 114 строкой:
```php
if (is_string($this->_output) && !empty($this->_output)) {
    /* turn the processed properties into placeholders */
    $this->xpdo->toPlaceholders($this->_properties, '', '.', true);
```
на
```php
if (is_string($this->_output) && !empty($this->_output)) {
    /* invoke OnParseTemplate event */
    $this->xpdo->invokeEvent('OnParseTemplate', array('content' => &$this->_output));  
    
    /* turn the processed properties into placeholders */
    $this->xpdo->toPlaceholders($this->_properties, '', '.', true);
```