<?php

abstract class ProcessMultimedia
{
    abstract public function pretty();
    abstract public function validate($pretty, $max_upload_size);
    abstract public function save_to_dir($pretty, $path);
}


class MultiFileConfig
{
    //IF WHITELIST IS SET, ALL OTHER EXTENTIONS WILL BE CONSIDERED BLACKLIST
    public static $white_list = array();
    //LIKEWISE IF BLACKLIST IS SET, ALL OTHER EXTENTIONS WILL BE CONSIDERED WHITELIST
    public static $blacklist = array();
    //THIS WILL CONTAIN ALL THE ERROR RETURNED FROM THE CLASS
    public static $errors = array();

    private function is_assoc(array $arr)
    {
        if (array() === $arr) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }



    //CONFIGURABLE ERRORS
  
    public static $UPLOAD_MAX_SIZE_USER = "Your file is too large";
    public static $UPLOAD_MAX_FORM= "EXCEEDED FORM MAX UPLOAD SIZE LIMIT";
    public static $FILE_CORRUPT = "Invalid, file may be corrupted";
    public static $ON_UPLOAD_EMPTY = "NO file has been uploaded";
    public static $PARTIAL_UPLOAD = "FILE WAS PARTIALY UPLOADED";
    public static $UPLOAD_ERR_UNKNOWN = "AN unknown error occured while uploading the data";
    public static $UPLOAD_ABORT_ON_EXT = "UPLOAD ABORTED BECAUSE OF BAD EXTENTION";
    public static $ON_BLACKLIST_BREACH = "This file type is blacklisted";
    public static $ON_WHITELIST_BREACH = "This file type is not accepted";
    public static $ERR_MOVE_TO_DIR = "An error occured while uploading the file";
    public static $UPLOAD_NUMBER_LIMIT_EXCEEDED = "exceeded the allowed file number upload";
    


    //SYSTEM ERRORS/WARNINGS/NOTICES
    public static $CONFIG_ERROR_BREACH = "Method expected an array, ";
    public static $CONFIG_ERROR_ASSOC_BREACH = "Unexpected key passed to, refer to the docs for valid keys";
    public static $EXT_DOUBLE_FILTER = "Cannot pass double extension filter";
    public static $UPLOAD_MAX_INI = "EXCEEDED MAX UPLOAD LIMIT IN php.ini, INCREASE MAX UPLOAD SIZE";
    public static $UPLOAD_TMP_EMPTY = "TMP IS EMPTY";//THROW WARNING
    public static $ARGUMENT_COUNT_ERR = "Method expected an argument, none found";





    public static function config_errors($errors)
    {
        if (is_array($errors)) {
            if (self::is_assoc($errors)) {
                try {
                    switch (true) {
                    case array_key_exists('UPLOAD_MAX_SIZE_USER', $errors):
                        self::$UPLOAD_MAX_SIZE_USER = $errors['UPLOAD_MAX_SIZE_USER'];
                        
                        // no break
                    case array_key_exists('UPLOAD_MAX_FORM', $errors):
                        array_key_exists('UPLOAD_MAX_FORM', $errors) ? self::$UPLOAD_MAX_FORM = $errors['UPLOAD_MAX_FORM']: false;
                        
                        // no break
                    case array_key_exists('FILE_CORRUPT', $errors):
                        array_key_exists('FILE_CORRUPT', $errors) ? self::$FILE_CORRUPT = $errors['FILE_CORRUPT']:false;
                        
                        // no break
                    case array_key_exists('ON_UPLOAD_EMPTY', $errors):
                        array_key_exists('ON_UPLOAD_EMPTY', $errors) ? self::$ON_UPLOAD_EMPTY = $errors['ON_UPLOAD_EMPTY']:false;
                        
                        // no break
                    case array_key_exists('PARTIAL_UPLOAD', $errors):
                        array_key_exists('PARTIAL_UPLOAD', $errors) ? self::$PARTIAL_UPLOAD = $errors['PARTIAL_UPLOAD']:false;
                        
                        // no break
                    case array_key_exists('UPLOAD_ERR_UNKNOWN', $errors):
                        array_key_exists('UPLOAD_ERR_UNKNOWN', $errors) ? self::$UPLOAD_ERR_UNKNOWN = $errors['UPLOAD_ERR_UNKNOWN']: false;
                        
                        // no break
                    case array_key_exists('UPLOAD_ABORT_ON_EXT', $errors):
                        array_key_exists('UPLOAD_ABORT_ON_EXT', $errors) ? self::$UPLOAD_ABORT_ON_EXT = $errors['UPLOAD_ABORT_ON_EXT']: false;
                        
                        // no break
                    case array_key_exists('ON_BLACKLIST_BREACH', $errors):
                        array_key_exists('UPLOAD_ABORT_ON_EXT', $errors) ? self::$ON_BLACKLIST_BREACH = $errors['ON_BLACKLIST_BREACH']:false;
                        
                        // no break
                    case array_key_exists('ON_WHITELIST_BREACH', $errors):
                        array_key_exists('ON_WHITELIST_BREACH', $errors) ? self::$ON_WHITELIST_BREACH = $errors['ON_WHITELIST_BREACH']:false;
                        
                        // no break
                    case array_key_exists('ERR_MOVE_TO_DIR', $errors):
                        array_key_exists('ERR_MOVE_TO_DIR', $errors) ? self::$ERR_MOVE_TO_DIR = $errors['ERR_MOVE_TO_DIR']:false;

                        // no break
                    case array_key_exists('UPLOAD_NUMBER_LIMIT_EXCEEDED', $errors):
                        array_key_exists('UPLOAD_NUMBER_LIMIT_EXCEEDED', $errors) ? self::$UPLOAD_NUMBER_LIMIT_EXCEEDED = $errors['UPLOAD_NUMBER_LIMIT_EXCEEDED']:false;
          
                }
                } catch (RuntimeException $th) {
                    throw $th;
                }
            } else {
                return trigger_error(MultiFileConfig::$CONFIG_ERROR_ASSOC_BREACH, E_ERROR);
            }
        } else {
            return trigger_error(MultiFileConfig::$CONFIG_ERROR_BREACH.gettype($errors)." passed", E_ERROR);
        }
    }
}







