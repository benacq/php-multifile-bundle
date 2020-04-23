<?php

/**
 * PHP MULTIFILE BUNDLE
 * @desc This is a multipurpose file upload handler for php, it handles both single and multiple files, ensures they are well validated before it is returned to the user for further operations
 * @author Benjamin Acquaah benacq44@gmail.com 
 * @twitter https://twitter.com/benacq44
 * @linkedIn https://www.linkedin.com/in/benjamin-acquaah-9294aa14b/
 * 
 */



abstract class ProcessMultimedia
{
    //These are the methods that does the magic
    abstract public function pretty();
    abstract public function upload_single();
    abstract public function validate($pretty, $max_upload_size);
    abstract public function save_to_dir($pretty, $path);
}



class MultiFileConfig
{
    //IF WHITELIST IS SET, ALL OTHER EXTENTIONS WILL BE CONSIDERED BLACKLIST
    public static $whitelist = array();
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

     /**
     * @static default errors that can be overriden
     */
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
    

     /**
     * @static system errors[errors/warnings/notices]
     */
    //SYSTEM ERRORS/WARNINGS/NOTICES
    public static $CONFIG_ERROR_BREACH = "Method expected an array, ";
    public static $CONFIG_ERROR_ASSOC_BREACH = "Unexpected key passed to, refer to the docs for valid keys";
    public static $EXT_DOUBLE_FILTER = "Cannot pass double extension filter";
    public static $LIMIT_SINGLE_FILE = "Unexpected argument, unable to set MAX UPLOADS to a single file";
    public static $UPLOAD_MAX_INI = "EXCEEDED MAX UPLOAD LIMIT IN php.ini, INCREASE MAX UPLOAD SIZE";
    public static $UPLOAD_TMP_EMPTY = "TMP IS EMPTY";//THROW WARNING
    public static $ARGUMENT_COUNT_ERR = "Method expected an argument, none found";
    public static $UPLOAD_MULTIPLE_NULL_LIMIT = "Method expects MAX UPLOAD NUMBER, none found";


