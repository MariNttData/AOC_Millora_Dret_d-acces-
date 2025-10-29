document.querySelector("form").addEventListener("submit", function (event) {
    let isValid = true;

    const nifInput = document.getElementById("nifInput");
    const nifValue = nifInput.value.trim().toUpperCase();
    const nifError = document.getElementById("nifError");

    const nifRegex = /^[0-9]{8}[A-Z]$/;
    const nieRegex = /^[XYZ][0-9]{7}[A-Z]$/;
    const passportRegex = /^[A-Z]{3}[0-9]{6}$/;

    if (!nifRegex.test(nifValue) && !nieRegex.test(nifValue) && !passportRegex.test(nifValue)) {
        nifInput.classList.add("is-invalid");
        nifError.textContent = "Introdueix un NIF, NIE o Passaport vàlid.";
        isValid = false;
    } else {
        nifInput.classList.remove("is-invalid");
        nifError.textContent = "";
    }

    const emailInput = document.getElementById('mail');
    const emailValue = emailInput.value.trim();
    const emailError = document.getElementById("emailError");

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (emailValue && !emailRegex.test(emailValue)) {
        emailInput.classList.add("is-invalid");
        emailError.textContent = "Introdueix un correu electrònic vàlid.";
        isValid = false;
    } else {
        emailInput.classList.remove("is-invalid");
        emailError.textContent = "";
    }

    const phoneInput = document.querySelector('input[type="tel"]');
    const phoneValue = phoneInput.value.trim();
    const telError = document.getElementById("telError");

    const phoneRegex = /^[0-9]{9}$/;
    

    if (phoneValue && !phoneRegex.test(phoneValue)) {
        phoneInput.classList.add("is-invalid");
        telError.textContent = "Introdueix un número de telèfon vàlid (9 dígits).";
        isValid = false;
    } else {
        phoneInput.classList.remove("is-invalid");
        telError.textContent = "";
    }

    if (!isValid) {
        event.preventDefault(); 
    }
});


