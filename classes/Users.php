<?php
require_once('../config.php');
Class Officers extends DBConnection {
    private $settings;
    public function __construct(){
        global $_settings;
        $this->settings = $_settings;
        parent::__construct();
    }
    public function __destruct(){
        parent::__destruct();
    }

    // Save or update officers
    public function save_officers(){
        extract($_POST);
        $data = '';
        foreach($_POST as $k => $v){
            if(!in_array($k, array('id', 'password'))){
                if(!empty($data)) $data .= ", ";
                $data .= " `{$k}` = '{$v}' ";
            }
        }
        if(!empty($password)){
            $password = md5($password);
            $data .= ", `password` = '{$password}' ";
        }

        // Handle file upload
        if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
            $fname = 'uploads/'.strtotime(date('Y-m-d H:i:s')).'_'.$_FILES['img']['name'];
            $move = move_uploaded_file($_FILES['img']['tmp_name'], '../'.$fname);
            if($move){
                $data .= ", `avatar` = '{$fname}' ";
                if(isset($_SESSION['userdata']['avatar']) && is_file('../'.$_SESSION['userdata']['avatar']) && $_SESSION['userdata']['id'] == $id){
                    unlink('../'.$_SESSION['userdata']['avatar']);
                }
            }
        }

        // Insert or update
        if(empty($id)){
            // Insert new officer
            $qry = $this->conn->query("INSERT INTO officers SET {$data}");
            if($qry){
                $this->settings->set_flashdata('success', 'Officer Details successfully saved.');
                return 1;
            } else {
                return json_encode(['status' => 'failed', 'error' => $this->conn->error]);
            }
        } else {
            // Update existing officer
            $qry = $this->conn->query("UPDATE officers SET {$data} WHERE id = {$id}");
            if($qry){
                $this->settings->set_flashdata('success', 'Officer Details successfully updated.');
                return 1;
            } else {
                return json_encode(['status' => 'failed', 'error' => $this->conn->error]);
            }
        }
    }

    // Delete officer
    public function delete_officers(){
        extract($_POST);
        $avatar = $this->conn->query("SELECT avatar FROM officers WHERE id = '{$id}'")->fetch_array()['avatar'];
        $qry = $this->conn->query("DELETE FROM officers WHERE id = '{$id}'");
        if($qry){
            $this->settings->set_flashdata('success', 'Officer Details successfully deleted.');
            if(is_file('../'.$avatar)) unlink('../'.$avatar);
            return json_encode(['status' => 'success']);
        } else {
            return json_encode(['status' => 'failed', 'error' => $this->conn->error]);
        }
    }
}

$officers = new Officers();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
switch ($action) {
    case 'save':
        echo $officers->save_officers();
        break;
    case 'delete':
        echo $officers->delete_officers();
        break;
    default:
        break;
}
