<?php
session_start();
include('includes/db.php'); // Ensure this file establishes a PDO connection as $conn


// Function to add a book to the cart
if (isset($_GET['action']) && $_GET['action'] == 'add') {
    $isbn = $_GET['isbn'];
    $qty = 1; // Default quantity

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

        if ($book) {
            $_SESSION['cart'][$isbn] = [
                'isbn' => $book['ISBN'],
                'title' => $book['Title'],
                'price' => $book['Retail'],
                'quantity' => $qty
            ];
        }
    }
    header("Location: homepage.php"); // Redirect back to the main page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Store</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
        }

        header {
            padding: 20px 0;
            background-color: #333;
            color: white;
            text-align: center;
        }

        .book-list {
            padding: 40px 0;
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
        }

        .book-card img {
            max-width: 100%;
            height: auto;
            margin-bottom: 15px;
        }

        .book-card button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 20px 0;
        }

        .main-nav {
    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
    padding: 1rem 0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.nav {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0 1rem;
}

.nav-item {
    position: relative;
}

.nav-link {
    color: white !important;
    padding: 0.5rem 1rem !important;
    font-weight: 500;
    transition: all 0.3s ease;
    text-decoration: none;
    font-size: 1rem;
    position: relative;
}

.nav-link:hover {
    color: rgba(255, 255, 255, 0.9) !important;
    transform: translateY(-2px);
}

.nav-link:after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: 0;
    left: 50%;
    background-color: white;
    transition: all 0.3s ease;
    transform: translateX(-50%);
}

.nav-link:hover:after {
    width: 70%;
}

/* Cart item count styling */
.cart-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.cart-count {
    background: #e74c3c;
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 50%;
    font-size: 0.8rem;
    font-weight: bold;
    min-width: 1.5rem;
    text-align: center;
}

/* User section styling */
.nav-user {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-left: auto;
    padding: 0 1rem;
}

.nav-user-email {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.9rem;
}

.btn-auth {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    padding: 0.5rem 1.5rem;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-auth:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
    color: white;
    text-decoration: none;
}

.btn-logout {
    background: #e74c3c;
    border: none;
}

.btn-logout:hover {
    background: #c0392b;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .nav {
        flex-direction: column;
        text-align: center;
        padding: 1rem;
    }

    .nav-user {
        flex-direction: column;
        margin: 1rem 0;
        padding: 0;
    }

    .nav-link:after {
        display: none;
    }
}
    </style>
</head>

<body>
<!-- Navigation Bar -->
<header>
    <h1>Book Store</h1>
    <p>Discover and shop the best books online!</p>
    <nav>
        <ul class="nav justify-content-center">
            <li class="nav-item"><a class="nav-link text-white" href="#">Home</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="#">Categories</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="cart.php">My Cart (<?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : '0'; ?>)</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="orders.php">My Orders</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="#">Contact</a></li>
            <?php if(isset($_SESSION['userID'])): ?>
                <div class="nav-user">
                    <span class="nav-user-email"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                    <a href="logout.php" class="btn-auth btn-logout">Logout</a>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn-auth">Login</a>
            <?php endif; ?>
        </ul>
    </nav>
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
                    JOIN BOOKAUTHOR ba ON b.ISBN = ba.ISBN
                    JOIN Author a ON ba.AuthorID = a.AuthorID";

            $stmt = $conn->prepare($sql);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<div class='book-card'>";
                    echo "<img src='https://via.placeholder.com/150' alt='Book Cover'>";
                    echo "<h3>" . htmlspecialchars($row['Title']) . "</h3>";
                    echo "<p><strong>Author:</strong> " . htmlspecialchars($row['Fname'] . " " . $row['Lname']) . "</p>";
                    echo "<p><strong>Publisher:</strong> " . htmlspecialchars($row['Publisher']) . "</p>";
                    echo "<p><strong>Category:</strong> " . htmlspecialchars($row['Category']) . "</p>";
                    echo "<p><strong>Price:</strong> $" . number_format($row['Retail'], 2) . "</p>";
                    echo "<a href='?action=add&isbn=" . urlencode($row['ISBN']) . "'><button><i class='fa fa-cart-plus'></i> Add to Cart</button></a>";
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
        <p>&copy; 2025 Online Library. All rights reserved.</p>
    </div>
</footer>
</body>
</html>
