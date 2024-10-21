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
