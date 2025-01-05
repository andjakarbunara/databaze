<?php
include('includes/header.php');
include('includes/db.php');
session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form inputs for user and customer information
    $email = $_POST['email'];
    $password = $_POST['password'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash the password

    // Customer Information
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip = $_POST['zip'];
    $region = $_POST['region'];
    $referred = empty($_POST['referred']) ? NULL : $_POST['referred'];

    // Check if the email already exists
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Email already exists
        $_SESSION['error'] = "Email is already taken. Please choose another one.";
        header("Location: register.php");
        exit();
    } else {
        // Insert the new user into the database
        $role = 'client'; // Default role
        $sql = "INSERT INTO users (email, password, role) VALUES (:email, :password, :role)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            // Get the last inserted user ID
            $userID = $conn->lastInsertId();

            // Generate a random customer ID (you can implement your own logic if needed)
            $customerId = rand(10000, 99999);

            // Insert the customer information
            $sql_customer = "INSERT INTO Customers (CustomerId, LastName, FirstName, Address, City, State, Zip, Referred, Region, userID) 
                             VALUES (:customerId, :lastName, :firstName, :address, :city, :state, :zip, :referred, :region, :userID)";
            $stmt_customer = $conn->prepare($sql_customer);
            $stmt_customer->bindParam(':customerId', $customerId);
            $stmt_customer->bindParam(':lastName', $lastName);
            $stmt_customer->bindParam(':firstName', $firstName);
            $stmt_customer->bindParam(':address', $address);
            $stmt_customer->bindParam(':city', $city);
            $stmt_customer->bindParam(':state', $state);
            $stmt_customer->bindParam(':zip', $zip);
            $stmt_customer->bindParam(':referred', $referred);
            $stmt_customer->bindParam(':region', $region);
            $stmt_customer->bindParam(':userID', $userID);

            if ($stmt_customer->execute()) {
                // Registration and customer info insertion successful
                $_SESSION['success'] = "Registration successful. You can now log in.";
                header("Location: login.php"); // Redirect to login page
                exit();
            } else {
                // Error during customer information insertion
                $_SESSION['error'] = "There was an error saving your customer information. Please try again.";
                header("Location: register.php");
                exit();
            }
        } else {
            // Error during user registration
            $_SESSION['error'] = "There was an error. Please try again.";
            header("Location: register.php");
            exit();
        }
    }
}
?>

<style>
.registration-container {
    background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
    min-height: 100vh;
    padding: 3rem 1rem;
}

.registration-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    padding: 2.5rem;
    transition: transform 0.3s ease;
    max-width: 1200px;
    margin: 0 auto;
}

.registration-card:hover {
    transform: translateY(-5px);
}

.form-title {
    color: #2c3e50;
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 2rem;
    position: relative;
    padding-bottom: 1rem;
}

.form-title:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 4px;
    background: #3498db;
    border-radius: 2px;
}

.form-control {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
}

.form-label {
    font-weight: 500;
    color: #34495e;
    margin-bottom: 0.5rem;
}

.btn-primary {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    border: none;
    border-radius: 8px;
    padding: 1rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2980b9 0%, #2573a7 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
}

.alert {
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    border: none;
}

.alert-danger {
    background-color: #fee2e2;
    color: #dc2626;
}

.alert-success {
    background-color: #dcfce7;
    color: #16a34a;
}

.login-link {
    color: #3498db;
    font-weight: 500;
    transition: color 0.3s ease;
}

.login-link:hover {
    color: #2980b9;
    text-decoration: none;
}

select.form-control {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 16px;
    padding-right: 2.5rem;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

.form-section {
    background: #f8fafc;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .registration-card {
        padding: 1.5rem;
    }
    
    .form-title {
        font-size: 1.75rem;
    }
}
</style>

<div class="registration-container">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-xxl-10">
                <div class="registration-card">
                    <h2 class="form-title text-center">Create an Account</h2>
                    <?php
                    if (isset($_SESSION['error'])) {
                        echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
                        unset($_SESSION['error']);
                    }
                    if (isset($_SESSION['success'])) {
                        echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
                        unset($_SESSION['success']);
                    }
                    ?>
                    <form id="registerForm" method="post">
                        <!-- User Information Section -->
                        <div class="form-section">
                            <h3 class="mb-4">Account Information</h3>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="Email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="Email" name="email" placeholder="Enter your email" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="Password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="Password" name="password" placeholder="Enter your password" required>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Information Section -->
                        <div class="form-section">
                            <h3 class="mb-4">Personal Information</h3>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="FirstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="FirstName" name="firstName" placeholder="Enter your first name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="LastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="LastName" name="lastName" placeholder="Enter your last name" required>
                                </div>
                            </div>
                        </div>

                        <!-- Address Information Section -->
                        <div class="form-section">
                            <h3 class="mb-4">Address Information</h3>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="Address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="Address" name="address" placeholder="Enter your address" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="City" class="form-label">City</label>
                                    <input type="text" class="form-control" id="City" name="city" placeholder="Enter your city" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="State" class="form-label">State</label>
                                    <input type="text" class="form-control" id="State" name="state" placeholder="Enter your state" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="Zip" class="form-label">Zip Code</label>
                                    <input type="text" class="form-control" id="Zip" name="zip" placeholder="Enter your zip code" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="Region" class="form-label">Region</label>
                                    <select class="form-control" id="Region" name="region" required>
                                        <option value="N">North</option>
                                        <option value="NW">Northwest</option>
                                        <option value="NE">Northeast</option>
                                        <option value="S">South</option>
                                        <option value="SE">Southeast</option>
                                        <option value="SW">Southwest</option>
                                        <option value="W">West</option>
                                        <option value="E">East</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="Referred" class="form-label">Referred By (Optional)</label>
                                    <input type="text" class="form-control" id="Referred" name="referred" placeholder="Enter referrer code or name">
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Register</button>
                        </div>
                        <p class="text-center mt-3">Already have an account? <a href="login.php" class="login-link">Login here</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
