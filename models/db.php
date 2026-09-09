<?php
/* =====================================================================
   models/db.php
   ---------------------------------------------------------------------
   This is the MODEL layer. Its only job is to talk to MySQL.

   Rule we follow everywhere in this file:
       - No page ever writes SQL itself.
       - A page calls a function from here, and gets back a PHP array.
       - This keeps "how we store data" separate from "how we show data",
         which is the whole point of MVC.

   HOW EVERY FUNCTION BELOW IS BUILT (they all follow the same 6 steps):

       Step 1  Write the SQL, putting a ?  wherever a value goes.
       Step 2  $conn->prepare($sql)      -> send the SQL shape to MySQL
       Step 3  $stmt->bind_param(...)    -> fill the ? holes with values
       Step 4  $stmt->execute()          -> actually run it
       Step 5  $stmt->get_result()       -> collect the rows (SELECT only)
       Step 6  $stmt->close()            -> tidy up, then return

   WHY THE ?  (this is the SQL-injection answer for the viva)
       If we glued the value straight into the text like this:
           "SELECT * FROM users WHERE email = '$email'"
       then someone could type   ' OR '1'='1   into the email box and
       change the meaning of our query.
       With a ? , MySQL is told the SHAPE of the query FIRST, and the
       value is sent separately afterwards. So whatever the user typed
       is always treated as plain text, never as SQL commands.
   ===================================================================== */


/* ---------------------------------------------------------------------
   Login details for the database.
   define() makes a constant - a value that never changes while the
   page runs. On XAMPP the default MySQL user is "root" with no password.
   --------------------------------------------------------------------- */
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "car_rental_db");


/* ---------------------------------------------------------------------
   By default, PHP 8 throws an "exception" when a query fails, which
   crashes the page with a scary white error screen.
   Turning that OFF means a failed query simply returns false instead,
   so we can check it with a normal  if  statement - no try/catch needed.
   --------------------------------------------------------------------- */
mysqli_report(MYSQLI_REPORT_OFF);


class mydb
{
    /* =================================================================
       CONNECTION
       ================================================================= */

    /*
       Opens the connection to MySQL and hands it back.
       Every page calls this once at the top, and calls $conn->close()
       when it is finished.
    */
    function openConn()
    {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // If XAMPP's MySQL is not running, stop here with a clear message
        // instead of letting the rest of the page fail confusingly.
        if ($conn->connect_error) {
            die("Could not connect to the database. Is MySQL running in XAMPP?");
        }

        // Tell MySQL we are sending and receiving normal UTF-8 text,
        // so names with accents or Bangla characters are not mangled.
        $conn->set_charset("utf8mb4");

        return $conn;
    }


    /* =================================================================
       USERS  -  registration, login and the profile page

       These are the methods the Task 1 controllers call. Their names and
       their argument order are fixed by those controllers, so do not
       change them without changing reg_control / login_control /
       profile_control to match.
       ================================================================= */

    /*
       Is this email address already registered?
       Used by registration, before creating a new account.
    */
    function emailExists($conn, $email)
    {
        $sql = "SELECT id FROM users WHERE email = ? LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);      // s = string
        $stmt->execute();

        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();    // one row, or null

        $stmt->close();

        if ($row) {
            return true;
        }
        return false;
    }


    /*
       Same question, but for the profile page.

       The difference: when you save your profile without changing your
       email, your OWN row would come back as a match and wrongly block
       the save. So this version skips the row belonging to $userId.

       Returns true only when SOMEBODY ELSE has that email.
    */
    function emailTakenByOther($conn, $email, $userId)
    {
        $sql = "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $email, $userId);   // s = string, i = integer
        $stmt->execute();

        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();

        $stmt->close();

        if ($row) {
            return true;
        }
        return false;
    }


