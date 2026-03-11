<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Employees";

$connection = new mysqli($servername, $username, $password, $dbname);
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $id = isset($_POST['id']) && $_POST['id'] !== '' ? $_POST['id'] : 0;
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $phone = $_POST['number'];
    $dob = $_POST['dob'];
    $status = $_POST['status'];
    $rating = $_POST['rating'];
    $sallary = $_POST['sallary'];

    // Check duplicate Email
    $sql = "SELECT * FROM employee WHERE email='$email' AND is_deleted=0 AND id != '$id'";
    $result = $connection->query($sql);

    if ($result->num_rows > 0) {
        echo "exists";
    } else {
        if ($id == 0) {
            // INSERT
            $sql = "INSERT INTO employee 
            (first_name, last_name, email, phone_number, date_of_birth, status, rating, sallary, is_deleted)
            VALUES ('$fname', '$lname', '$email', '$phone', '$dob', '$status', '$rating', '$sallary', 0)";
        } else {
            // UPDATE
            $sql = "UPDATE employee SET 
                first_name='$fname',
                last_name='$lname',
                email='$email',
                phone_number='$phone',
                date_of_birth='$dob',
                status='$status',
                rating='$rating',
                sallary='$sallary'
                WHERE id=$id";
        }

        if ($connection->query($sql)) {
            echo "success";
        } else {
            echo "error: " . $connection->error;
        }
    }
}
$connection->close();
?>
