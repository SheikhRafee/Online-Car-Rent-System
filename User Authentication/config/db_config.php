<?php
/**
 * Database connection settings.
 *
 * Everyone on the team should be able to run this locally by only editing
 * this one file (e.g. changing DB_PASS to match their own XAMPP/WAMP/MAMP
 * root password). Nothing else in the project should hardcode credentials.
 *
 * Import database/car_rental_db.sql into a database named DB_NAME before
 * running the app.
 */

define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "car_rental_db");

/**
 * Secret used to sign "Remember me" cookies (see models/session_helper.php).
 * This is NOT a database credential — change it to any random string of
 * your own before deploying anywhere other than localhost.
 */
define("REMEMBER_ME_SECRET", "change-this-secret-before-deploying");