<?php
session_start();
include('includes/db.php');

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch Publishers and Authors for the form
try {
    // Fetch publishers
    $publisherStmt = $conn->prepare("SELECT PubId, Name FROM Publisher");
    $publisherStmt->execute();
    $publishers = $publisherStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch authors
    $authorStmt = $conn->prepare("SELECT AuthorId, CONCAT(Fname, ' ', Lname) AS FullName FROM Author");
    $authorStmt->execute();
    $authors = $authorStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}

// Handle book creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $isbn = rand(9950000000, 9959999999);
    $title = $_POST['title'];
    $cost = $_POST['cost'];
    $retail = $_POST['retail'];
    $discount = $_POST['discount'];
    $category = $_POST['category'];
    $pubId = $_POST['publisher'];
    $authorIds = $_POST['authors'];

    try {
        // Insert the book into the Books table
        $bookStmt = $conn->prepare(
            "INSERT INTO Books (ISBN, Title, PubDate, PubID, Cost, Retail, Discount, Category) 
            VALUES (:isbn, :title, CURDATE(), :pubId, :cost, :retail, :discount, :category)"
        );
        $bookStmt->execute([
            ':isbn' => $isbn,
            ':title' => $title,
            ':pubId' => $pubId,
            ':cost' => $cost,
            ':retail' => $retail,
            ':discount' => $discount,
            ':category' => $category,
        ]);

        // Insert authors into the BookAuthor table
        $bookAuthorStmt = $conn->prepare(
            "INSERT INTO BOOKAUTHOR (ISBN, AuthorID) VALUES (:isbn, :authorId)"
        );
        foreach ($authorIds as $authorId) {
            $bookAuthorStmt->execute([
                ':isbn' => $isbn,
                ':authorId' => $authorId,
            ]);
        }

        // Redirect to books dashboard
        header('Location: books_dashboard.php');
        exit();
    } catch (PDOException $e) {
        echo "Error adding book: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Book</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .container {
            margin-top: 20px;
            max-width: 800px;
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
        <h1>Add New Book</h1>
        <form method="POST">
            <div class="form-group mb-3">
                <label for="title">Title</label>
                <input type="text" name="title" id="title" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="publisher">Publisher</label>
                <select name="publisher" id="publisher" class="form-control" required>
                    <option value="" disabled selected>Select Publisher</option>
                    <?php foreach ($publishers as $publisher): ?>
                        <option value="<?= $publisher['PubId'] ?>"><?= htmlspecialchars($publisher['Name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-3">
                <label for="authors">Authors</label>
                <select name="authors[]" id="authors" class="form-control" required>
                    <option value="" disabled selected>Select Author</option>
                    <?php foreach ($authors as $author): ?>
                        <option value="<?= $author['AuthorId'] ?>"><?= htmlspecialchars($author['FullName']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-3">
                <label for="cost">Cost</label>
                <input type="number" name="cost" id="cost" class="form-control" step="0.01" required>
            </div>
            <div class="form-group mb-3">
                <label for="retail">Retail Price</label>
                <input type="number" name="retail" id="retail" class="form-control" step="0.01" required>
            </div>
            <div class="form-group mb-3">
                <label for="discount">Discount (%)</label>
                <input type="number" name="discount" id="discount" class="form-control" step="0.01" required>
            </div>
            <div class="form-group mb-3">
                <label for="category">Category</label>
                <input type="text" name="category" id="category" class="form-control" required>
            </div>
            <button type="submit" name="save" class="btn btn-primary">Save Book</button>
            <a href="books_dashboard.php" class="btn btn-secondary">Back</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>