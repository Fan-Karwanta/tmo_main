<?php
// Include database connection
include './../classes/DBConnection.php'; // Ensure the path is correct

// Check if the request is to save officer data
if (isset($_GET['f']) && $_GET['f'] == 'save') {
    $response = 0; // Default response (failure)

    // Sanitize and validate input data
    $id = isset($_POST['id']) && $_POST['id'] !== "" ? intval($_POST['id']) : null;
    $tmoId = trim($_POST['tmoId'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = isset($_POST['password']) && $_POST['password'] !== "" ? $_POST['password'] : null;

    // Validate required fields
    if (empty($tmoId) || empty($name) || empty($birthday) || empty($gender) || empty($address) || empty($username)) {
        echo json_encode(['status' => 0, 'message' => 'Please fill out all required fields.']);
        exit();
    }

    // Check if the username already exists (use prepared statements)
    $checkStmt = $conn->prepare("SELECT id FROM officers WHERE username = ? AND id != ?");
    $checkStmt->bind_param("si", $username, $id);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo json_encode(['status' => 2, 'message' => 'Username already exists.']);
        exit();
    }

    // Insert or update officer data
    if ($id === null) {
        // Insert new officer
        $sql = "INSERT INTO officers (tmoId, name, birthday, gender, address, username, password, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($sql);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param("sssssss", $tmoId, $name, $birthday, $gender, $address, $username, $hashedPassword);
    } else {
        // Update existing officer
        if (!empty($password)) {
            $sql = "UPDATE officers SET tmoId = ?, name = ?, birthday = ?, gender = ?, address = ?, username = ?, password = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param("sssssssi", $tmoId, $name, $birthday, $gender, $address, $username, $hashedPassword, $id);
        } else {
            // Update without password
            $sql = "UPDATE officers SET tmoId = ?, name = ?, birthday = ?, gender = ?, address = ?, username = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $tmoId, $name, $birthday, $gender, $address, $username, $id);
        }
    }

    // Execute the query and return the response
    if ($stmt->execute()) {
        echo json_encode(['status' => 1, 'message' => 'Record saved successfully.']);
    } else {
        echo json_encode(['status' => 0, 'message' => 'Failed to save record: ' . $stmt->error]);
    }

    // Close statements
    $checkStmt->close();
    $stmt->close();
}
?>
