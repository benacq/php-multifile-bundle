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
    $multifile = new MultifileBundle(array files [, int max_upload]);
    $multifile->pretty();
```
The pretty method returns an organized file that more easier to work with
    

### UPLOADING SINGLE FILES
    using the same constructor above we call the upload_single method of the class to handle single file uploads.
    it takes an integer, the maximum file size allowed
 ```php
<?
    $validated_single = $multifile->upload_single(int max_file_size);
```
    
    
    


