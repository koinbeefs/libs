<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "Invalid Book!";
    exit();
}

// Fetch book information including the cover image
$bookResult = $conn->query("SELECT title, author, published_date, description, cover_image FROM books WHERE id='$id'");
$book = $bookResult->fetch_assoc();

if (!$book) {
    echo "Book not found!";
    exit();
}

$message = '';

// Handle form submissions for adding content
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_content'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    if (!empty($title) && !empty($content)) {
        // Calculate the next page number based on the current content
        $totalPagesQuery = $conn->prepare("SELECT COUNT(DISTINCT page) AS total FROM book_contents WHERE book_id=?");
        $totalPagesQuery->bind_param("i", $id);
        $totalPagesQuery->execute();
        $totalResult = $totalPagesQuery->get_result()->fetch_assoc();
        $nextPageNumber = $totalResult['total'] + 1; // New content goes to the next page

        // Insert new content
        $stmt = $conn->prepare("INSERT INTO book_contents (book_id, title, content, page) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $id, $title, $content, $nextPageNumber);
        if ($stmt->execute()) {
            $message = 'Content added successfully!';
        } else {
            $message = 'Error adding content.';
        }
        $stmt->close();
        header("Location: view_book.php?id=$id&message=" . urlencode($message));
        exit();
    }
}

// Handle content editing
if (isset($_POST['edit_content'])) {
    $content_id = $_POST['content_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $page = $_POST['page'];

    $stmt = $conn->prepare("UPDATE book_contents SET title=?, content=?, page=? WHERE id=?");
    $stmt->bind_param("ssii", $title, $content, $page, $content_id);
    $stmt->execute();
    $message = 'Content updated successfully!';
    $stmt->close();
    header("Location: view_book.php?id=$id&message=" . urlencode($message));
    exit();
}

// Handle content deletion
if (isset($_GET['delete_content'])) {
    $content_id = $_GET['delete_content'];
    $stmt = $conn->prepare("DELETE FROM book_contents WHERE id=?");
    $stmt->bind_param("i", $content_id);
    if ($stmt->execute()) {
        $message = 'Content deleted successfully!';
    } else {
        $message = 'Error deleting content.';
    }
    $stmt->close();
    header("Location: view_book.php?id=$id&message=" . urlencode($message));
    exit();
}

// Pagination setup for reading content
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1);

// Fetch the content for the particular page only
$contentQuery = $conn->prepare("SELECT * FROM book_contents WHERE book_id=? AND page=?");
$contentQuery->bind_param("ii", $id, $page);
$contentQuery->execute();
$results = $contentQuery->get_result();

// Get the total number of contents for pagination
$totalContentsQuery = $conn->prepare("SELECT DISTINCT page FROM book_contents WHERE book_id=?");
$totalContentsQuery->bind_param("i", $id);
$totalContentsQuery->execute();
$totalResult = $totalContentsQuery->get_result();
$totalItems = $totalResult->num_rows; // Count total unique pages
$totalPages = $totalItems; // Assuming each page has at least one content

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Read Book</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script>
        // Function to toggle content display
        function toggleContent() {
            const contentDiv = document.getElementById('bookContents');
            contentDiv.style.display = contentDiv.style.display === "none" || contentDiv.style.display === "" ? "block" : "none";
        }

        // Function to populate and display the edit modal with the content data
        function showEditModal(contentId) {
            $('#editContentModal' + contentId).modal('show');
        }   

        // Function to open delete confirmation modal
        function openDeleteModal(contentId) {
            document.getElementById('confirmDelete').onclick = function() {
                window.location.href = "?id=<?php echo $id; ?>&delete_content=" + contentId;
            };
            $('#deleteModal').modal('show');
        }
    </script>
    <style>
        th,td{
            color: white;
        }
        .td{
            font-size: 25px;
        }
    </style>
