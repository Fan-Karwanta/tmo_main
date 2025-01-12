<?php
require_once('../config.php');
Class Master extends DBConnection {
	private $settings;
	public function __construct(){
		global $_settings;
		$this->settings = $_settings;
		$this->permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		parent::__construct();
	}
	public function __destruct(){
		parent::__destruct();
	}
	
	public function save_accident() {
		try {
			extract($_POST);
			$columns = [];
			$values = [];
			
			// Debug log
			error_log("Saving accident report - POST data: " . print_r($_POST, true));
			
			foreach ($_POST as $k => $v) {
				if (!in_array($k, array('id'))) {
					$columns[] = "`$k`";
					$values[] = $this->conn->real_escape_string($v);
				}
			}
			
			if (empty($id)) {
				$sql = "INSERT INTO `accident_reports` (" . implode(',', $columns) . ") VALUES ('" . implode("','", $values) . "')";
			} else {
				$updates = [];
				foreach ($columns as $index => $col) {
					$updates[] = "$col = '{$values[$index]}'";
				}
				$sql = "UPDATE `accident_reports` SET " . implode(',', $updates) . " WHERE id = '{$this->conn->real_escape_string($id)}'";
			}
			
			// Log the SQL query for debugging
			error_log("Accident report SQL query: " . $sql);
			
			$save = $this->conn->query($sql);
			
			if ($save) {
				$resp['status'] = 'success';
				$this->settings->set_flashdata('success', empty($id) ? "New Accident Report successfully saved." : "Accident Report successfully updated.");
			} else {
				$resp['status'] = 'failed';
				$resp['err'] = $this->conn->error;
				$resp['sql'] = $sql;
				error_log("Database error: " . $this->conn->error);
			}
			
			return json_encode($resp);
			
		} catch (Exception $e) {
			error_log("Exception in save_accident: " . $e->getMessage());
			$resp['status'] = 'failed';
			$resp['err'] = $e->getMessage();
			return json_encode($resp);
		}
	}
	
	
	public function delete_accident_report(){
		extract($_POST);
		$del = $this->conn->query("DELETE FROM `accident_reports` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Accident Report successfully deleted.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}
	function capture_err(){
		if(!$this->conn->error)
			return false;
		else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
			return json_encode($resp);
			exit;
		}
	}
	function save_offense(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id','description'))){
				if(!empty($data)) $data .=",";
				$data .= " `{$k}`='{$v}' ";
			}
		}
		if(isset($_POST['description'])){
			if(!empty($data)) $data .=",";
				$data .= " `description`='".addslashes(htmlentities($description))."' ";
		}
		$check = $this->conn->query("SELECT * FROM `offenses` where `code` = '{$code}' ".(!empty($id) ? " and id != {$id} " : "")." ")->num_rows;
		if($this->capture_err())
			return $this->capture_err();
		if($check > 0){
			$resp['status'] = 'failed';
			$resp['msg'] = "Offense code already exist.";
			return json_encode($resp);
			exit;
		}
		if(empty($id)){
			$sql = "INSERT INTO `offenses` set {$data} ";
			$save = $this->conn->query($sql);
		}else{
			$sql = "UPDATE `offenses` set {$data} where id = '{$id}' ";
			$save = $this->conn->query($sql);
		}
		if($save){
			$resp['status'] = 'success';
			if(empty($id))
				$this->settings->set_flashdata('success',"New Offense successfully saved.");
			else
				$this->settings->set_flashdata('success',"Offense successfully updated.");
		}else{
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
		return json_encode($resp);
	}
	function delete_offense(){
		extract($_POST);
		$del = $this->conn->query("DELETE FROM `offenses` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"offense successfully deleted.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function generate_string($input, $strength = 10) {
		
		$input_length = strlen($input);
		$random_string = '';
		for($i = 0; $i < $strength; $i++) {
			$random_character = $input[mt_rand(0, $input_length - 1)];
			$random_string .= $random_character;
		}
	 
		return $random_string;
	}
	function upload_files(){
		extract($_POST);
		$data = "";
		if(empty($upload_code)){
			while(true){
				$code = $this->generate_string($this->permitted_chars);
				$chk = $this->conn->query("SELECT * FROM `uploads` where dir_code ='{$code}' ")->num_rows;
				if($chk <= 0){
					$upload_code = $code;
					$resp['upload_code'] =$upload_code;
					break;
				}
			}
		}

		if(!is_dir(base_app.'uploads/blog_uploads/'.$upload_code))
			mkdir(base_app.'uploads/blog_uploads/'.$upload_code);
		$dir = 'uploads/blog_uploads/'.$upload_code.'/';
		$images = array();
		for($i = 0;$i < count($_FILES['img']['tmp_name']); $i++){
			if(!empty($_FILES['img']['tmp_name'][$i])){
				$fname = $dir.(time()).'_'.$_FILES['img']['name'][$i];
				$f = 0;
				while(true){
					$f++;
					if(is_file(base_app.$fname)){
						$fname = $f."_".$fname;
					}else{
						break;
					}
				}
				$move = move_uploaded_file($_FILES['img']['tmp_name'][$i],base_app.$fname);
				if($move){
					$this->conn->query("INSERT INTO `uploads` (dir_code,user_id,file_path)VALUES('{$upload_code}','{$this->settings->userdata('id')}','{$fname}')");
					$this->capture_err();
					$images[] = $fname;
				}
			}
		}
		$resp['images'] = $images;
		$resp['status'] = 'success';
		return json_encode($resp);
	}
	function save_driver(){
		foreach($_POST as $k =>$v){
			$_POST[$k] = addslashes($v);
		}
		extract($_POST);
		$name = ucwords($lastname.', '.$firstname.' '.$middlename);
		$chk = $this->conn->query("SELECT * FROM `drivers_list` where  license_id_no = '{$license_id_no}' ".($id>0? " and id!= '{$id}' " : ""))->num_rows;
		$this->capture_err();
		if($chk > 0){
			$resp['status'] = 'failed';
			$resp['msg'] = "Licesnse ID already exist in the database. Please review and try again.";
			return json_encode($resp);
			exit;
		}
		if(empty($id))
			$sql1 = "INSERT INTO `drivers_list` set `name` = '{$name}', license_id_no = '{$license_id_no}' ";
		else
			$sql1 = "UPDATE `drivers_list` set `name` = '{$name}', license_id_no = '{$license_id_no}' where id = '{$id}' ";
		
		$save1 = $this->conn->query($sql1);
		$this->capture_err();
		$driver_id = empty($id) ? $this->conn->insert_id : $id ;
		$this->conn->query("DELETE FROM `drivers_meta` where driver_id = '{$driver_id}' ");
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id'))){
				if(!empty($data)) $data .=",";
				$v = addslashes($v);
				$data .= " ('{$driver_id}','{$k}','{$v}') ";
			}
		}
		$data .= ",('{$driver_id}','driver_id','{$driver_id}')";

		
		$sql = "INSERT INTO `drivers_meta` (`driver_id`,`meta_field`,`meta_value`) VALUES {$data} ";
		$save = $this->conn->query($sql);
		$this->capture_err();
		if($save){
			$resp['status'] = 'success';
			if(empty($id))
				$this->settings->set_flashdata('success',"New Driver successfully saved.");
			else
				$this->settings->set_flashdata('success',"Driver Details successfully updated.");
			$id = empty($id) ? $this->conn->insert_id : $id;
			$dir = 'uploads/drivers/';
			if(!is_dir(base_app.$dir))
				mkdir(base_app.$dir);
			if(isset($_FILES['img'])){
				if(!empty($_FILES['img']['tmp_name'])){
					$fname = $dir.$driver_id.".".(pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION));
					$move =  move_uploaded_file($_FILES['img']['tmp_name'],base_app.$fname);
					if($move){
						$this->conn->query("INSERT INTO `drivers_meta` set `meta_value` = '{$fname}', driver_id = '{$driver_id}',`meta_field` = 'image_path' ");
						if(!empty($image_path) && is_file(base_app.$image_path))
							unlink(base_app.$image_path);
					}
				}
			}
		}else{
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
		return json_encode($resp);
	}
	function delete_driver(){
		extract($_POST);
		$qry = $this->conn->query("SELECT * FROM `drivers_meta` where driver_id = '{$id}'");
		while($row=$qry->fetch_assoc()){
			${$row['meta_field']} = $row['meta_value'];
		}
		$del = $this->conn->query("DELETE FROM `drivers_list` where id = '{$id}'");
		$this->capture_err();
		if($del){
			$resp['status'] = 'success';
			if(is_file(base_app.$image_path))
				unlink((base_app.$image_path));
			$this->settings->set_flashdata('success',"Driver's Info successfully deleted.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function delete_img(){
		extract($_POST);
		if(is_file(base_app.$path)){
			if(unlink(base_app.$path)){
				$del = $this->conn->query("DELETE FROM `uploads` where file_path = '{$path}'");
				$resp['status'] = 'success';
			}else{
				$resp['status'] = 'failed';
				$resp['error'] = 'failed to delete '.$path;
			}
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = 'Unkown '.$path.' path';
		}
		return json_encode($resp);
	}
	function save_offense_record(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id','fine','offense_id'))){
				$v = addslashes($v);
				if(!empty($data)) $data .=",";
				$data .= " `{$k}`='{$v}' ";
			}
		}
		$chk = $this->conn->query("SELECT * FROM `offense_list` where  ticket_no = '{$ticket_no}' ".(($id>0)? " and id!= '{$id}' " : "")." ")->num_rows;
		$this->capture_err();
		if($chk > 0){
			$resp['status'] = 'failed';
			$resp['msg'] = "Offense Ticker No. already exist in the database. Please review and try again.";
			return json_encode($resp);
			exit;
		}

		if(empty($id)){
			$sql = "INSERT INTO `offense_list` set {$data} ";
		}else{
			$sql = "UPDATE `offense_list` set {$data} where id = '{$id}' ";
		}
		$save = $this->conn->query($sql);
		$this->capture_err();
		$driver_offense_id = empty($id) ? $this->conn->insert_id : $id;
		$this->conn->query("DELETE FROM `offense_items` where `driver_offense_id` = '{$driver_offense_id}'");
		$this->capture_err();
		$data = "";
		foreach($offense_id as $k => $v){
			if(!empty($data)) $data .= ", ";
			$data .= "('{$driver_offense_id}','{$v}','{$fine[$k]}','{$status}','{$date_created}')";
		}
		$save2= $this->conn->query("INSERT INTO `offense_items` (`driver_offense_id`,`offense_id`,`fine`,`status`,`date_created`) VALUES {$data}");
		$this->capture_err();
		if($save && $save2){
			if(empty($id))
				$this->settings->set_flashdata('success'," New Offense Record successfully saved.");
			else
				$this->settings->set_flashdata('success'," Offense Record successfully updated.");
			$resp['status'] = 'success';
			$resp['id'] = $driver_offense_id;
		}else{
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
		return json_encode($resp);
	}


	function delete_offense_record(){
		extract($_POST);
		$del = $this->conn->query("DELETE FROM `offense_list` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Offense Record successfully deleted.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
    function get_accident_locations() {
        extract($_POST);
        
        $where = "";
        switch($filter) {
            case 'today':
                $where = "WHERE DATE(date_of_accident) = CURDATE()";
                break;
            case 'week':
                $where = "WHERE date_of_accident >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
                break;
            case 'month':
                $where = "WHERE date_of_accident >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
                break;
            case 'year':
                $where = "WHERE date_of_accident >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
                break;
            default:
                $where = "";
        }

        $sql = "SELECT 
                    latitude,
                    longitude,
                    location,
                    COUNT(*) as incident_count,
                    MAX(date_of_accident) as latest_date
                FROM accident_reports
                {$where}
                GROUP BY latitude, longitude
                HAVING latitude IS NOT NULL AND longitude IS NOT NULL";

        $qry = $this->conn->query($sql);
        $data = array();
        
        while($row = $qry->fetch_assoc()) {
            $data[] = array(
                'latitude' => floatval($row['latitude']),
                'longitude' => floatval($row['longitude']),
                'intensity' => min($row['incident_count'] / 10, 1), // Normalize intensity
                'count' => $row['incident_count'],
                'location' => $row['location'],
                'date' => date('M d, Y', strtotime($row['latest_date']))
            );
        }

        return json_encode(array(
            'status' => 'success',
            'data' => $data
        ));
    }
    
    function get_accident_map_data() {
        extract($_POST);
        
        $where = array();
        
        // Time filter
        switch($time_filter) {
            case 'today':
                $where[] = "DATE(date_of_accident) = CURDATE()";
                break;
            case 'week':
                $where[] = "date_of_accident >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
                break;
            case 'month':
                $where[] = "date_of_accident >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
                break;
            case 'year':
                $where[] = "date_of_accident >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
                break;
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        // Get accident data
        $sql = "SELECT 
                    ar.*,
                    dl.name as driver_name
                FROM accident_reports ar 
                LEFT JOIN drivers_list dl ON ar.driver_id = dl.id 
                {$where_clause}
                ORDER BY ar.date_of_accident DESC";
                
        $qry = $this->conn->query($sql);
        
        if(!$qry) {
            return json_encode(array(
                'status' => 'error',
                'error' => $this->conn->error,
                'sql' => $sql
            ));
        }
        
        $data = array();
        $total_accidents = 0;
        $unique_locations = array();
        
        while($row = $qry->fetch_assoc()) {
            // Only include records with valid coordinates
            if(!empty($row['latitude']) && !empty($row['longitude'])) {
                $data[] = array(
                    'id' => $row['id'],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'location' => $row['location'],
                    'date' => date('M d, Y h:i A', strtotime($row['date_of_accident'])),
                    'description' => $row['description'],
                    'driver_name' => $row['driver_name']
                );
                
                $location_key = $row['latitude'] . ',' . $row['longitude'];
                if(!isset($unique_locations[$location_key])) {
                    $unique_locations[$location_key] = true;
                }
                
                $total_accidents++;
            }
        }
        
        // Debug information
        $debug = array(
            'total_records' => $qry->num_rows,
            'valid_coordinates' => count($data),
            'sql_query' => $sql,
            'where_clause' => $where_clause,
            'post_data' => $_POST
        );
        
        return json_encode(array(
            'status' => 'success',
            'data' => $data,
            'stats' => array(
                'total_accidents' => $total_accidents,
                'unique_locations' => count($unique_locations)
            ),
            'debug' => $debug
        ));
    }
    
    function get_accident_analytics() {
        extract($_POST);
        
        $where = array();
        
        // Time filter
        switch($time_filter) {
            case 'week':
                $where[] = "date_of_accident >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
                break;
            case 'month':
                $where[] = "date_of_accident >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
                break;
            case 'year':
                $where[] = "date_of_accident >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
                break;
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        // Monthly Trend Data
        $monthly_sql = "SELECT 
                DATE_FORMAT(date_of_accident, '%Y-%m') as month,
                COUNT(*) as count
            FROM accident_reports
            {$where_clause}
            GROUP BY DATE_FORMAT(date_of_accident, '%Y-%m')
            ORDER BY month ASC
            LIMIT 12";
            
        $monthly_qry = $this->conn->query($monthly_sql);
        $monthly_trend = array('labels' => array(), 'data' => array());
        
        while($row = $monthly_qry->fetch_assoc()) {
            $monthly_trend['labels'][] = date('M Y', strtotime($row['month'] . '-01'));
            $monthly_trend['data'][] = intval($row['count']);
        }
        
        // Location Statistics
        $location_sql = "SELECT 
                location,
                COUNT(*) as count
            FROM accident_reports
            {$where_clause}
            GROUP BY location
            ORDER BY count DESC
            LIMIT 10";
            
        $location_qry = $this->conn->query($location_sql);
        $location_stats = array('labels' => array(), 'data' => array());
        
        while($row = $location_qry->fetch_assoc()) {
            $location_stats['labels'][] = $row['location'];
            $location_stats['data'][] = intval($row['count']);
        }
        
        // Time Distribution (by hour)
        $time_sql = "SELECT 
                HOUR(date_of_accident) as hour,
                COUNT(*) as count
            FROM accident_reports
            {$where_clause}
            GROUP BY HOUR(date_of_accident)
            ORDER BY hour ASC";
            
        $time_qry = $this->conn->query($time_sql);
        $time_distribution = array('labels' => array(), 'data' => array());
        
        // Initialize all hours with 0
        for($i = 0; $i < 24; $i++) {
            $time_distribution['labels'][] = sprintf("%02d:00", $i);
            $time_distribution['data'][] = 0;
        }
        
        while($row = $time_qry->fetch_assoc()) {
            $time_distribution['data'][$row['hour']] = intval($row['count']);
        }
        
        // Officer Statistics
        $officer_sql = "SELECT 
                o.name as officer_name,
                COUNT(a.id) as count
            FROM accident_reports a
            LEFT JOIN officers o ON a.officer_id = o.id
            {$where_clause}
            GROUP BY a.officer_id
            ORDER BY count DESC
            LIMIT 5";
            
        $officer_qry = $this->conn->query($officer_sql);
        $officer_stats = array('labels' => array(), 'data' => array());
        
        while($row = $officer_qry->fetch_assoc()) {
            $officer_stats['labels'][] = $row['officer_name'] ?: 'Unassigned';
            $officer_stats['data'][] = intval($row['count']);
        }
        
        // Driver Statistics
        $driver_sql = "SELECT 
                d.name as driver_name,
                COUNT(*) as count
            FROM accident_reports a
            LEFT JOIN drivers_list d ON a.driver_id = d.id
            {$where_clause}
            GROUP BY a.driver_id
            ORDER BY count DESC
            LIMIT 8";
            
        $driver_qry = $this->conn->query($driver_sql);
        $driver_stats = array('labels' => array(), 'data' => array());
        
        while($row = $driver_qry->fetch_assoc()) {
            $driver_stats['labels'][] = $row['driver_name'] ?: 'Unknown Driver';
            $driver_stats['data'][] = intval($row['count']);
        }
        
        return json_encode(array(
            'status' => 'success',
            'monthly_trend' => $monthly_trend,
            'location_stats' => $location_stats,
            'time_distribution' => $time_distribution,
            'officer_stats' => $officer_stats,
            'driver_stats' => $driver_stats
        ));
    }
    
    function get_dashboard_analytics() {
        // Time Analysis Data
        $time_sql = "SELECT 
                HOUR(date_created) as hour,
                COUNT(*) as count
            FROM offense_list
            GROUP BY HOUR(date_created)
            ORDER BY hour ASC";
            
        $time_qry = $this->conn->query($time_sql);
        $time_analysis = array('labels' => array(), 'data' => array());
        
        // Initialize all hours with 0
        for($i = 0; $i < 24; $i++) {
            $time_analysis['labels'][] = sprintf("%02d:00", $i);
            $time_analysis['data'][] = 0;
        }
        
        while($row = $time_qry->fetch_assoc()) {
            $time_analysis['data'][$row['hour']] = intval($row['count']);
        }

        return json_encode(array(
            'status' => 'success',
            'time_analysis' => $time_analysis
        ));
    }
}

$Master = new Master();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$sysset = new SystemSettings();
switch ($action) {
	case 'save_offense':
		echo $Master->save_offense();
	break;
	case 'delete_offense':
		echo $Master->delete_offense();
	break;
	case 'upload_files':
		echo $Master->upload_files();
	break;
	case 'save_driver':
		echo $Master->save_driver();
	break;
	case 'delete_driver':
		echo $Master->delete_driver();
	break;
	
	case 'save_offense_record':
		echo $Master->save_offense_record();
	break;
	case 'delete_offense_record':
		echo $Master->delete_offense_record();
	break;
	case 'delete_img':
		echo $Master->delete_img();
	break;
	case 'save_accident':
        echo $Master->save_accident();
    break;
	case 'delete_accident_report':
		echo $Master->delete_accident_report();
	break;
	case 'get_accident_locations':
        echo $Master->get_accident_locations();
    break;
	case 'get_accident_map_data':
        echo $Master->get_accident_map_data();
    break;
	case 'get_accident_analytics':
        echo $Master->get_accident_analytics();
    break;
	case 'get_dashboard_analytics':
        echo $Master->get_dashboard_analytics();
    break;
	default:
		// echo $sysset->index();
		break;
}