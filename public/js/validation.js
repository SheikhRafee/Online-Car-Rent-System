/* =====================================================================
   public/js/validation.js
   ---------------------------------------------------------------------
   All the client-side JavaScript for the project.

   TWO JOBS:

     1. FORM VALIDATION
        Each form calls one of the validateXxxForm() functions from its
        onsubmit attribute. If the function returns false the browser
        does not send the form, and the user sees the messages straight
        away instead of waiting for a page reload.

        IMPORTANT (and a very likely viva question):
        This is only for CONVENIENCE. Anyone can switch JavaScript off,
        or send a request with a tool that never runs it. So every one
        of these checks is done AGAIN in PHP on the server, and the PHP
        one is the check that actually protects the database.

     2. AJAX
        On the car details page, changing a date quietly asks
        controllers/ajax_cost.php how many days and how much it costs,
        and writes the answer into the summary box - no page reload.

   The file is loaded on every page. Each function only looks for its
   own boxes when it is actually called, so no page minds that the
   others are there.
   ===================================================================== */


/* =====================================================================
   SMALL HELPERS
   ===================================================================== */

/*
   Put a message into the little <span class="error"> under a field.
   Passing "" clears it.

   document.getElementById() finds an element by its id attribute.
   .innerHTML is the text inside that element.
*/
function showError(spanId, message) {

    var span = document.getElementById(spanId);

    if (span) {
        span.innerHTML = message;
    }
}


/* Read a text box and remove spaces from both ends. */
function readField(fieldId) {

    var box = document.getElementById(fieldId);

    if (!box) {
        return "";
    }

    return box.value.trim();
}


/*
   Does this look like an email address?
   indexOf() returns the position of a character, or -1 if it is not
   there at all. We want an @ somewhere, and a dot after it.
*/
function looksLikeEmail(text) {

    var atPosition  = text.indexOf("@");
    var dotPosition = text.lastIndexOf(".");

    if (atPosition < 1) {
        return false;
    }

    if (dotPosition < atPosition) {
        return false;
    }

    return true;
}


/*
   Is this a usable phone number?

   The rule has to match the one the PHP controllers use, which allows
   digits, a plus sign, dashes and spaces, and a length of 7 to 20.
   If the two rules disagreed, a number could pass here and then be
   rejected by the server, which looks like a bug to the user.
*/
function isValidPhone(text) {

    if (text.length < 7 || text.length > 20) {
        return false;
    }

    var i;
    var c;

    for (i = 0; i < text.length; i = i + 1) {

        c = text[i];

        // Not a digit, and not one of the three allowed symbols.
        if ((c < "0" || c > "9") && c !== "+" && c !== "-" && c !== " ") {
            return false;
        }
    }

    return true;
}


/* Today's date as text: "2026-08-31", the same shape a date box uses. */
function todayAsText() {

    var now = new Date();

    var year  = now.getFullYear();
    var month = now.getMonth() + 1;      // getMonth() counts from 0, so add 1
    var day   = now.getDate();

    // A single-digit month must become "09", not "9", or the text
    // comparison with the date box's value would be wrong.
    if (month < 10) { month = "0" + month; }
    if (day   < 10) { day   = "0" + day;   }

    return year + "-" + month + "-" + day;
}


/* =====================================================================
   LOGIN FORM
   ===================================================================== */

function validateLoginForm() {

    var email    = readField("email");
    var password = document.getElementById("password").value;

    var ok = true;

    showError("emailError", "");
    showError("passwordError", "");

    if (email === "") {
        showError("emailError", "Please enter your email address.");
        ok = false;
    } else if (!looksLikeEmail(email)) {
        showError("emailError", "That does not look like an email address.");
        ok = false;
    }

    if (password === "") {
        showError("passwordError", "Please enter your password.");
        ok = false;
    }

    // Returning false stops the browser sending the form.
    return ok;
}


/* =====================================================================
   REGISTRATION FORM
   ===================================================================== */

