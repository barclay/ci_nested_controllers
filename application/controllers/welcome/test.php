<?php 

/**
 * Example nested controller. 
 *
 * Maps to the following URL
 *      http://example.com/index.php/welcome/test/...
 *
 * @author Barclay Loftus
 */
class Test extends CI_Controller {
    
    /**
     * The default method for this controller. 
     * 
     * Responds to:  /welcome/test/
     *               /welcome/test/index
     *
     * @return void
     * @author Barclay Loftus
     */
    public function index() { 
        echo 'welcome/test/index called';
    }
    
     /**
     * Another test method for this controller. 
     * 
     * Responds to:  /welcome/test/foo
     *
     * @return void
     * @author Barclay Loftus
     */
    public function foo() { 
        echo 'welcome/test/foo called';
    }

}