</head>
<body style="background-color: #b8a38f;">
    <nav class="navbar navbar-expand-lg navbar-light bg-green" style="background-color: #8a653f;">
        <a class="navbar-brand" href="view_books.php">
            <img src="books.jpg" alt="Logo" class="rounded-circle" style="width: 70px; height: 70px;"> <!-- Adjust path and size -->
        </a>
    
        <a class="navbar-brand" style="font-size: 40px;" href="view_books.php">Books</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto"></ul>
            <form action="logout.php" method="post" class="ml-2"> 
                <button type="submit" class="btn btn-warning">Logout</button>
            </form>
        </div>
    </nav>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Book Cover" class="img-fluid" style="width: 250px; height: 450px; margin-right: 20px; border: 1px solid #ccc; border-radius: 5px;">
            </div>
            <div class="col-md-8">
                <h2 style="font-size: 40px;"><?php echo htmlspecialchars($book['title']); ?></h2>
                <p style="font-size: 20px;"><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
                <p style="font-size: 20px;"><strong>Published Date:</strong> <?php echo htmlspecialchars($book['published_date']); ?></p>
                <p style="font-size: 20px; text-align: justify;"><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($book['description'])); ?></p>

                <?php if ($message): ?>
                    <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <div class="d-flex justify-content-between mb-3">
                    <button class="btn btn-primary" onclick="toggleContent()">Read Contents</button>
                    <!-- Trigger button for the modal -->
                    <button class="btn btn-success" data-toggle="modal" data-target="#addContentModal">Add Content</button>
                </div>

                <!-- Modal for Adding Content -->
                <div class="modal fade" id="addContentModal" tabindex="-1" role="dialog" aria-labelledby="addContentModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content" style="margin-top: 250px;">
                            <div class="modal-header" style="background-color: #8a653f;">
                                <h5 class="modal-title" id="addContentModalLabel">Add New Content</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form method="POST">
                                    <div class="form-group">
                                        <label for="title">Title</label>
                                        <input type="text" class="form-control" name="title" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="content">Content</label>
                                        <textarea class="form-control" name="content" rows="4" required></textarea>
                                    </div>
                                    <button type="submit" name="add_content" class="btn btn-success">Add Content</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="bookContents" style="margin-top: 20px;">
                    <h4>Page <?php echo $page; ?></h4>
                    <nav aria-label="Page navigation">
                        <ul class="pagination mt-3">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                    <a class="page-link" href="?id=<?php echo $id; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <ul class="list-group">
                        <?php while ($content = $results->fetch_assoc()): ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-warning btn-sm mr-2" onclick="showEditModal(<?php echo $content['id']; ?>)">Edit</button>
                                    <button class="btn btn-danger btn-sm" onclick="openDeleteModal(<?php echo $content['id']; ?>)">Delete</button>
                                </div>
                                <strong><?php echo htmlspecialchars($content['title']); ?></strong><br>
                                <p><?php echo nl2br(htmlspecialchars($content['content'])); ?></p>
                                <small>Page: <?php echo htmlspecialchars($content['page']); ?></small>

                                <!-- Edit Content Modal -->
                                <div class="modal fade" id="editContentModal<?php echo $content['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?php echo $content['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content" style="margin-top: 250px;">
                                            <div class="modal-header" style="background-color: #8a653f;">
                                                <h5 class="modal-title" id="editModalLabel<?php echo $content['id']; ?>">Edit Content</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="POST">
                                                    <input type="hidden" name="content_id" value="<?php echo $content['id']; ?>">
                                                    <div class="form-group">
                                                        <label for="title">Title</label>
                                                        <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($content['title']); ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="content">Content</label>
                                                        <textarea class="form-control" name="content" rows="4" required><?php echo htmlspecialchars($content['content']); ?></textarea>
                                                    </div>
                                                    <input type="hidden" name="page" value="<?php echo $content['page']; ?>">
                                                    <button type="submit" name="edit_content" class="btn btn-primary">Update Content</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                            </li>
                        <?php endwhile; ?>
                    </ul>

                    <!-- Pagination -->
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Delete Confirmation -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="margin-top: 300px;">
                <div class="modal-header" style="background-color: #8a653f;">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this content?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
