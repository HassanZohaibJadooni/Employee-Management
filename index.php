<?php
$servername = "localhost";
$username = "root";
$password = "";

// Connection
$connection = new mysqli($servername, $username, $password);
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Create Database
$sql = "CREATE DATABASE IF NOT EXISTS Employees";
$connection->query($sql);
$connection->close();

// Reconnect with Databases
$connection = new mysqli($servername, $username, $password, "Employees");

// Create Table if not exists
$sql = "CREATE TABLE IF NOT EXISTS employee(
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(15),
    date_of_birth DATE NOT NULL,
    status VARCHAR(50) NOT NULL,
    rating DECIMAL NOT NULL,
    sallary DECIMAL NOT NULL,
    is_deleted TINYINT(1) DEFAULT 0
)";
$connection->query($sql);

$result = $connection->query("SELECT * FROM employee WHERE is_deleted=0");

// Delete 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];
    $connection->query("UPDATE employee SET is_deleted=1 WHERE id=$deleteId");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Employee</title>
    <link rel="stylesheet" href="stylesheet.css">
    <script src="jquerylibrary.js"></script>
</head>

<body>

    <button id="showForm">Add New Employee Detail</button>

    <!-- Form -->
    <form action="" id="myForm" method="post" style="display:none;">
        <input id="emp_id" type="hidden" name="emp_id">
        <div class="form-row">
            <label>First Name:</label><input id="fname" type="text" name="fname" placeholder="User First Name">
        </div>
        <div class="form-row">
            <label>Last Name:</label><input id="lname" type="text" name="lname" placeholder="User Last Name">
            <span class="error"></span>
        </div>
        <div class="form-row">
            <label>Email:</label><input id="email" type="email" name="email" placeholder="User Email">
            <span class="error"></span>
        </div>
        <div class="form-row">
            <label>Phone Number:</label><input id="number" type="number" name="number" placeholder="User Phone Number">
        </div>
        <div class="form-row">
            <label>Date Of Birth:</label><input id="dob" type="date" name="dob" placeholder="User BirthDay">
            <span class="error"></span>
        </div>
        <div class="form-row">
            <label>Status:</label>
            <select name="status" id="status">
                <option value="">Select status</option>
                <option value="Active">Active</option>
                <option value="InActive">InActive</option>
            </select>
            <span class="error"></span>
        </div>
        <div class="form-row">
            <label>Rating:</label>
            <input name="rating" id="rating" type="number" step="0.1" min="1" max="10" placeholder="Enter Number 1 - 10">
            <span class="error"></span>
        </div>
        <div class="form-row">
            <label>Sallary:</label><input name="sallary" step="0.01" id="sallary" type="number" placeholder="User Sallary">
            <span class="error"></span>
        </div>

        <button type="submit" class="submitBtn" name="add" id="addBtn">Add Details</button>
        <button type="submit" class="submitBtn" name="update" id="updateBtn" style="display:none;">Update</button>
    </form>

    <!-- Table -->
    <div id="tableDiv" style="margin-top:20px;">
        <?php
        echo "<table border='1' cellpadding='5' cellspacing='0'>
        <tr>
            <th>ID</th><th>First Name</th><th>Last Name</th>
            <th>Email</th><th>Phone Number</th><th>Date Of Birth</th>
            <th>Status</th><th>Rating</th><th>Sallary</th><th>Actions</th>
        </tr>";
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $phone_show = ($row['phone_number'] === null || $row['phone_number'] === '') ? '' : $row['phone_number'];
                $rating_show = $row['rating'];
                $sallary_show = $row['sallary'];
                echo "<tr>
            <td>" . $row['id'] . "</td>
            <td>" . $row['first_name'] . "</td>
            <td>" . $row['last_name'] . "</td>
            <td>" . $row['email'] . "</td>
            <td>" . $phone_show . "</td>
            <td>" . date("d - m - Y", strtotime($row['date_of_birth'])) . "</td>
            <td>" . $row['status'] . "</td>
            <td>" . $rating_show . "</td>
            <td>" . $sallary_show . "</td>
            <td>
                <form method='post' class='deleteForm' style='display:inline;'>
                    <input type='hidden' name='delete_id' value='" . $row['id'] . "'>
                    <button type='submit' class='delete' name='delete'>Delete</button>
                </form>
                <button class='editBtn' 
                    data-id='" . $row['id'] . "' 
                    data-fname='" . $row['first_name'] . "' 
                    data-lname='" . $row['last_name'] . "' 
                    data-email='" . $row['email'] . "' 
                    data-phone='" . $row['phone_number'] . "' 
                    data-dob='" . $row['date_of_birth'] . "' 
                    data-status='" . $row['status'] . "' 
                    data-rating='" . $row['rating'] . "' 
                    data-sallary='" . $row['sallary'] . "'>Edit</button>
                <button class='detailBtn' 
                    data-fname='" . $row['first_name'] . "' 
                    data-lname='" . $row['last_name'] . "' 
                    data-email='" . $row['email'] . "' 
                    data-phone='" . $row['phone_number'] . "' 
                    data-dob='" . date("d - m - Y", strtotime($row['date_of_birth'])) . "' 
                    data-status='" . $row['status'] . "' 
                    data-rating='" . $row['rating'] . "' 
                    data-sallary='" . $row['sallary'] . "'>Details</button>
            </td>
        </tr>";
            }
            echo "</table>";
        }
        ?>
    </div>

    <!-- Details popup -->
    <div id="popup" style="display:none;">
        <button id="closePopup">Close</button>
        <p id="popupContent"></p>
    </div>

    <script>
        $(document).ready(function() {
            $("#showForm").click(function() {
                $(this).hide();
                $("#myForm").show();
                $("#tableDiv").hide();
                $("#addBtn").show();
                $("#updateBtn").hide();
                $("#popup, #popupContent").hide();
            });

            $(".editBtn").click(function() {
                $("#showForm").hide();
                $("#emp_id").val($(this).data("id"));
                $("#fname").val($(this).data("fname"));
                $("#lname").val($(this).data("lname"));
                $("#email").val($(this).data("email"));
                $("#number").val($(this).data("phone"));
                $("#dob").val($(this).data("dob"));
                $("#status").val($(this).data("status"));
                $("#rating").val($(this).data("rating"));
                $("#sallary").val($(this).data("sallary"));
                $("#myForm").show();
                $("#tableDiv").hide();
                $("#addBtn").hide();
                $("#updateBtn").show();
                $("#popup, #popupContent").hide();
            });

            // Delete confirm
            $(".deleteForm").submit(function(e) {
                if (!confirm("Are you sure you want to delete this record?")) {
                    e.preventDefault();
                }
            });

            // Details button 
            $(".detailBtn").click(function() {
                $("#tableDiv").hide();
                $("#showForm").hide();
                $("#myForm").hide();
                $("#addBtn").hide();
                $("#updateBtn").hide();
                let content = '<strong id="detail">' +
                    "<span style='margin-right : 20px;'>Name   </span><span style='margin-right : 20px';>:</span>" + "<span style='margin-right : 20px';>" + $(this).data("fname") + "" + $(this).data("lname") + "</span><br>" +
                    "<span style='margin-right : 20px;'>Email  </span><span style='margin-right : 20px';>:</span>" + "<span style='margin-right : 20px';>" + $(this).data("email") + "</span><br>" +
                    "<span style='margin-right : 15px;'>Phone  </span><span style='margin-right : 20px';>:</span>" + "<span style='margin-right : 20px';>" + $(this).data("phone") + "</span><br>" +
                    "<span style='margin-right : 28px;'>DOB    </span><span style='margin-right : 20px';>:</span>" + "<span style='margin-right : 20px';>" + $(this).data("dob") + "</span><br>" +
                    "<span style='margin-right : 15px;'>Status </span><span style='margin-right : 20px';>:</span>" + "<span style='margin-right : 20px';>" + $(this).data("status") + "</span><br>" +
                    "<span style='margin-right : 15px;'>Rating </span><span style='margin-right : 20px';>:</span>" + "<span style='margin-right : 20px';>" + $(this).data("rating") + "</span><br>" +
                    "<span style='margin-right : 16px;'>Sallary</span><span style='margin-right : 20px';>:</span>" + "<span style='margin-right : 20px';>" + $(this).data("sallary") + "</span></strong>";
                $("#popupContent").html(content);
                $("#popup").show();
            });

            $("#closePopup").click(function() {
                $("#popup").hide();
                $("#showForm").hide();
                $("#tableDiv").show();
                $("#showForm").show();
                $("#myForm").hide();
                $("#addBtn").show();
                $("#updateBtn").show();
            });

            // Validation
            $("#myForm").submit(function(e) {
                let valid = true;
                $(".error").text("");


                // Last Name
                if ($("#lname").val().trim() === "") {
                    $("#lname").next(".error").text("This field is required");
                    valid = false;
                }

                // date validtion
                let dob = new Date($("#dob").val());
                let today = new Date();
                if ($("#dob").val() === "") {
                    $("#dob").next(".error").text("This field is required");
                    valid = false;
                } else if (today < dob) {
                    $("#dob").next(".error").text("Date of birth  must be greater then today");
                    valid = false;
                }

                // Status
                if ($("#status").val() === "") {
                    $("#status").next(".error").text("This field is required");
                    valid = false;
                }

                // Rating
                if ($("#rating").val().trim() === "") {
                    $("#rating").next(".error").text("This field is required");
                    valid = false;
                }

                // Sallary
                if ($("#sallary").val().trim() === "") {
                    $("#sallary").next(".error").text("This field is required");
                    valid = false;
                }

                // Email
                let email = $("#email").val().trim();
                if (email === "") {
                    $("#email").next(".error").text("This field is required");
                    valid = false;
                } else {
                    let empId = $("#emp_id").val() || 0;
                    let fname = $("#fname").val();
                    let lname = $("#lname").val();
                    let number = $("#number").val();
                    let dob = $("#dob").val();
                    let status = $("#status").val();
                    let rating = $("#rating").val();
                    let sallary = $("#sallary").val();

                    e.preventDefault(); // Stop default first

                    $.ajax({
                        url: "check_email.php",
                        type: "POST",
                        data: {
                            email: email,
                            id: empId,
                            fname: fname,
                            lname: lname,
                            number: number,
                            dob: dob,
                            status: status,
                            rating: rating,
                            sallary: sallary,
                        },
                        success: function(response) {
                            response = response.trim();
                            if (response === "exists") {
                                alert("Email already exists!");
                            } else if (response === "success") {
                                alert("Record saved successfully!");
                                window.location.reload();
                            }
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>