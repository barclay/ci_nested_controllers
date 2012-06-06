<?

/**
 * An extension of CI_Router that adds in support for nested controller 
 * directories. And have the routing still work as expected. 
 * 
 * Example 
 *  controllers/
 *      interview.php      => responds to /interview/...
 *      interview/
 *          locations.php  => responds to /interview/locations/...
 * 
 * NOTE: If you have both a controller action, AND a subdirectory with the 
 * same name, the subdirectory has priority. So don't do that. 
 * 
 * @package Readyforce.Libs
 * @author barclay loftus
 */
class MY_Router extends CI_Router {

    /**
     * Method that sets the routing for a given request. 
     * 
     * @return void
     */
    public function _set_routing() {

        // fire off the _set_routing in the base class. 
        //
        parent::_set_routing();

        // re-routed url, add 
        //
        if ($this->uri->rsegments != $this->segments) {
            if (count ( $this->uri->rsegments ) > 0 && isset($this->route['directory'])) {
                foreach($this->route['directory'] as $dir) {
                    array_unshift( $this->uri->rsegments, $dir);
                }
            }
        }
    }

    /**
     * Set the Route
     *
     * This function takes an array of URI segments as
     * input, and sets the current class/method
     *
     * @param   array
     * @param   bool
     * @return  void
     */
    public function _set_request($segments = array()) {
        $segments = $this->_validate_request($segments);

        if (count($segments) == 0)
            return $this->_set_default_controller();

        $this->set_class($segments[0]);

        // A standard method request
        //
        if (isset($segments[1]))
            $this->set_method($segments[1]);
        else
            $segments[1] = 'index';

        // Update our "routed" segment array to contain the segments.
        // Note: If there is no custom routing, this array will be
        // identical to $this->uri->segments
        //
        $this->uri->rsegments = $segments;
    }

    /**
     * Method that validates the request, and overrides the base _validate_request(). 
     * In our version, we'll build up the segments based on if we can sniff out a 
     * directory and sub controller (or not). If a subdirectory is found, it will 
     * be in the first position of the segments. If not, the controller will be. 
     * 
     * @param  array $segments 
     * @return array 
     */
    public function _validate_request($segments) {

        $segments = $this->build_sub_route($segments);

        // if the first argument is a subfolder AND the controller exists, 
        // then it gets the priority. 
        // 
        if (count($segments) > 0 && 
            isset($segments[1]) && 
            is_dir(APPPATH."controllers/{$segments[0]}") && 
            file_exists(APPPATH."controllers/{$segments[0]}/{$segments[1]}".EXT))  {
            $this->set_directory($segments[0]);
            return @array_slice($segments, 1);
        }

        // otherwise, let's just look for the controller. 
        //
        if (!empty($segments) && file_exists(APPPATH."controllers/{$segments[0]}".EXT)) {
            return $segments;
        }

        // or if we have only the directory, set that, and let it fall below and get
        // called with the default controller. 
        // NOTE: this is fucking stupid. who does this?
        //
        if (count($segments) == 1 && is_dir(APPPATH."controllers/{$segments[0]}")) {
            $this->set_directory($segments[0]);
            $segments = array();
        }

        // or, if we have no requests in the segments, do the default. 
        //
        if (empty($segments)) {
            $this->set_class($this->default_controller);
            $this->set_method ('index');
            return array();
        }
        
        // Anything else, you get a 404. 
        //
        show_404();
    }
    
    /**
     * Builds up the route based on the controller, directory(s) and route params
     * 
     * @param  array  $segments  The array of segments from this request's url
     * @return array 
     */
    private function build_sub_route($segments) {
        
        $route = array ();
        
        // Recurse through the sub directory route finding the directory path, 
        // controller and method
        //
        $this->recurse_sub_route ( $segments );
        
        // Build the route to the directory, controller, method with parameters
        //
        if (isset($this->route ['directory']) && $this->route ['directory'])
            $route[0] = implode ($this->route['directory'], DIRECTORY_SEPARATOR );
        //else 
        //    $route[0] = null;
        $route = @array_merge ( $route, $this->route ['controller'] );
        $route = @array_merge ( $route, $this->route ['parameter'] );
        $this->segments = $route;
        return $route;
    }
    
    /**
     * Method that recourses through the controllers directory to match the nested controller
     * with the current route. If this is a nested controller request, we'll set the 
     * current route member to have a 'directory' entry
     * 
     * @param  array  $segments
     * @return void
     */
    private function recurse_sub_route($segments) {
      
        // Find the all directories and files to be routed
        //
        foreach ($segments as $k=>$segment) {
            
            $directory = @implode($this->route ['directory'], DIRECTORY_SEPARATOR);
            $match     = false;
 
            // Find all directories
            //
            if (is_dir(APPPATH.'controllers/'.$directory.DIRECTORY_SEPARATOR.$segment)) {
                $this->route['directory'][$k] = $segment;
                $match = true;
            }
 
            // Find all controllers
            //
            if (is_file(APPPATH.'controllers/'.$directory.DIRECTORY_SEPARATOR.$segment.EXT)) {
                $this->route ['controller'] [$k] = $segment;
                $match = true;
            }

            if (!$match)
                break;
        }

        // so this is a little wacky. If we're not starting in a directory, punt
        // on the whole thing. This is for preventing partial matches elsewhere in 
        // the URL i.e.: 
        //
        //    /scheduler/candidate/interview/signup/21917
        //
        // would match on 'interview' since there's an interview directory, but 
        // it's later in the URL and actually a parameter. 
        //
        if (!isset($this->route['directory'][0])) {
            $this->route['directory']  = null;
            $this->route['controller'] = array($segments[0]);
            $this->route['parameter']  = @array_slice($segments, 1); 
            return;
        } 

        // otherwise let's do some magic. 
        // Find the (last) controller in the current route. 
        //
        $controller = @array_slice($this->route['controller'], - 1, 1, true);

        // Determine the parameters after controller
        //
        $this->route['parameter'] = @array_slice($segments, key($controller) + 1);

        // Remove controller binding from directory route
        //
        if (@array_key_exists(key($controller), $this->route['directory']))
            unset ($this->route ['directory'][key($controller )]);
        
        // Remove any directories from controller route
        //
        if (!empty($this->route['directory']))
            $this->route['controller'] = @array_diff($this->route['controller'], $this->route['directory']);

        // now, if there's no directories, just unset it. 
        //
        if (empty($this->route['directory']))
            unset($this->route['directory']);
    }

    /**
     * Method that returns the offset from the uri segments of where the directory, 
     * class, and method end, and the arguments begin. This is in support of controller
     * subdirectories. 
     * 
     * @return int
     */
    public function getArgumentOffset() {
        $offset = 2; 
        if (isset($this->route['directory']))
            $offset += count($this->route['directory']);
        return $offset;
    }
}

