<?php
/* =====================================================================
   models/session_helper.php
   ---------------------------------------------------------------------
   Everything to do with "who is logged in".

   Include this file at the VERY TOP of any page that needs a user. It
   does four jobs:

       1. Starts the session (so $_SESSION works).
       2. logInUser() / requireLogin() / requireMember() - the guards.
       3. The "Remember me" cookie.
       4. A CSRF token for our forms.

   WHAT A SESSION IS (viva answer)
       HTTP forgets you between pages. A session fixes that: PHP gives
       the browser a small cookie holding an ID, and keeps a matching
       box of data on the server. We put the user's id, name and role in
       that box at login, and every page afterwards reads it back out of
       $_SESSION. The browser only ever sees the ID, never the contents.
   ===================================================================== */

/* We need the mydb class, because requireLogin() looks the user up.
   require_once means "load it, unless it is already loaded", so it is
   safe even though most pages also require db.php themselves. */
require_once __DIR__ . "/db.php";


/* ---------------------------------------------------------------------
   session_start() must run before ANY html is printed.
   session_status() checks whether a session is already running, so that
   including this file twice does not cause a warning.
   --------------------------------------------------------------------- */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* ---------------------------------------------------------------------
   THE ONE LINE YOU MAY NEED TO CHANGE.

   This is the project's address inside htdocs, as the browser sees it.
   Our files live at:      E:\xampp\htdocs\WebTech_Project\Online-Car-Rent-System
   So the browser URL is:  http://localhost/WebTech_Project/Online-Car-Rent-System

   We use it for redirects that are fired from a controller the browser
   opened DIRECTLY (order_control.php, ajax_cost.php) - there, a
   relative "login.php" would point at the wrong folder.
   If you rename or move the project folder, fix this line.
   --------------------------------------------------------------------- */
define("BASE_URL", "/WebTech_Project/Online-Car-Rent-System");


/* Any long random text of your own. It signs the Remember-me cookie. */
define("REMEMBER_SECRET", "car-rental-2026-change-this-line-to-anything-long");


/* ---------------------------------------------------------------------
   Is anybody logged in right now?
   --------------------------------------------------------------------- */
function isLoggedIn()
{
    if (isset($_SESSION["user_id"])) {
        return true;
    }
    return false;
}


/* ---------------------------------------------------------------------
   Put a user into the session. This IS "logging in".

   session_regenerate_id(true) gives the session a brand new ID now that
   the visitor's privileges have changed. It stops an attacker who
   already knew the old ID from riding along on the logged-in session.
   (That attack has a name: session fixation.)
   --------------------------------------------------------------------- */
function logInUser($user)
{
    session_regenerate_id(true);

    $_SESSION["user_id"] = $user["id"];
    $_SESSION["name"]    = $user["name"];
    $_SESSION["role"]    = $user["role"];
}


/* ---------------------------------------------------------------------
   THE "REMEMBER ME" COOKIE

   A session ends when the browser closes. A cookie can outlive it, so
   ticking "Remember me" lets someone come back tomorrow still logged in.

   The danger is obvious: if the cookie just said "user_id=5", anybody
   could edit it to 1 and become the admin. So we SIGN it.

   hash_hmac() mixes the id with our secret text and produces a long
   fingerprint. Change the id and the fingerprint no longer matches, and
   you cannot work out the right fingerprint without knowing the secret.
   --------------------------------------------------------------------- */

function setRememberMeCookie($userId)
{
    $signature = hash_hmac("sha256", $userId, REMEMBER_SECRET);

    // The cookie's value is  "5|a3f9c2..."  - the id, then the signature.
    $value = $userId . "|" . $signature;

    // 60 x 60 x 24 x 30  =  thirty days from now.
    setcookie("remember_me", $value, time() + (60 * 60 * 24 * 30), "/");
}


function clearRememberMeCookie()
{
    // The way to delete a cookie is to set it again with an expiry time
    // in the past, so the browser removes it straight away.
    setcookie("remember_me", "", time() - 3600, "/");
}


