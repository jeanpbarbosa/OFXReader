// Input file change value
document.querySelector('.custom-file-input').addEventListener('change', function (e) {
  var fileName = document.getElementById("upload_files").files[0].name;
  var nextSibling = e.target.nextElementSibling
  nextSibling.innerText = fileName
});

// Delete files
$(document).ready(function () {
  $('#form_delete').submit(function (e) {
    e.preventDefault();
    var form = $(this);
    var dados = new FormData(this);
    $.ajax({
      beforeSend: function () {
        $('#spinner-full-page').show();
      },
      url: form.attr('action'),
      type: form.attr('method'),
      data: dados,
      dataType: 'json',
      processData: false,
      cache: false,
      contentType: false,
      success: function (dataReturn) {
        $('#spinner-full-page').hide();
        if (dataReturn["status_tipo"] == 'OK') {
          alert(dataReturn["status_msg"]);
          // Reload page
          setTimeout(function () {
            window.location.reload();
          }, 0);
        } else {
          alert(dataReturn["status_msg"]);
        };
      },
      error: function (requestHttp, statusAjax, messageAjax) {
        $('#spinner-full-page').hide();
        if (requestHttp.responseText.length != 0) {
          alert('<b>Falha ao comunicar com API</b><br/>Recarregue a página e tente realizar o processo novamente. Se o problema persistir, contate o administrador.<br/><br/><b>Detalhe:</b> ' + (requestHttp.responseText.replace(/<[^>]*>?/gm, '')));
        } else {
          alert('<b>Falha ao comunicar com API</b><br/>Recarregue a página e tente realizar o processo novamente. Se o problema persistir, contate o administrador.<br/><br/></b>Nenhuma informação de detalhe foi recebida.</b>');
        };
      }
    });
  });
});

// Upload files
$(document).ready(function () {
  $('#form_upload').submit(function (e) {
    e.preventDefault();
    var form = $(this);
    var dados = new FormData(this);
    $.ajax({
      beforeSend: function () {
        $('#spinner-full-page').show();
      },
      url: form.attr('action'),
      type: form.attr('method'),
      data: dados,
      dataType: 'json',
      processData: false,
      cache: false,
      contentType: false,
      success: function (dataReturn) {
        $('#spinner-full-page').hide();
        if (dataReturn["status_tipo"] == 'OK') {
          alert(dataReturn["status_msg"]);
          // Reload page
          setTimeout(function () {
            window.location.reload();
          }, 0);
        } else {
          alert(dataReturn["status_msg"]);
        };
      },
      error: function (requestHttp, statusAjax, messageAjax) {
        $('#spinner-full-page').hide();
        if (requestHttp.responseText.length != 0) {
          alert('<b>Falha ao comunicar com API</b><br/>Recarregue a página e tente realizar o processo novamente. Se o problema persistir, contate o administrador.<br/><br/><b>Detalhe:</b> ' + (requestHttp.responseText.replace(/<[^>]*>?/gm, '')));
        } else {
          alert('<b>Falha ao comunicar com API</b><br/>Recarregue a página e tente realizar o processo novamente. Se o problema persistir, contate o administrador.<br/><br/></b>Nenhuma informação de detalhe foi recebida.</b>');
        };
      }
    });
  });
});

// Read files
$(document).ready(function () {
  $('#form_read').submit(function (e) {
    e.preventDefault();
    var form = $(this);
    var dados = new FormData(this);
    $.ajax({
      beforeSend: function () {
        $('#spinner-full-page').show();
      },
      url: form.attr('action'),
      type: form.attr('method'),
      data: dados,
      dataType: 'json',
      processData: false,
      cache: false,
      contentType: false,
      success: function (dataReturn) {
        $('#spinner-full-page').hide();
        if (dataReturn["status_tipo"] == 'OK') {
          alert(dataReturn["status_msg"]);
          // Reload page
          setTimeout(function () {
            var link = document.createElement('a');
            link.href = "vendor/OFXConverted.csv";
            link.download = "OFXConverted.csv";
            link.click();
            link.remove();
            // window.location.reload();
          }, 0);
        } else {
          alert(dataReturn["status_msg"]);
        };
      },
      error: function (requestHttp, statusAjax, messageAjax) {
        $('#spinner-full-page').hide();
        if (requestHttp.responseText.length != 0) {
          alert('<b>Falha ao comunicar com API</b><br/>Recarregue a página e tente realizar o processo novamente. Se o problema persistir, contate o administrador.<br/><br/><b>Detalhe:</b> ' + (requestHttp.responseText.replace(/<[^>]*>?/gm, '')));
        } else {
          alert('<b>Falha ao comunicar com API</b><br/>Recarregue a página e tente realizar o processo novamente. Se o problema persistir, contate o administrador.<br/><br/></b>Nenhuma informação de detalhe foi recebida.</b>');
        };
      }
    });
  });
});