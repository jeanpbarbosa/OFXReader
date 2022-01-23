<?php
// Retirar erros
error_reporting(0);

// Medidor de tempo
$processingTime = microtime(true);

require_once "OfxParser/OfxAutoLoad.php";
require_once "formatar-data.php";

// Ler pasta de arquivos
// https://stackoverflow.com/questions/42264823/how-to-count-number-of-txt-files-in-a-directory-in-php
$directory = new DirectoryIterator(__DIR__ . '/../read_this_folder');
$fileCountQtd = 0;
$fileCountNames = [];
$fileCountPaths = [];
foreach ($directory as $fileinfo) {
  if ($fileinfo->isFile()) {
    if(strtolower($fileinfo->getExtension()) == 'ofx') {
      $fileCountQtd++;
      $fileCountNames[] = $fileinfo->getFilename();
      $fileCountPaths[] = $fileinfo->getRealPath();
    }
  }
}

// Verifica se tem arquivos na pasta
if ($fileCountQtd < 1) {
  echo json_encode(array(
    "status_tipo" => "NOK",
    "status_msg" => "No files found in folder."
  ), JSON_UNESCAPED_UNICODE);
  exit;
}

// Inicia arquivo csv
$fileData = fopen("OFXConverted.csv", 'w');

// Títulos
$fileFields = "";
$fileFields = array();
$fileFields[] = 'fileName';
$fileFields[] = 'date';
$fileFields[] = 'uniqueId';
$fileFields[] = 'name';
$fileFields[] = 'memo';
$fileFields[] = 'checkNumber';
$fileFields[] = 'type';
$fileFields[] = 'amount';
$fileFields[] = 'signOn code';
$fileFields[] = 'signOn severity';
$fileFields[] = 'signOn message';
$fileFields[] = 'accountInfo desc';
$fileFields[] = 'accountInfo number';
$fileFields[] = 'transactionUid';
$fileFields[] = 'agencyNumber';
$fileFields[] = 'accountNumber';
$fileFields[] = 'routingNumber';
$fileFields[] = 'accountType';
$fileFields[] = 'balance';
$fileFields[] = 'balanceDate';
$fileFields[] = 'startDate';
$fileFields[] = 'endDate';
$fileFields[] = 'currency';

// Grava Títulos
fputcsv($fileData, $fileFields, ";");

// Loop arquivos
foreach ($fileCountPaths as $keyFile => $file) {

  $ofxParser = new \OfxParser\Parser();
  $ofx = $ofxParser->loadFromFile($file);

  // ofx headers
  $signOn = reset($ofx->signOn);
  if($ofx->accountInfo != null) {
    $accountInfo = reset($ofx->accountInfo);
  } else {
    $accountInfo->desc = '';
    $accountInfo->number = '';
  }
  $bankAccount = reset($ofx->bankAccounts);

  // ofx bankAccount
  $transactionUid = $bankAccount->transactionUid;
  $agencyNumber = $bankAccount->agencyNumber;
  $accountNumber = $bankAccount->accountNumber;
  $routingNumber = $bankAccount->routingNumber;
  $accountType = $bankAccount->accountType;
  $balance = $bankAccount->balance;
  $balanceDate = $bankAccount->balanceDate;

  // ofx bankAccount->statement
  $startDate = $bankAccount->statement->startDate;
  $endDate = $bankAccount->statement->endDate;
  $currency = $bankAccount->statement->currency;
  $transactions = $bankAccount->statement->transactions;

  // * adjuste by bank
  // nubank send [0:GMT] and [-3:BRT] in date value, remove this before read
  $balanceDate = str_replace('[0:GMT]', '', strval($balanceDate));
  $balanceDate = str_replace('[-3:BRT]', '', strval($balanceDate));
  $startDate = str_replace('[0:GMT]', '', strval($startDate));
  $startDate = str_replace('[-3:BRT]', '', strval($startDate));
  $endDate = str_replace('[0:GMT]', '', strval($endDate));
  $endDate = str_replace('[-3:BRT]', '', strval($endDate));
  // itau send [-03:EST] in date value, remove this before read
  $balanceDate = str_replace('[-03:EST]', '', strval($balanceDate));
  $startDate = str_replace('[-03:EST]', '', strval($startDate));
  $endDate = str_replace('[-03:EST]', '', strval($endDate));
  // sicredi send [-3:GMT] in date value, remove this before read
  $balanceDate = str_replace('[-3:GMT]', '', strval($balanceDate));
  $startDate = str_replace('[-3:GMT]', '', strval($startDate));
  $endDate = str_replace('[-3:GMT]', '', strval($endDate));

  // Conteúdo csv
  foreach ($transactions as $key => $linhaLoop) {
    $fileContent = "";
    $fileContent = array();
    $fileContent[] = pathinfo($file, PATHINFO_FILENAME);

    // * adjuste by bank
    // nubank send [0:GMT] and [-3:BRT] in date value, remove this before read
    $transactions[$key]->date = str_replace('[0:GMT]', '', strval($transactions[$key]->date));
    $transactions[$key]->date = str_replace('[-3:BRT]', '', strval($transactions[$key]->date));
    // itau send [-03:EST] in date value, remove this before read
    $transactions[$key]->date = str_replace('[-03:EST]', '', strval($transactions[$key]->date));
    // sicredi send [-3:GMT] in date value, remove this before read
    $transactions[$key]->date = str_replace('[-3:GMT]', '', strval($transactions[$key]->date));

    $fileContent[] = dataEnParaPt($transactions[$key]->date);

    $fileContent[] = $transactions[$key]->uniqueId;
    $fileContent[] = $transactions[$key]->name;
    $fileContent[] = $transactions[$key]->memo;
    $fileContent[] = $transactions[$key]->checkNumber;
    $fileContent[] = $transactions[$key]->type;
    $fileContent[] = str_replace('.', ',', $transactions[$key]->amount);
    $fileContent[] = $signOn->code;
    $fileContent[] = $signOn->severity;
    $fileContent[] = $signOn->message;
    $fileContent[] = $accountInfo->desc;
    $fileContent[] = $accountInfo->number;
    $fileContent[] = $transactionUid;
    $fileContent[] = $agencyNumber;
    $fileContent[] = $accountNumber;
    $fileContent[] = $routingNumber;
    $fileContent[] = $accountType;
    $fileContent[] = str_replace('.', ',', $balance);
    $fileContent[] = dataEnParaPt($balanceDate);
    $fileContent[] = dataEnParaPt($startDate);
    $fileContent[] = dataEnParaPt($endDate);
    $fileContent[] = $currency;

    $fileContent = array_map("utf8_decode", $fileContent);

    // Grava Conteúdo
    fputcsv($fileData, $fileContent, ";");
  }
  
}

// Fecha arquivo csv
fclose($fileData);

// Medidor de tempo
$processingTime = microtime(true) - $processingTime;
$processingTime = number_format($processingTime, 4, ',' , '.');

// Msgm Fim
echo json_encode(array(
  "status_tipo" => "OK",
  "status_msg" => "Done! " . $fileCountQtd . " read file(s) in " . $processingTime . " sec."
), JSON_UNESCAPED_UNICODE);