/*
   Called at the top of the login and registration pages.

   If there is no session but there IS a valid Remember-me cookie, log
   the person straight back in. If there is no cookie, it quietly does
   nothing - it never blocks or redirects by itself.
*/
function ensureLoggedIn()
{
    // Already logged in? Nothing to do.
    if (isLoggedIn()) {
        return;
    }

    if (!isset($_COOKIE["remember_me"])) {
        return;
    }

    // explode() cuts "5|a3f9c2..." at the | into two pieces.
    $parts = explode("|", $_COOKIE["remember_me"]);

    if (count($parts) != 2) {
        return;
    }

    $userId    = (int) $parts[0];
    $signature = $parts[1];

    $expected = hash_hmac("sha256", $userId, REMEMBER_SECRET);

    /* hash_equals() compares two strings without leaking, through how
       long it takes, how much of them matched. For a signature check
       that is the correct tool; a plain != would also work here. */
    if (!hash_equals($expected, $signature)) {
        clearRememberMeCookie();
        return;
    }

    // The cookie is genuine, so fetch that user and log them in.
    $db   = new mydb();
    $conn = $db->openConn();

    $user = $db->findUserById($conn, $userId);

    $conn->close();

    if ($user) {
        logInUser($user);
    } else {
        clearRememberMeCookie();
    }
}


/* ---------------------------------------------------------------------
   Guard for any page that needs a login.

   Put this as the first thing a protected page does. If the visitor is
   not logged in we send them to the login page and stop the script -
   exit is important, because without it PHP would carry on and print
   the protected page anyway.

   It RETURNS the logged-in user's row, so a page can write:
       $currentUser = requireLogin();
       echo $currentUser["name"];
   --------------------------------------------------------------------- */
function requireLogin()
{
    ensureLoggedIn();

    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "/views/login.php");
        exit;
    }

    $db   = new mydb();
    $conn = $db->openConn();

    $user = $db->findUserById($conn, $_SESSION["user_id"]);

    $conn->close();

    // The account was deleted by an admin while we were logged in, so
    // the session is stale. Throw it away and start again.
    if ($user == null) {
        $_SESSION = array();
        session_destroy();
        clearRememberMeCookie();
        header("Location: " . BASE_URL . "/views/login.php");
        exit;
    }

    return $user;
}


/* ---------------------------------------------------------------------
   Stricter guard: logged in AND role = member.

   Renting a car is a member-only action. An admin landing here is not
   an attack - they are just in the wrong place - so we show a short
   message instead of bouncing them to a login page they already passed.
   --------------------------------------------------------------------- */
function requireMember()
{
    $user = requireLogin();

    if ($user["role"] != "member") {
        echo "<h1>Members only</h1>";
        echo "<p>Only member accounts can rent cars.</p>";
        echo "<p><a href='" . BASE_URL . "/views/homepage.php'>Back to the home page</a></p>";
        exit;
    }

    return $user;
}


/* ---------------------------------------------------------------------
   CSRF PROTECTION

   The problem: you are logged in to our site. Another website secretly
   makes your browser send a POST to our order_control.php. Your session
   cookie goes along automatically, so to us it looks like you clicked
   the button yourself.

   The fix: we invent one long random string, keep it in the session,
   and print it as a hidden field in our forms. The attacking site
   cannot read our session, so it cannot guess the string. If a POST
   arrives without the matching string, we throw it away.
   --------------------------------------------------------------------- */

/* Make the token once per session and return it. */
function getToken()
{
    if (!isset($_SESSION["csrf_token"])) {
        // 16 random bytes, turned into readable letters and numbers.
        $_SESSION["csrf_token"] = bin2hex(random_bytes(16));
    }

    return $_SESSION["csrf_token"];
}


/* Check a submitted form's token. Stops the page if it does not match. */
function checkToken()
{
    $sent = "";

    if (isset($_POST["csrf_token"])) {
        $sent = $_POST["csrf_token"];
    }

    if ($sent == "" || $sent != getToken()) {
        echo "<h1>Security check failed</h1>";
        echo "<p>Please go back and submit the form again.</p>";
        exit;
    }
}
