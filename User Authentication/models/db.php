<?php
/**
 * Data access layer for the shared `car_rental_db` schema.
 *
 * Every query here uses a prepared statement — no user input is ever
 * concatenated directly into SQL, per the project's security requirement.
 */

require_once __DIR__ . "/../config/db_config.php";

class mydb
{
    /** Open a new mysqli connection. Caller is responsible for closing it. */
    function openConn()
    {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            die("Database connection failed: " . $conn->connect_error);
        }

        return $conn;
    }

    // ------------------------------------------------------------------
    // users
    // ------------------------------------------------------------------

    /** True if a user with this email already exists. */
    function emailExists($conn, $email)
    {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        $exists = $stmt->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    /** Insert a new user (admin or member). $passwordHash must already be hashed. */
    function insertUser($conn, $name, $email, $passwordHash, $address, $phone, $role)
    {
        $stmt = $conn->prepare(
            "INSERT INTO users (NAME, email, password_hash, address, phone, role)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssss", $name, $email, $passwordHash, $address, $phone, $role);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    /** Fetch one user by email (login lookup). Returns assoc array or null. */
    function findUserByEmail($conn, $email)
    {
        $stmt = $conn->prepare(
            "SELECT id, NAME AS name, email, password_hash, role, profile_picture,
                    address, phone, created_at
             FROM users WHERE email = ? LIMIT 1"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        return $user ?: null;
    }

    /** Fetch one user by id (session/profile lookup). Returns assoc array or null. */
    function findUserById($conn, $id)
    {
        $stmt = $conn->prepare(
            "SELECT id, NAME AS name, email, password_hash, role, profile_picture,
                    address, phone, created_at
             FROM users WHERE id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        return $user ?: null;
    }

    /** Update the editable profile fields (not the password). */
    function updateProfile($conn, $id, $name, $email, $address, $phone, $profilePicture = null)
    {
        if ($profilePicture !== null) {
            $stmt = $conn->prepare(
                "UPDATE users SET NAME = ?, email = ?, address = ?, phone = ?, profile_picture = ?
                 WHERE id = ?"
            );
            $stmt->bind_param("sssssi", $name, $email, $address, $phone, $profilePicture, $id);
        } else {
            $stmt = $conn->prepare(
                "UPDATE users SET NAME = ?, email = ?, address = ?, phone = ?
                 WHERE id = ?"
            );
            $stmt->bind_param("ssssi", $name, $email, $address, $phone, $id);
        }

        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    /** True if this email belongs to a different user (used when editing your own profile). */
    function emailTakenByOther($conn, $email, $excludeId)
    {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
        $stmt->bind_param("si", $email, $excludeId);
        $stmt->execute();
        $stmt->store_result();

        $exists = $stmt->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    /** Overwrite the stored password hash (caller must have verified the current password). */
    function updatePassword($conn, $id, $newPasswordHash)
    {
        $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->bind_param("si", $newPasswordHash, $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    // ------------------------------------------------------------------
    // cars
    // ------------------------------------------------------------------

    /** All cars marked available, optionally filtered to one type/category. */
    function getAvailableCars($conn, $type = null)
    {
        if ($type !== null && $type !== "" && strtolower($type) !== "all") {
            $stmt = $conn->prepare(
                "SELECT id, NAME AS name, model, TRIM(TYPE) AS type, price_per_day,
                        availability_status, image_path, description, created_at
                 FROM cars
                 WHERE availability_status = 'available' AND TRIM(TYPE) = ?
                 ORDER BY created_at DESC"
            );
            $stmt->bind_param("s", $type);
        } else {
            $stmt = $conn->prepare(
                "SELECT id, NAME AS name, model, TRIM(TYPE) AS type, price_per_day,
                        availability_status, image_path, description, created_at
                 FROM cars
                 WHERE availability_status = 'available'
                 ORDER BY created_at DESC"
            );
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $cars   = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $cars;
    }

    /** Distinct car types/categories currently in the table, for the category bar. */
    function getDistinctCarTypes($conn)
    {
        $result = $conn->query("SELECT DISTINCT TRIM(TYPE) AS type FROM cars ORDER BY TRIM(TYPE) ASC");

        $types = [];
        while ($row = $result->fetch_assoc()) {
            $types[] = $row["type"];
        }

        return $types;
    }
}
