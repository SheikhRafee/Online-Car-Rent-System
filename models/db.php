<?php
mydb
{
    function openConn()
    {
        return new mysqli("localhost", "root", "", "car_rental_db");
    }

    /* ---------------- CARS ---------------- */

    function getAllCars($conn)
    {
        $sql = "SELECT * FROM cars ORDER BY id DESC";
        return $conn->query($sql);
    }

    function findCarById($id, $conn)
    {
        $id = (int)$id;
        $sql = "SELECT * FROM cars WHERE id = $id";
        return $conn->query($sql);
    }

    function insertCar($name, $model, $type, $price, $availability, $image, $description, $conn)
    {
        $name         = $conn->real_escape_string($name);
        $model        = $conn->real_escape_string($model);
        $type         = $conn->real_escape_string($type);
        $price        = (float)$price;
        $availability = $conn->real_escape_string($availability);
        $image        = $conn->real_escape_string($image);
        $description  = $conn->real_escape_string($description);

        $sql = "INSERT INTO cars (`NAME`, model, `TYPE`, price_per_day, availability_status, image_path, description)
                VALUES ('$name', '$model', '$type', $price, '$availability', '$image', '$description')";
        return $conn->query($sql);
    }

    function updateCar($id, $name, $model, $type, $price, $availability, $description, $conn)
    {
        $id           = (int)$id;
        $name         = $conn->real_escape_string($name);
        $model        = $conn->real_escape_string($model);
        $type         = $conn->real_escape_string($type);
        $price        = (float)$price;
        $availability = $conn->real_escape_string($availability);
        $description  = $conn->real_escape_string($description);

        $sql = "UPDATE cars SET `NAME`='$name', model='$model', `TYPE`='$type', price_per_day=$price, availability_status='$availability', description='$description' WHERE id=$id";
        return $conn->query($sql);
    }

    function updateCarImage($id, $image, $conn)
    {
        $id    = (int)$id;
        $image = $conn->real_escape_string($image);
        $sql = "UPDATE cars SET image_path='$image' WHERE id=$id";
        return $conn->query($sql);
    }

    function deleteCar($id, $conn)
    {
        $id = (int)$id;
        $sql = "DELETE FROM cars WHERE id = $id";
        return $conn->query($sql);
    }

    function carHasActiveOrders($carId, $conn)
    {
        $carId = (int)$carId;
        $sql = "SELECT COUNT(*) AS cnt FROM orders WHERE car_id = $carId AND `STATUS` IN ('pending','confirmed')";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        return $row["cnt"] > 0;
    }

    function countCars($conn)
    {
        $result = $conn->query("SELECT COUNT(*) AS cnt FROM cars");
        return $result->fetch_assoc()["cnt"];
    }

    /* ---------------- MEMBERS (users where role='member') ---------------- */

    function getAllMembers($conn)
    {
        $sql = "SELECT id, `NAME`, email, phone, address, profile_picture, created_at
                FROM users WHERE role = 'member' ORDER BY created_at DESC";
        return $conn->query($sql);
    }

    function findMemberById($id, $conn)
    {
        $id = (int)$id;
        $sql = "SELECT * FROM users WHERE id = $id AND role = 'member'";
        return $conn->query($sql);
    }

    function deleteMember($id, $conn)
    {
        
        $id = (int)$id;
        $sql = "DELETE FROM users WHERE id = $id AND role = 'member'";
        return $conn->query($sql);
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

        if (!empty($status)) {
            $status = $conn->real_escape_string($status);
            $sql .= " AND o.`STATUS` = '$status'";
        }
        if (!empty($dateFrom)) {
            $dateFrom = $conn->real_escape_string($dateFrom);
            $sql .= " AND DATE(o.order_date) >= '$dateFrom'";
        }
        if (!empty($dateTo)) {
            $dateTo = $conn->real_escape_string($dateTo);
            $sql .= " AND DATE(o.order_date) <= '$dateTo'";
        }
        $sql .= " ORDER BY o.order_date DESC";

        return $conn->query($sql);
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
