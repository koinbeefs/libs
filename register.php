<?php
session_start();

include('db.php');

$message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type']; // Get user type (librarian or non-librarian)

    // Check if passwords match
    if ($password !== $confirm_password) {
        $message = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Check for existing user
        $result = $conn->query("SELECT * FROM users WHERE username='$username'");

        if ($result->num_rows == 0) {
            // Insert new user into the database
            $stmt = $conn->prepare("INSERT INTO users (username, password, user_type) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashed_password, $user_type);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $message = "Error creating account: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $message = "Username already taken!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #b8a38f;
        }
        .container {
            max-width: 400px;
            margin-top: 100px;
            padding: 20px;
            background-color: #78593b;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: white;
        }
        .btn-success {
            width: 100%;
        }
        .alert {
            text-align: center;
        }
        label {
	        color: #FFFFFF;
        }
        a{
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-green" style="background-color: #8a653f;">
        <a class="navbar-brand" href="view_books.php">
            <img src="logo.jpg" alt="Logo" class="rounded-circle" style="width: 70px; height: 70px;"> <!-- Adjust path and size -->
        </a>
    
        <a class="navbar-brand" style="font-size: 40px;" href="view_books.php">Books</a>

        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto"></ul>
            <form class="ml-2">
                <a class="btn btn-warning" style="font-size: 20px;" href="login.php">Login</a>
                <a class="btn btn-warning" style="font-size: 20px;" href="register.php">Register</a>
            </form>
            
        </div>
    </nav>
    <div class="container">
        <h2>Register</h2>

        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" class="form-control" name="confirm_password" required>
            </div>
            <div class="form-group">
                <label for="user_type">User Type</label>
                <select class="form-control" name="user_type" required>
                    <option value="librarian">Librarian</option>
                    <option value="non-librarian">Non-Librarian</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Register</button>
        </form>
        
        <div class="text-center mt-3">
            <a href="login.php">Already have an account? Log in here</a>
        </div>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
