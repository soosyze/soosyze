function togglePassword(button, idPasswordInput) {
    var passwordInput = document.getElementById(idPasswordInput);

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        button.firstChild.classList.add("fa-eye-slash");
    } else {
        passwordInput.type = 'password';
        button.firstChild.classList.remove("fa-eye-slash");
    }
}

function getRandomColor() {
    var letters = "0123456789abcdef";
    var color = "#";
    for (var i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

function passwordPolicy(idPasswordInput)
{
    var
        value    = idPasswordInput.value,
        elements = document.querySelectorAll('#password_policy li');

    Array.prototype.forEach.call(elements, function (el) {
        reg = new RegExp(el.dataset.pattern);
        if(reg.test(value) ){
            el.style.color = 'green';
        } else {
            el.style.color = 'rgba(17, 17, 17, 0.6)';
        }
    });
}