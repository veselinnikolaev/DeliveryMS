<?php
// Include database connection
require_once 'config.php';

// Fetch users from database
$query = "SELECT * FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>
<div class="container-scroller">
    <!-- Partial: Navbar -->
    <?php include 'partials/navbar.php'; ?>

    <div class="row">
        <div class="col-sm-12">
            <div class="home-tab">
                <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active ps-0" id="home-tab" href="users_list.php">User List</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">Create User</a>
                        </li>
                    </ul>
                    <div>
                        <div class="btn-wrapper">
                            <a href="#" class="btn btn-otline-dark align-items-center"><i class="icon-share"></i> Share</a>
                            <a href="#" class="btn btn-otline-dark"><i class="icon-printer"></i> Print</a>
                            <a href="users.php" class="btn btn-primary text-white me-0"><i class="icon-plus"></i> New User</a>
                        </div>
                    </div>
                </div>

                <div class="card card-rounded mt-3">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table select-table">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="form-check form-check-flat mt-0">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input">
                                                </label>
                                            </div>
                                        </th>
                                        <th>User ID</th>
                                        <th>User Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($user = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-flat mt-0">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input">
                                                </label>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="actionDropdown<?php echo $user['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="actionDropdown<?php echo $user['id']; ?>">
                                                    <li><a class="dropdown-item" href="view-user.php?id=<?php echo $user['id']; ?>">View Details</a></li>
                                                    <li><a class="dropdown-item" href="edit-user.php?id=<?php echo $user['id']; ?>">Edit</a></li>
                                                    <li><a class="dropdown-item text-danger" href="delete-user.php?id=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
