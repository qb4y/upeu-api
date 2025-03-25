<?php
    namespace App\Http\Controllers\Setup\Person;
    class mydb extends \SQLite3 {

        function __construct() {
            $this->open(dirname(__FILE__) . '/solver.db');
        }

}

?>
