<?php
session_start();
include('connection.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Set default active tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'users';

// Pagination settings
$records_per_page = 5;
$booking_records_per_page = 5;

// Search functionality
$search_query = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = mysqli_real_escape_string($connection, $_GET['search']);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        // Add user logic
        $name = mysqli_real_escape_string($connection, $_POST['name']);
        $email = mysqli_real_escape_string($connection, $_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $query = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
        mysqli_query($connection, $query);
        $success = "User added successfully!";
    }

    if (isset($_POST['edit_user'])) {
        // Edit user logic
        $id = $_POST['user_id'];
        $name = mysqli_real_escape_string($connection, $_POST['name']);
        $email = mysqli_real_escape_string($connection, $_POST['email']);

        $query = "UPDATE users SET name='$name', email='$email' WHERE id=$id";
        mysqli_query($connection, $query);
        $success = "User updated successfully!";
    }

    if (isset($_POST['delete_user'])) {
        // Delete user logic
        $id = $_POST['user_id'];
        $query = "DELETE FROM users WHERE id=$id";
        mysqli_query($connection, $query);
        $success = "User deleted successfully!";
    }

    if (isset($_POST['edit_package'])) {
        // Edit package logic
        $id = $_POST['package_id'];
        $package_name = mysqli_real_escape_string($connection, $_POST['package_name']);
        $description = mysqli_real_escape_string($connection, $_POST['description']);
        $price = $_POST['price'];
        $availability = isset($_POST['availability']) ? 1 : 0;

        $query = "UPDATE packages SET package_name='$package_name', description='$description', price=$price, availability=$availability WHERE id=$id";
        mysqli_query($connection, $query);
        $success = "Package updated successfully!";
    }

    if (isset($_POST['delete_package'])) {
        // Delete package logic
        $id = $_POST['package_id'];
        $query = "DELETE FROM packages WHERE id=$id";
        mysqli_query($connection, $query);
        $success = "Package deleted successfully!";
    }

    if (isset($_POST['add_booking'])) {
        // Add booking logic
        $user_id = $_POST['user_id'];
        $phone = mysqli_real_escape_string($connection, $_POST['phone']);
        $address = mysqli_real_escape_string($connection, $_POST['address']);
        $package = mysqli_real_escape_string($connection, $_POST['package']);
        $guests = $_POST['guests'];
        $arrivals = $_POST['arrivals'];
        $leaving = $_POST['leaving'];
        $payment_method = mysqli_real_escape_string($connection, $_POST['payment_method']);
        $reference_number = isset($_POST['reference_number']) ? mysqli_real_escape_string($connection, $_POST['reference_number']) : '';
        $status = 'Pending';

        // Get package price
        $price_query = mysqli_query($connection, "SELECT price FROM packages WHERE package_name = '$package'");
        $price_row = mysqli_fetch_assoc($price_query);
        $total_amount = $price_row['price'] * $guests;

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

        $payment_details_json = json_encode($payment_details);

        $query = "INSERT INTO booking (user_id, phone, address, package, guests, arrivals, leaving, payment_method, payment_details, reference_number, total_amount, status) 
                  VALUES ($user_id, '$phone', '$address', '$package', $guests, '$arrivals', '$leaving', '$payment_method', '$payment_details_json', '$reference_number', $total_amount, '$status')";
        mysqli_query($connection, $query);
        $success = "Booking added successfully!";
    }

    if (isset($_POST['edit_booking'])) {
        // Edit booking logic
        $id = $_POST['booking_id'];
        $user_id = $_POST['user_id'];
        $phone = mysqli_real_escape_string($connection, $_POST['phone']);
        $address = mysqli_real_escape_string($connection, $_POST['address']);
        $package = mysqli_real_escape_string($connection, $_POST['package']);
        $guests = $_POST['guests'];
        $arrivals = $_POST['arrivals'];
        $leaving = $_POST['leaving'];
        $payment_method = mysqli_real_escape_string($connection, $_POST['payment_method']);
        $reference_number = isset($_POST['reference_number']) ? mysqli_real_escape_string($connection, $_POST['reference_number']) : '';
        $status = $_POST['status'];

        // Get package price
        $price_query = mysqli_query($connection, "SELECT price FROM packages WHERE package_name = '$package'");
        $price_row = mysqli_fetch_assoc($price_query);
        $total_amount = $price_row['price'] * $guests;

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

        $payment_details_json = json_encode($payment_details);

        $query = "UPDATE booking SET 
                  user_id=$user_id, 
                  phone='$phone', 
                  address='$address', 
                  package='$package', 
                  guests=$guests, 
                  arrivals='$arrivals', 
                  leaving='$leaving',
                  payment_method='$payment_method',
                  payment_details='$payment_details_json',
                  reference_number='$reference_number',
                  total_amount=$total_amount,
                  status='$status'
                  WHERE id=$id";
        mysqli_query($connection, $query);
        $success = "Booking updated successfully!";
    }

    if (isset($_POST['delete_booking'])) {
        // Delete booking logic
        $id = $_POST['booking_id'];
        $query = "DELETE FROM booking WHERE id=$id";
        mysqli_query($connection, $query);
        $success = "Booking deleted successfully!";
    }

    if (isset($_POST['update_booking_status'])) {
        // Update booking status logic
        $id = $_POST['booking_id'];
        $status = $_POST['status'];

        $query = "UPDATE booking SET status='$status' WHERE id=$id";
        mysqli_query($connection, $query);
        $success = "Booking status updated successfully!";
    }
}

