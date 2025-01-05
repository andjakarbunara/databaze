<?php
include('includes/header.php');
include('includes/db.php');
session_start();

// Check if the user is already logged in
if (isset($_SESSION['userID'])) {
    header("Location: homepage.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form inputs
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query the database to find the user by email
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $conn->prepare($sql);
    
    // Bind the parameter using bindParam (PDO method)
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Verify the password (assuming hashed password in the database)
        if (password_verify($password, $result['password'])) {
            // Password is correct, set session variables
            $_SESSION['userID'] = $result['userID'];
            $_SESSION['email'] = $result['email'];
            $_SESSION['role'] = $result['role'];

            // Redirect based on role
            if ($result['role'] != null) {
                header("Location: homepage.php");
                exit();
            } else {
                $_SESSION['error'] = "Invalid role.";
                header("Location: login.php");
                exit();
            }
        } else {
            // Incorrect password
            $_SESSION['error'] = "Invalid password.";
            header("Location: login.php");
            exit();
        }
    } else {
        // No user found with this email
        $_SESSION['error'] = "No user found with this email.";
        header("Location: login.php");
        exit();
    }
}

?>

<div class="container">
    <div class="login-form">
        <!-- Image Section -->
        <div class="login-image">
            <img src="images/login.jpg" class="img-fluid" alt="profile">
        </div>

        <!-- Form Content Section -->
        <div class="form-content">
            <h2 class="text-center">Login</h2>
            <form id="loginForm" method="post">
                <div class="mb-3">
                    <label for="Email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="Email" name="email" placeholder="Enter your email" required>
                    <div class="form-text">
                        <span id="EmailHelp" class="error"></span>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="Password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="Password" name="password" placeholder="Enter your password" required>
                    <div class="form-text">
                        <span id="PasswordHelp" class="error"></span>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-login">Login</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
