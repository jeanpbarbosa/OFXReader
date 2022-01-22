<?php
// https://stackoverflow.com/questions/2704314/multiple-file-upload-in-php
//$files = array_filter($_FILES['upload']['name']); //something like that to be used before processing files.

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
      unlink($fileinfo->getRealPath());
    }
  }
}

// Contagem para mensagem final
$fileCountQtd = 0;

foreach($_FILES['upload_files']['name'] as $i => $name) {

  $tmp_nameFile = $_FILES['upload_files']['tmp_name'][$i];
  $nameFile = $_FILES['upload_files']['name'][$i];
  $extensionFile = strtolower(pathinfo($_FILES['upload_files']['name'][$i], PATHINFO_EXTENSION));
  $newFilePath = "../read_this_folder/" . $nameFile;

  if ($extensionFile == 'ofx') {
    if(move_uploaded_file($tmp_nameFile, $newFilePath)) {
      $fileCountQtd++;
    }
  }

}

//Msgm Fim
echo json_encode(array(
  "status_tipo" => "OK",
  "status_msg" => "Done! " . $fileCountQtd . " imported file(s)."
), JSON_UNESCAPED_UNICODE);