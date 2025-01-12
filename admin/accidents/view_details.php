<?php
require_once('../../config.php');
if(isset($_GET['id']) && $_GET['id'] > 0){
    $qry = $conn->query("SELECT a.*, d.name as driver_name, d.license_id_no, o.name as officer_name 
                        FROM `accident_reports` a 
                        INNER JOIN `drivers_list` d ON a.driver_id = d.id 
                        LEFT JOIN `officers` o ON a.officer_id = o.id 
                        WHERE a.id = '{$_GET['id']}'");
    if($qry->num_rows > 0){
        foreach($qry->fetch_assoc() as $k => $v){
            $$k=$v;
        }
    }else{
        echo '<script>alert("Unknown Accident ID."); location.replace("./?page=accidents")</script>';
    }
}else{
    echo '<script>alert("Unknown Accident ID."); location.replace("./?page=accidents")</script>';
}
?>
<style>
    #uni_modal .modal-footer{
        display:none;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Accident Information -->
            <dl>
                <dt class="text-muted">Report Date:</dt>
                <dd class='pl-4'><?= isset($date_created) ? date("F d, Y h:i A",strtotime($date_created)) : '' ?></dd>
                <dt class="text-muted">Date of Accident:</dt>
                <dd class='pl-4'><?= isset($date_of_accident) ? date("F d, Y h:i A",strtotime($date_of_accident)) : '' ?></dd>
                <dt class="text-muted">Location:</dt>
                <dd class='pl-4'><?= isset($location) ? $location : '' ?></dd>
            </dl>
            
            <!-- Driver Information -->
            <div class="border-top border-bottom pt-2 pb-2">
                <h5 class="text-muted">Driver Information</h5>
                <dl>
                    <dt class="text-muted">Name:</dt>
                    <dd class='pl-4'><?= isset($driver_name) ? $driver_name : '' ?></dd>
                    <dt class="text-muted">License ID:</dt>
                    <dd class='pl-4'><?= isset($license_id_no) ? $license_id_no : '' ?></dd>
                </dl>
            </div>
            
            <!-- Officer Information -->
            <div class="border-bottom pt-2 pb-2">
                <h5 class="text-muted">Officer Information</h5>
                <dl>
                    <dt class="text-muted">Name:</dt>
                    <dd class='pl-4'><?= isset($officer_name) ? $officer_name : 'N/A' ?></dd>
                </dl>
            </div>
            
            <!-- Accident Description -->
            <div class="pt-2">
                <h5 class="text-muted">Accident Description</h5>
                <div class="pl-4"><?= isset($description) ? nl2br($description) : '' ?></div>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-12 text-right">
            <button class="btn btn-flat btn-sm btn-dark" type="button" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
        </div>
    </div>
</div>