function validateRegistrationForm() {

    var name            = readField("name");
    var email           = readField("email");
    var password        = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirm").value;
    var address         = readField("address");
    var phone           = readField("phone");
    var role            = document.getElementById("role").value;

    var ok = true;

    // Clear any messages left over from the last attempt.
    showError("nameError", "");
    showError("emailError", "");
    showError("passwordError", "");
    showError("confirmError", "");
    showError("addressError", "");
    showError("phoneError", "");
    showError("roleError", "");

    if (name === "") {
        showError("nameError", "Please enter your name.");
        ok = false;
    }

    if (email === "") {
        showError("emailError", "Please enter your email address.");
        ok = false;
    } else if (!looksLikeEmail(email)) {
        showError("emailError", "That does not look like an email address.");
        ok = false;
    }

    if (password === "") {
        showError("passwordError", "Please choose a password.");
        ok = false;
    } else if (password.length < 8) {
        showError("passwordError", "Password must be at least 8 characters.");
        ok = false;
    }

    if (confirmPassword === "") {
        showError("confirmError", "Please type your password again.");
        ok = false;
    } else if (password !== confirmPassword) {
        showError("confirmError", "The two passwords do not match.");
        ok = false;
    }

    if (address === "") {
        showError("addressError", "Please enter your address.");
        ok = false;
    }

    if (phone === "") {
        showError("phoneError", "Please enter your phone number.");
        ok = false;
    } else if (!isValidPhone(phone)) {
        showError("phoneError", "Enter a valid phone number.");
        ok = false;
    }

    // The first option in the dropdown has an empty value, so this
    // catches a user who never opened it.
    if (role === "") {
        showError("roleError", "Please choose an account type.");
        ok = false;
    }

    return ok;
}


/* =====================================================================
   PROFILE DETAILS FORM
   ===================================================================== */

function validateProfileForm() {

    var name    = readField("name");
    var email   = readField("email");
    var address = readField("address");
    var phone   = readField("phone");

    var ok = true;

    showError("nameError", "");
    showError("emailError", "");
    showError("addressError", "");
    showError("phoneError", "");
    showError("pictureError", "");

    if (name === "") {
        showError("nameError", "Please enter your name.");
        ok = false;
    }

    if (email === "") {
        showError("emailError", "Please enter your email address.");
        ok = false;
    } else if (!looksLikeEmail(email)) {
        showError("emailError", "That does not look like an email address.");
        ok = false;
    }

    if (address === "") {
        showError("addressError", "Please enter your address.");
        ok = false;
    }

    if (phone === "") {
        showError("phoneError", "Please enter your phone number.");
        ok = false;
    } else if (!isValidPhone(phone)) {
        showError("phoneError", "Enter a valid phone number.");
        ok = false;
    }

    /*
       The picture is optional, so we only check it when one was chosen.
       A file input keeps its chosen files in a list called .files ,
       and each file knows its own .size in bytes.
       2 * 1024 * 1024 is 2MB.
    */
    var pictureBox = document.getElementById("profile_picture");

    if (pictureBox && pictureBox.files.length > 0) {

        if (pictureBox.files[0].size > 2 * 1024 * 1024) {
            showError("pictureError", "That picture is bigger than 2MB.");
            ok = false;
        }
    }

    return ok;
}


/* =====================================================================
   CHANGE PASSWORD FORM
   ===================================================================== */

function validatePasswordForm() {

    var current = document.getElementById("current_password").value;
    var fresh   = document.getElementById("new_password").value;
    var confirm = document.getElementById("confirm_password").value;

    var ok = true;

    showError("currentPasswordError", "");
    showError("newPasswordError", "");
    showError("confirmPasswordError", "");

    if (current === "") {
        showError("currentPasswordError", "Please enter your current password.");
        ok = false;
    }

    if (fresh === "") {
        showError("newPasswordError", "Please enter a new password.");
        ok = false;
    } else if (fresh.length < 8) {
        showError("newPasswordError", "Password must be at least 8 characters.");
        ok = false;
    }

    if (confirm === "") {
        showError("confirmPasswordError", "Please type the new password again.");
        ok = false;
    } else if (fresh !== confirm) {
        showError("confirmPasswordError", "The two passwords do not match.");
        ok = false;
    }

    return ok;
}


/* =====================================================================
   ORDER FORM (car details page)
   ===================================================================== */

function validateOrderForm() {

    var startDate = readField("start_date");
    var endDate   = readField("end_date");

    var ok = true;

    showError("startDateError", "");
    showError("endDateError", "");

    if (startDate === "") {
        showError("startDateError", "Please choose a collection date.");
        ok = false;
    } else if (startDate < todayAsText()) {
        /*
           Both are text like "2026-09-15". Because the year comes
           first, comparing them alphabetically gives the same answer
           as comparing them as dates.
        */
        showError("startDateError", "The collection date cannot be in the past.");
        ok = false;
    }

    if (endDate === "") {
        showError("endDateError", "Please choose a return date.");
        ok = false;
    } else if (startDate !== "" && endDate <= startDate) {
        showError("endDateError", "The return date must be after the collection date.");
        ok = false;
    }

    return ok;
}


/* =====================================================================
   PAYMENT FORM
   ===================================================================== */

