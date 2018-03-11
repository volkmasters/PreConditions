## PreConditions

MODX Revolution компонент который позволяет манипулировать данными находящимися в специальных тегах до обработки шаблонов
основным парсером (на первом OnParseDocument) используя синтаксис условных модификаторов (Conditional output modifiers).

### Назначение:

Позволяет гарантированно избежать обработки части шаблона основным парсером. Пример:
```
[[^isfolder:is=`1`:then=`[[-`]]
[[$Chunk]]
[[^isfolder:is=`1`:then=`]]`]]
```
В данном примере чанк Chunk будет обработан только при условии, что текущий документ не папка.  
В противном случае код будет обернут в комментарии, что полностью исключит его из обработки.