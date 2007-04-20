<?php
/**
 *
 */

require_once 'Savant3.php';

/**
 *
 */
class FeedMagick2_Template extends Savant3 {

    public $captured_output;

    public function __construct($config = null) 
    {
        parent::__construct($config);
        $this->captured_output = array();
    }

    /**
     * Begin capturing template output, for storage with given key.
     * @param $key - string key used to identify output capture
     */
    public function startOutputCapture($key)
    {
        ob_start();
    }

    /**
     * Stop capturing template output, storing the result with given key.
     * @param $key - string key used to identify output capture
     * @return The captured output.
     */
    function endOutputCapture($key)
    {
        $output = ob_get_contents();
        ob_end_clean();
        return $this->captured_output[$key] = $output;
    }

    /**
     * Fetch the captured output for the given key.
     * @param $key - String key used to fetch captured output
     * @return The captured output.
     */
    function getCapturedOutput($key)
    {
        return isset($this->captured_output[$key]) ? $this->captured_output[$key] : FALSE;
    }

}
