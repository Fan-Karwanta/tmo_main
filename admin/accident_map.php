<?php require_once('../config.php'); ?>

<style>
    #accident-map { 
        height: 700px; 
        width: 100%;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .custom-info-box {
        padding: 10px;
        background: white;
        border-radius: 5px;
        box-shadow: 0 0 15px rgba(0,0,0,0.2);
        min-width: 200px;
    }
    .debug-info {
        margin-top: 10px;
        padding: 10px;
        background: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Mapping of Accident Prone Areas</h3>
        <div class="card-tools">
            <select id="time-filter" class="form-control">
                <option value="all">All Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="year">This Year</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-9">
                <div id="accident-map"></div>
                <!--<div class="debug-info">
                    <strong>Debug Info:</strong>
                    <pre id="debug-info"></pre>
                </div> -->
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Statistics</h4>
                    </div>
                    <div class="card-body">
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-car-crash"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Accidents</span>
                                <span class="info-box-number" id="total-accidents">0</span>
                            </div>
                        </div>
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-map-marker-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Unique Locations</span>
                                <span class="info-box-number" id="unique-locations">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Leaflet CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<!-- Include Leaflet MarkerCluster -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

<script>
$(function(){
    // Initialize the map centered on Nasugbu, Batangas
    var map = L.map('accident-map').setView([14.0726, 120.6321], 13);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: ' OpenStreetMap contributors'
    }).addTo(map);

    // Initialize marker cluster group with options
    var markers = L.markerClusterGroup({
        chunkedLoading: true,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: true,
        zoomToBoundsOnClick: true
    });

    function loadAccidentData(timeFilter = 'all') {
        // Clear existing markers
        markers.clearLayers();
        map.removeLayer(markers);

        $.ajax({
            url: _base_url_+"classes/Master.php?f=get_accident_map_data",
            method: 'POST',
            data: { time_filter: timeFilter },
            dataType: 'json',
            success: function(resp) {
                console.log("Response:", resp); // Debug log
                
                if(resp.status == 'success') {
                    // Update statistics
                    $('#total-accidents').text(resp.stats.total_accidents);
                    $('#unique-locations').text(resp.stats.unique_locations);
                    
                    // Update debug info
                    $('#debug-info').html(
                        JSON.stringify(resp.debug, null, 2)
                    );

                    // Add markers for each accident location
                    resp.data.forEach(function(accident) {
                        if (accident.latitude && accident.longitude) {
                            console.log("Adding marker at:", accident.latitude, accident.longitude);
                            
                            var marker = L.marker([
                                parseFloat(accident.latitude), 
                                parseFloat(accident.longitude)
                            ]);
                            
                            var popupContent = `
                                <div class="custom-info-box">
                                    <h5>Accident Details</h5>
                                    <p><strong>Date:</strong> ${accident.date}</p>
                                    <p><strong>Location:</strong> ${accident.location}</p>
                                    <p><strong>Description:</strong> ${accident.description || 'No description available'}</p>
                                    <p><strong>Driver:</strong> ${accident.driver_name || 'N/A'}</p>
                                </div>
                            `;
                            
                            marker.bindPopup(popupContent);
                            markers.addLayer(marker);
                        }
                    });

                    // Add marker cluster to map
                    map.addLayer(markers);

                    // Fit bounds if we have markers
                    if(resp.data.length > 0) {
                        try {
                            var bounds = markers.getBounds();
                            map.fitBounds(bounds);
                        } catch(e) {
                            console.error("Error fitting bounds:", e);
                        }
                    }
                } else {
                    console.error("Failed to load accident data:", resp);
                    alert_toast("Failed to load accident data", 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {xhr: xhr, status: status, error: error});
                alert_toast("An error occurred while loading accident data", 'error');
            }
        });
    }

    // Initial load
    loadAccidentData();

    // Handle filter changes
    $('#time-filter').change(function() {
        loadAccidentData($(this).val());
    });
});
</script>