class MultifileBundle extends ProcessMultimedia
{
    private $files_limit = 0;
    private $files = array();
    private $SYSTEM_MAX_UPLOAD_SIZE;

    public function __construct($files, $files_limit, $custom_error = false)
    {
        $this->SYSTEM_MAX_UPLOAD_SIZE = $this->parse_size(ini_get('upload_max_filesize'));
        $this->files_limit = $files_limit;
        $this->files = $files;
    }

    // Prettify data
    private function prettify_files($files)
    {
        $pretty = array();
        if (count($files['name']) > $this->files_limit) {
            array_push(MultiFileConfig::$errors, MultiFileConfig::$UPLOAD_NUMBER_LIMIT_EXCEEDED);
            return MultiFileConfig::$UPLOAD_NUMBER_LIMIT_EXCEEDED;
            exit();
        } else {
            foreach ($files as $key => $files_all) {
                foreach ($files_all as $index => $file_data) {
                    $pretty[$index][$key]  = $file_data;
                }
            }
        }
        return $pretty;
    }

    private function parse_size($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }


    public function pretty()
    {
        try {
            return $this->prettify_files($this->files);
        } catch (ArgumentCountError $ex) {
            trigger_error(MultiFileConfig::$ARGUMENT_COUNT_ERR, E_USER_ERROR);
        }
    }
    
