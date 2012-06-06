CodeIgniter Nested Controller Support 
=====================================

###What this is: 

An override to the CI_Router class, and a slight patch to system/core/CodeIgniter.php to allow nested directories of controllers. This way you can break controllers up into smaller, more manageable files. (Nobody likes working in a 3,000 line file)

Example:

    application/controllers/
        interview.php      => responds to /interview/...
        interview/
            locations.php  => responds to /interview/locations/...
  
**NOTE:** If you have both a controller action, **AND** a subdirectory with the same name, the subdirectory has priority. So don't do that.

To use, clone this repo, move the files to your CI working directory. If you're using a custom prefix, update the /application/core/MY_Router.php. 

This works with CodeIgniter 2.0.3 - 2.1

