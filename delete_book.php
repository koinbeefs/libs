<?php
session_start();
include('db.php');

// Assume you are getting the book ID from a GET request
$bookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($bookId > 0) {
    // Step 1: Delete related records from book_contents
    $deleteContentsQuery = "DELETE FROM book_contents WHERE book_id = ?";
    $stmt = $conn->prepare($deleteContentsQuery);
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    
    // Step 2: Now delete the book record
    $deleteBookQuery = "DELETE FROM books WHERE id = ?";
    $stmt = $conn->prepare($deleteBookQuery);
    $stmt->bind_param("i", $bookId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Location: view_book.php";
    } else {
        echo "Error deleting book: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "Invalid book ID.";
}

$conn->close();
?>
