<?php
class mydb
{
    function openConn()
    {
        require_once __DIR__ . "/../config/db_config.php";
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Database connection failed: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
        return $conn;
    }

    function getAllCars($conn)
    {
        $sql = "SELECT * FROM cars ORDER BY id DESC";
        return $conn->query($sql);
    }

    function findCarById($id, $conn)
    {
        $sql = "SELECT * FROM cars WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    function insertCar($name, $model, $type, $price, $availability, $image, $description, $conn)
    {
        $sql = "INSERT INTO cars (`NAME`, model, `TYPE`, price_per_day, availability_status, image_path, description)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdsss", $name, $model, $type, $price, $availability, $image, $description);
        return $stmt->execute();
    }

    function updateCar($id, $name, $model, $type, $price, $availability, $description, $conn)
    {
        $sql = "UPDATE cars
                SET `NAME` = ?, model = ?, `TYPE` = ?, price_per_day = ?,
                    availability_status = ?, description = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdssi", $name, $model, $type, $price, $availability, $description, $id);
        return $stmt->execute();
    }

    function updateCarImage($id, $image, $conn)
    {
        $sql = "UPDATE cars SET image_path = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $image, $id);
        return $stmt->execute();
    }

    function deleteCar($id, $conn)
    {
        $sql = "DELETE FROM cars WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    function carHasActiveOrders($carId, $conn)
    {
        $sql = "SELECT COUNT(*) AS cnt FROM orders WHERE car_id = ? AND `STATUS` IN ('pending','confirmed')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $carId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row["cnt"] > 0;
    }

    function countCars($conn)
    {
        $result = $conn->query("SELECT COUNT(*) AS cnt FROM cars");
        return $result->fetch_assoc()["cnt"];
    }

    function getAllMembers($conn)
    {
        $sql = "SELECT id, `NAME`, email, phone, address, profile_picture, created_at
                FROM users WHERE role = 'member' ORDER BY created_at DESC";
        return $conn->query($sql);
    }

    function findMemberById($id, $conn)
    {
        $sql = "SELECT * FROM users WHERE id = ? AND role = 'member'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    function deleteMember($id, $conn)
    {
        $sql = "DELETE FROM users WHERE id = ? AND role = 'member'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    function countMembers($conn)
    {
        $result = $conn->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'member'");
        return $result->fetch_assoc()["cnt"];
    }

    

    function getAllOrders($status, $dateFrom, $dateTo, $conn)
    {
        $sql = "SELECT o.id, o.start_date, o.end_date, o.total_cost, o.`STATUS` AS status,
                       o.payment_method, o.order_date,
                       u.`NAME` AS member_name, u.email AS member_email,
                       c.`NAME` AS car_name, c.model AS car_model, c.`TYPE` AS car_type
                FROM orders o
                JOIN users u ON u.id = o.user_id
                JOIN cars c ON c.id = o.car_id
                WHERE 1=1";

        $types  = "";
        $values = [];

        if (!empty($status)) {
            $sql .= " AND o.`STATUS` = ?";
            $types .= "s";
            $values[] = $status;
        }
        if (!empty($dateFrom)) {
            $sql .= " AND DATE(o.order_date) >= ?";
            $types .= "s";
            $values[] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $sql .= " AND DATE(o.order_date) <= ?";
            $types .= "s";
            $values[] = $dateTo;
        }
        $sql .= " ORDER BY o.order_date DESC";

        $stmt = $conn->prepare($sql);
        if ($types != "") {
            $stmt->bind_param($types, ...$values);
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    function countOrders($conn)
    {
        $result = $conn->query("SELECT COUNT(*) AS cnt FROM orders");
        return $result->fetch_assoc()["cnt"];
    }

    function countBlogs($conn)
    {
        $result = $conn->query("SELECT COUNT(*) AS cnt FROM blogs");
        return $result->fetch_assoc()["cnt"];
    }
}
