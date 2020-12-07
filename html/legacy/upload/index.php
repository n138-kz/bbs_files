<?php
header('Content-Type: Application/json');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header('Pragma: no-cache');
date_default_timezone_set('Asia/Tokyo');

const UPLOAD_DIR='../../../data/';
if(!file_exists(UPLOAD_DIR))
{
  http_response_code(500);
  exit();
}
if(!is_writable(UPLOAD_DIR))
{
  http_response_code(500);
  exit();
}
if(!isset($_FILES))
{
  http_response_code(400);
  echo 'E'.__LINE__."\n";
  exit();
}
if (empty($_FILES))
{
  http_response_code(400);
  echo json_encode(['OVER FILE SIZE', $_POST, $_FILES]);
  exit();
}

if(!isset($_POST))
{
  http_response_code(400);
  echo 'E'.__LINE__."\n";
  exit();
}
if (empty($_POST))
{
  http_response_code(400);
  echo json_encode(['OVER FILE SIZE', $_POST, $_FILES]);
  exit();
}

const SECRET='$2y$10$AYgjQFddKadBogCDIT/QreQO3i2A4HIjdvI8TCe2qVgaAwMgrE7Ka';// n138
if(!password_verify($_POST['secret'], SECRET))
{
  http_response_code(401);
  echo json_encode(['UNAUTHORIZED', $_POST, $_FILES]);
  exit();
}

for ($i=0;$i<count($_FILES['file']['tmp_name']);$i++) {
  if ($_FILES['file']['error'][$i]!=0)
  {
    // エラーは無視
    switch ($_FILES['file']['error'][$i]) {
      case UPLOAD_ERR_INI_SIZE:  $errstatus='OVER FILE SIZE';break;
      case UPLOAD_ERR_FORM_SIZE: $errstatus='OVER FILE SIZE';break;
      case UPLOAD_ERR_PARTIAL:   $errstatus='NOT ACCEPTABLE';break;
      case UPLOAD_ERR_NO_FILE:   $errstatus='NO DATA';break;
      default:$errstatus='UNKNOWN ERROR';break;
    }

    echo json_encode([$_FILES['file']['name'][$i].' is Error('.$errstatus.')', $_POST, $_FILES]);
    continue;
  }
  if(!is_uploaded_file($_FILES['file']['tmp_name'][$i]))
  {
    // エラーは無視
    echo 'E'.__LINE__."\n";
    continue;
  }
  if ($_FILES['file']['size'][$i]==0)
  {
    // 0byteは無視
    echo 'E'.__LINE__."\n";
    continue;
  }
  foreach (['/\.dat$/i'] as $v)
  {
    if (preg_match($v, $_FILES['file']['name'][$i])===1)
    {
      // 登録拡張子は無視
      echo 'E'.__LINE__."\n";
      $is_continue=TRUE;
    }
  }
  if ($is_continue)
  {
    continue;
  }
  $upload_file=UPLOAD_DIR.basename($_FILES['file']['name'][$i]);
  echo json_encode(['OK', $_POST, $_FILES, [
    UPLOAD_DIR,
    $_FILES['file']['type'][$i],
    $_FILES['file']['tmp_name'][$i],
    $_FILES['file']['name'][$i],
    $upload_file,
    move_uploaded_file($_FILES['file']['tmp_name'][$i], $upload_file),
  ]]);
}
