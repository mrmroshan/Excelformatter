<?php
class ExcelUploader_model extends CI_Model {

        public $title;
        public $content;
        public $date;

        public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
        }

        public function get_all_field_info(){
        	
        	$this->db->select('A.*,B.*');
        	$this->db->from('field_list A');
        	$this->db->join('field_category B', 'A.CATEGORY_ID = B.ID', 'left');
        	$this->db->order_by("A.FIELD_INDEX","ASC");
        	$query = $this->db->get();
        	
        	return $query->result_array();
        }

        public function get_last_ten_entries()
        {
                $query = $this->db->get('entries', 10);
                return $query->result();
        }

        public function insert_entry()
        {
                $this->title    = $_POST['title']; // please read the below note
                $this->content  = $_POST['content'];
                $this->date     = time();

                $this->db->insert('entries', $this);
        }

        public function update_entry()
        {
                $this->title    = $_POST['title'];
                $this->content  = $_POST['content'];
                $this->date     = time();

                $this->db->update('entries', $this, array('id' => $_POST['id']));
        }

}
?>