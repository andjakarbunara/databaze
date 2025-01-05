<?php
$servername = "localhost";
$username = "root";
$password = "njegjeqetambajmend123@";
$dbname = "det_kursi_databasa";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>