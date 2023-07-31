<?php
require(__DIR__.'/vendor/autoload.php');

$reader = new PhpOffice\PhpSpreadsheet\Reader\Xlsx;
$load_file = $reader->load(__DIR__.DIRECTORY_SEPARATOR.'translation.xlsx');
$destination = __DIR__;
$directory = 'translation';

$A_to_Z = range('A', 'Z');
$translations = array();

foreach ($load_file->getSheetNames() as $name) {
  $sheet = $load_file->getSheetByName($name);
  for ($row = 1; $row < ($sheet->getHighestRow() + 1); $row ++) {
    if ($row === 1) {
      continue;
    }

    for ($col = 0; $col < (array_search($sheet->getHighestColumn(), $A_to_Z) + 1); $col++) {
      if ($row === 2 OR $col === 0) {
        if ($col > 0) {
          $LANG = $sheet->getCell($A_to_Z[$col].$row)->getOldCalculatedValue(); // get value of formula
          $LANG = $LANG === NULL ? $sheet->getCell($A_to_Z[$col].$row)->getValue() : $LANG;

          if (gettype($LANG) !== 'NULL') {
            $LANG = strtolower($LANG);
            $translations[$LANG] = array();
          }
        }

        continue;
      }

      $LANG = $sheet->getCell($A_to_Z[$col].'2')->getOldCalculatedValue(); // get value of formula
      $LANG = $LANG === NULL ? $sheet->getCell($A_to_Z[$col].'2')->getValue() : $LANG; // get value without formula
      $LANG = $LANG !== NULL ? strtolower($LANG) : $LANG;

      $KEY = $sheet->getCell('A'.$row)->getOldCalculatedValue();
      $KEY = $KEY === NULL ? $sheet->getCell('A'.$row)->getValue() : $KEY;

      $VAL = $sheet->getCell($A_to_Z[$col].$row)->getOldCalculatedValue();
      $VAL = $VAL === NULL ? $sheet->getCell($A_to_Z[$col].$row)->getValue() : $VAL;

      if (gettype($LANG) !== 'NULL' && gettype($KEY) !== 'NULL') {
        $translations[$LANG][$KEY] = $VAL;
      }
    }

    foreach (array_keys($translations) as $language) {
      if (!file_exists($destination.DIRECTORY_SEPARATOR.$directory.DIRECTORY_SEPARATOR.$language)) {
        mkdir($destination.DIRECTORY_SEPARATOR.$directory.DIRECTORY_SEPARATOR.$language, 06444, true);
      }

      file_put_contents($destination.DIRECTORY_SEPARATOR.$directory.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$name.'.json', json_encode($translations[$language], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
  }
}
