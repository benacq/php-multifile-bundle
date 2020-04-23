# OVERVIEW
I don't know how long it took for you to fully understand file uploads and implement it on your own, but this was a big challenge to me especially uploading multiple files, it took quite long for me to grasp the whole thing.
To save everyone from going through what i went through i have taken time to research on best practices on file validation and file uploads as a whole and written a class to do the magic for you just by calling methods.
By calling a minimum of 4 methods you can upload as many files as possible.

__PHP MULTIFILE BUNDLE__ PHP Multifile Bundle is a php class that handles file uploads,validation and other cool stuff when uploading files, it provides you with the following services.

- __Prettify__ The standard php $_FILES superglobal array is quite messy and unorganized, by calling the 'pretty' method in this class returns a more organized array that is easy to understand.

- __File validator__ The Multifile Bundle class comes with a validator that takes the pretty method as an argument and pass it through series of checks to make sure the file(s) can be trusted before it returns the file back

- __Blacklist and Whitelist Extensions__ The class makes sure all the standard file processing procedures are followed, so it provides a way for you to add blacklist or whitelist extensions and the validator acts accordingly.

- __Override Custom Errors__ The class comes with default errors in case something goes wrong, but the user has the chance to overide it anytime by pass their custom errors throug a static configuration class.

## INSTALLATION
  Clone this repository by typing the line below
  Open your terminal in your project directory and type

  ```git clone git@github.com:benacq/php-multifile-bundle.git```
## USAGE
    include the class file into your project 

    The class takes two arguments into it's constructor, the file array and maximum number of uploads, both argumets are required when you pass a multiple file, but when you pass a single file only the file argument is required.

### UPLOADING MULTIPLE FILES
```php    
<?
    $multifile = new MultifileBundle(array $files [, int $max_upload]);
    $pretty_array = $multifile->pretty();
    $validated_pretty = $multifile->validate(array $pretty_array, int $max_upload_size);
    $multifile->save_to_dir(array $validated_pretty, string $path);
```
The __pretty__ method returns an error if something goes wrong, otherwise a more organized file array that is more easier to work with, users can choose to implement their own validation with the returned array or use the validator that comes with the class.

The __validate__ method also returns a pretty, but this time it has undergone validation and can be trusted unlike the pretty_array which is not validated.

The final method __save_to_dir__ takes two required arguments, the validated_pretty and the preferred directory path and moves the file into the specified directory passed through the argument.

With these four lines of code you will have your files uploaded safely into the specified directory.
    

### UPLOADING SINGLE FILES
    using the same constructor above we call the upload_single method of the class to handle single file uploads.
    it takes one required argument, the maximum file size allowed
 ```php
<?
    $validated_single = $multifile->upload_single(int $max_upload_size);
    $multifile->save_to_dir(array $validated_single, string $path);
```
 ## Configurations
    All the configurations are handled by a class named MultifileConfig.
    With this class you can override error message and set extension restrictions.
### NOTE
*All configurations must be done before creating an instance of the MultifileBundle class*
## Blacklisting and Whitelisting extentions
    The configuration class has two static arrays which handle extension restrictions, all you have to do is add extensions to the array.

```php
<?
    array_push(MultiFileConfig::$blacklist, "png","jpg","html","jpeg");//By adding these line, any file with any of these extensions will be seen as malicious and therefore will be rejected.

    array_push(MultiFileConfig::$whitelist, "mp4","mp3");//By adding these line, only files with these extensions will be accepted.
```
### NOTE
    You cannot set both blacklist and whitelist at the same time, if you set a whitelist, automatically all files that do not fall within the whitelist are considered blacklisted.
    Same way if you set a blacklist all other files automatically becomes whitelisted and therefore will be accepted.

## Setting Custom Errors
    The configuration class comes with a predefined error messages for all errors that may occur, these messages are configured by a static method called config_errors which takes an associative array as an argument. This array has a fixed key which should match the one in the class, the value is where your custom error goes.
```php
<?
  $my_custom_errors = array(
    "UPLOAD_NUMBER_LIMIT_EXCEEDED"=>"custom upload exceeded message",
    "FILE_CORRUPT"=>"custom file corrupt message",
    "PARTIAL_UPLOAD"=>"custom partial upload message",
    "UPLOAD_NUMBER_LIMIT_EXCEEDED"=>"custom upload exceeded message"
  );
  MultiFileConfig::config_errors($my_custom_errors);
```
below are all the error keys that can be overriden with a custom error.

| Error Keys                    | Meaning                                                                           |
| ----------------------------- |:---------------------------------------------------------------------------------:|
| UPLOAD_MAX_SIZE_USER          | When the file size exceeds the maximum size limit set by the developer            |
| UPLOAD_MAX_FORM               | When uploaded file size exceeds the specified in your html form                   |
| FILE_CORRUPT                  | This normally happens when the error status code is null or is an array           |
| ON_UPLOAD_EMPTY               | When the user tries to submit the form without choosing a file                    |
| PARTIAL_UPLOAD                | When the selected file fails to upload fully to the server                        |
| UPLOAD_ERR_UNKNOWN            | All validations are done but something went wrong , its quite unlikely to happen  |
| UPLOAD_ABORT_ON_EXT           | This error occurs when the upload terminate because of the file extension         |
| ON_BLACKLIST_BREACH           | This occurs when a blacklisted file is detected                                   |
| ON_WHITELIST_BREACH           | When an unknown file is detected, files that does not exist in the whitelist      |
| ERR_MOVE_TO_DIR               | When an error occur while moving the file to the specified directory              |
| UPLOAD_NUMBER_LIMIT_EXCEEDED  | When the user tries to upload more files than is allowed
                                

# FULL EXAMPLE UPLOAD CODE
```php    
<?
    //ALL CONFIGURATIONS MUST BE DONE BEFORE INSTANTIATING THE CLASS
    $my_custom_errors = array(
    "UPLOAD_NUMBER_LIMIT_EXCEEDED"=>"custom upload exceeded message",
    "FILE_CORRUPT"=>"custom file corrupt message",
    );
    MultiFileConfig::config_errors($my_custom_errors);

    //NOTE: Only one of these two can be set, either whitelist or blacklist, never both.
    array_push(MultiFileConfig::$blacklist, "exe","bin","bat","php");

    $multifile = new MultifileBundle($_FILES['file_name'], 5);
    $pretty_array = $multifile->pretty();//This returns an array or error
    $validated_pretty = $multifile->validate($pretty_array, 2000000);//Same with validate, an error or array
    $multifile->save_to_dir($validated_pretty, "./my_uploads");// This returns the path if successfull otherwise error
```


## Contact
- Twitter [@benacq44](https://twitter.com/benacq44)
- Email benacq44@gmail.com
- LinkedIn [Benjamin Acquaah](https://www.linkedin.com/in/benjamin-acquaah-9294aa14b/)












































