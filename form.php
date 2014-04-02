<?php
use milanpetrovic\UploadFile;

session_start();
require_once "class/UploadFile.php";

if ( !isset($_SESSION["maxfiles"]) ) {
    $_SESSION["maxfiles"] = ini_get("max_file_uploads");
    $_SESSION["postmax"] = UploadFile::convertToBytes(ini_get("post_max_size"));
    $_SESSION["displaymax"] = UploadFile::convertFromBytes($_SESSION["postmax"]);
}

$maxSize = 100 * 1024;
$messages = array();

if( isset($_POST["upload"]) ) {
    $destination = ""; // Path to destination directory - recommended to be outside of root

    try {
        $upload = new UploadFile($destination);
        $upload->setMaxFileSize($maxSize);
        $upload->allowAllTypes("mp");
        $upload->upload();
        $messages = $upload->getMessages();
    } catch (Exception $e) {
        $messages[] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Upload Class Demo Page</title>
        <meta charset="utf-8"/>

        <!-- Latest compiled and minified Bootstrap CSS -->
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
        <link rel="stylesheet" href="css/master.css"/>
    </head>

    <body>
    <nav class="navbar navbar-default navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <a class="navbar-brand" href="#">PHP Upload Class</a>
            </div>
        </div><!-- /.container-fluid -->
    </nav>

    <div class="container">
        <div class="col-md-10 col-md-offset-1">
            <?php if ( $messages ) : ?>
                <ul class="list-group">
                <?php foreach( $messages as $message ) : ?>
                     <li class="list-group-item list-group-item-info">
                         <?php echo $message; ?>
                     </li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">

                <!-- IMPORTANT: hidden field always goes before file input field -->
                <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $maxSize; ?>">

                <div class="form-group">
                    <label for="fileName" class="control-label">Select File: </label>
                    <input type="file" name="fileName[]" id="fileName" multiple>
                </div>
                <div class="panel panel-warning">
                    <div class="panel-heading">
                        Up to <?php echo $_SESSION["maxfiles"]; ?> files can be uploaded simultaneously.
                    </div>
                </div>
                <div class="panel  panel-warning">
                    <div class="panel-heading">
                        Each file shouldn't be larger than <?php echo UploadFile::convertFromBytes($maxSize); ?>.
                    </div>
                </div>
                <div class="panel  panel-warning">
                    <div class="panel-heading">
                        Combined total shouldn't exceed <?php echo $_SESSION["displaymax"]; ?>.
                    </div>
                </div>
                <button type="submit" class="btn btn-success btn-lg" name="upload">Upload File(s)</button>
            </form>
        </div>
    </div>

    <script>

    </script>
    </body>
</html>