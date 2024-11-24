<?php
// admin/index.php - Admin dashboard
session_start();
require_once '../config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != true) {
    $_SESSION['error'] = "You don't have permission to access the admin panel.";
    header("Location: ../login.php");
    exit();
}

// Handle event creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_event'])) {
    $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, location, max_participants) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['title'],
        $_POST['description'],
        $_POST['event_date'],
        $_POST['location'],
        $_POST['max_participants']
    ]);
    $success = "Event added successfully!";
}

// Handle registration approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $stmt = $pdo->prepare("UPDATE registrations SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['registration_id']]);
    $success = "Registration status updated!";
}

// Handle event deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_event'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$_POST['event_id']]);

        // Add success message after deletion
        $_SESSION['success'] = "Event deleted successfully!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Error deleting event: " . $e->getMessage();
    }

    // Redirect back to avoid resubmission issues
    header("Location: index.php");
    exit();
}

// Fetch pending registrations
$stmt = $pdo->query("
    SELECT r.*, e.title as event_title, u.username
    FROM registrations r
    JOIN events e ON r.event_id = e.id
    JOIN users u ON r.user_id = u.id
    WHERE r.status = 'pending'
    ORDER BY r.registration_date DESC
");
$registrations = $stmt->fetchAll();

// Fetch all events
$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <!-- Display Success or Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>



        <div class="row">
            <!-- Add New Event Form -->
            <div class="col-md-6">
                <h3>Add New Event</h3>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="event_date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Maximum Participants</label>
                        <input type="number" class="form-control" name="max_participants" required>
                    </div>
                    
                    <button type="submit" name="add_event" class="btn btn-primary">Add Event</button>
                </form>
            </div>

            <!-- Pending Registrations -->
            <div class="col-md-6">
                <h3>Pending Registrations</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>User</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registrations as $reg): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($reg['event_title']); ?></td>
                                    <td><?php echo htmlspecialchars($reg['username']); ?></td>
                                    <td><?php echo $reg['registration_date']; ?></td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="registration_id" value="<?php echo $reg['id']; ?>">
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" name="update_status" class="btn btn-success btn-sm">Approve</button>
                                        </form>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="registration_id" value="<?php echo $reg['id']; ?>">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" name="update_status" class="btn btn-danger btn-sm">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Event List -->
        <div class="mt-4">
            <h3>All Events</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Max Participants</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['title']); ?></td>
                                <td><?php echo $event['event_date']; ?></td>
                                <td><?php echo htmlspecialchars($event['location']); ?></td>
                                <td><?php echo $event['max_participants']; ?></td>
                                <td>
                                    <!-- Delete Event -->
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                        <button type="submit" name="delete_event" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                    <!-- View Registered Members -->
                                    <a href="view_members.php?event_id=<?php echo $event['id']; ?>" class="btn btn-info btn-sm">View Members</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
