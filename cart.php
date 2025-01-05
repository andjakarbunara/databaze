<?php
session_start();
include('includes/db.php');

// Redirect to homepage if cart is empty and trying to checkout
if (isset($_GET['action']) && $_GET['action'] == 'checkout' && (!isset($_SESSION['cart']) || empty($_SESSION['cart']))) {
    $_SESSION['error'] = "Your cart is empty. Please add items before checking out.";
    header("Location: homepage.php");
    exit();
}

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
        $quantity = (int)$quantity;
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$isbn]);
        } else {
            // Verify book exists and get current price
            $stmt = $conn->prepare("SELECT ISBN, Retail, Discount FROM Books WHERE ISBN = ?");
            $stmt->execute([$isbn]);
            $book = $stmt->fetch();
            
            if ($book) {
                $price = $book['Retail'] - ($book['Retail'] * ($book['Discount'] / 100));
                $_SESSION['cart'][$isbn]['quantity'] = $quantity;
                $_SESSION['cart'][$isbn]['price'] = $price;
            }
        }
    }
    header("Location: cart.php");
    exit();
}

// Calculate cart total for checkout
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $cartTotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $cartTotal += $item['price'] * $item['quantity'];
    }
    $_SESSION['cart_total'] = $cartTotal;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - Online Library</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        header {
            background-color: #343a40;
            color: white;
            padding: 20px 0;
        }
        header h1 { margin: 0; font-size: 28px; }
        header nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 15px;
        }
        header nav ul li { display: inline; }
        header nav ul li a {
            color: white;
            text-decoration: none;
        }
        header nav ul li a:hover { text-decoration: underline; }
        .cart-list table { width: 100%; border-collapse: collapse; }
        .cart-list th, .cart-list td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .cart-total {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cart-total p {
            font-size: 18px;
            font-weight: bold;
        }
        footer {
            margin-top: 30px;
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 15px 0;
        }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1>My Cart</h1>
                <nav>
                    <ul class="d-flex">
                        <li><a href="homepage.php">Home</a></li>
                        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                            <li><a href="checkout.php">Checkout</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <section class="cart-list my-5">
        <div class="container">
            <h2 class="mb-4">Your Shopping Cart</h2>

            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                <form method="POST" action="cart.php">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Title</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total = 0;
                            foreach ($_SESSION['cart'] as $isbn => $item):
                                $itemTotal = $item['price'] * $item['quantity'];
                                $total += $itemTotal;
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <input type="number" name="quantity[<?php echo $isbn; ?>]" 
                                               value="<?php echo $item['quantity']; ?>" 
                                               min="0" class="form-control" style="width: 80px;">
                                    </td>
                                    <td>$<?php echo number_format($itemTotal, 2); ?></td>
                                    <td>
                                        <a href="cart.php?action=remove&isbn=<?php echo $isbn; ?>" 
                                           class="btn btn-danger btn-sm">Remove</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="cart-total">
                        <p>Total: $<?php echo number_format($total, 2); ?></p>
                        <div>
                            <button type="submit" name="update" class="btn btn-primary">Update Cart</button>
                            <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    Your cart is empty. <a href="homepage.php">Continue shopping</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2025 Online Library. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>