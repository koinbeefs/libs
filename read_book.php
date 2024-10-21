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

// Fetch book information
$bookResult = $conn->prepare("SELECT title, author, published_date, description, cover_image FROM books WHERE id = ?");
$bookResult->bind_param("i", $id);
$bookResult->execute();
$book = $bookResult->get_result()->fetch_assoc();

if (!$book) {
    echo "Book not found!";
    exit();
}

// Fetch all book contents
$contentsQuery = $conn->prepare("SELECT title, content, page FROM book_contents WHERE book_id = ? ORDER BY page");
$contentsQuery->bind_param("i", $id);
$contentsQuery->execute();
$contents = $contentsQuery->get_result();

// Get total contents and chapters
$total_contents = $contents->num_rows;
$chapters = [];
while ($row = $contents->fetch_assoc()) {
    $chapters[] = $row;
}

// Determine the first page content
$firstPageContent = $chapters[0] ?? null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($book['title']); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script>
        function showReadingContent(pageNumber) {
            document.getElementById('bookDetails').style.display = 'none';
            document.getElementById('readingContent').style.display = 'block';
            document.getElementById('backButton').style.display = 'block';
            loadPage(pageNumber);
        }

        function hideReadingContent() {
            document.getElementById('bookDetails').style.display = 'block';
            document.getElementById('readingContent').style.display = 'none';
            document.getElementById('backButton').style.display = 'none';
        }

        function loadPage(pageNumber) {
            const chapters = <?php echo json_encode($chapters); ?>;
            const currentContent = chapters[pageNumber - 1];
    
            // Update the title and content
            document.getElementById('currentPageTitle').innerHTML = currentContent.title + ' (Page ' + pageNumber + ')';
            document.getElementById('currentPageContent').innerHTML = currentContent.content.replace(/\n/g, "<br>");

            // Scroll the content area to the top
            document.querySelector('.content-area').scrollTop = 0;

            // Update pagination
            let paginationHtml = '';
            for (let i = 1; i <= chapters.length; i++) {
                paginationHtml += `<li class="page-item ${i === pageNumber ? 'active' : ''}">
                                    <a class="page-link" href="javascript:void(0);" onclick="loadPage(${i});">${i}</a>
                                    </li>`;
            }
            document.getElementById('pagination').innerHTML = paginationHtml;
        }
    </script>
        <style>
        h2,p{
            color: black;
        }
        .td{
            font-size: 25px;
        }
        h2{
            font-size: 40px;
        }
        p{
            font-size: 20px;
        }
    </style>
</head>
<body style="background-color: #8f7e6d;">
<nav class="navbar navbar-expand-lg navbar-light bg-green" style="background-color: #8a653f;">
    <a class="navbar-brand" href="my_library.php">
        <img src="books.jpg" alt="Logo" class="rounded-circle" style="width: 70px; height: 70px;"> <!-- Adjust path and size -->
    </a>
    <a class="navbar-brand" style="font-size:40px" href="my_library.php">Library</a>
    
    <div class="collapse navbar-collapse">     
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="browse_books.php" style="font-size:20px">Browse Books</a>
            </li>
        </ul>
        <form action="logout.php" method="post" class="ml-2">
            <button type="submit" class="btn btn-warning" style="font-size:20px">Logout</button>
        </form>
    </div>
</nav>
    <div class="container mt-5">
        <div id="bookDetails">
            <div id="bookDetails" class="d-flex align-items-start">
            <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Book Cover" style="width: 250px; height: 450px; margin-right: 20px; border: 1px solid #ccc; border-radius: 5px;">
            <div>
                <h2><?php echo htmlspecialchars($book['title']); ?></h2>
                <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
                <p><strong>Published Date:</strong> <?php echo htmlspecialchars($book['published_date']); ?></p>
                <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
            </div>
        </div>
            <h4>Chapters:</h4>
            <ul class="list-group mb-4" >
                <?php foreach ($chapters as $index => $chapter): ?>
                    <li class="list-group-item" >
                        <a href="javascript:void(0);" onclick="showReadingContent(<?php echo $index + 1; ?>)">
                            <?php echo htmlspecialchars($chapter['title']); ?> (Page <?php echo htmlspecialchars($chapter['page']); ?>)
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <a href="javascript:void(0);" onclick="showReadingContent(1);" class="btn btn-primary">Read First</a>
            <a href="javascript:void(0);" onclick="showReadingContent(<?php echo $total_contents; ?>);" class="btn btn-primary">Read Last</a>
        </div>

        <div id="readingContent" class="container p-4 bg-gray border rounded" style="display: none;">
            <button id="backButton" class="btn btn-secondary mb-3" style="display: none; background-color: #17a2b8; font-size: 25px;" onclick="hideReadingContent();">Back</button>
            <div class="border-bottom mb-3">
                <h4 id="currentPageTitle"></h4>
            </div>
            <div class="content-area mb-4" style="max-height: 700px; overflow-y: auto; padding: 10px; background-color: #f8f9fa; border: 1px solid #ced4da; border-radius: 5px; font-size: 19px;">
                <p id="currentPageContent"></p>
            </div>

            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center" id="pagination"></ul>
            </nav>
        </div>

    </div>
</body>
</html>

<?php
// Clean up
$bookResult->close();
$contentsQuery->close();
$conn->close();
?>
