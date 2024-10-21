<?php
session_start();
include('db.php');

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get book ID from the request
if (isset($_GET['id'])) {
    $bookId = (int)$_GET['id'];
    
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();
        echo json_encode($book); // Return book details as JSON
    } else {
        echo json_encode([]);
    }
    
    $stmt->close();
}
$conn->close();
?>
