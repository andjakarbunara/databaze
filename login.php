<?php
include('includes/header.php');
include('includes/db.php');
session_start();

if (isset($_SESSION['error'])) {
    echo "<p style='color:red'>" . $_SESSION['error'] . "</p>";
    unset($_SESSION['error']); // Clear error message after displaying
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
                 <button type="submit" class="btn btn-primary btn-login" onclick="login();">Login</button>
             </div>
         </form>
       </div>
    </div>
   </div>
</div>

<?php include('includes/footer.php'); ?>
