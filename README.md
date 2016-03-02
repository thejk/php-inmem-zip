# php-inmem-zip
Minimal in memory on the fly zip generation for php

# minimal example
```php
require('zip.inc.php');
header('Content-Type: application/zip');
$zip = zip_start();
zip_dataentry($zip, 'file.txt', "Hello World\n");
zip_end($zip);
```