// Fetch data with pagination and search for each tab
// ==================================================
// USERS TAB
$user_page = isset($_GET['user_page']) ? (int)$_GET['user_page'] : 1;
$user_offset = ($user_page - 1) * $records_per_page;

$user_query = "SELECT * FROM users";
if ($search_query && $active_tab == 'users') {
    $user_query .= " WHERE name LIKE '%$search_query%' OR email LIKE '%$search_query%'";
}
$user_query .= " LIMIT $user_offset, $records_per_page";

$users = mysqli_query($connection, $user_query);
$total_users = mysqli_query($connection, "SELECT COUNT(*) as total FROM users");
$total_users = mysqli_fetch_assoc($total_users)['total'];
$total_user_pages = ceil($total_users / $records_per_page);

// PACKAGES TAB
$package_page = isset($_GET['package_page']) ? (int)$_GET['package_page'] : 1;
$package_offset = ($package_page - 1) * $records_per_page;

$package_query = "SELECT * FROM packages";
if ($search_query && $active_tab == 'packages') {
    $package_query .= " WHERE package_name LIKE '%$search_query%' OR description LIKE '%$search_query%'";
}
$package_query .= " LIMIT $package_offset, $records_per_page";

$packages = mysqli_query($connection, $package_query);
$total_packages = mysqli_query($connection, "SELECT COUNT(*) as total FROM packages");
$total_packages = mysqli_fetch_assoc($total_packages)['total'];
$total_package_pages = ceil($total_packages / $records_per_page);

// BOOKINGS TAB
$booking_page = isset($_GET['booking_page']) ? (int)$_GET['booking_page'] : 1;
$booking_offset = ($booking_page - 1) * $booking_records_per_page;

$booking_query = "SELECT booking.*, users.name as user_name FROM booking LEFT JOIN users ON booking.user_id = users.id";
if ($search_query && $active_tab == 'bookings') {
    $booking_query .= " WHERE users.name LIKE '%$search_query%' OR booking.package LIKE '%$search_query%' OR booking.phone LIKE '%$search_query%' OR booking.payment_method LIKE '%$search_query%' OR booking.status LIKE '%$search_query%'";
}
$booking_query .= " ORDER BY booking.created_at DESC LIMIT $booking_offset, $booking_records_per_page";

