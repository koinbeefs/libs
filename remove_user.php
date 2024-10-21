<?php
session_start();
include('db.php'); // Connection to your database

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];

    // Begin a transaction
    $conn->begin_transaction();

    try {
        // First delete any records in the user_library table that reference this user
        $deleteLibraryStmt = $conn->prepare("DELETE FROM user_library WHERE user_id = ?");
        $deleteLibraryStmt->bind_param("i", $user_id);
        $deleteLibraryStmt->execute();
        $deleteLibraryStmt->close();

        // Now delete the user from the users table
        $deleteUserStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $deleteUserStmt->bind_param("i", $user_id);
        $deleteUserStmt->execute();
        $deleteUserStmt->close();
        
        // Commit the transaction
        $conn->commit();

        // Redirect back to user_selection.php with a success message
        header("Location: user_selection.php?message=User+deleted+successfully.");
        exit();
    } catch (Exception $e) {
        // Rollback the transaction if anything goes wrong
        $conn->rollback();
        
        // Redirect back with error message
        header("Location: user_selection.php?error=Error+deleting+user.");
        exit();
    }
} else {
    // Invalid request, redirect back without changing anything
    header("Location: user_selection.php");
    exit();
}

$conn->close();
?>
