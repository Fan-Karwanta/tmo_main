<?php
if(isset($_GET['id']) && $_GET['id'] > 0){
    $qry = $conn->query("SELECT a.*, d.name as driver_name, d.license_id_no, o.name as officer_name 
                        FROM `accident_reports` a 
                        INNER JOIN `drivers_list` d ON a.driver_id = d.id 
                        LEFT JOIN `officers` o ON a.officer_id = o.id 
                        WHERE a.id = '{$_GET['id']}'");
    if($qry->num_rows > 0){
        foreach($qry->fetch_assoc() as $k => $v){
            $$k = $v;
        }
    }
}
?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><?php echo isset($id) ? "Update Accident Report" : "Create New Accident Report" ?></h3>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <form action="" id="accident-form">
                <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
                
                <div class="form-group">
                    <label for="driver_id">Driver</label>
                    <select name="driver_id" id="driver_id" class="form-control select2bs4" required>
                        <option value="" disabled <?php echo !isset($driver_id) ? "selected" : '' ?>></option>
                        <?php
                        $drivers = $conn->query("SELECT * FROM `drivers_list` order by name asc");
                        while($row = $drivers->fetch_assoc()):
                        ?>
                        <option value="<?php echo $row['id'] ?>" <?php echo isset($driver_id) && $driver_id == $row['id'] ? 'selected' : '' ?>><?php echo $row['name'] ?> (<?php echo $row['license_id_no'] ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="officer_id">Officer</label>
                    <select name="officer_id" id="officer_id" class="form-control select2bs4">
                        <option value="" <?php echo !isset($officer_id) ? "selected" : '' ?>></option>
                        <?php
                        $officers = $conn->query("SELECT * FROM `officers` order by name asc");
                        while($row = $officers->fetch_assoc()):
                        ?>
                        <option value="<?php echo $row['id'] ?>" <?php echo isset($officer_id) && $officer_id == $row['id'] ? 'selected' : '' ?>><?php echo $row['name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date_of_accident">Date and Time of Accident</label>
                    <input type="datetime-local" name="date_of_accident" id="date_of_accident" class="form-control" value="<?php echo isset($date_of_accident) ? date('Y-m-d\TH:i',strtotime($date_of_accident)) : '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="location">Location</label>
                    <div id="map" style="height: 400px; width: 100%; margin-bottom: 10px;"></div>
                    <input type="text" name="location" id="location" class="form-control" value="<?php echo isset($location) ? $location : ''; ?>" readonly required>
                    <input type="hidden" name="latitude" id="latitude" value="<?php echo isset($latitude) ? $latitude : ''; ?>">
                    <input type="hidden" name="longitude" id="longitude" value="<?php echo isset($longitude) ? $longitude : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea rows="5" name="description" id="description" class="form-control" required><?php echo isset($description) ? $description : ''; ?></textarea>
                </div>
            </form>
        </div>
    </div>
    <div class="card-footer">
        <button class="btn btn-flat btn-primary" form="accident-form">Save</button>
        <a class="btn btn-flat btn-default" href="?page=accidents">Cancel</a>
    </div>
</div>

<!-- Include Leaflet CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<script>
$(document).ready(function(){
    $('.select2bs4').select2({theme:"bootstrap4"});
    
    // Initialize the map
    var map = L.map('map').setView([14.0583, 120.6350], 13); // Default center on Nasugbu, Batangas

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: ' OpenStreetMap contributors'
    }).addTo(map);

    var marker;

    // If editing and coordinates exist, show the marker
    <?php if(isset($latitude) && isset($longitude)): ?>
        marker = L.marker([<?php echo $latitude; ?>, <?php echo $longitude; ?>]).addTo(map);
        map.setView([<?php echo $latitude; ?>, <?php echo $longitude; ?>], 15);
    <?php endif; ?>

    // Handle map clicks
    map.on('click', function(e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;

        // Remove existing marker if any
        if (marker) {
            map.removeLayer(marker);
        }

        // Add new marker
        marker = L.marker([lat, lng]).addTo(map);

        // Update form fields
        $('#latitude').val(lat);
        $('#longitude').val(lng);

        // Reverse geocode to get address
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(response => response.json())
            .then(data => {
                $('#location').val(data.display_name);
            })
            .catch(error => {
                console.error('Error:', error);
                $('#location').val(`${lat}, ${lng}`);
            });
    });
    
    $('#accident-form').submit(function(e){
        e.preventDefault();
        var _this = $(this)
        $('.err-msg').remove();
        start_loader();
        $.ajax({
            url:_base_url_+"classes/Master.php?f=save_accident",
            data: new FormData($(this)[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            type: 'POST',
            dataType: 'json',
            error:err=>{
                console.log(err)
                alert_toast("An error occurred", 'error');
                end_loader();
            },
            success:function(resp){
                if(typeof resp =='object' && resp.status == 'success'){
                    location.href = "./?page=accidents";
                }else if(resp.status == 'failed' && !!resp.msg){
                    var el = $('<div>')
                        el.addClass("alert alert-danger err-msg").text(resp.msg)
                        _this.prepend(el)
                        el.show('slow')
                }else{
                    alert_toast("An error occurred", 'error');
                }
                end_loader();
            }
        })
    })
});
</script>