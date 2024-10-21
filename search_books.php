<?php
session_start();
include('db.php');

$search = isset($_GET['search_term']) ? $conn->real_escape_string($_GET['search_term']) : '';

// Fetch the results based on the search
$query = "SELECT * FROM books WHERE title LIKE '%$search%' OR author LIKE '%$search%'";
$result = $conn->query($query);

$output = '<table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Published Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>';

if ($result && $result->num_rows > 0) {
    while ($book = $result->fetch_assoc()) {
        $output .= '<tr>
                        <td>' . $book['id'] . '</td>
                        <td>' . $book['title'] . '</td>
                        <td>' . $book['author'] . '</td>
                        <td>' . $book['published_date'] . '</td>
                        <td>
                            <a href="view_book.php?id=' . $book['id'] . '" class="btn btn-info">Read The Book</a>
                            <button type="button" class="btn btn-warning" onclick="showEditBookModal(' . $book['id'] . ')">Edit</button>
                            <a href="?id=' . $book['id'] . '" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to delete this book?\');">Delete</a>
                        </td>
                    </tr>';
    }
} else {
    $output .= '<tr><td colspan="5">No results found.</td></tr>';
}

$output .= '</tbody></table>';
echo $output;

$conn->close();
?>
