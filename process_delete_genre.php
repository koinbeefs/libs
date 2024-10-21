<?php
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $genreId = $_POST['id'];

    // Delete the genre from the database
    $stmt = $conn->prepare("DELETE FROM genres WHERE id=?");
    $stmt->bind_param("i", $genreId);

    if ($stmt->execute()) {
        header("Location: view_genres.php?message=" . urlencode("Genre deleted successfully!"));
        exit();
    } else {
        die("Error deleting genre: " . $conn->error);
    }

    $stmt->close();
}

$conn->close();
?>