$bookings = mysqli_query($connection, $booking_query);
$total_bookings = mysqli_query($connection, "SELECT COUNT(*) as total FROM booking");
$total_bookings = mysqli_fetch_assoc($total_bookings)['total'];
$total_booking_pages = ceil($total_bookings / $booking_records_per_page);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel De Luna Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
            --sidebar-bg: #2c3e50;
            --sidebar-active: #3498db;
            --sidebar-hover: #34495e;
        }

        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fc;
            color: #333;
        }

        /* Sidebar Styles */
        .sidebar {
            min-height: 100vh;
            background-color: var(--sidebar-bg);
            background-image: linear-gradient(180deg, var(--sidebar-bg) 10%, #224abe 100%);
            background-size: cover;
            transition: all 0.3s;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 1rem;
            font-weight: 600;
            border-left: 0.25rem solid transparent;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background-color: var(--sidebar-hover);
        }

        .sidebar .nav-link.active {
            color: #fff;
            background-color: var(--sidebar-hover);
            border-left-color: var(--sidebar-active);
        }

        .sidebar .nav-link i {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }

        .sidebar .nav-item .logout-link {
            color: rgba(255, 255, 255, 0.5);
        }

        .sidebar .nav-item .logout-link:hover {
            color: #fff;
            background-color: rgba(255, 99, 71, 0.2);
        }

        /* Main Content Styles */
        .main-content {
            padding: 2rem;
        }

        .topbar {
            height: 4.375rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            background-color: #fff;
        }

        .topbar .navbar-search {
            width: 25rem;
        }

        .topbar .navbar-search input {
            font-size: 0.85rem;
            height: auto;
        }

        .topbar .topbar-divider {
            width: 0;
            border-right: 1px solid #e3e6f0;
            height: calc(4.375rem - 2rem);
            margin: auto 1rem;
        }

        .topbar .nav-item .nav-link {
            height: 4.375rem;
            display: flex;
            align-items: center;
            padding: 0 0.75rem;
            color: #d1d3e2;
        }

        .topbar .nav-item .nav-link:hover {
            color: #b7b9cc;
        }

        .topbar .nav-item .nav-link .badge-counter {
            position: absolute;
            transform: scale(0.7);
            transform-origin: top right;
            right: 0.25rem;
            margin-top: -0.25rem;
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.35rem;
            font-weight: 700;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
        }

        .table {
            margin-bottom: 0;
            color: #5a5c69;
        }

        .table th {
            border-top: none;
            border-bottom: 1px solid #e3e6f0;
            font-weight: 700;
            padding: 1rem;
            text-transform: uppercase;
            font-size: 0.65rem;
            letter-spacing: 0.1rem;
            color: var(--secondary-color);
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-top: 1px solid #e3e6f0;
        }

        .table tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        /* Button Styles */
        .btn {
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 0.35rem;
            transition: all 0.3s;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }

        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }

        .btn-info {
            background-color: var(--info-color);
            border-color: var(--info-color);
        }

        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
        }

        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        /* Badge Styles */
        .badge {
            font-weight: 600;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            border-radius: 0.25rem;
        }

        .badge-primary {
            background-color: var(--primary-color);
        }

        .badge-success {
            background-color: var(--success-color);
        }

        .badge-info {
            background-color: var(--info-color);
        }

        .badge-warning {
            background-color: var(--warning-color);
        }

        .badge-danger {
            background-color: var(--danger-color);
        }

        /* Status Badges */
        .status-badge {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 0.25rem;
            display: inline-block;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }

        .status-completed {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Pagination Styles */
        .pagination .page-item .page-link {
            color: var(--primary-color);
            border: 1px solid #ddd;
            margin-left: -1px;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .pagination .page-item.disabled .page-link {
            color: #6c757d;
        }
        /* Enhanced Booking Table Styles */
        .booking-table th {
            white-space: nowrap;
            vertical-align: middle;
        }
        
        .booking-table td {
            vertical-align: middle;
        }
        
        .booking-user-cell {
            min-width: 180px;
        }
        
        .booking-package-cell {
            min-width: 150px;
        }
        
        .booking-dates-cell {
            min-width: 180px;
        }
        
        .booking-payment-cell {
            min-width: 120px;
        }
        
        .booking-amount-cell {
            min-width: 100px;
            text-align: right;
        }
        
        .booking-status-cell {
            min-width: 120px;
        }
        
        .booking-actions-cell {
            min-width: 140px;
        }
        
        .payment-method-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            border-radius: 4px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        
        .user-info {
            line-height: 1.3;
        }
        
        .user-info .user-name {
            font-weight: 600;
            display: block;
        }
        
        .user-info .user-phone {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .dates-info {
            line-height: 1.3;
        }
        
        .dates-info .date-range {
            font-weight: 500;
            display: block;
        }
        
        .dates-info .guests {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        /* Enhanced Payment Details Modal */
        .payment-details-table th {
            width: 30%;
            white-space: nowrap;
        }
        
        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .booking-table-container {
                overflow-x: auto;
            }
        }

        /* Form Styles */
        .form-control, .form-select {
            border-radius: 0.35rem;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d3e2;
        }

        .form-control:focus, .form-select:focus {
            border-color: #bac8f3;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        /* Alert Styles */
        .alert {
            border-radius: 0.35rem;
            padding: 1rem 1.5rem;
        }

        /* Payment Method Icons */
        .payment-method-icon {
            width: 24px;
            height: 24px;
            margin-right: 5px;
            vertical-align: middle;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .sidebar .nav-link {
                padding: 0.5rem 1rem;
            }
            .main-content {
                padding: 1rem;
            }
            .topbar .navbar-search {
                width: 100%;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-dark" id="sidebarMenu">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">Hotel De Luna Admin</h4>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_tab == 'users' ? 'active' : ''; ?>" href="?tab=users">
                                <i class="bi bi-people"></i>
                                Users Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_tab == 'packages' ? 'active' : ''; ?>" href="?tab=packages">
                                <i class="bi bi-box-seam"></i>
                                Packages Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_tab == 'bookings' ? 'active' : ''; ?>" href="?tab=bookings">
                                <i class="bi bi-calendar-check"></i>
                                Bookings Management
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link logout-link" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Top Navigation -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="bi bi-list"></i>
                    </button>

                    <!-- Topbar Search -->
                    <form method="GET" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <input type="hidden" name="tab" value="<?php echo $active_tab; ?>">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" name="search" placeholder="Search for..." value="<?php echo htmlspecialchars($search_query); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                    </ul>
                </nav>

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                        <?php if ($active_tab == 'bookings'): ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookingModal">
                                <i class="bi bi-plus-circle"></i> Add Booking
                            </button>
                        <?php elseif ($active_tab == 'users'): ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="bi bi-plus-circle"></i> Add User
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Users Tab -->
                        <div class="col-12 <?php echo $active_tab == 'users' ? '' : 'd-none'; ?>">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Users Management</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                            <div class="dropdown-header">Actions:</div>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                                <i class="bi bi-plus"></i> Add User
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#">
                                                <i class="bi bi-download"></i> Export Data
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Created At</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($user = mysqli_fetch_assoc($users)): ?>
                                                    <tr class="fade-in">
                                                        <td><?php echo $user['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $user['id']; ?>">
                                                                <i class="bi bi-pencil-square"></i> Edit
                                                            </button>
                                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal<?php echo $user['id']; ?>">
                                                                <i class="bi bi-trash"></i> Delete
                                                            </button>
                                                        </td>
                                                    </tr>

                                                    <!-- Edit User Modal -->
                                                    <div class="modal fade" id="editUserModal<?php echo $user['id']; ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit User</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <form method="POST" action="admin.php?tab=users">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                        <div class="mb-3">
                                                                            <label for="name" class="form-label">Name</label>
                                                                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="email" class="form-label">Email</label>
                                                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                        <button type="submit" name="edit_user" class="btn btn-primary">Save changes</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Delete User Modal -->
                                                    <div class="modal fade" id="deleteUserModal<?php echo $user['id']; ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Delete User</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <form method="POST" action="admin.php?tab=users">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                        <p>Are you sure you want to delete this user: <?php echo htmlspecialchars($user['name']); ?>?</p>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="delete_user" class="btn btn-danger">Delete</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination -->
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-center">
                                            <?php if ($user_page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?tab=users&user_page=<?php echo $user_page - 1; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>" aria-label="Previous">
                                                        <span aria-hidden="true">&laquo;</span>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php for ($i = 1; $i <= $total_user_pages; $i++): ?>
                                                <li class="page-item <?php echo $i == $user_page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?tab=users&user_page=<?php echo $i; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>

                                            <?php if ($user_page < $total_user_pages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?tab=users&user_page=<?php echo $user_page + 1; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>" aria-label="Next">
                                                        <span aria-hidden="true">&raquo;</span>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>

                                    <div class="text-center text-muted">
                                        Showing <?php echo ($user_offset + 1) . ' to ' . min($user_offset + $records_per_page, $total_users); ?> of <?php echo $total_users; ?> users
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Packages Tab -->
                        <div class="col-12 <?php echo $active_tab == 'packages' ? '' : 'd-none'; ?>">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Packages Management</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                            <div class="dropdown-header">Actions:</div>
                                            <a class="dropdown-item" href="#">
                                                <i class="bi bi-plus"></i> Add Package
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#">
                                                <i class="bi bi-download"></i> Export Data
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Package Name</th>
                                                    <th>Description</th>
                                                    <th>Price</th>
                                                    <th>Availability</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($package = mysqli_fetch_assoc($packages)): ?>
                                                    <tr class="fade-in">
                                                        <td><?php echo $package['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($package['package_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($package['description']); ?></td>
                                                        <td>₱<?php echo number_format($package['price'], 2); ?></td>
                                                        <td>
                                                            <?php if ($package['availability']): ?>
                                                                <span class="badge bg-success">Available</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Not Available</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editPackageModal<?php echo $package['id']; ?>">
                                                                <i class="bi bi-pencil-square"></i> Edit
                                                            </button>
                                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deletePackageModal<?php echo $package['id']; ?>">
                                                                <i class="bi bi-trash"></i> Delete
                                                            </button>
                                                        </td>
                                                    </tr>

                                                    <!-- Edit Package Modal -->
                                                    <div class="modal fade" id="editPackageModal<?php echo $package['id']; ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit Package</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <form method="POST" action="admin.php?tab=packages">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">
                                                                        <div class="mb-3">
                                                                            <label for="package_name" class="form-label">Package Name</label>
                                                                            <input type="text" class="form-control" id="package_name" name="package_name" value="<?php echo htmlspecialchars($package['package_name']); ?>" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="description" class="form-label">Description</label>
                                                                            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($package['description']); ?></textarea>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="price" class="form-label">Price</label>
                                                                            <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo $package['price']; ?>" required>
                                                                        </div>
                                                                        <div class="mb-3 form-check form-switch">
                                                                            <input class="form-check-input" type="checkbox" id="availability" name="availability" <?php echo $package['availability'] ? 'checked' : ''; ?>>
                                                                            <label class="form-check-label" for="availability">Available</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                        <button type="submit" name="edit_package" class="btn btn-primary">Save changes</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Delete Package Modal -->
                                                    <div class="modal fade" id="deletePackageModal<?php echo $package['id']; ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Delete Package</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <form method="POST" action="admin.php?tab=packages">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">
                                                                        <p>Are you sure you want to delete this package: <?php echo htmlspecialchars($package['package_name']); ?>?</p>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="delete_package" class="btn btn-danger">Delete</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination -->
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-center">
                                            <?php if ($package_page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?tab=packages&package_page=<?php echo $package_page - 1; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>" aria-label="Previous">
                                                        <span aria-hidden="true">&laquo;</span>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php for ($i = 1; $i <= $total_package_pages; $i++): ?>
                                                <li class="page-item <?php echo $i == $package_page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?tab=packages&package_page=<?php echo $i; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>

                                            <?php if ($package_page < $total_package_pages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?tab=packages&package_page=<?php echo $package_page + 1; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>" aria-label="Next">
                                                        <span aria-hidden="true">&raquo;</span>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>

                                    <div class="text-center text-muted">
                                        Showing <?php echo ($package_offset + 1) . ' to ' . min($package_offset + $records_per_page, $total_packages); ?> of <?php echo $total_packages; ?> packages
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bookings Tab -->
                        <div class="col-12 <?php echo $active_tab == 'bookings' ? '' : 'd-none'; ?>">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Bookings Management</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                            <div class="dropdown-header">Actions:</div>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#addBookingModal">
                                                <i class="bi bi-plus"></i> Add Booking
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#">
                                                <i class="bi bi-download"></i> Export Data
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>User</th>
                                                    <th>Package</th>
                                                    <th>Dates</th>
                                                    <th>Payment</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($booking = mysqli_fetch_assoc($bookings)):
                                                    $payment_details = json_decode($booking['payment_details'], true);
                                                ?>
                                                    <tr class="fade-in">
                                                        <td><?php echo $booking['id']; ?></td>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($booking['user_name']); ?></strong><br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($booking['phone']); ?></small>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($booking['package']); ?></td>
                                                        <td>
                                                            <?php echo date('M j', strtotime($booking['arrivals'])); ?> - 
                                                            <?php echo date('M j, Y', strtotime($booking['leaving'])); ?><br>
                                                            <small><?php echo $booking['guests']; ?> guest(s)</small>
                                                        </td>
                                                        <td>
                                                            <?php if ($booking['payment_method'] == 'GCash'): ?>
                                                                <img src="images/GCash_logo.png" class="payment-method-icon" alt="GCash">
                                                            <?php elseif ($booking['payment_method'] == 'Maya'): ?>
                                                                <img src="images/Paymaya_logo.png" class="payment-method-icon" alt="Maya">
                                                            <?php elseif ($booking['payment_method'] == 'PayPal'): ?>
                                                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/b5/PayPal.svg/1200px-PayPal.svg.png" class="payment-method-icon" alt="PayPal">
                                                            <?php endif; ?>
                                                            <?php echo $booking['payment_method']; ?>
                                                        </td>
                                                        <td>₱<?php echo number_format($booking['total_amount'], 2); ?></td>
                                                        <td>
                                                            <span class="status-badge status-<?php echo strtolower($booking['status']); ?>">
                                                                <?php echo $booking['status']; ?>
                                                            </span>
                                                            <?php if ($booking['reference_number']): ?>
                                                                <br><small>Ref: <?php echo $booking['reference_number']; ?></small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="dropdown">
                                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="bi bi-gear"></i> Actions
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                                    <li>
                                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editBookingModal<?php echo $booking['id']; ?>">
                                                                            <i class="bi bi-pencil"></i> Edit Booking
                                                                        </a>
                                                                    </li>
                                                                    
                                                                    <li><hr class="dropdown-divider"></li>
                                                                    <li>
                                                                        <form method="POST" action="admin.php?tab=bookings" class="px-3 py-1">
                                                                            <label class="form-label small">Quick Status Update</label>
                                                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                                                <option value="Pending" <?php echo $booking['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                                                <option value="Paid" <?php echo $booking['status'] == 'Paid' ? 'selected' : ''; ?>>Paid</option>
                                                                                <option value="Completed" <?php echo $booking['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                                                <option value="Cancelled" <?php echo $booking['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                                            </select>
                                                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                                            <input type="hidden" name="update_booking_status" value="1">
                                                                        </form>
                                                                    </li>
                                                                    <li><hr class="dropdown-divider"></li>
                                                                    <li>
                                                                        <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteBookingModal<?php echo $booking['id']; ?>">
                                                                            <i class="bi bi-trash"></i> Delete
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                   
                                                    <!-- Edit Booking Modal -->
                                                    <div class="modal fade" id="editBookingModal<?php echo $booking['id']; ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit Booking</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <form method="POST" action="admin.php?tab=bookings">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">

                                                                        <div class="row">
                                                                            <div class="col-md-6 mb-3">
                                                                                <label for="user_id" class="form-label">User</label>
                                                                                <select class="form-select" id="user_id" name="user_id" required>
                                                                                    <?php
                                                                                    $users_for_select = mysqli_query($connection, "SELECT * FROM users");
                                                                                    while ($user = mysqli_fetch_assoc($users_for_select)):
                                                                                    ?>
                                                                                        <option value="<?php echo $user['id']; ?>" <?php echo $user['id'] == $booking['user_id'] ? 'selected' : ''; ?>>
                                                                                            <?php echo htmlspecialchars($user['name']); ?>
                                                                                        </option>
                                                                                    <?php endwhile; ?>
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-md-6 mb-3">
                                                                                <label for="package" class="form-label">Package</label>
                                                                                <select class="form-select" id="package" name="package" required>
                                                                                    <?php
                                                                                    $packages_for_select = mysqli_query($connection, "SELECT * FROM packages");
                                                                                    while ($package = mysqli_fetch_assoc($packages_for_select)):
                                                                                    ?>
                                                                                        <option value="<?php echo htmlspecialchars($package['package_name']); ?>" <?php echo $package['package_name'] == $booking['package'] ? 'selected' : ''; ?>>
                                                                                            <?php echo htmlspecialchars($package['package_name']); ?>
                                                                                            (₱<?php echo number_format($package['price'], 2); ?>)
                                                                                        </option>
                                                                                    <?php endwhile; ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="col-md-6 mb-3">
                                                                                <label for="phone" class="form-label">Phone</label>
                                                                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($booking['phone']); ?>" required>
                                                                            </div>
                                                                            <div class="col-md-6 mb-3">
                                                                                <label for="guests" class="form-label">Guests</label>
                                                                                <input type="number" class="form-control" id="guests" name="guests" value="<?php echo $booking['guests']; ?>" required>
                                                                            </div>
                                                                        </div>

                                                                        <div class="mb-3">
                                                                            <label for="address" class="form-label">Address</label>
                                                                            <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($booking['address']); ?>" required>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="col-md-6 mb-3">
                                                                                <label for="arrivals" class="form-label">Arrival Date</label>
                                                                                <input type="date" class="form-control" id="arrivals" name="arrivals" value="<?php echo $booking['arrivals']; ?>" required>
                                                                            </div>
                                                                            <div class="col-md-6 mb-3">
                                                                                <label for="leaving" class="form-label">Leaving Date</label>
                                                                                <input type="date" class="form-control" id="leaving" name="leaving" value="<?php echo $booking['leaving']; ?>" required>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="col-md-6 mb-3">
                                                                                <label for="status" class="form-label">Status</label>
                                                                                <select class="form-select" id="status" name="status" required>
                                                                                    <option value="Pending" <?php echo $booking['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                                                    <option value="Paid" <?php echo $booking['status'] == 'Paid' ? 'selected' : ''; ?>>Paid</option>
                                                                                    <option value="Completed" <?php echo $booking['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                                                    <option value="Cancelled" <?php echo $booking['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-md-6 mb-3">
                                                                                <label for="reference_number" class="form-label">Reference Number</label>
                                                                                <input type="text" class="form-control" id="reference_number" name="reference_number" value="<?php echo htmlspecialchars($booking['reference_number']); ?>">
                                                                            </div>
                                                                        </div>

                                                                        <div class="mb-3">
                                                                            <label class="form-label">Payment Method</label>
                                                                            <div class="payment-methods">
                                                                                <div class="form-check">
                                                                                    <input class="form-check-input" type="radio" name="payment_method" id="gcash<?php echo $booking['id']; ?>" value="GCash" <?php echo $booking['payment_method'] == 'GCash' ? 'checked' : ''; ?>>
                                                                                    <label class="form-check-label" for="gcash<?php echo $booking['id']; ?>">
                                                                                        <img src="images/GCash_logo.png" height="20" alt="GCash">
                                                                                    </label>
                                                                                </div>
                                                                                <div class="form-check">
                                                                                    <input class="form-check-input" type="radio" name="payment_method" id="maya<?php echo $booking['id']; ?>" value="Maya" <?php echo $booking['payment_method'] == 'Maya' ? 'checked' : ''; ?>>
                                                                                    <label class="form-check-label" for="maya<?php echo $booking['id']; ?>">
                                                                                        <img src="images/Paymaya_logo.png" height="20" alt="Maya">
                                                                                    </label>
                                                                                </div>
                                                                                <div class="form-check">
                                                                                    <input class="form-check-input" type="radio" name="payment_method" id="paypal<?php echo $booking['id']; ?>" value="PayPal" <?php echo $booking['payment_method'] == 'PayPal' ? 'checked' : ''; ?>>
                                                                                    <label class="form-check-label" for="paypal<?php echo $booking['id']; ?>">
                                                                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/b5/PayPal.svg/1200px-PayPal.svg.png" height="20" alt="PayPal">
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div id="paymentDetailsContainer<?php echo $booking['id']; ?>">
                                                                            <?php if ($booking['payment_method'] == 'GCash'): ?>
                                                                                <div class="row">
                                                                                    <div class="col-md-6 mb-3">
                                                                                        <label for="gcash_number" class="form-label">GCash Number</label>
                                                                                        <input type="text" class="form-control" id="gcash_number" name="gcash_number" value="<?php echo htmlspecialchars($payment_details['number'] ?? ''); ?>" pattern="^09\d{9}$" maxlength="11">
                                                                                    </div>
                                                                                    <div class="col-md-6 mb-3">
                                                                                        <label for="gcash_name" class="form-label">Account Name</label>
                                                                                        <input type="text" class="form-control" id="gcash_name" name="gcash_name" value="<?php echo htmlspecialchars($payment_details['name'] ?? ''); ?>">
                                                                                    </div>
                                                                                </div>
                                                                            <?php elseif ($booking['payment_method'] == 'Maya'): ?>
                                                                                <div class="row">
                                                                                    <div class="col-md-6 mb-3">
                                                                                        <label for="maya_number" class="form-label">Maya Number</label>
                                                                                        <input type="text" class="form-control" id="maya_number" name="maya_number" value="<?php echo htmlspecialchars($payment_details['number'] ?? ''); ?>" pattern="^09\d{9}$" maxlength="11">
                                                                                    </div>
                                                                                    <div class="col-md-6 mb-3">
                                                                                        <label for="maya_name" class="form-label">Account Name</label>
                                                                                        <input type="text" class="form-control" id="maya_name" name="maya_name" value="<?php echo htmlspecialchars($payment_details['name'] ?? ''); ?>">
                                                                                    </div>
                                                                                </div>
                                                                            <?php elseif ($booking['payment_method'] == 'PayPal'): ?>
                                                                                <div class="row">
                                                                                    <div class="col-md-6 mb-3">
                                                                                        <label for="paypal_email" class="form-label">PayPal Email</label>
                                                                                        <input type="email" class="form-control" id="paypal_email" name="paypal_email" value="<?php echo htmlspecialchars($payment_details['email'] ?? ''); ?>">
                                                                                    </div>
                                                                                    <div class="col-md-6 mb-3">
                                                                                        <label for="paypal_name" class="form-label">Account Name</label>
                                                                                        <input type="text" class="form-control" id="paypal_name" name="paypal_name" value="<?php echo htmlspecialchars($payment_details['name'] ?? ''); ?>">
                                                                                    </div>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                        <button type="submit" name="edit_booking" class="btn btn-primary">Save changes</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Delete Booking Modal -->
                                                    <div class="modal fade" id="deleteBookingModal<?php echo $booking['id']; ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Delete Booking</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <form method="POST" action="admin.php?tab=bookings">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                                        <p>Are you sure you want to delete this booking for <?php echo htmlspecialchars($booking['user_name']); ?>?</p>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="delete_booking" class="btn btn-danger">Delete</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination -->
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-center">
                                            <?php if ($booking_page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?tab=bookings&booking_page=<?php echo $booking_page - 1; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>" aria-label="Previous">
                                                        <span aria-hidden="true">&laquo;</span>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php for ($i = 1; $i <= $total_booking_pages; $i++): ?>
                                                <li class="page-item <?php echo $i == $booking_page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?tab=bookings&booking_page=<?php echo $i; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>

                                            <?php if ($booking_page < $total_booking_pages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?tab=bookings&booking_page=<?php echo $booking_page + 1; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>" aria-label="Next">
                                                        <span aria-hidden="true">&raquo;</span>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>

                                    <div class="text-center text-muted">
                                        Showing <?php echo ($booking_offset + 1) . ' to ' . min($booking_offset + $records_per_page, $total_bookings); ?> of <?php echo $total_bookings; ?> bookings
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </main>
            <!-- End of Main Content -->
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="admin.php?tab=users">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Booking Modal -->
    <div class="modal fade" id="addBookingModal" tabindex="-1" aria-labelledby="addBookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addBookingModalLabel">Add New Booking</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="admin.php?tab=bookings" id="addBookingForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_id" class="form-label">User</label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <?php
                                    $users_for_select = mysqli_query($connection, "SELECT * FROM users");
                                    while ($user = mysqli_fetch_assoc($users_for_select)):
                                    ?>
                                        <option value="<?php echo $user['id']; ?>">
                                            <?php echo htmlspecialchars($user['name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="package" class="form-label">Package</label>
                                <select class="form-select" id="package" name="package" required>
                                    <?php
                                    $packages_for_select = mysqli_query($connection, "SELECT * FROM packages");
                                    while ($package = mysqli_fetch_assoc($packages_for_select)):
                                    ?>
                                        <option value="<?php echo htmlspecialchars($package['package_name']); ?>">
                                            <?php echo htmlspecialchars($package['package_name']); ?>
                                            (₱<?php echo number_format($package['price'], 2); ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="guests" class="form-label">Guests</label>
                                <input type="number" class="form-control" id="guests" name="guests" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="arrivals" class="form-label">Arrival Date</label>
                                <input type="date" class="form-control" id="arrivals" name="arrivals" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="leaving" class="form-label">Leaving Date</label>
                                <input type="date" class="form-control" id="leaving" name="leaving" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="reference_number" class="form-label">Reference Number (Optional)</label>
                            <input type="text" class="form-control" id="reference_number" name="reference_number">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <div class="payment-methods">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="gcash" value="GCash" checked>
                                    <label class="form-check-label" for="gcash">
                                        <img src="images/GCash_logo.png" height="20" alt="GCash">
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="maya" value="Maya">
                                    <label class="form-check-label" for="maya">
                                        <img src="images/Paymaya_logo.png" height="20" alt="Maya">
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="PayPal">
                                    <label class="form-check-label" for="paypal">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/b5/PayPal.svg/1200px-PayPal.svg.png" height="20" alt="PayPal">
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="paymentDetailsContainer">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="gcash_number" class="form-label">GCash Number</label>
                                    <input type="text" class="form-control" id="gcash_number" name="gcash_number" pattern="^09\d{9}$" maxlength="11">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="gcash_name" class="form-label">Account Name</label>
                                    <input type="text" class="form-control" id="gcash_name" name="gcash_name">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_booking" class="btn btn-primary">Add Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle the side navigation
        document.getElementById('sidebarToggleTop').addEventListener('click', function(e) {
            e.preventDefault();
            document.body.classList.toggle('sidebar-toggled');
            document.getElementById('sidebarMenu').classList.toggle('collapse');
        });

        // Activate tab based on URL parameter
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');

            if (tab) {
                const tabLink = document.querySelector(`.nav-link[href="?tab=${tab}"]`);
                if (tabLink) {
                    tabLink.classList.add('active');
                }
            }

            // Clear search when switching tabs
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function() {
                    if (this.getAttribute('href').includes('tab=')) {
                        const searchInputs = document.querySelectorAll('input[name="search"]');
                        searchInputs.forEach(input => input.value = '');
                    }
                });
            });

            // Payment method selection handler
            document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    updatePaymentDetailsFields(this.value);
                });
            });

            // Function to update payment details fields based on selected method
            function updatePaymentDetailsFields(method) {
                const container = document.getElementById('paymentDetailsContainer');

                switch (method) {
                    case 'GCash':
                        container.innerHTML = `
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="gcash_number" class="form-label">GCash Number</label>
                                    <input type="text" class="form-control" id="gcash_number" name="gcash_number" pattern="^09\d{9}$" maxlength="11">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="gcash_name" class="form-label">Account Name</label>
                                    <input type="text" class="form-control" id="gcash_name" name="gcash_name">
                                </div>
                            </div>
                        `;
                        break;
                    case 'Maya':
                        container.innerHTML = `
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="maya_number" class="form-label">Maya Number</label>
                                    <input type="text" class="form-control" id="maya_number" name="maya_number" pattern="^09\d{9}$" maxlength="11">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="maya_name" class="form-label">Account Name</label>
                                    <input type="text" class="form-control" id="maya_name" name="maya_name">
                                </div>
                            </div>
                        `;
                        break;
                    case 'PayPal':
                        container.innerHTML = `
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="paypal_email" class="form-label">PayPal Email</label>
                                    <input type="email" class="form-control" id="paypal_email" name="paypal_email">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="paypal_name" class="form-label">Account Name</label>
                                    <input type="text" class="form-control" id="paypal_name" name="paypal_name">
                                </div>
                            </div>
                        `;
                        break;
                }
            }

            // For edit modals
            document.querySelectorAll('[id^="paymentDetailsContainer"]').forEach(container => {
                const bookingId = container.id.replace('paymentDetailsContainer', '');
                const radioButtons = document.querySelectorAll(`input[name="payment_method"]`);

                radioButtons.forEach(radio => {
                    radio.addEventListener('change', function() {
                        updateEditPaymentDetailsFields(this.value, bookingId);
                    });
                });
            });

            function updateEditPaymentDetailsFields(method, bookingId) {
                const container = document.getElementById(`paymentDetailsContainer${bookingId}`);

                switch (method) {
                    case 'GCash':
                        container.innerHTML = `
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="gcash_number" class="form-label">GCash Number</label>
                                    <input type="text" class="form-control" id="gcash_number" name="gcash_number" pattern="^09\d{9}$" maxlength="11">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="gcash_name" class="form-label">Account Name</label>
                                    <input type="text" class="form-control" id="gcash_name" name="gcash_name">
                                </div>
                            </div>
                        `;
                        break;
                    case 'Maya':
                        container.innerHTML = `
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="maya_number" class="form-label">Maya Number</label>
                                    <input type="text" class="form-control" id="maya_number" name="maya_number" pattern="^09\d{9}$" maxlength="11">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="maya_name" class="form-label">Account Name</label>
                                    <input type="text" class="form-control" id="maya_name" name="maya_name">
                                </div>
                            </div>
                        `;
                        break;
                    case 'PayPal':
                        container.innerHTML = `
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="paypal_email" class="form-label">PayPal Email</label>
                                    <input type="email" class="form-control" id="paypal_email" name="paypal_email">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="paypal_name" class="form-label">Account Name</label>
                                    <input type="text" class="form-control" id="paypal_name" name="paypal_name">
                                </div>
                            </div>
                        `;
                        break;
                }
            }
        });

        // SCHEDULING VALIDATION
        document.addEventListener('DOMContentLoaded', function() {
            const addBookingForm = document.getElementById('addBookingForm');
            const arrivalsInput = document.getElementById('arrivals');
            const leavingInput = document.getElementById('leaving');
            const leavingFeedback = document.getElementById('leavingFeedback');

            // Validate dates when either field changes
            arrivalsInput.addEventListener('change', validateModalDates);
            leavingInput.addEventListener('change', validateModalDates);

            // Validate before form submission
            addBookingForm.addEventListener('submit', function(e) {
                if (!validateModalDates()) {
                    e.preventDefault();
                }
            });

            function validateModalDates() {
                const arrivalsDate = new Date(arrivalsInput.value);
                const leavingDate = new Date(leavingInput.value);

                if (arrivalsInput.value && leavingInput.value) {
                    if (leavingDate < arrivalsDate) {
                        leavingInput.classList.add('is-invalid');
                        leavingFeedback.style.display = 'block';
                        return false;
                    } else {
                        leavingInput.classList.remove('is-invalid');
                        leavingFeedback.style.display = 'none';
                    }
                }
                return true;
            }
        });
    </script>
</body>

</html>