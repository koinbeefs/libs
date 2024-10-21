<?php
include('db.php');

$search = isset($_GET['search_term']) ? $_GET['search_term'] : '';
$search = $conn->real_escape_string($search);

// Query to fetch books based on the search term
$query = "SELECT * FROM books WHERE title LIKE '%$search%' OR author LIKE '%$search%' LIMIT 5";
$result = $conn->query($query);

// Display the results as table rows
if ($result->num_rows > 0) {
    while ($book = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $book['id'] . '</td>';
        echo '<td>' . htmlspecialchars($book['title']) . '</td>';
        echo '<td>' . htmlspecialchars($book['author']) . '</td>';
        echo '<td>' . htmlspecialchars($book['published_date']) . '</td>';
        echo '<td>
                <a href="view_book.php?id=' . $book['id'] . '" class="btn btn-info">Read The Book</a>
                <button type="button" class="btn btn-warning" onclick="showEditBookModal(' . $book['id'] . ')">Edit</button>
                <a href="#" class="btn btn-danger" onclick="showDeleteBookModal(' . $book['id'] . ')">Delete</a>
              </td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="5">No books found</td></tr>';
}

$conn->close();
?>