function validatePaymentForm() {

    var transactionId = readField("transaction_id");

    var ok = true;

    showError("methodError", "");
    showError("transactionError", "");

    /*
       Radio buttons all share one name, so the browser gives us a LIST.
       We walk through it looking for the one that is checked.
       document.getElementsByName() finds elements by their name
       attribute (not their id).
    */
    var radios  = document.getElementsByName("payment_method");
    var chosen  = false;
    var i;

    for (i = 0; i < radios.length; i = i + 1) {

        if (radios[i].checked) {
            chosen = true;
        }
    }

    if (!chosen) {
        showError("methodError", "Please choose a payment method.");
        ok = false;
    }

    if (transactionId === "") {
        showError("transactionError", "Please enter your transaction or reference number.");
        ok = false;
    } else if (transactionId.length < 4) {
        showError("transactionError", "That reference looks too short.");
        ok = false;
    }

    return ok;
}


/* =====================================================================
   CANCEL CONFIRMATION (invoice page)
   ===================================================================== */

/*
   confirm() shows the browser's Yes/No box and returns true or false.
   Returning that straight out of onsubmit means "No" stops the form.
*/
function confirmCancel() {
    return confirm("Cancel this order? This cannot be undone.");
}


/* =====================================================================
   THE AJAX PART - live cost on the car details page
   ===================================================================== */

/*
   Ask the server for the days and the total, then write the answer
   into the summary box.

   The five steps of an AJAX call with XMLHttpRequest:
       1. Build the address to call, with the values as ?name=value
       2. Make a new XMLHttpRequest object
       3. Say what to do when the answer arrives (onreadystatechange)
       4. open()  - set the method and the address
       5. send()  - fire it off, and carry on without waiting
*/
function updateCostSummary() {

    var carIdBox = document.getElementById("carId");
    var startBox = document.getElementById("start_date");
    var endBox   = document.getElementById("end_date");

    // Not on the car details page? Then there is nothing to do.
    if (!carIdBox || !startBox || !endBox) {
        return;
    }

    var carId     = carIdBox.value;
    var startDate = startBox.value;
    var endDate   = endBox.value;

    // Always show the two dates the user picked, even before the
    // server answers. An empty box shows a dash instead.
    if (startDate === "") {
        document.getElementById("sOut").innerHTML = "&mdash;";
    } else {
        document.getElementById("sOut").innerHTML = startDate;
    }

    if (endDate === "") {
        document.getElementById("sIn").innerHTML = "&mdash;";
    } else {
        document.getElementById("sIn").innerHTML = endDate;
    }

    // Wait until both dates are filled in.
    if (startDate === "" || endDate === "") {
        document.getElementById("sDays").innerHTML  = "&mdash;";
        document.getElementById("sTotal").innerHTML = "&mdash;";
        return;
    }

    /* STEP 1 - build the address.
       encodeURIComponent() makes a value safe to put in a URL. */
    var url = "../controllers/ajax_cost.php"
            + "?car_id="     + encodeURIComponent(carId)
            + "&start_date=" + encodeURIComponent(startDate)
            + "&end_date="   + encodeURIComponent(endDate);

    /* STEP 2 */
    var request = new XMLHttpRequest();

    /* STEP 3 - this function runs later, when the server replies.
       readyState 4 means "finished"; status 200 means "all good". */
    request.onreadystatechange = function () {

        if (request.readyState === 4 && request.status === 200) {

            /*
               The server sent back ONE line of plain text, with the
               pieces separated by a | character:

                   OK|3|12600                     <- it worked
                   ERROR|That is not a real date. <- it did not

               split("|") cuts that line at every | and gives us an
               array, so for "OK|3|12600" we get:

                   parts[0] = "OK"
                   parts[1] = "3"        the number of days
                   parts[2] = "12600"    the total cost

               request.responseText is simply whatever the server
               printed, as text.
            */
            var parts = request.responseText.split("|");

            if (parts[0] === "OK") {

                document.getElementById("sDays").innerHTML  = parts[1];
                document.getElementById("sTotal").innerHTML = parts[2] + " BDT";

                showError("endDateError", "");

            } else {

                document.getElementById("sDays").innerHTML  = "&mdash;";
                document.getElementById("sTotal").innerHTML = "&mdash;";

                // parts[1] is the message. It is empty while the member
                // is still choosing, which clears the box instead of
                // nagging them halfway through.
                showError("endDateError", parts[1]);
            }
        }
    };

    /* STEP 4 and 5 */
    request.open("GET", url, true);
    request.send();
}


/*
   Wire the two date boxes up to the function above.

   window.onload waits until the whole page has been drawn - if we did
   this straight away, the date boxes might not exist yet and
   getElementById would give us nothing.
*/
window.onload = function () {

    var startBox = document.getElementById("start_date");
    var endBox   = document.getElementById("end_date");

    if (startBox && endBox) {

        startBox.onchange = updateCostSummary;
        endBox.onchange   = updateCostSummary;

        // Run once at the start too, so a form that came back with
        // dates already in it shows its totals immediately.
        updateCostSummary();
    }
};
