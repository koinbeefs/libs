<?php
session_start();
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['message' => 'You must be logged in to add books to your library.']);
    exit();
}

// Get the book ID from the request
$book_id = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;
$user_id = $_SESSION['user_id'];

// Check if the book is already in the library
$checkQuery = $conn->prepare("SELECT * FROM user_library WHERE user_id = ? AND book_id = ?");
$checkQuery->bind_param("ii", $user_id, $book_id);
$checkQuery->execute();
$checkResult = $checkQuery->get_result();

if ($checkResult->num_rows > 0) {
    // Book is already in the library
    echo json_encode(['message' => 'Already in the library.']);
} else {
    // Add the book to the library
    $addQuery = $conn->prepare("INSERT INTO user_library (user_id, book_id) VALUES (?, ?)");
    $addQuery->bind_param("ii", $user_id, $book_id);

    if ($addQuery->execute()) {
        echo json_encode(['message' => 'Book added to library.']);
    } else {
        echo json_encode(['message' => 'Failed to add book to library.']);
    }
}

// Close the database connections
$checkQuery->close();
$addQuery->close();
$conn->close();
?>
