<?php
session_start();
include('includes/db.php');

// Check if the user is logged in
if (!isset($_SESSION['userID' ])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    die("Access Denied: You do not have permission to access this page.");
}

// Handle author creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $phone = $_POST['phone'];

    // Generate a random AuthorId
    $pubID = rand(1, 99);

    try {
        // Insert the new author into the Authors table
        $stmt = $conn->prepare(
            "INSERT INTO Publisher (PubId, Name, Contact, Phone) 
            VALUES (:pubID, :name, :contact, :phone)"
        );
        $stmt->execute([
            ':pubID' => $pubID,
            ':name' => $name,
            ':contact' => $contact,
            ':phone' => $phone
        ]);

        // Redirect to authors list or success page
        header('Location: books_dashboard.php');
        exit();
    } catch (PDOException $e) {
        echo "Error adding author: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Publisher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 20px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: black;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .btn {
            margin: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Create New Publisher</h1>
    <form method="POST">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="contact">Contact</label>
            <input type="text" name="contact" id="contact" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" name="phone" id="phone" class="form-control" required>
        </div>
        <button type="submit" name="save" class="btn btn-primary">Save Publisher</button>
        <a href="books_dashboard.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
