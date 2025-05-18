<?php
session_start();
include('connection.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Not authorized']);
    exit;
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    if (isset($_GET['date'])) {
        // Handle date-specific request
        $date = $_GET['date'];
        
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new Exception('Invalid date format');
        }
        
        $query = "SELECT booking.*, users.name as user_name 
                  FROM booking 
                  LEFT JOIN users ON booking.user_id = users.id
                  WHERE ? BETWEEN arrivals AND leaving";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        
        echo json_encode($bookings);
        
    } elseif (isset($_GET['month']) && isset($_GET['year'])) {
        // Handle month/year request
        $month = (int)$_GET['month'];
        $year = (int)$_GET['year'];
        
        $query = "SELECT booking.*, users.name as user_name 
                  FROM booking 
                  LEFT JOIN users ON booking.user_id = users.id
                  WHERE (MONTH(arrivals) = ? OR MONTH(leaving) = ?) 
                  AND (YEAR(arrivals) = ? OR YEAR(leaving) = ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("iiii", $month, $month, $year, $year);
        $stmt->execute();
        $result = $stmt->get_result();

        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }

        echo json_encode($bookings);
    } else {
        throw new Exception('Invalid request parameters');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>