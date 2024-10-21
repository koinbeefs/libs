<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['book_id'])) {
    $book_id = $_GET['book_id'];
    $user_id = $_SESSION['user_id'];

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Remove the book only from the current user's library
        $stmt = $conn->prepare("DELETE FROM user_library WHERE user_id = ? AND book_id = ?");
        $stmt->bind_param("ii", $user_id, $book_id);
        $stmt->execute();

        // Commit the transaction
        $conn->commit();
        header("Location: my_library.php"); // Redirect after successful removal
    } catch (Exception $e) {
        // Rollback if something goes wrong
        $conn->rollback();
        echo "Error removing book: " . $e->getMessage();
    } finally {
        $stmt->close();
        $conn->close();
    }
}
?>
