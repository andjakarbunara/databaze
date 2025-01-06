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
    if ($_SESSION['role'] === 'client') {
        $stmt = $conn->prepare(
            "SELECT o.OrderId, c.FirstName, c.LastName, o.OrderDate, o.ShipDate, o.ShipCity, o.ShipState, o.ShipZip, o.ShipCost
        FROM Orders o
        JOIN Customers c ON o.CustomerId = c.CustomerId
        WHERE c.userID = :userId
        ORDER BY o.OrderId"
        );
        $stmt->execute([':userId' => $userId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $conn->prepare(
            "SELECT o.OrderId, c.FirstName, c.LastName, o.OrderDate, o.ShipDate, o.ShipCity, o.ShipState, o.ShipZip, o.ShipCost
        from Orders o
        join Customers c on o.CustomerId = c.CustomerId
        ORDER BY o.OrderId"
        );
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
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

        if ($_SESSION['role'] === 'client') {
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
        $verifyStmt->execute(params: [':orderId' => $orderId, ':userId' => $userId]);
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
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --danger-color: #e74c3c;
            --success-color: #2ecc71;
            --background-color: #f5f7fa;
            --text-color: #2c3e50;
            --border-color: #e2e8f0;
        }

        body {
            background-color: #f8f9fa;
            font-family: system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            margin-top: 20px;
        }

        /* Header and Navigation Styles */
        header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            margin: 0;
            padding: 0 2rem;
            font-size: 1.5rem;
        }

        .nav {
            display: flex;
            align-items: center;
            justify-content: space-evenly;
            padding: 0.5rem 2rem;
            gap: 1rem;
            list-style: none;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
        }

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
            background: var(--danger-color);
            border: none;
        }

        .btn-logout:hover {
            background: #c0392b;
        }

        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 2rem;
        }

        .container h1 {
            margin-top: 0;
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* Table Styles */
        .table {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(44, 62, 80, 0.1);
            margin: 2rem 0;
            border: 1px solid var(--border-color);
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: #f8fafc;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--primary-color);
            border-bottom: 2px solid var(--border-color);
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .table tbody tr:hover {
            background: #f8fafc;        }

        /* Edit mode styling */
        .edit-mode {
            background-color: rgba(52, 152, 219, 0.1) !important;
        }

        .edit-mode td {
            padding: 0.5rem !important;
        }

        .edit-input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 1rem;
        }

        .edit-input:focus {
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
            border-color: var(--secondary-color);
            outline: none;
        }

        /* Button Styles */
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.2s ease;
            font-weight: 500;
            cursor: pointer;
            border: none;
        }

        .btn-warning {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
        }

        .btn-warning:hover {
            background-color: #2980b9;
            border-color: #2980b9;
            color: white;
        }

        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
            border-color: #c0392b;
        }

        .save-btn {
            background-color: var(--success-color);
            color: white;
        }

        .save-btn:hover {
            background-color: #27ae60;
        }

        .admin-btn {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .admin-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
        }

        .edit-btn {
            background: var(--primary-color);
            color: white;
            margin-right: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {

            .table thead th,
            .table tbody td {
                padding: 0.75rem 0.5rem;
                font-size: 0.9rem;
            }

            .btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.9rem;
            }

            .table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .admin-controls {
                flex-direction: column;
            }
        }

        /* Footer Styles */
        footer {
            margin-top: 20px;
            text-align: center;
            color: #6c757d;
            padding: 1rem;
        }
    </style>
</head>

<body>

    <header>
        <h1>My Orders</h1>
        <nav>
            <ul class="nav">
                <?php if (isset($_SESSION['userID'])): ?>
                    <!-- Display the links based on user role -->
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <!-- Admin's navbar -->
                        <li class="nav-item"><a class="nav-link" href="books_dashboard.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="orders.php">Orders</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
                        <div class="nav-user">
                            <span class="nav-user-email"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                            <a href="logout.php" class="btn-auth btn-logout">Logout</a>
                        </div>
                    <?php elseif ($_SESSION['role'] === 'client'): ?>
                        <!-- Client's navbar -->
                        <li class="nav-item"><a class="nav-link" href="homepage.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Categories</a></li>
                        <li class="nav-item"><a class="nav-link" href="cart.php">My Cart
                                (<?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : '0'; ?>)</a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="orders.php">My Orders</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
                        <div class="nav-user">
                            <span class="nav-user-email"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                            <a href="logout.php" class="btn-auth btn-logout">Logout</a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Navbar for guests (not logged in) -->
                    <li class="nav-item"><a class="nav-link" href="homepage.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Categories</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
                    <a href="login.php" class="btn-auth">Login</a>
                <?php endif; ?>
            </ul>
        </nav>

    </header>
    <div class="container">
        <h1>Order Management</h1>
        <table class="table table-bordered table-hover">
            <thead>
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
                                        <input type="text" name="shipCity" value="<?= htmlspecialchars($order['ShipCity']) ?>"
                                            class="form-control" required>
                                    <?php else: ?>
                                        <?= htmlspecialchars($order['ShipCity']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($_GET['edit']) && $_GET['edit'] == $order['OrderId']): ?>
                                        <input type="text" name="shipState" value="<?= htmlspecialchars($order['ShipState']) ?>"
                                            class="form-control" required>
                                    <?php else: ?>
                                        <?= htmlspecialchars($order['ShipState']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($_GET['edit']) && $_GET['edit'] == $order['OrderId']): ?>
                                        <input type="text" name="shipZip" value="<?= htmlspecialchars($order['ShipZip']) ?>"
                                            class="form-control" required>
                                    <?php else: ?>
                                        <?= htmlspecialchars($order['ShipZip']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($_GET['edit']) && $_GET['edit'] == $order['OrderId']): ?>
                                        <input type="number" step="0.01" name="shipCost"
                                            value="<?= htmlspecialchars($order['ShipCost']) ?>" class="form-control" required>
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
                                        <a href="orders.php?delete=<?= $order['OrderId'] ?>" class="btn btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this order?')">Delete</a>
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