<!DOCTYPE html>
<html lang="en">
<head>
  <title>OFX Reader!</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css' />
  <link href="https://fonts.googleapis.com/css?family=Bangers&display=swap" rel="stylesheet">

  <link rel='stylesheet' href='vendor/index.css'>
</head>

<body>
  <div id="spinner-full-page">
    <div class="spinner-border text-info">
      <span class="sr-only">Loading...</span>
    </div>
  </div>
  <div class="container">
    <div class="row align-items-center" style="height: 100vh">
      <div class="col-sm-12 offset-md-4 col-md-4">
        <h1 class="text-center text-hover text-title">OFX Reader!</h1>
        <p class="text-center text-hover icon-title"><i class="fas fa-file-invoice-dollar"></i></p>
        <hr>
        <div class="form-group">
          <label for="fileCount">OFX file count found:</label>
          <?php
            // https://stackoverflow.com/questions/42264823/how-to-count-number-of-txt-files-in-a-directory-in-php
            $directory = new DirectoryIterator(__DIR__ . '/read_this_folder');
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
          ?>
          <input type="text" class="form-control" id="fileCount" name="fileCount" aria-describedby="rootPathHelp" value="<?php echo $fileCountQtd ?>" disabled style="background-color: #fff; cursor: not-allowed;">
          <label for="fileCount"style="margin-top: 14px">OFX file names found:</label>
          <textarea class="form-control" id="" name="" rows="4" style="font-size: 10px; background-color: #fff; cursor: not-allowed;" disabled><?php
              foreach ($fileCountNames as $key=>$row) {
                echo $key+1 . ') ' . $row . PHP_EOL;
              }
          ?></textarea>
          <small id="rootPathHelp" class="form-text text-muted">Default path: /read_this_folder</small>
        </div>
        <form id="form_delete" method="post" enctype="multipart/form-data" action="vendor/delete-files.php">
          <div class="text-center">
            <button type="submit" class="btn btn-hover btn-clear" title="Delete files from import folder.">Clear</button>
          </div>
        </form>
        <hr>
        <form id="form_upload" method="post" enctype="multipart/form-data" action="vendor/upload-files.php">
          <p>Import OFX file(s):</p>
          <div class="input-group my-3">
            <div class="custom-file">
              <input type="file" class="custom-file-input" id="upload_files" name="upload_files[]" multiple required>
              <label class="custom-file-label" for="upload_files" style="overflow: hidden;">Choose file(s)</label>
            </div>
            <div class="input-group-append">
              <button type="submit" class="btn btn-hover btn-upload">Upload</button>
            </div>
          </div>
          <hr>
        </form>
        <form id="form_read" method="post" enctype="multipart/form-data" action="vendor/read-files.php">
          <div class="text-center my-3">
            <button type="submit" class="btn btn-hover btn-reader" title="Read files and save csv.">Reader!</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  <script src="vendor/index.js"></script>
</body>
</html>