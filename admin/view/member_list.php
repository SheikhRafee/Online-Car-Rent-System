<?php
include "../control/member_list_control.php";
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Members</title>
<link rel="stylesheet" type="text/css" href="../css/admin_car.css">
</head>
<body>

<?php include "nav.php"; ?>

<h1>Manage Members</h1>

<input type="hidden" id="csrfToken" value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">

<table class="admin-table" border="1" cellpadding="6">
<tr>
    <th>Name</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Address</th>
    <th>Joined</th>
    <th>Actions</th>
</tr>

<?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr id="member-row-<?php echo (int) $row["id"]; ?>">
        <td><?php echo htmlspecialchars($row["NAME"]); ?></td>
        <td><?php echo htmlspecialchars($row["email"]); ?></td>
        <td><?php echo htmlspecialchars($row["phone"]); ?></td>
        <td><?php echo htmlspecialchars($row["address"]); ?></td>
        <td><?php echo htmlspecialchars($row["created_at"]); ?></td>
        <td>
            <button type="button" onclick="deleteMember(<?php echo (int) $row["id"]; ?>, '<?php echo htmlspecialchars($row["NAME"], ENT_QUOTES); ?>')">
                Delete
            </button>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="6">No members found.</td></tr>
<?php endif; ?>
</table>

<p id="myprint"></p>

<script src="../js/admin_car.js"></script>

</body>
</html>
