<?php

// Call to get final zip size in bytes
function zip_filesize($entries_size) {
  return 22 + $entries_size;
}

// Bytes needed for entry in zip.
// Name and size must match what later is used when calling zip_filentry()
// or zip_dataentry() or the returned value will not be correct
function zip_entrysize($name, $size) {
  return 30 + 46 + strlen($name) * 2 + $size;
}

// Start writing a zip file, give the returned object
// as first argument to other methods
function zip_start() {
  return array('central' => '',
               'central_offset' => 0,
               'central_count' => 0);
}

function zip_writeentryheaders(&$zip, $name, $crc, $size) {
  $crcnum = unpack('N', pack('H*', $crc))[1];
  echo pack('VvvvvvVVVvv',
            0x04034b50,  // header
            10,          // min version needed to extract
            0,           // bit flags
            0,           // compression method
            0,           // file last mod time
            0,           // file last mod date
            $crcnum,     // crc-32
            $size,       // uncompressed size
            $size,       // compressed size
            strlen($name),  // filename length
            0);             // extra length
  echo $name;
  $zip['central'] .= pack('VvvvvvvVVVvvvvvVV',
                          0x02014b50,  // header
                          0x030a,      // made by version
                          10,          // min version needed to extract
                          0,           // bit flags
                          0,           // compression method
                          0,           // file last mod time
                          0,           // file last mod date
                          $crcnum,     // crc-32
                          $size,       // uncompressed size
                          $size,       // compressed size
                          strlen($name), // filename length
                          0,           // extra length
                          0,           // comment length
                          0,           // first disk for entry
                          0,           // internal attr
                          0x81a40000,  // external attr
                          $zip['central_offset']);  // local header offset
  $zip['central'] .= $name;
  $zip['central_offset'] += 30 + strlen($name) + $size;
  $zip['central_count']++;
}

// Write one entry to zip file
// $name is entry name in zip file, should be US-ASCII and may contain '/'
// $filename is local filename for the file to add
function zip_fileentry(&$zip, $name, $filename) {
  zip_writeentryheaders(
    $zip, $name, hash_file('crc32b', $filename), filesize($filename));
  readfile($filename);
}

// Write one entry to zip file
// $name is entry name in zip file, should be US-ASCII and may contain '/'
// $data is file content to add
function zip_dataentry(&$zip, $name, $data) {
  zip_writeentryheaders($zip, $name, hash('crc32b', $data), strlen($data));
  echo $data;
}

// Write the end of the zip file
function zip_end($zip) {
  echo $zip['central'];
  echo pack('VvvvvVVv',
            0x06054b50,  // header
            0,           // number of this disk
            0,           // start disk for central directory
            $zip['central_count'],  // central directories on this disk
            $zip['central_count'],  // total directories on this disk
            strlen($zip['central']),  // size of central directory
            $zip['central_offset'],  // offset of start of central directory
            0);          // comment length
}

?>