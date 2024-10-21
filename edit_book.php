<?php
session_start();
include('db.php');

// Fetch book details when GET request
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();
        echo json_encode($book);
        exit;
    } else {
        echo json_encode([]);
        exit;
    }
}

// Update book details when POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_id = (int)$_POST['book_id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $published_date = $_POST['published_date'];
    $description = $_POST['description'];

    // Prepare and execute the SQL update query
    $stmt = $conn->prepare("UPDATE books SET title = ?, author = ?, published_date = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $title, $author, $published_date, $description, $book_id);
    
    if ($stmt->execute()) {
        echo "Book updated successfully.";
    } else {
        echo "Error updating book.";
    }
    exit;
}
?>
