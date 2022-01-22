<?php
// Ler pasta de arquivos
// https://stackoverflow.com/questions/42264823/how-to-count-number-of-txt-files-in-a-directory-in-php
// Excluir
$directory = new DirectoryIterator(__DIR__ . '/../read_this_folder');
$fileCountQtd = 0;
$fileCountNames = [];
$fileCountPaths = [];
foreach ($directory as $fileinfo) {
  if ($fileinfo->isFile()) {
    if(strtolower($fileinfo->getExtension()) == 'ofx') {
      $fileCountQtd++;
      unlink($fileinfo->getRealPath());
    }
  }
}

//Msgm Fim
echo json_encode(array(
  "status_tipo" => "OK",
  "status_msg" => "Done! " . $fileCountQtd . " deleted file(s)."
), JSON_UNESCAPED_UNICODE);