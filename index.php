<?php
include_once 'classes/test.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multimedia Bundle Test</title>
</head>
<body>
    <center>
        <form action="classes/test.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="file_upload[]" id="file_upload" multiple>
            <input type="submit" name="process_file" value="Process Test">
        </form>
        <br>
        <br>
        <br>
        <form action="classes/php-media-bundle.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="file_upload[]" id="file_upload" multiple>
            <input type="submit" name="process_file" value="Process">
        </form>
    </center>
</body>
</html>



