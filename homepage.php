<?php
session_start();
include('includes/db.php'); // Make sure db.php has your PDO connection
// Function to add book to the cart
if (isset($_GET['action']) && $_GET['action'] == 'add') {
    $isbn = $_GET['isbn'];
    $qty = 1; // default quantity

    // Check if cart already exists
    if (isset($_SESSION['cart'][$isbn])) {
        // Increase the quantity if the book is already in the cart
        $_SESSION['cart'][$isbn]['quantity'] += 1;
    } else {
        // Otherwise, add the book to the cart
        $stmt = $conn->prepare("SELECT * FROM Books WHERE ISBN = :isbn");
        $stmt->bindParam(':isbn', $isbn);
        $stmt->execute();
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        $_SESSION['cart'][$isbn] = [
            'isbn' => $book['ISBN'],
            'title' => $book['Title'],
            'price' => $book['Retail'],
            'quantity' => $qty
        ];
    }
    header("Location: index.php"); // Redirect back to the main page
    exit();
}
//?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Store</title>
    <link rel="stylesheet"  href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .book-list{
            background-image: url('/images/homepage.jpg');
            background-size: cover;  /* Ensures the image covers the entire area */
            background-repeat: no-repeat;  /* Prevents repetition */
            background-position: center center;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            color: #333;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            padding: 20px 0;
            text-align: center;
        }

        /*header h1 {*/
        /*    font-size: 2.5em;*/
        /*    margin-bottom: 10px;*/
        /*}*/

        header nav ul {
            list-style: none;
            margin-top: 10px;
            display: flex;
            justify-content: center;
        }

        header nav ul li {
            margin: 0 20px;
        }

        header nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 1.2em;
        }

        header nav ul li a:hover {
            text-decoration: underline;
        }

        .book-list {
            padding: 40px 0;
            background-color: white;
        }

        .book-list h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.2em;
            color: darkmagenta;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
        }

        .book-card {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .book-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 16px 24px rgba(0, 0, 0, 0.2);
        }

        .book-cover {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .book-card h3 {
            font-size: 1.5em;
            margin-bottom: 15px;
        }

        .book-card p {
            margin: 5px 0;
        }

        .book-card button {
            background-color: #28a745;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
            transition: background-color 0.3s;
        }

        .book-card button:hover {
            background-color: #218838;
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
        }

        footer p {
            font-size: 1em;
        }

        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }

            header h1 {
                font-size: 2em;
            }

            header nav ul li {
                font-size: 1.1em;
            }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script> <!-- Font Awesome for icons -->
</head>

<body>
<!-- Navigation Bar -->
<header>
    <div class="bg-dark text-white text-center py-3">
            <h1>Book Store</h1>
            <p>Discover and shop the best books online!</p>
<!--    </div>-->
<!--    <div>-->
        <nav>
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="#">Categories</a></li>
                <li><a href="cart.php">My Account(<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : '0'; ?>)</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </nav>
    </div>
</header>

<!-- Main Book Section -->
<section class="book-list">
    <div class="container">
        <h2>Featured Books</h2>
        <div class="grid-container">
            <?php
            // SQL query to fetch books and their authors and publishers
            $sql = "SELECT b.ISBN, b.Title, b.Cost, b.Retail, b.Category, p.Name AS Publisher, a.Fname, a.Lname
                        FROM Books b
                        JOIN Publisher p ON b.PubID = p.PubID
                        JOIN BookAuthor ba ON b.ISBN = ba.ISBN
                        JOIN Author a ON ba.AuthorID = a.AuthorID";

            $stmt = $conn->prepare($sql);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<div class='book-card'>";
                    echo "<img src='https://via.placeholder.com/150' alt='Book Cover' class='book-cover'>";
                    echo "<h3>" . $row['Title'] . "</h3>";
                    echo "<p><strong>Author:</strong> " . $row['Fname'] . " " . $row['Lname'] . "</p>";
                    echo "<p><strong>Publisher:</strong> " . $row['Publisher'] . "</p>";
                    echo "<p><strong>Category:</strong> " . $row['Category'] . "</p>";
                    echo "<p><strong>Price:</strong> $" . number_format($row['Retail'], 2) . "</p>";
                    echo "<button class='add-to-cart'><i class='fa fa-cart-plus'></i> Add to Cart</button>";
                    echo "</div>";
                }
            } else {
                echo "<p>No books available at the moment.</p>";
            }
            ?>
        </div>
    </div>
</section>

<!-- Footer Section -->
<footer>
    <div class="container">
        <p>&copy; 2024 Online Library. All rights reserved.</p>
    </div>
</footer>
</body>
</html>



