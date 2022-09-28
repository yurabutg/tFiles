$(document).ready(function () {
    disableButtons();
})

function disableButtons() {
    $('.g-recaptcha').click(function () {
        $(this).remove();
        $('.meter').removeClass('hidden');
    })
}

function setTelegramUser(data) {
    $.ajax({
        method: "POST",
        url: "/home/saveTelegramAuthUser",
        data: data
    }).done(function () {
        location.reload();
    });
}

function deleteTelegramAuthUser() {
    $.ajax({
        method: "POST",
        url: "/home/deleteTelegramAuthUser"
    }).done(function () {
        location.reload();
    });
}
