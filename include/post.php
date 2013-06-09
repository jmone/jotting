<?php
class post{
    private $db;
    public function __construct() {
        global $db;
        $this->db = $db;
    }

    public function create($data){
        print_r($data);
	return 1;
    }
    public function delete($id){
        
    }
    public function get($id){
        
    }
    public function getList($page, $size){
        
    }
}
?>
