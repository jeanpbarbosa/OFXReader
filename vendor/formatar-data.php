<?php
function dataPtParaEn($data) {
  if($data == '' || $data == 0) {
    return '0000-00-00';
  }

  return date('Y-m-d', strtotime(str_replace("/", "-", $data)));
}

function dataHoraPtParaEn($data) {
  if($data == '' || $data == 0) {
    return '0000-00-00 00:00:00';
  }

  return date('Y-m-d H:i:s', strtotime(str_replace("/", "-", $data)));
}

function dataEnParaPt($data) {
  if($data == '' || $data == 0) {
    return '00/00/0000';
  }

  return date('d/m/Y', strtotime($data));
}

function dataHoraEnParaPt($data) {
  if($data == '' || $data == 0) {
    return '00/00/0000 00:00:00';
  }

  return date('d/m/Y H:i:s', strtotime($data));
}

function dataEnParaMesAnoPt($data) {
  if($data == '' || $data == 0) {
    return '00/0000';
  }

  return date('m/Y', strtotime($data));
}

function dataEnParaMesAnoEn($data) {
  if($data == '' || $data == 0) {
    return '00-0000';
  }

  return date('m-Y', strtotime($data));
}