    /*
       Create a new account.

       IMPORTANT: $passwordHash must ALREADY be hashed by the controller
       with password_hash(). A real password is never stored in the table.

       Returns the new user's id (a truthy number), or 0 if it failed.
    */
    function insertUser($conn, $name, $email, $passwordHash, $address, $phone, $role)
    {
        $sql = "INSERT INTO users (NAME, email, password_hash, address, phone, role)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $name, $email, $passwordHash, $address, $phone, $role);

        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            return $conn->insert_id;    // the id MySQL just generated
        }
        return 0;
    }


    /*
       Find one user by email. Used by login.
       We fetch password_hash too, so the controller can compare it
       against what was typed.
       Returns an array, or null if that email is not registered.
    */
    function findUserByEmail($conn, $email)
    {
        $sql = "SELECT id, NAME AS name, email, password_hash, role,
                       profile_picture, address, phone
                FROM users
                WHERE email = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();

        $stmt->close();

        return $row;    // null when not found
    }


    /*
       Find one user by id.
       Used by the profile page and by requireLogin(), which knows who
       you are from the session and needs your current details.
    */
    function findUserById($conn, $userId)
    {
        $sql = "SELECT id, NAME AS name, email, password_hash, role,
                       profile_picture, address, phone
                FROM users
                WHERE id = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();

        $stmt->close();

        return $row;
    }


    /*
       Save the profile page's four text fields, and optionally a newly
       uploaded picture.

       $newPictureFilename is null when the member did not choose a new
       picture. We must NOT write null into the column in that case, or
       we would wipe the picture they already had - so there are two
       versions of the UPDATE and an if/else picks the right one.

       Returns true on success.
    */
    function updateProfile($conn, $userId, $name, $email, $address, $phone, $newPictureFilename)
    {
        if ($newPictureFilename == null) {

            // No new picture: leave the profile_picture column alone.
            $sql = "UPDATE users
                    SET NAME = ?, email = ?, address = ?, phone = ?
                    WHERE id = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $name, $email, $address, $phone, $userId);

        } else {

            // A new picture was uploaded, so save its file name too.
            $sql = "UPDATE users
                    SET NAME = ?, email = ?, address = ?, phone = ?, profile_picture = ?
                    WHERE id = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $name, $email, $address, $phone, $newPictureFilename, $userId);
        }

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }


    /* Save a new password. Again, $passwordHash is already hashed. */
    function updatePassword($conn, $userId, $passwordHash)
    {
        $sql = "UPDATE users SET password_hash = ? WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $passwordHash, $userId);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }


    /* =================================================================
       CARS  -  the home page and the car details page
       ================================================================= */

    /*
       The list of car categories for the category bar.
       DISTINCT means "one of each" - so three Private Cars in the table
       still give us just one "Private Car" entry.
       TRIM() removes accidental spaces around the value.
    */
    function getDistinctCarTypes($conn)
    {
        $sql = "SELECT DISTINCT TRIM(TYPE) AS type
                FROM cars
                ORDER BY type ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $result = $stmt->get_result();

        // Build a plain list like: array("Microbus", "Pick-up", "Private Car")
        $types = array();

        while ($row = $result->fetch_assoc()) {
            $types[] = $row["type"];
        }

        $stmt->close();

        return $types;
    }


    /*
       All cars that are currently available.

       $type is the category chosen on the home page.
       When it is "All" we want every car, otherwise only that category.
       Two separate SQL statements are easier to read (and safer) than
       trying to build one clever query, so we just use an if/else.
    */
    function getAvailableCars($conn, $type)
    {
        if ($type == "All") {

            $sql = "SELECT id, NAME AS name, model, TRIM(TYPE) AS type,
                           price_per_day, availability_status,
                           image_path, IFNULL(description, '') AS description
                    FROM cars
                    WHERE availability_status = 'available'
                    ORDER BY id ASC";

            $stmt = $conn->prepare($sql);
            // no ? in this version, so nothing to bind

        } else {

            $sql = "SELECT id, NAME AS name, model, TRIM(TYPE) AS type,
                           price_per_day, availability_status,
                           image_path, IFNULL(description, '') AS description
                    FROM cars
                    WHERE availability_status = 'available'
                      AND TRIM(TYPE) = ?
                    ORDER BY id ASC";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $type);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $cars = array();

        while ($row = $result->fetch_assoc()) {
            $cars[] = $row;
        }

        $stmt->close();

        return $cars;
    }


    /* One car by its id. Returns null when there is no car with that id. */
    function findCarById($conn, $carId)
    {
        $sql = "SELECT id, NAME AS name, model, TRIM(TYPE) AS type,
                       price_per_day, availability_status,
                       image_path, IFNULL(description, '') AS description
                FROM cars
                WHERE id = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $carId);
        $stmt->execute();

        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();

        $stmt->close();

        return $row;
    }


    /* =================================================================
       ORDERS
       ================================================================= */

    /*
       Is this car already booked by somebody during these dates?

       Two date ranges overlap when:
           the existing booking STARTS before our return date
           AND it ENDS after our collection date.

       Draw it on paper and it is obvious: if the other booking finishes
       before we collect, or starts after we return, there is no clash.
       Everything else is a clash.

       We only count 'confirmed' orders. A 'pending' order is somebody
       still looking at their invoice - they have not paid, so the car
       is not really theirs yet and it should stay bookable. A
       'cancelled' one obviously does not block anything either.

       Returns true if there is a clash.
    */
    function isCarBooked($conn, $carId, $startDate, $endDate)
    {
        $sql = "SELECT id
                FROM orders
                WHERE car_id = ?
                  AND STATUS = 'confirmed'
                  AND start_date < ?
                  AND end_date   > ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $carId, $endDate, $startDate);
        $stmt->execute();

        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();

        $stmt->close();

        if ($row) {
            return true;
        }
        return false;
    }


    /*
       Create a new order. Every new order starts as 'pending' - it is
       not a real booking until the member pays for it.

       Returns the new order's id, or 0 if the insert failed.
    */
    function insertOrder($conn, $userId, $carId, $startDate, $endDate, $totalCost)
    {
        $sql = "INSERT INTO orders (user_id, car_id, start_date, end_date, total_cost, STATUS)
                VALUES (?, ?, ?, ?, ?, 'pending')";

        $stmt = $conn->prepare($sql);
        // i = integer, s = string, d = decimal number
        $stmt->bind_param("iissd", $userId, $carId, $startDate, $endDate, $totalCost);

        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            return $conn->insert_id;
        }
        return 0;
    }


    /*
       Fetch one order together with its car details - but ONLY if that
       order belongs to this user.

       Notice the WHERE has TWO conditions: the order id AND the user id.
       The user id comes from the session, never from the URL. That is
       what stops somebody typing invoice.php?id=7 and reading another
       member's invoice: for them, order 7 simply "does not exist".

       Returns null when not found (or not yours).
    */
    function findOrderForUser($conn, $orderId, $userId)
    {
        $sql = "SELECT o.id, o.start_date, o.end_date, o.total_cost,
                       o.STATUS AS status, o.payment_method, o.order_date,
                       c.NAME AS car_name, c.model, TRIM(c.TYPE) AS type,
                       c.price_per_day, c.image_path
                FROM orders o
                JOIN cars c ON c.id = o.car_id
                WHERE o.id = ? AND o.user_id = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $orderId, $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();

        $stmt->close();

        return $row;
    }


    /*
       Every order this member has placed, newest first.
       JOIN pulls the car's name and picture in from the cars table, so
       the history page can show "Toyota Premio" instead of "car #2".
    */
    function findOrdersForUser($conn, $userId)
    {
        $sql = "SELECT o.id, o.start_date, o.end_date, o.total_cost,
                       o.STATUS AS status, o.payment_method, o.order_date,
                       c.NAME AS car_name, c.model, TRIM(c.TYPE) AS type,
                       c.price_per_day, c.image_path
                FROM orders o
                JOIN cars c ON c.id = o.car_id
                WHERE o.user_id = ?
                ORDER BY o.id DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        $orders = array();

        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        $stmt->close();

        return $orders;
    }


    /*
       Change an order's status - we use it for cancelling.

       Look at the WHERE line: it has THREE guards.
           id = ?              the right order
           user_id = ?         belonging to the right person
           STATUS = 'pending'  and still waiting

       That last guard matters. If the member double-clicks Cancel, or
       refreshes the page, the second request finds no row that is still
       'pending', so nothing happens twice.

       affected_rows tells us how many rows actually changed. We return
       true only when it is exactly 1.
    */
    function updateOrderStatus($conn, $orderId, $userId, $status)
    {
        $sql = "UPDATE orders
                SET STATUS = ?
                WHERE id = ? AND user_id = ? AND STATUS = 'pending'";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $status, $orderId, $userId);
        $stmt->execute();

        $changed = $stmt->affected_rows;

        $stmt->close();

        if ($changed == 1) {
            return true;
        }
        return false;
    }


    /*
       Mark an order as paid for. Same three guards as above, so a
       refreshed payment page cannot confirm the same order twice.
    */
    function confirmOrder($conn, $orderId, $userId, $method)
    {
        $sql = "UPDATE orders
                SET STATUS = 'confirmed', payment_method = ?
                WHERE id = ? AND user_id = ? AND STATUS = 'pending'";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $method, $orderId, $userId);
        $stmt->execute();

        $changed = $stmt->affected_rows;

        $stmt->close();

        if ($changed == 1) {
            return true;
        }
        return false;
    }


    /* =================================================================
       PAYMENTS
       ================================================================= */

    /*
       Save a payment record.

       $amount is passed in from orders.total_cost - NEVER from the form.
       If we trusted the form, somebody could open the browser's dev
       tools, change the hidden price to 1, and pay 1 taka for a car.

       We store only the method and the reference number. No card
       numbers are ever kept in our database.
    */
    function insertPayment($conn, $orderId, $amount, $method, $transactionId)
    {
        $sql = "INSERT INTO payments (order_id, amount, payment_method, transaction_id)
                VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("idss", $orderId, $amount, $method, $transactionId);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }


    /* The payment saved against an order, or null if it has not been paid. */
    function findPaymentForOrder($conn, $orderId)
    {
        $sql = "SELECT id, amount, payment_method, transaction_id, payment_date
                FROM payments
                WHERE order_id = ?
                ORDER BY id DESC
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();

        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();

        $stmt->close();

        return $row;
    }
}
