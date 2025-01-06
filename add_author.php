<?php
session_start();
include('includes/db.php');

// Check if the user is logged in
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    die("Access Denied: You do not have permission to access this page.");
}

// Handle author creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];

    // Generate a random AuthorId
    $authorId = rand(1000, 9999);

    try {
        // Insert the new author into the Authors table
        $stmt = $conn->prepare(
            "INSERT INTO Author (AuthorId, Fname, Lname) 
            VALUES (:authorId, :firstName, :lastName)"
        );
        $stmt->execute([
            ':authorId' => $authorId,
            ':firstName' => $firstName,
            ':lastName' => $lastName
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
    <title>Create Author</title>
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
        <h1>Create New Author</h1>
        <form method="POST">
            <div class="form-group">
                <label for="firstName">First Name</label>
                <input type="text" name="firstName" id="firstName" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="lastName">Last Name</label>
                <input type="text" name="lastName" id="lastName" class="form-control" required>
            </div>
            <button type="submit" name="save" class="btn btn-primary">Save Author</button>
            <a href="books_dashboard.php" class="btn btn-secondary">Back</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>