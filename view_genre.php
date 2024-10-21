<?php
include('db.php');

// Fetch genres from the database
$result = $conn->query("SELECT * FROM genres");

if ($result->num_rows > 0) {
    echo '<table border="1">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>';

    while ($row = $result->fetch_assoc()) {
        echo '<tr>
                <td>' . $row['id'] . '</td>
                <td>' . htmlspecialchars($row['name']) . '</td>
                <td>
                    <a href="edit_genre.php?id=' . $row['id'] . '">Edit</a>
                    <form action="process_delete_genre.php" method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="' . $row['id'] . '">
                        <button type="submit" onclick="return confirm(\'Are you sure you want to delete this genre?\');">Delete</button>
                    </form>
                </td>
              </tr>';
    }
    
    echo '</table>';
} else {
    echo 'No genres found.';
}

$conn->close();
?>
