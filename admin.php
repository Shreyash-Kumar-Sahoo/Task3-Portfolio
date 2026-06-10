<?php 
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role_name'] !== 'Admin') {
    header("location: login.php");
    exit;
}
// admin.php - Combined Dashboard
include('db.php'); 
include('db_user.php'); 
$page_title = "Admin Dashboard";
include('header.php'); 

$status_message = "";
$user_status_message = "";

// Handle Inquiry Deletion (Task 1)
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $stmt = $conn->prepare("DELETE FROM inquiries WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $status_message = "<div class='alert success'>Inquiry deleted successfully.</div>";
        } else {
            $status_message = "<div class='alert error'>Failed to delete inquiry.</div>";
        }
        $stmt->close();
    }
}

// Handle User Deletion Message (Task 3)
if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $user_status_message = "<div class='alert success'>User successfully deleted!</div>";
}

// Fetch all users along with their role names (Task 3)
$query = "SELECT users.id, users.username, users.email, users.profile_picture, users.created_at, roles.role_name 
          FROM users 
          JOIN roles ON users.role_id = roles.id 
          ORDER BY users.created_at ASC";
$result_users = mysqli_query($conn_user, $query);
?>
<main>
    <!-- Section 1: User Management (Task 3) -->
    <section>
        <h2>User Management Dashboard</h2>
        <?php echo $user_status_message; ?>

        <div style="text-align: right; margin-bottom: 20px;">
            <a href="admin_create.php" class="btn-enlighten" style="background: #a88e7a; color: white;">+ Add New User</a>
        </div>

        <div class="table-container admin-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_users && mysqli_num_rows($result_users) > 0) {
                        $counter = 1;
                        while ($row = mysqli_fetch_assoc($result_users)) {
                            echo "<tr>";
                            echo "<td>" . $counter++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td><strong>" . htmlspecialchars($row['role_name']) . "</strong></td>";
                            echo "<td>" . htmlspecialchars(date('M d, Y h:i A', strtotime($row['created_at']))) . "</td>";
                            echo "<td>";
                            echo "<a href='admin_update.php?id=" . $row['id'] . "' class='btn-enlighten' style='margin-right:10px; background: #d2b48c;'>Edit</a>";
                            echo "<a href='admin_delete.php?id=" . $row['id'] . "' class='btn-enlighten' onclick=\"return confirm('Are you sure you want to completely delete user: " . htmlspecialchars($row['username']) . "? This action cannot be undone.');\">Delete</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center;'>No users found. Click 'Add New User' to create one.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Section 2: Contact Inquiries (Task 1) -->
    <section style="margin-top: 50px;">
        <h2>Contact Inquiries Dashboard</h2>
        <?php echo $status_message; ?>
        <div class="table-container admin-table">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th> 
                        <th>Priority</th> 
                        <th>Message</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $res = $conn->query("SELECT * FROM inquiries");
                    if($res->num_rows > 0): $c=1; while($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $c++; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['priority']); ?></td>
                        <td><?php echo htmlspecialchars($row['message']); ?></td>
                        <td>
                            <a href="admin.php?del=<?php echo $row['id']; ?>" class="btn-enlighten">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php include('footer.php'); ?>