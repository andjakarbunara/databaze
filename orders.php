<?php
session_start();
include('includes/db.php');

// Check if the user is logged in
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['userID'];

// Fetch orders for the logged-in user
try {
    $stmt = $conn->prepare(
        "SELECT o.OrderId, c.FirstName, c.LastName, o.OrderDate, o.ShipDate, o.ShipCity, o.ShipState, o.ShipZip, o.ShipCost
        FROM Orders o
        JOIN Customers c ON o.CustomerId = c.CustomerId
        WHERE c.userID = :userId
        ORDER BY o.OrderId"
    );
    $stmt->execute([':userId' => $userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

// Handle saving edited data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $orderId = $_POST['orderId'];
    $shipCity = $_POST['shipCity'];
    $shipState = $_POST['shipState'];
    $shipZip = $_POST['shipZip'];
    $shipCost = $_POST['shipCost'];

    try {
        // Verify that the order belongs to the logged-in user before updating
        $verifyStmt = $conn->prepare(
            "SELECT o.OrderId
            FROM Orders o
            JOIN Customers c ON o.CustomerId = c.CustomerId
            WHERE o.OrderId = :orderId AND c.userID = :userId"
        );
        $verifyStmt->execute([':orderId' => $orderId, ':userId' => $userId]);
        if ($verifyStmt->rowCount() === 0) {
            echo "Unauthorized action.";
            exit();
        }

        $updateStmt = $conn->prepare(
            "UPDATE Orders 
            SET ShipCity = :shipCity, ShipState = :shipState, ShipZip = :shipZip, ShipCost = :shipCost
            WHERE OrderId = :orderId"
        );
        $updateStmt->execute([
            ':shipCity' => $shipCity,
            ':shipState' => $shipState,
            ':shipZip' => $shipZip,
            ':shipCost' => $shipCost,
            ':orderId' => $orderId
        ]);
        header('Location: orders.php');
        exit();
    } catch (PDOException $e) {
        echo "Error updating order: " . $e->getMessage();
    }
}

// Handle deleting an order
if (isset($_GET['delete'])) {
    $orderId = $_GET['delete'];

    try {
        // Verify that the order belongs to the logged-in user before deleting
        $verifyStmt = $conn->prepare(
            "SELECT o.OrderId
            FROM Orders o
            JOIN Customers c ON o.CustomerId = c.CustomerId
            WHERE o.OrderId = :orderId AND c.userID = :userId"
        );
        $verifyStmt->execute([':orderId' => $orderId, ':userId' => $userId]);
        if ($verifyStmt->rowCount() === 0) {
            echo "Unauthorized action.";
            exit();
        }

        $deleteStmt = $conn->prepare("DELETE FROM Orders WHERE OrderId = :orderId");
        $deleteStmt->execute([':orderId' => $orderId]);
        header('Location: orders.php');
        exit();
    } catch (PDOException $e) {
        echo "Error deleting order: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
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
        .nav {
        margin: 0;
        padding: 0;
    }
    .nav-link {
        color: black !important; 
        font-size: 16px;
    }
    .nav-link:hover {
        color:rgb(28, 20, 20); 
        text-decoration: underline;
    }
        .table {
            background: #ffffff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .edit-mode {
            background-color: #fff3cd;
        }
        .btn {
            margin: 2px;
        }
        .nav-user {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-left: auto;
    padding: 0 1rem;
}
        .save-btn {
            background-color: #28a745;
            color: white;
        }
        .save-btn:hover {
            background-color: #218838;
        }
        footer {
            margin-top: 20px;
            text-align: center;
            color: #6c757d;
        }
    </style>
</head>
<body>

<header>
    <h1>My Orders</h1>
    <nav>
        <ul class="nav justify-content-center">
            <li class="nav-item"><a class="nav-link text-white" href="homepage.php">Home</a></li>
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
<div class="container">
    <h1>Order Management</h1>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Order Date</th>
                <th>Ship Date</th>
                <th>Ship City</th>
                <th>Ship State</th>
                <th>Ship Zip</th>
                <th>Ship Cost</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <form method="POST">
                        <tr class="<?= isset($_GET['edit']) && $_GET['edit'] == $order['OrderId'] ? 'edit-mode' : '' ?>">
                            <td><?= htmlspecialchars($order['OrderId']) ?></td>
                            <td><?= htmlspecialchars($order['FirstName'] . ' ' . $order['LastName']) ?></td>
                            <td><?= htmlspecialchars($order['OrderDate']) ?></td>
                            <td><?= htmlspecialchars($order['ShipDate']) ?></td>
                            <td>
                                <?php if (isset($_GET['edit']) && $_GET['edit'] == $order['OrderId']): ?>
                                    <input type="text" name="shipCity" value="<?= htmlspecialchars($order['ShipCity']) ?>" class="form-control" required>
                                <?php else: ?>
                                    <?= htmlspecialchars($order['ShipCity']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($_GET['edit']) && $_GET['edit'] == $order['OrderId']): ?>
                                    <input type="text" name="shipState" value="<?= htmlspecialchars($order['ShipState']) ?>" class="form-control" required>
                                <?php else: ?>
                                    <?= htmlspecialchars($order['ShipState']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($_GET['edit']) && $_GET['edit'] == $order['OrderId']): ?>
                                    <input type="text" name="shipZip" value="<?= htmlspecialchars($order['ShipZip']) ?>" class="form-control" required>
                                <?php else: ?>
                                    <?= htmlspecialchars($order['ShipZip']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($_GET['edit']) && $_GET['edit'] == $order['OrderId']): ?>
                                    <input type="number" step="0.01" name="shipCost" value="<?= htmlspecialchars($order['ShipCost']) ?>" class="form-control" required>
                                <?php else: ?>
                                    $<?= number_format($order['ShipCost'], 2) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($_GET['edit']) && $_GET['edit'] == $order['OrderId']): ?>
                                    <input type="hidden" name="orderId" value="<?= $order['OrderId'] ?>">
                                    <button type="submit" name="save" class="btn save-btn">Save</button>
                                <?php else: ?>
                                    <a href="orders.php?edit=<?= $order['OrderId'] ?>" class="btn btn-warning">Edit</a>
                                    <a href="orders.php?delete=<?= $order['OrderId'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this order?')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </form>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">No orders found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<footer>
    &copy; <?= date('Y') ?> Order Management System. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
