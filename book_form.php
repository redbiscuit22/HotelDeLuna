<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get all form data
$user_id = $_SESSION['user_id'];
$name = mysqli_real_escape_string($connection, $_POST['name']);
$email = mysqli_real_escape_string($connection, $_POST['email']);
$phone = mysqli_real_escape_string($connection, $_POST['phone']);
$address = mysqli_real_escape_string($connection, $_POST['address']);
$package = mysqli_real_escape_string($connection, $_POST['package']);
$guests = intval($_POST['guests']);
$arrivals = $_POST['arrivals'];
$leaving = $_POST['leaving'];
$payment_method = mysqli_real_escape_string($connection, $_POST['payment_method']);
$reference_number = isset($_POST['reference_number']) ? mysqli_real_escape_string($connection, $_POST['reference_number']) : '';

// Get payment details based on method
$payment_details = [];
switch ($payment_method) {
    case 'GCash':
        $payment_details = [
            'number' => mysqli_real_escape_string($connection, $_POST['gcash_number']),
            'name' => mysqli_real_escape_string($connection, $_POST['gcash_name'])
        ];
        break;
    case 'Maya':
        $payment_details = [
            'number' => mysqli_real_escape_string($connection, $_POST['maya_number']),
            'name' => mysqli_real_escape_string($connection, $_POST['maya_name'])
        ];
        break;
    case 'PayPal':
        $payment_details = [
            'email' => mysqli_real_escape_string($connection, $_POST['paypal_email']),
            'name' => mysqli_real_escape_string($connection, $_POST['paypal_name'])
        ];
        break;
}

// Calculate total amount
$price_query = $connection->prepare("SELECT price FROM packages WHERE package_name = ?");
$price_query->bind_param("s", $package);
$price_query->execute();
$price_result = $price_query->get_result();
$price_row = $price_result->fetch_assoc();
$package_price = $price_row['price'];
$total_amount = $package_price * $guests;

// Start transaction
mysqli_begin_transaction($connection);

try {
    // 1. Verify package exists
    $check_query = "SELECT 1 FROM packages WHERE package_name = '$package'";
    $check_result = mysqli_query($connection, $check_query);
    
    if (!$check_result || mysqli_num_rows($check_result) === 0) {
        throw new Exception("Package '$package' not found in database");
    }

    // 2. Insert the booking record with all columns
    $insert_query = "INSERT INTO booking (
        user_id, 
        name,
        email,
        phone, 
        address, 
        package, 
        guests, 
        arrivals, 
        leaving,
        payment_method,
        payment_details,
        reference_number,
        total_amount,
        status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
    
    $stmt = $connection->prepare($insert_query);
    $payment_details_json = json_encode($payment_details);
    
    $stmt->bind_param(
        "isssssisssssd", 
        $user_id,
        $name,
        $email,
        $phone,
        $address,
        $package,
        $guests,
        $arrivals,
        $leaving,
        $payment_method,
        $payment_details_json,
        $reference_number,
        $total_amount
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Booking failed: " . $stmt->error);
    }

    // Commit transaction if successful
    mysqli_commit($connection);
    
    echo "<script>
        alert('Booking successful! We will contact you for payment confirmation.');
        window.location.href = 'index.php';
    </script>";
    exit;

} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($connection);
    
    echo "<script>
        alert('Booking failed: " . addslashes($e->getMessage()) . "');
        window.history.back();
    </script>";
    exit;
} finally {
    // Close connection
    if (isset($stmt)) $stmt->close();
    mysqli_close($connection);
}
?>