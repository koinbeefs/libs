<?php
include('db.php'); // Include your database connection

if (isset($_GET['id'])) {
    $bookId = (int)$_GET['id'];

    // Query to get the book data
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();
        echo json_encode($book); // Return book data in JSON format
    } else {
        echo json_encode([]);
    }

    $stmt->close();
}

$conn->close();
?>
