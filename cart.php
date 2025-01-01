<?php
session_start();
include('includes/db.php');

// Handle removing an item from the cart
if (isset($_GET['action']) && $_GET['action'] == 'remove') {
    $isbn = $_GET['isbn'];
    unset($_SESSION['cart'][$isbn]);
    header("Location: cart.php");
    exit();
}

// Handle updating the cart quantities
if (isset($_POST['update'])) {
    foreach ($_POST['quantity'] as $isbn => $quantity) {
        if ($quantity == 0) {
            unset($_SESSION['cart'][$isbn]);
        } else {
            $_SESSION['cart'][$isbn]['quantity'] = $quantity;
        }
    }
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - Online Library</title>
    <link rel="stylesheet" href="css/homepage.css">
</head>

<body>

    <header>
        <div class="container">
            <h1>My Cart</h1>
            <nav>
                <ul>
                    <li><a href="homepage.php">Home</a></li>
                    <li><a href="checkout.php">Checkout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="cart-list">
        <div class="container">
            <h2>Your Shopping Cart</h2>

            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                <form method="POST" action="cart.php">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total = 0;
                            foreach ($_SESSION['cart'] as $isbn => $item) {
                                echo "<tr>";
                                echo "<td>" . $item['title'] . "</td>";
                                echo "<td>$" . number_format($item['price'], 2) . "</td>";
                                echo "<td><input type='number' name='quantity[$isbn]' value='" . $item['quantity'] . "' min='1'></td>";
                                echo "<td>$" . number_format($item['price'] * $item['quantity'], 2) . "</td>";
                                echo "<td><a href='cart.php?action=remove&isbn=$isbn' class='remove'>Remove</a></td>";
                                echo "</tr>";
                                $total += $item['price'] * $item['quantity'];
                            }
                            ?>
                        </tbody>
                    </table>

                    <div class="cart-total">
                        <p>Total: $<?php echo number_format($total, 2); ?></p>
                        <button type="submit" name="update" class="update-cart">Update Cart</button>
                    </div>
                </form>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2024 Online Library. All rights reserved.</p>
        </div>
    </footer>

</body>

</html>

