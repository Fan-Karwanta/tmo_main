<?php require_once('../config.php'); ?>
 <!DOCTYPE html>
<html lang="en" class="" style="height: auto;">
<?php require_once('inc/header.php') ?>
  <body class="sidebar-mini layout-fixed control-sidebar-slide-open layout-navbar-fixed sidebar-mini-md sidebar-mini-xs text-sm" data-new-gr-c-s-check-loaded="14.991.0" data-gr-ext-installed="" style="height: auto;">
    <div class="wrapper">
     <?php require_once('inc/topBarNav.php') ?>
     <?php require_once('inc/navigation.php') ?>
              
     <?php $page = isset($_GET['page']) ? $_GET['page'] : 'home';  ?>
      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper pt-3" style="min-height: 567.854px;">
     
        <!-- Main content -->
        <section class="content text-dark">
          <div class="container-fluid">
            <?php
            // If it's the home page, show dashboard
            if($page == 'home'): ?>
              <h1>Welcome to <?php echo $_settings->info('name') ?></h1>
              <hr class="bg-light">
              
              <!-- Info Boxes -->
              <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                  <div class="info-box">
                    <span class="info-box-icon bg-light elevation-1"><i class="fas fa-calendar-day"></i></span>
                    <div class="info-box-content">
                      <span class="info-box-text">Today's Offenses</span>
                      <span class="info-box-number text-right">
                        <?php 
                          $offense = $conn->query("SELECT * FROM `offense_list` where date(date_created) = '".date('Y-m-d')."' ")->num_rows;
                          echo number_format($offense);
                        ?>
                      </span>
                    </div>
                  </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                  <div class="info-box mb-3">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-id-card"></i></span>
                    <div class="info-box-content">
                      <span class="info-box-text">Total Driver's Listed</span>
                      <span class="info-box-number text-right">
                        <?php 
                          $drivers = $conn->query("SELECT id FROM `drivers_list`")->num_rows;
                          echo number_format($drivers);
                        ?>
                      </span>
                    </div>
                  </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                  <div class="info-box mb-3">
                    <span class="info-box-icon bg-lightblue elevation-1"><i class="fas fa-traffic-light"></i></span>
                    <div class="info-box-content">
                      <span class="info-box-text">Total Traffic Offenses</span>
                      <span class="info-box-number text-right">
                        <?php 
                          $to = $conn->query("SELECT id FROM `offenses` where status = 1")->num_rows;
                          echo number_format($to);
                        ?>
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Charts Row -->
              <div class="row mt-4">
                <!-- Monthly Offense Trends -->
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header">
                      <h3 class="card-title">Monthly Offense Trends</h3>
                    </div>
                    <div class="card-body">
                      <canvas id="monthlyTrendsChart"></canvas>
                    </div>
                  </div>
                </div>

                <!-- Most Common Violations -->
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header">
                      <h3 class="card-title">Most Common Violations</h3>
                    </div>
                    <div class="card-body">
                      <canvas id="commonViolationsChart"></canvas>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row mt-4">
                <!-- Monthly Revenue -->
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header">
                      <h3 class="card-title">Monthly Revenue from Fines</h3>
                    </div>
                    <div class="card-body">
                      <canvas id="monthlyRevenueChart"></canvas>
                    </div>
                  </div>
                </div>

                <!-- Driver Violation Distribution -->
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header">
                      <h3 class="card-title">Driver Violation Distribution</h3>
                    </div>
                    <div class="card-body">
                      <canvas id="driverViolationChart" style="min-height: 250px; height: 280px; max-height: 280px; max-width: 100%;"></canvas>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row mt-4">
                <!-- Time Analysis Chart -->
                <div class="col-12">
                  <div class="card">
                    <div class="card-header">
                      <h3 class="card-title">Report Filing Time Analysis (24-Hour Distribution)</h3>
                    </div>
                    <div class="card-body">
                      <div style="position: relative; height: 60vh; width: 100%;">
                        <canvas id="timeAnalysisChart"></canvas>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <?php 
              // Fetch Monthly Offense Data
              $months = [];
              $offense_counts = [];
              $revenue_data = [];

              $monthly_query = $conn->query("
                  SELECT 
                      MONTH(date_created) as month,
                      COUNT(*) as offense_count,
                      SUM(total_amount) as monthly_revenue
                  FROM offense_list 
                  WHERE date_created >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                  GROUP BY MONTH(date_created)
                  ORDER BY date_created ASC
              ");

              while($row = $monthly_query->fetch_assoc()) {
                  $month_name = date("F", mktime(0, 0, 0, $row['month'], 1));
                  $months[] = $month_name;
                  $offense_counts[] = $row['offense_count'];
                  $revenue_data[] = $row['monthly_revenue'];
              }

              // Fetch Most Common Violations
              $violations = [];
              $violation_counts = [];

              $violations_query = $conn->query("
                  SELECT 
                      o.name,
                      COUNT(oi.offense_id) as violation_count
                  FROM offenses o
                  LEFT JOIN offense_items oi ON o.id = oi.offense_id
                  GROUP BY o.id
                  ORDER BY violation_count DESC
                  LIMIT 5
              ");

              while($row = $violations_query->fetch_assoc()) {
                  $violations[] = $row['name'];
                  $violation_counts[] = $row['violation_count'];
              }

              // Fetch Driver Violation Distribution
              $driver_violations = [];
              $violation_per_driver = [];

              $driver_query = $conn->query("
                  SELECT 
                      d.name,
                      COUNT(ol.id) as violation_count
                  FROM drivers_list d
                  LEFT JOIN offense_list ol ON d.id = ol.driver_id
                  GROUP BY d.id
                  ORDER BY violation_count DESC
                  LIMIT 5
              ");

              while($row = $driver_query->fetch_assoc()) {
                  $driver_violations[] = $row['name'];
                  $violation_per_driver[] = $row['violation_count'];
              }

              // Query to get report counts by hour
              $time_analysis = array_fill(0, 24, 0); // Initialize array for 24 hours
              $time_query = $conn->query("
                  SELECT 
                      HOUR(date_created) as hour_of_day,
                      COUNT(*) as report_count
                  FROM offense_list 
                  GROUP BY HOUR(date_created)
                  ORDER BY hour_of_day
              ");
              while($row = $time_query->fetch_assoc()){
                  $time_analysis[$row['hour_of_day']] = $row['report_count'];
              }
              ?>

              <!-- Include Chart.js -->
              <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

              <script>
              // Monthly Trends Chart
              new Chart(document.getElementById('monthlyTrendsChart'), {
                  type: 'line',
                  data: {
                      labels: <?php echo json_encode($months) ?>,
                      datasets: [{
                          label: 'Number of Offenses',
                          data: <?php echo json_encode($offense_counts) ?>,
                          borderColor: 'rgb(75, 192, 192)',
                          tension: 0.1
                      }]
                  },
                  options: {
                      responsive: true,
                      scales: {
                          y: {
                              beginAtZero: true
                          }
                      }
                  }
              });

              // Common Violations Chart
              new Chart(document.getElementById('commonViolationsChart'), {
                  type: 'bar',
                  data: {
                      labels: <?php echo json_encode($violations) ?>,
                      datasets: [{
                          label: 'Number of Violations',
                          data: <?php echo json_encode($violation_counts) ?>,
                          backgroundColor: 'rgba(54, 162, 235, 0.5)',
                          borderColor: 'rgb(54, 162, 235)',
                          borderWidth: 1
                      }]
                  },
                  options: {
                      responsive: true,
                      scales: {
                          y: {
                              beginAtZero: true
                          }
                      }
                  }
              });

              // Monthly Revenue Chart
              new Chart(document.getElementById('monthlyRevenueChart'), {
                  type: 'bar',
                  data: {
                      labels: <?php echo json_encode($months) ?>,
                      datasets: [{
                          label: 'Revenue (PHP)',
                          data: <?php echo json_encode($revenue_data) ?>,
                          backgroundColor: 'rgba(75, 192, 192, 0.5)',
                          borderColor: 'rgb(75, 192, 192)',
                          borderWidth: 1
                      }]
                  },
                  options: {
                      responsive: true,
                      scales: {
                          y: {
                              beginAtZero: true
                          }
                      }
                  }
              });

              // Driver Violation Distribution Chart
              new Chart(document.getElementById('driverViolationChart'), {
                  type: 'pie',
                  data: {
                      labels: <?php echo json_encode($driver_violations) ?>,
                      datasets: [{
                          data: <?php echo json_encode($violation_per_driver) ?>,
                          backgroundColor: [
                              'rgba(255, 99, 132, 0.5)',
                              'rgba(54, 162, 235, 0.5)',
                              'rgba(255, 206, 86, 0.5)',
                              'rgba(75, 192, 192, 0.5)',
                              'rgba(153, 102, 255, 0.5)'
                          ],
                          borderColor: [
                              'rgb(255, 99, 132)',
                              'rgb(54, 162, 235)',
                              'rgb(255, 206, 86)',
                              'rgb(75, 192, 192)',
                              'rgb(153, 102, 255)'
                          ],
                          borderWidth: 1
                      }]
                  },
                  options: {
                      responsive: true
                  }
              });

              // Time Analysis Chart
              new Chart(document.getElementById('timeAnalysisChart'), {
                  type: 'bar',
                  data: {
                      labels: Array.from({length: 24}, (_, i) => String(i).padStart(2, '0') + ':00'),
                      datasets: [{
                          label: 'Number of Reports',
                          data: <?php echo json_encode(array_values($time_analysis)); ?>,
                          backgroundColor: 'rgba(54, 162, 235, 0.7)',
                          borderColor: 'rgba(54, 162, 235, 1)',
                          borderWidth: 1,
                          barThickness: 'flex',
                          maxBarThickness: 50
                      }]
                  },
                  options: {
                      responsive: true,
                      maintainAspectRatio: false,
                      scales: {
                          y: {
                              beginAtZero: true,
                              ticks: {
                                  stepSize: 1,
                                  font: {
                                      size: 12
                                  }
                              },
                              grid: {
                                  color: 'rgba(0, 0, 0, 0.1)'
                              }
                          },
                          x: {
                              grid: {
                                  display: false
                              },
                              ticks: {
                                  font: {
                                      size: 12
                                  },
                                  maxRotation: 45,
                                  minRotation: 45
                              }
                          }
                      },
                      plugins: {
                          title: {
                              display: true,
                              text: 'Distribution of Reports Throughout the Day',
                              font: {
                                  size: 16,
                                  weight: 'bold'
                              },
                              padding: 20
                          },
                          legend: {
                              display: true,
                              position: 'top',
                              labels: {
                                  font: {
                                      size: 12
                                  }
                              }
                          }
                      },
                      layout: {
                          padding: {
                              left: 10,
                              right: 10,
                              top: 0,
                              bottom: 0
                          }
                      }
                  }
              });
              </script>

            <?php else: ?>
              <?php 
              if(!file_exists($page.".php") && !is_dir($page)){
                  include '404.html';
              }else{
                if(is_dir($page))
                  include $page.'/index.php';
                else
                  include $page.'.php';
              }
              ?>
            <?php endif; ?>
          </div>
        </section>
        <!-- /.content -->

        <!-- Modals -->
        <div class="modal fade" id="confirm_modal" role='dialog'>
          <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Confirmation</h5>
              </div>
              <div class="modal-body">
                <div id="delete_content"></div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-primary" id='confirm' onclick="">Continue</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
        <div class="modal fade" id="uni_modal" role='dialog'>
          <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"></h5>
              </div>
              <div class="modal-body">
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Save</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              </div>
            </div>
          </div>
        </div>
        <div class="modal fade" id="uni_modal_right" role='dialog'>
          <div class="modal-dialog modal-full-height  modal-md" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span class="fa fa-arrow-right"></span>
                </button>
              </div>
              <div class="modal-body">
              </div>
            </div>
          </div>
        </div>
        <div class="modal fade" id="viewer_modal" role='dialog'>
          <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
              <button type="button" class="btn-close" data-dismiss="modal"><span class="fa fa-times"></span></button>
              <img src="" alt="">
            </div>
          </div>
        </div>
      </div>
      <!-- /.content-wrapper -->
      <?php require_once('inc/footer.php') ?>
  </body>
</html>
