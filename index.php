<?php
function convertFile($uploaddir, $uploadfile, $id){
    global $giphy;
    $size = getimagesize($uploadfile);
    $x = $size[0];
    if($x > 800){
        $x = 800;
    }
    $xcg = $x/2;

    exec("convert {$uploadfile} -resize {$x}x  -background '#FFFFFF' -flatten null: \( {$uploaddir}cg.gif -coalesce -resize {$xcg}x \) -gravity SouthEast -layers composite -layers optimize {$uploaddir}{$id}_cg.gif");
    unlink($uploadfile);
    $img = "img/{$id}_cg.gif";

    // curl -v -F "username=zedmaster" -F "file=@/home/www/cg/www/cg/img/cbi0bb8nfqv2hnicc6r2nik8u2_cg.gif"  -F "api_key=dc6zaTOxFJmzC" -F "tags=tag1,tag2,tag3" -F "source_post_url=http://giphy.com/channel/caguei/" "http://upload.giphy.com/v1/gifs"
    //curl -v -F "username=yourusername" -F "source_image_url=http://domain.com/public/path/to/giphy.gif"  -F "api_key=YOUR_API_KEY" -F "tags=tag1,tag2,tag3" -F "source_post_url=http://your.source-url.tld" "http://upload.giphy.com/v1/gifs"
    $result = json_decode(exec('curl -v -F "username=zedmaster" -F "file=@/home/www/cg/www/cg/img/cbi0bb8nfqv2hnicc6r2nik8u2_cg.gif"  -F "api_key=dc6zaTOxFJmzC" -F "tags=caguei" -F "source_post_url=http://giphy.com/channel/caguei/" "http://upload.giphy.com/v1/gifs"'));
    $id_giphy = isset($result->data->id) ? $result->data->id : false;

    unlink("{$uploaddir}{$id}_cg.gif");
    //echo '<pre>';
    //var_dump($id);
    //echo '</pre>';

    if($id){
        $img = "http://i.giphy.com/{$id_giphy}.gif";
        $giphy = "http://giphy.com/gifs/{$id_giphy}";
    }



    return $img;
}


session_start();

$giphy = false;
$typeImages = [
    'png' => 'image/png',
    'jpe' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'jpg' => 'image/jpeg',
    'gif' => 'image/gif',
];
$id = session_id();
$img = false;
$uploaddir = dirname ( __FILE__ ).'/img/';
$uploadfile = $uploaddir . basename($id."_upload");
$cgFile = "{$uploaddir}{$id}_cg.gif";

if(file_exists($cgFile)){
    $img = "img/{$id}_cg.gif";
}


if(strlen($_FILES['userfile']['tmp_name']) > 0){

    if(in_array(mime_content_type($_FILES['userfile']['tmp_name']), $typeImages)){
        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
            $img = convertFile($uploaddir, $uploadfile, $id);
        }else{
            echo "Erro ao gravar imagem!";
        }
    }else{
        echo "Enviado um tipo inválido de imagem!";
    }

}elseif(isset($_POST['url'])){
    $uploadfile = $uploaddir . "_url";
    //$tmpImg = file_get_contents($_POST['url']);
    $tmpImg = file_get_contents($_POST['url'], 1000000);

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if(in_array($finfo->buffer($tmpImg), $typeImages)){
        $out = file_put_contents($uploadfile, $tmpImg);
        $img = convertFile($uploaddir, $uploadfile, $id);
    }else{
        echo "Tipo inválido de imagem!";
    }
}

?>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link rel=icon href=favicon.ico>
    <title>Caguei</title>
</head>
<body>
<div class="page-header">
    <h1>Caguei</h1>
</div>
<div class="jumbotron">
    <div class="container">
        <p>Insira a imagem de fundo:<p>
        <form enctype="multipart/form-data" action="" method="POST" class="form-horizontal">
            <div class="form-group">
                <label for="url" class="col-sm-2 control-label">URL</label>
                <div class="col-sm-10">
                    <input id="url" name="url" type="text"  class="form-control" >
                </div>
            </div>
            <div class="form-group">
                <label for="exampleInputFile" class="col-sm-2 control-label">Arquivo</label>
                <div class="col-sm-10">
                    <input type="file" id="exampleInputFile" name="userfile" class="form-control" >
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-default">Enviar</button>
                </div>
            </div>
        </form>

        <div class="text-center">
            <?php if($img):?>
                <div><img src="<?php echo $img;?>"/></div>
                <a href="<?php echo $img;?>"/><?php echo $img;?></a>
            <?php endif;?>
        </div>
        <br/>
        <div class="text-center">
            <?php if($giphy):?>
                <a target="new" href="<?php echo $giphy;?>"/><?php echo $giphy;?></a>
            <?php endif;?>
        </div>
    </div>
</div>
</body>
</html>
