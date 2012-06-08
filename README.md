CodeIgniter Nested Controller Support 
=====================================

###What this is: 

An override to the ``CI_Router`` class, and a slight patch to ``system/core/CodeIgniter.php`` to allow nested directories for your controllers. This way you can break controllers up into smaller, more manageable sizes. (*Nobody* likes working in a 3,000 line controller file)

Example:

    application/controllers/
        interview.php      => responds to /interview/...
        interview/
            locations.php  => responds to /interview/locations/...
  
**NOTE:** If you have both a controller action, **AND** a subdirectory with the same name, the subdirectory has priority. So don't do that.

To use, clone this repo, move the files to your CI working directory. If you're using a custom prefix, update the ``/application/core/MY_Router.php``. 

If you like, you can have your nested controller subclass, so you can have access to shared methods and properties. 

```php
<?
// file: application/controllers/interview.php
//
class Interview extends MY_Contoller { 

    protected function getUser() {
        //...
    }

}
```
```php
<?
// file: application/controllers/interview/locations.php
//
require_once (APP_PATH . 'controllers/interview.php');
class Locations extends Interview { 
    
    public function index() { 
    
        // since we've subclassed interview, we get access to getUser()
        //
        $user = $this->getUser();
        
        //...
    }
    
}
```
This works with CodeIgniter 2.0.3 - 2.1

