<?php
session_start();
include('includes/db.php');

// Redirect to cart if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Handle the checkout form submission
if (isset($_POST['submit'])) {
    $lastName = htmlspecialchars($_POST['lastName']);
    $firstName = htmlspecialchars($_POST['firstName']);
    $address = htmlspecialchars($_POST['address']);
    $city = htmlspecialchars($_POST['city']);
    $state = htmlspecialchars($_POST['state']);
    $zip = htmlspecialchars($_POST['zip']);
    $email = htmlspecialchars($_POST['email']);

    try {
        $conn->beginTransaction();

        // Generate a random OrderId (ensure it doesn't conflict with existing IDs)
        do {
            $orderId = mt_rand(100000, 999999); // Generates a random 6-digit number
            $stmt = $conn->prepare("SELECT COUNT(*) FROM Orders WHERE OrderId = ?");
            $stmt->execute([$orderId]);
            $idExists = $stmt->fetchColumn();
        } while ($idExists);

        // Use default customer ID 101 since we don't have login system
        $customerId = 1005;

        // Calculate shipping cost (example: $5 flat rate)
        $shipCost = 5.00;

        // Insert order with the random OrderId
        $stmt = $conn->prepare("INSERT INTO Orders (OrderId, CustomerId, OrderDate, ShipStreet, ShipCity, 
                              ShipState, ShipZip, ShipCost) 
                              VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?)");
        $stmt->execute([$orderId, $customerId, $address, $city, $state, $zip, $shipCost]);

        // Insert order items
        $itemId = 1;
        foreach ($_SESSION['cart'] as $isbn => $item) {
            $stmt = $conn->prepare("INSERT INTO ORDERITEMS (OrderId, ItemId, ISBN, Quantity, PaidEach) 
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$orderId, $itemId, $isbn, $item['quantity'], $item['price']]);
            $itemId++;
        }

        $conn->commit();
        // Clear the cart after successful order
        unset($_SESSION['cart']);
        $orderPlaced = true;
        
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "An error occurred while processing your order: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Online Library</title>
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
        footer {
            margin-top: 30px;
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 15px 0;
        }
        .checkout-form label { font-weight: bold; }
        .order-summary { position: sticky; top: 20px; }
    </style>
</head>
<body>
<header>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Checkout</h1>
            <nav>
                <ul class="d-flex">
                    <li><a href="homepage.php">Home</a></li>
                    <li><a href="cart.php">My Cart</a></li>
                </ul>
            </nav>
        </div>
    </div>
</header>

<main class="container my-5">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($orderPlaced) && $orderPlaced): ?>
        <div class="alert alert-success text-center" role="alert">
            <h2>Thank you for your purchase!</h2>
            <p>Your order has been placed successfully.</p>
            <p>We will send a confirmation email to: <?php echo htmlspecialchars($email); ?></p>
            <a href="homepage.php" class="btn btn-primary mt-3">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <h2 class="mb-4">Shipping Details</h2>
                <form method="POST" action="checkout.php" class="checkout-form">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName" class="form-label">First Name:</label>
                            <input type="text" id="firstName" name="firstName" class="form-control" 
                                   maxlength="10" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="lastName" class="form-label">Last Name:</label>
                            <input type="text" id="lastName" name="lastName" class="form-control" 
                                   maxlength="10" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address:</label>
                        <input type="text" id="address" name="address" class="form-control" 
                               maxlength="20" required>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label">City:</label>
                            <input type="text" id="city" name="city" class="form-control" 
                                   maxlength="12" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="state" class="form-label">State:</label>
                            <input type="text" id="state" name="state" class="form-control" 
                                   maxlength="2" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="zip" class="form-label">Zip Code:</label>
                            <input type="text" id="zip" name="zip" class="form-control" 
                                   maxlength="5" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address:</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>

                    <button type="submit" name="submit" class="btn btn-success w-100">Place Order</button>
                </form>
            </div>
            
            <div class="col-md-4">
                <div class="card order-summary">
                    <div class="card-header">
                        <h3 class="card-title h5 mb-0">Order Summary</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $subtotal = 0;
                        foreach ($_SESSION['cart'] as $item) {
                            $itemTotal = $item['price'] * $item['quantity'];
                            $subtotal += $itemTotal;
                            ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?php echo htmlspecialchars($item['title']); ?> (x<?php echo $item['quantity']; ?>)</span>
                                <span>$<?php echo number_format($itemTotal, 2); ?></span>
                            </div>
                        <?php } ?>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span>$5.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <strong>$<?php echo number_format($subtotal + 5.00, 2); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<footer>
    <div class="container">
        <p>&copy; 2025 Online Library. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>