    /**
     * @static For error configurations
     * @desc It overrides the default errors by the class and replaces them with user specified error
     * @param array multidimesional  
     * 
     */
    public static function config_errors($errors)
    {
        if (is_array(@$errors)) {
            if (self::is_assoc(@$errors)) {
                try {
                    switch (true) {
                    case array_key_exists('UPLOAD_MAX_SIZE_USER', $errors):
                        self::$UPLOAD_MAX_SIZE_USER = $errors['UPLOAD_MAX_SIZE_USER'];
                        
                        
                    case array_key_exists('UPLOAD_MAX_FORM', $errors):
                        array_key_exists('UPLOAD_MAX_FORM', $errors) ? self::$UPLOAD_MAX_FORM = $errors['UPLOAD_MAX_FORM']: false;
                        
                        
                    case array_key_exists('FILE_CORRUPT', $errors):
                        array_key_exists('FILE_CORRUPT', $errors) ? self::$FILE_CORRUPT = $errors['FILE_CORRUPT']:false;
                        
                        
                    case array_key_exists('ON_UPLOAD_EMPTY', $errors):
                        array_key_exists('ON_UPLOAD_EMPTY', $errors) ? self::$ON_UPLOAD_EMPTY = $errors['ON_UPLOAD_EMPTY']:false;
                        
                        
                    case array_key_exists('PARTIAL_UPLOAD', $errors):
                        array_key_exists('PARTIAL_UPLOAD', $errors) ? self::$PARTIAL_UPLOAD = $errors['PARTIAL_UPLOAD']:false;
                        
                        
                    case array_key_exists('UPLOAD_ERR_UNKNOWN', $errors):
                        array_key_exists('UPLOAD_ERR_UNKNOWN', $errors) ? self::$UPLOAD_ERR_UNKNOWN = $errors['UPLOAD_ERR_UNKNOWN']: false;
                        
                        
                    case array_key_exists('UPLOAD_ABORT_ON_EXT', $errors):
                        array_key_exists('UPLOAD_ABORT_ON_EXT', $errors) ? self::$UPLOAD_ABORT_ON_EXT = $errors['UPLOAD_ABORT_ON_EXT']: false;
                        
                        
                    case array_key_exists('ON_BLACKLIST_BREACH', $errors):
                        array_key_exists('UPLOAD_ABORT_ON_EXT', $errors) ? self::$ON_BLACKLIST_BREACH = $errors['ON_BLACKLIST_BREACH']:false;
                        
                        
                    case array_key_exists('ON_WHITELIST_BREACH', $errors):
                        array_key_exists('ON_WHITELIST_BREACH', $errors) ? self::$ON_WHITELIST_BREACH = $errors['ON_WHITELIST_BREACH']:false;
                        
                        
                    case array_key_exists('ERR_MOVE_TO_DIR', $errors):
                        array_key_exists('ERR_MOVE_TO_DIR', $errors) ? self::$ERR_MOVE_TO_DIR = $errors['ERR_MOVE_TO_DIR']:false;

                        
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
    private $file_single;
    private $SYSTEM_MAX_UPLOAD_SIZE;
    
    /**
     * __construct
     * 
     * @desc takes the file uploaded file and maximum number of uploads into the class' constructor
     * also checks if the file is single or multiple and act accordingly.
     * @param  array $files
     * @param  int $files_limit
     * @return void
     */
    public function __construct($files, $files_limit = null)
    {
        $this->SYSTEM_MAX_UPLOAD_SIZE = $this->parse_size(ini_get('upload_max_filesize'));

        switch (!is_array($files['name'])) {
            case true:
                if (!is_null($files_limit)) {
                    trigger_error(MultiFileConfig::$LIMIT_SINGLE_FILE, E_USER_WARNING);
                    exit();
                }
                $this->file_single = $files;
                break;
            default:
                if (is_null($files_limit)) {
                    trigger_error(MultiFileConfig::$UPLOAD_MULTIPLE_NULL_LIMIT, E_USER_WARNING);
                    exit();
                }
                $this->files_limit = $files_limit;
                $this->files = $files;
                    // echo "multiple";
                break;
        }
    }

    
    /**
     * prettify_filess
     * @param  array $files
     * @return array pretty[file] well formatted $_FILES superglobal array
     */
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

        
    /**
     * @source https://stackoverflow.com/questions/13076480/php-get-actual-maximum-upload-size 
     * parse_size
     * @desc parses the php.ini max from short byte size into a standard file size(B,KB,MB, GB)
     * @param  short-byte MAX_UPLOAD SIZE
     * @return size
     */
    private function parse_size($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Removes the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Removes the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

    
    /**
     * pretty
     * @desc a public method that returns the prettified file array to the user[developer]
     * @return array pretty
     */
    public function pretty()
    {
        try {
            return $this->prettify_files($this->files);
        } catch (ArgumentCountError $ex) {
            trigger_error(MultiFileConfig::$ARGUMENT_COUNT_ERR, E_USER_ERROR);
        }
    }

    
    /**
     * upload_single
     *
     * @param  int $max_upload_size
     * @return array validated_pretty
     */
    public function upload_single($max_upload_size = 0)
    {
        if ($max_upload_size == 0) {
            $max_upload_size = $this->SYSTEM_MAX_UPLOAD_SIZE;
            return $this->validate(array($this->file_single), $this->SYSTEM_MAX_UPLOAD_SIZE)[0];
        } else {
            return $this->validate(array($this->file_single), $max_upload_size)[0];
        }
    }

        
    /**
     * validate
     * @desc validates the passed file and returns a validated pretty file array otherwise an error
     * developers may have to set it to a variable and print_r to see the error in case there is any
     * 
     * @param  array $pretty
     * @param  int $max_upload_size defaults to 0
     * @return array validated_pretty
     */
    public function validate($pretty, $max_upload_size = 0)
    {
        $max_upload_size = $max_upload_size == 0 ? $this->SYSTEM_MAX_UPLOAD_SIZE : $max_upload_size;
        $finfo = new finfo(FILEINFO_MIME_TYPE);

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

                    if (!empty(MultiFileConfig::$whitelist) && !empty(MultiFileConfig::$blacklist)) {
                        trigger_error(MultiFileConfig::$EXT_DOUBLE_FILTER, E_USER_WARNING);
                        exit();
                    } elseif (!empty(MultiFileConfig::$whitelist) || !empty(MultiFileConfig::$blacklist)) {
                        switch (empty(MultiFileConfig::$whitelist)) {
                        case true:
                            //BLACKLIST CHECK
                            if (false !== $ext = array_search(basename($finfo->file($pretty[$index]['tmp_name'])), MultiFileConfig::$blacklist, true)) {
                                array_push(MultiFileConfig::$errors, MultiFileConfig::$ON_BLACKLIST_BREACH);
                                return MultiFileConfig::$ON_BLACKLIST_BREACH;
                            }
                            break;
                        case false:
                            if (false === $ext = array_search(basename($finfo->file($pretty[$index]['tmp_name'])), MultiFileConfig::$whitelist, true)) {
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

    
    /**
     * save_to_dir
     *
     * @param  array $file
     * @param  string $path
     * @return path
     */
    public function save_to_dir($file, $path)
    {
        if (!is_array($file)) {
            trigger_error("Method expects 2 paramenters array[file], string[path], ".gettype($file).", ".gettype($path)." passed", E_USER_WARNING);
            exit();
        }
        $pretty = array_key_exists('name',$file) ? array($file) : $file;
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

