

function nameValidation() {
      var name = document.getElementById("name").value;
     if (name == "") {
         document.getElementById("name-error").innerHTML = "Please enter car name";
        return false;
    }
    document.getElementById("name-error").innerHTML = "";
}

function modelValidation() {
    var model = document.getElementById("model").value;
    if (model == "") {
        document.getElementById("model-error").innerHTML = "Please enter model";
        return false;
    }
    document.getElementById("model-error").innerHTML = "";
}

function priceValidation() {
    var price = document.getElementById("price_per_day").value;
    if (price == "" || isNaN(price) || Number(price) <= 0) {
        document.getElementById("price-error").innerHTML = "Price must be a number greater than 0";
        return false;
    }
    document.getElementById("price-error").innerHTML = "";
}

function myCarValidation() {
    if (nameValidation() == false || modelValidation() == false || priceValidation() == false) {
        return false;
    } else {
        return true;
    }
}

function deleteMember(id, name) {

    if (!confirm("Delete member \"" + name + "\"? This will also remove their orders and blog posts.")) {
        return;
    }

    var csrfToken = document.getElementById("csrfToken").value;

    var xttp = new XMLHttpRequest();

    xttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            var data = JSON.parse(this.responseText);
            if (data.success) {
                var row = document.getElementById("member-row-" + id);
                if (row) {
                    row.remove();
                }
                document.getElementById("myprint").innerHTML = data.message;
            } else {
                document.getElementById("myprint").innerHTML = data.message;
            }
        }
    };

    xttp.open("POST", "../control/delete_member_control.php", true);
    xttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xttp.send("id=" + id + "&csrf_token=" + encodeURIComponent(csrfToken));
}
