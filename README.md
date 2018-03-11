## PreConditions

MODx Revolution Extra which allow manipulate data with specified tags only in templates before
main parser execution (on first OnParseDocument) by using Conditional output modifiers syntax.

### Purpose:

Allow guarantly exclude parts of template from main parser processing. Example: 
```
[[^isfolder:is=`1`:then=`[[-`]]
[[$Chunk]]
[[^isfolder:is=`1`:then=`]]`]]
```
In this example chunk Chunk will be processed only if current document is not a folder.  
Otherwise the code will be wrapped in comments tags and totally excluded from processing.