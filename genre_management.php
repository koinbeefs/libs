<?php
session_start();
include('db.php');

// Handle adding a new genre
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_genre'])) {
    $genreName = $_POST['genre_name'];
    $stmt = $conn->prepare("INSERT INTO book_genre (name) VALUES (?)");
    $stmt->bind_param("s", $genreName);
    $stmt->execute();
    $stmt->close();
}

// Fetch all genres
$genresResult = $conn->query("SELECT * FROM book_genre");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Genres</title>
    <link rel="stylesheet" href="path/to/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Manage Genres</h1>
        <form method="POST" action="">
            <div class="form-group">
                <label for="genre_name">Add New Genre</label>
                <input type="text" class="form-control" id="genre_name" name="genre_name" required>
            </div>
            <button type="submit" class="btn btn-primary" name="add_genre">Add Genre</button>
        </form>

        <h2 class="mt-5">Existing Genres</h2>
        <ul class="list-group">
            <?php while ($genre = $genresResult->fetch_assoc()): ?>
                <li class="list-group-item">
                    <?php echo htmlspecialchars($genre['name']); ?>
                    <!-- Add buttons for editing and deleting genres here -->
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
    <script src="path/to/jquery.min.js"></script>
    <script src="path/to/bootstrap.bundle.min.js"></script>
</body>
</html>
