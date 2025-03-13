$(function () {
    $('#submit').on('click', function () {
        $('#form').trigger('submit');
    });
});

document.addEventListener("DOMContentLoaded", function () {
    let accessLevelSelect = document.getElementById("access_level");
    let teamFormGroup = document.getElementById("team-group");
    let schoolFormGroup = document.getElementById("school-group");

    function updateFormGroupVisibility() {
        if (accessLevelSelect.value === "59" || accessLevelSelect.value === "79") {
            teamFormGroup.style.display = "block";
            schoolFormGroup.style.display = "none";
        } else if (accessLevelSelect.value === "9") {
            teamFormGroup.style.display = "none";
            schoolFormGroup.style.display = "block";
        } else {
            teamFormGroup.style.display = "none";
            schoolFormGroup.style.display = "none";
        }
    }

    // Show or hide the form groups based on the initial value of the "access_level" select box
    updateFormGroupVisibility();

    $('#access_level').on('change', function () {
        updateFormGroupVisibility();
    });
});