<?php
session_start();
include('db.php'); // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'] ?? null;
    $title = $_POST['title'] ?? null;
    $content = $_POST['content'] ?? null;

    if ($book_id && $title && $content) {
        // Prepare and execute the SQL insert
        $stmt = $conn->prepare("INSERT INTO book_contents (book_id, title, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $book_id, $title, $content);

        if ($stmt->execute()) {
            echo "Content added successfully!";
        } else {
            echo "Error adding content: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Required fields are missing.";
    }
} else {
    echo "Invalid request method.";
}

// Close the connection
$conn->close();
?>
