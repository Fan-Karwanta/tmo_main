<?php
// Include the database connection class
require_once './../classes/DBConnection.php';

// Initialize the database connection
$db = new DBConnection();
$conn = $db->conn;

// Insert data into the database
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tmoId = $_POST['tmoId'];
    $name = $_POST['name'];
    $birthday = $_POST['birthday'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $username_input = $_POST['username'];
    $password_input = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure the password
    $created_at = date("Y-m-d H:i:s");
    $updated_at = date("Y-m-d H:i:s");

    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO officers (tmoId, name, birthday, gender, address, username, password, created_at, updated_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $tmoId, $name, $birthday, $gender, $address, $username_input, $password_input, $created_at, $updated_at);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Record added successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Officer Form</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h4>Insert Officer Information</h4>
            </div>
            <div class="card-body">
                <div id="msg"></div>
                <form method="POST" action="" id="manage-officers">
                    <div class="form-group">
                        <label for="tmoId">TMO ID</label>
                        <input type="text" name="tmoId" id="tmoId" class="form-control" value="TMO<?php echo rand(1000, 9999); ?>" readonly required>
                    </div>

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="birthday">Birthday</label>
                        <input type="date" name="birthday" id="birthday" class="form-control" max="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select name="gender" id="gender" class="custom-select" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea name="address" id="address" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" class="form-control" required autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required autocomplete="off">
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="./" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Include jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
    $(document).ready(function() {
    // Restrict the max date for the birthday field
    document.getElementById('birthday').max = new Date().toISOString().split("T")[0];

    $('#manage-officers').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: '', // Current file handles the POST request
            data: $(this).serialize(),
            dataType: 'json', // Expect JSON response
            success: function(response) {
                if (response.status === 'success') {
                    // Redirect to list.php with a success query parameter
                    window.location.href = './officers/list.php';
                } else {
                    // Display error message
                    $('#msg').html("<div class='alert alert-danger'>" + response.message + "</div>");
                }
            },
            error: function() {
                $('#msg').html("<div class='alert alert-danger'>An unexpected error occurred.</div>");
            }
        });
    });
});


</script>

</body>
</html>
