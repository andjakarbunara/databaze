<?php
session_start();
include('includes/db.php');

// Check if the cart is empty
//if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
//    header("Location: homepage.php");
//    exit();
//}

// Handle the checkout form submission
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip = $_POST['zip'];
    $email = $_POST['email'];

    // For simplicity, we won't implement payment, but we can simulate placing an order

    // Insert order into the database (you can extend this further to store orders in a database)
    $stmt = $conn->prepare("INSERT INTO Orders (Name, Address, City, State, Zip, Email) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $address, $city, $state, $zip, $email]);

    // Insert order details into the order_items table
    $orderId = $conn->lastInsertId();
    foreach ($_SESSION['cart'] as $isbn => $item) {
        $stmt = $conn->prepare("INSERT INTO OrderItems (OrderId, ISBN, Quantity, Price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$orderId, $isbn, $item['quantity'], $item['price']]);
    }

    // Clear the cart after the order is placed
    unset($_SESSION['cart']);

    echo "<p>Thank you for your purchase! Your order has been placed successfully.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Online Library</title>
    <link rel="stylesheet" href="css/homepage.css">
</head>
<body>

<header>
    <div class="container">
        <h1>Checkout</h1>
        <nav>
            <ul>
                <li><a href="homepage.php">Home</a></li>
                <li><a href="cart.php">My Cart</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="checkout">
    <div class="container">
        <h2>Shipping Details</h2>
        <form method="POST" action="checkout.php">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required>

            <label for="city">City:</label>
            <input type="text" id="city" name="city" required>

            <label for="state">State:</label>
            <input type="text" id="state" name="state" required>

            <label for="zip">Zip Code:</label>
            <input type="text" id="zip" name="zip" required>

            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" required>

            <button type="submit" name="submit">Place Order</button>
        </form>
    </div>
</section>

<footer>
    <div class="container">
        <p>&copy; 2024 Online Library. All rights reserved.</p>
    </div>
</footer>

</body>
</html>

