# MediaWiki XML library

This library can be used to create XML that can be imported into MediaWiki.

See also https://www.mediawiki.org/wiki/Manual:ImportDump.php

## How to use

```php
$builder = new Builder();
$builder->addRevision( "Some page title", "Some wiki text" );
$builder->buildAndSave( "/path/to/ouput.xml" );
```
