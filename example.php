<?php

require('zip.inc.php');

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="php-inmem-zip.zip"');

$files = array('LICENSE', 'README.md', 'zip.inc.php', 'example.php');
$today = date('Y-m-d H:i:s') . "\n";

// Not needed, but nice to give browser a Content-Length to expect
$entries_size = 0;
foreach ($files as $file) {
  $entries_size += zip_entrysize($file, filesize($file));
}
$entries_size += zip_entrysize('date', strlen($today));
header('Content-Length: ' . zip_filesize($entries_size));

// Turn off output buffering, otherwise we might run out of memory
if (ob_get_level()) {
  ob_end_clean();
}

// Create a zip handle
$zip = zip_start();
foreach ($files as $file) {
  zip_fileentry($zip, $file, $file);
}
zip_dataentry($zip, 'date', $today);
// And finish up
zip_end($zip);

?>