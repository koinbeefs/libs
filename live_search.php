<?php
session_start();
include('db.php');

// Initialize search variable
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch books from the database based on the search query
$searchQuery = "%" . $conn->real_escape_string($search) . "%";
$bookQuery = $conn->prepare("
    SELECT * FROM books 
    WHERE title LIKE ? OR author LIKE ?
    LIMIT 15
");
$bookQuery->bind_param("ss", $searchQuery, $searchQuery);
$bookQuery->execute();
$results = $bookQuery->get_result();
?>

<div class="book-grid">
    <?php if ($results->num_rows > 0): ?>
        <?php while ($book = $results->fetch_assoc()): ?>
            <div class="book-card">
                <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Book Cover" class="book-cover">
                <h5><?php echo htmlspecialchars($book['title']); ?></h5>
                <a href="read_book.php?id=<?php echo $book['id']; ?>" class="btn btn-primary">Read</a>
                <a href="add_to_library.php?book_id=<?php echo $book['id']; ?>" class="btn btn-success">Add to Library</a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No books found.</p>
    <?php endif; ?>
</div>

<?php
$bookQuery->close();
$conn->close();
?>
