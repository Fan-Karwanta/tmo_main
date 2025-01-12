<?php require_once('../config.php'); ?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Accident Report Analytics</h3>
        <div class="card-tools">
            <select id="time-filter" class="form-control">
                <option value="all">All Time</option>
                <option value="year">This Year</option>
                <option value="month">This Month</option>
                <option value="week">This Week</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Monthly Trend -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title">Monthly Accident Trends</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Driver Involvement Analysis -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title">Driver Involvement Analysis</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="locationChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Daily Time Distribution -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title">Accidents by Time of Day</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="timeDistributionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Officer Distribution -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title">Cases by Officer</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="officerChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(function(){
    // Chart color palette
    const colors = [
        'rgba(255, 99, 132, 0.7)',
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 206, 86, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(153, 102, 255, 0.7)',
        'rgba(255, 159, 64, 0.7)'
    ];

    function loadChartData(timeFilter = 'all') {
        $.ajax({
            url: _base_url_ + "classes/Master.php?f=get_accident_analytics",
            method: 'POST',
            data: { time_filter: timeFilter },
            dataType: 'json',
            success: function(resp) {
                if(resp.status == 'success') {
                    // Monthly Trend Chart
                    new Chart(document.getElementById('monthlyTrendChart'), {
                        type: 'line',
                        data: {
                            labels: resp.monthly_trend.labels,
                            datasets: [{
                                label: 'Number of Accidents',
                                data: resp.monthly_trend.data,
                                borderColor: colors[0],
                                tension: 0.1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Monthly Accident Trends'
                                }
                            }
                        }
                    });

                     // Driver Involvement Analysis
                     new Chart(document.getElementById('locationChart'), {
                        type: 'doughnut',
                        data: {
                            labels: resp.driver_stats.labels,
                            datasets: [{
                                data: resp.driver_stats.data,
                                backgroundColor: colors,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Driver Involvement Analysis'
                                },
                                legend: {
                                    position: 'right',
                                    labels: {
                                        boxWidth: 12
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            let value = context.raw || 0;
                                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            let percentage = Math.round((value * 100) / total);
                                            return `${label}: ${value} (${percentage}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // Time Distribution Chart
                    new Chart(document.getElementById('timeDistributionChart'), {
                        type: 'bar',
                        data: {
                            labels: resp.time_distribution.labels,
                            datasets: [{
                                label: 'Number of Accidents',
                                data: resp.time_distribution.data,
                                backgroundColor: colors[2]
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Accidents by Time of Day'
                                }
                            }
                        }
                    });

                    // Officer Distribution Chart
                    new Chart(document.getElementById('officerChart'), {
                        type: 'pie',
                        data: {
                            labels: resp.officer_stats.labels,
                            datasets: [{
                                data: resp.officer_stats.data,
                                backgroundColor: colors
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Cases Handled by Officers'
                                }
                            }
                        }
                    });
                }
            },
            error: function(err) {
                console.error(err);
                alert_toast("An error occurred while loading analytics data", 'error');
            }
        });
    }

    // Initial load
    loadChartData();

    // Handle filter changes
    $('#time-filter').change(function() {
        loadChartData($(this).val());
    });
});
</script>
