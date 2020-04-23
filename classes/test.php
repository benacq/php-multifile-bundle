<?php
include './php-multifile-bundle.php';
function process_upload()
{
    if (isset($_POST['process_file'])) {
        $files = $_FILES['file_upload'];

        // print_r($files);
    
        //YOU CAN CONFIGURE THE ERRORS BY SETTING YOUR OWN CUSTOM ERROR TO OVERRIDE THE DEFAULT ONES
        // $my_custom_errors = array(
        // "UPLOAD_NUMBER_LIMIT_EXCEEDED"=>"upload exceeded"
        // );

        //YOU CALL THIS STATIC METHOD WHICH TAKES AN ASSOCIATIVE ARRAY WHICH CONTAINS YOUR CUSTOM ERRORS
        // MultiFileConfig::config_errors($my_custom_errors);

        //THIS SETS A BLACKLIST OF FILES THAT ARE FORBIDDEN AND SHOULD ALWAYS BE REJECTED
        // array_push(MultiFileConfig::$blacklist, "png");
    
        //YOU INSTANTIATE THE CLASS AND PASS THE FILES AND THE MAX NUMBER OF UPLOADS ALLOWED
        $media_bundle = new MultifileBundle($files);
        $validated_single = $media_bundle->upload_single(4000000);
        // print_r($validated_single);
        $media_bundle->save_to_dir($validated_single, "../uploaded");
        //YOU CALL THE PRETTY METHOD WHICH REARRANGE THE $_FILES ARRAY INTO  A MORE READABLE FORM
        //IT RETURNS THE PRETTY ARRAY, YOU CAN IMPLEMENT YOUR OWN VALIDATION WITH THAT
        // $pretty = $media_bundle->pretty();
        // print_r($pretty);
        
        //BUT THE CLASS HAS A VALIDATOR WHICH TAKES THE PRETTY FILE AND MAXIMUM FILE SIZE ALLOWED
        //IT USES THE php.ini MAX UPLOAD SIZE BY DEFAULT
        //THIS ALSO RETURNS THE PRETTY ARRAY, BUT HAS PASSED THROUGH VALIDATION UNLIKE THE ONE FROM THE PRETTY METHOD
        // $validated_pretty = $media_bundle->validate($pretty, 1000000);
        // print_r($validated_pretty);
        //FINALLY YOU CALL save_to_dir() AND PASS THE VALIDATED ARRAY AND THE PATH TO SAVE THE FILES
        // $media_bundle->save_to_dir($validated_pretty, "../uploaded");
    }

}
process_upload();