    public function validate($pretty, $max_upload_size = 0)
    {
        $max_upload_size = $max_upload_size == 0 ? $this->SYSTEM_MAX_UPLOAD_SIZE : $max_upload_size;
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        array_push(MultiFileConfig::$white_list, "html");


        if (is_array($pretty)) {
            foreach ($pretty as $index=>$data) {
                try {
                    if (!isset($pretty[$index]['error']) || is_array($pretty[$index]['error'])) {
                        array_push(MultiFileConfig::$errors, MultiFileConfig::$FILE_CORRUPT);
                        return MultiFileConfig::$FILE_CORRUPT;
                    }
                    //visit https://www.php.net/manual/en/features.file-upload.errors.php to know more about upload errors
                    switch ($pretty[$index]['error']) {
                    case UPLOAD_ERR_OK:
                    break;
                    case UPLOAD_ERR_PARTIAL:
                        array_push(MultiFileConfig::$errors, MultiFileConfig::$PARTIAL_UPLOAD);
                        return MultiFileConfig::$PARTIAL_UPLOAD;
                    break;
                    case UPLOAD_ERR_NO_FILE:
                        array_push(MultiFileConfig::$errors, MultiFileConfig::$ON_UPLOAD_EMPTY);
                        return MultiFileConfig::$ON_UPLOAD_EMPTY;
                    break;
                    case UPLOAD_ERR_INI_SIZE:
                        array_push(MultiFileConfig::$errors, MultiFileConfig::$UPLOAD_MAX_INI);
                        return MultiFileConfig::$UPLOAD_MAX_INI;
                    break;
                    case UPLOAD_ERR_FORM_SIZE:
                        array_push(MultiFileConfig::$errors, MultiFileConfig::$UPLOAD_MAX_FORM);
                        return MultiFileConfig::$UPLOAD_MAX_FORM;
                    break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        array_push(MultiFileConfig::$errors, MultiFileConfig::$UPLOAD_TMP_EMPTY);
                        return MultiFileConfig::$UPLOAD_TMP_EMPTY;
                    break;
                    case UPLOAD_ERR_EXTENSION:
                        array_push(MultiFileConfig::$errors, MultiFileConfig::$UPLOAD_ABORT_ON_EXT);
                        return MultiFileConfig::$UPLOAD_ABORT_ON_EXT;
                        //A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help. Introduced
                    break;
                    default:
                        trigger_error(MultiFileConfig::$UPLOAD_ERR_UNKNOWN, E_USER_ERROR);
                        // throw new RuntimeException(MultiFileConfig::$UPLOAD_ERR_UNKNOWN);
                    break;
          }
          
                    if ($pretty[$index]['size'] > $max_upload_size) {
                        array_push(MultiFileConfig::$errors, MultiFileConfig::$UPLOAD_MAX_SIZE_USER);
                        return MultiFileConfig::$UPLOAD_MAX_SIZE_USER;
                    }

                    $finfo = new finfo(FILEINFO_MIME_TYPE);

                    if (!empty(MultiFileConfig::$white_list) && !empty(MultiFileConfig::$blacklist)) {
                        trigger_error(MultiFileConfig::$EXT_DOUBLE_FILTER, E_USER_WARNING);
                        exit();
                    } elseif (!empty(MultiFileConfig::$white_list) || !empty(MultiFileConfig::$blacklist)) {
                        switch (empty(MultiFileConfig::$white_list)) {
                        case true:
                            //BLACKLIST CHECK
                            if (false !== $ext = array_search(basename($finfo->file($pretty[$index]['tmp_name'])), MultiFileConfig::$blacklist, true)) {
                                array_push(MultiFileConfig::$errors, MultiFileConfig::$ON_BLACKLIST_BREACH);
                                return MultiFileConfig::$ON_BLACKLIST_BREACH;
                            }
                            break;
                        case false:
                            if (false === $ext = array_search(basename($finfo->file($pretty[$index]['tmp_name'])), MultiFileConfig::$white_list, true)) {
                                array_push(MultiFileConfig::$errors, MultiFileConfig::$ON_WHITELIST_BREACH);
                                return MultiFileConfig::$ON_WHITELIST_BREACH;
                            }
                            break;
                    }
                    }
                    // print_r($pretty);
                    return $pretty;
                } catch (RuntimeException $e) {
                    echo $e->getMessage();
                }
            }
        } else {
            return false;
        }
    }

    public function save_to_dir($pretty, $path)
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $ext = basename($finfo->file($pretty[0]['tmp_name']));
        $clean_path = substr($path, -1) !== '/' ? $path."/" : $path;

        foreach ($pretty as $index=> $data) {
            if (!is_string($path) || is_null($path)) {
                trigger_error("Method expects 2 paramenters array[file], string[path], ".gettype($pretty).", ".gettype($path)." passed", E_USER_WARNING);
            } else {
                if (is_string($path)) {
                    if (is_dir($path) && file_exists($path)) {
                        if (!move_uploaded_file(
                            $pretty[$index]['tmp_name'],
                            sprintf(
                                $clean_path.'%s.%s',
                                sha1_file($pretty[$index]['tmp_name']),
                                $ext
                            )
                        )
                        
                        ) {
                            array_push(MultiFileConfig::$errors, MultiFileConfig::$ERR_MOVE_TO_DIR);
                            return MultiFileConfig::$ERR_MOVE_TO_DIR;
                        } else {
                            return $clean_path;
                        }
                    } else {
                        trigger_error("paramenter $path not a valid directory", E_USER_NOTICE);
                    }
                }
            }
        }
    }
}



// //TEST OBJECT
// if (isset($_POST['process_file'])) {
//     $my_custom_errors = array(
//     "UPLOAD_MAX_SIZE_USER" => "A new value passed",
//     "ERR_MOVE_TO_DIR"=>"another change",
//     "ON_WHITELIST_BREACH"=>"White list error",
//     "UPLOAD_NUMBER_LIMIT_EXCEEDED"=>"upload exceeded. DEV TEST"
//     );
    
//     MultiFileConfig::config_errors($my_custom_errors);
//     array_push(MultiFileConfig::$white_list, "html");


//     $files = $_FILES['file_upload'];
//     $media_bundle = new MultifileBundle($files, 5);
//     $pretty = $media_bundle->pretty();
//     $validate_pretty = $media_bundle->validate($pretty, 1000000);
//     echo $validate_pretty."<br>";
//     // $media_bundle->save_to_dir($validate_pretty, "../uploaded");
// }
