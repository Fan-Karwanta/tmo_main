<?php
require_once('../config.php');
?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Accident-Prone Areas Map</h3>
        <div class="card-tools">
            <select id="heatmap-filter" class="form-control">
                <option value="all">All Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="year">This Year</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <div id="heatmap" style="height: 600px; width: 100%;"></div>
    </div>
</div>

<!-- Include Leaflet CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<!-- Include Leaflet Heat plugin -->
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>

<script>
$(document).ready(function(){
    // Initialize the map
    var map = L.map('heatmap').setView([14.0583, 120.6350], 13); // Center on Nasugbu, Batangas

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    var heatLayer;
    
    function loadHeatmapData(filter = 'all') {
        $.ajax({
            url: _base_url_+"classes/Master.php?f=get_accident_locations",
            method: 'POST',
            data: { filter: filter },
            dataType: 'json',
            success: function(resp) {
                if(resp.status == 'success') {
                    // Remove existing heatmap layer if it exists
                    if (heatLayer) {
                        map.removeLayer(heatLayer);
                    }

                    // Create heatmap data points
                    var heatData = [];
                    resp.data.forEach(function(point) {
                        heatData.push([point.latitude, point.longitude, point.intensity]);
                    });

                    // Create and add the heatmap layer
                    heatLayer = L.heatLayer(heatData, {
                        radius: 25,
                        blur: 15,
                        maxZoom: 10,
                        max: 1.0,
                        gradient: {
                            0.4: 'blue',
                            0.6: 'yellow',
                            0.8: 'orange',
                            1.0: 'red'
                        }
                    }).addTo(map);

                    // Add markers with popups for each accident location
                    resp.data.forEach(function(point) {
                        var marker = L.marker([point.latitude, point.longitude])
                            .bindPopup(
                                '<strong>Date:</strong> ' + point.date + '<br>' +
                                '<strong>Location:</strong> ' + point.location + '<br>' +
                                '<strong>Incidents:</strong> ' + point.count
                            );
                        marker.addTo(map);
                    });
                }
            },
            error: function(err) {
                console.log(err);
                alert_toast("An error occurred while loading heatmap data", 'error');
            }
        });
    }

    // Initial load
    loadHeatmapData();

    // Handle filter changes
    $('#heatmap-filter').change(function() {
        loadHeatmapData($(this).val());
    });
});
</script>
