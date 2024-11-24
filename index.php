<?php
// index.php - Event listing and registration page
session_start();
require_once 'config.php';

// Fetch all events
$stmt = $pdo->query("SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date");
$events = $stmt->fetchAll();

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    $event_id = $_POST['event_id'];
    $user_id = $_SESSION['user_id'];
    
    // Check if already registered
    $stmt = $pdo->prepare("SELECT * FROM registrations WHERE event_id = ? AND user_id = ?");
    $stmt->execute([$event_id, $user_id]);
    
    if ($stmt->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO registrations (event_id, user_id) VALUES (?, ?)");
        $stmt->execute([$event_id, $user_id]);
        $success = "Registration submitted successfully!";
    } else {
        $error = "You have already registered for this event!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Event Management</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <span class="nav-link">Welcome, <?php echo $_SESSION['username']; ?></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="signup.php">Sign Up</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <h2>Upcoming Events</h2>
        <div class="row">
            <?php foreach ($events as $event): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($event['description']); ?></p>
                            <p><strong>Date:</strong> <?php echo $event['event_date']; ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                            
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form method="post">
                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                    <button type="submit" name="register" class="btn btn-primary">Register</button>
                                </form>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary">Login to Register</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>