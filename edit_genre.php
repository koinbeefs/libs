<?php
include('db.php');

$genreId = $_GET['id'];

// Fetch the genre details
$stmt = $conn->prepare("SELECT * FROM genres WHERE id=?");
$stmt->bind_param("i", $genreId);
$stmt->execute();
$result = $stmt->get_result();
$genre = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];

    // Update the genre in the database
    $updateStmt = $conn->prepare("UPDATE genres SET name=? WHERE id=?");
    $updateStmt->bind_param("si", $name, $genreId);

    if ($updateStmt->execute()) {
        header("Location: view_genres.php?message=" . urlencode("Genre updated successfully!"));
        exit();
    } else {
        die("Error updating genre: " . $conn->error);
    }
}

$stmt->close();
?>

<form action="" method="POST">
    <label for="name">Genre Name:</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($genre['name']); ?>" required>
    <button type="submit">Update Genre</button>
</form>
