

function loadCarsByCategory(type) {
    var xttp = new XMLHttpRequest();

    xttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("carListContainer").innerHTML = this.responseText;
        }
    };

    xttp.open("GET", "../controllers/cars_ajax.php?type=" + encodeURIComponent(type), true);
    xttp.send();
}

document.addEventListener("DOMContentLoaded", function () {

    var categoryLinks = document.querySelectorAll(".category-link");
    categoryLinks.forEach(function (link) {
        link.addEventListener("click", function (e) {
            e.preventDefault();

            var type = this.textContent.trim();

            categoryLinks.forEach(function (l) {
                l.classList.remove("active");
            });
            this.classList.add("active");

            loadCarsByCategory(type);
        });
    });

    var select = document.getElementById("categorySelect");
    if (select) {
        select.addEventListener("change", function () {
            loadCarsByCategory(this.value);
        });
    }
});