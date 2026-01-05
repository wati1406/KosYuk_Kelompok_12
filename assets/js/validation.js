function validateForm(formId) {
  const form = document.getElementById(formId);
  const password = form.querySelector("#password");
  const confirmPassword = form.querySelector("#confirmPassword");
  const passwordError = form.querySelector("#passwordError");
  const ktpInput = form.querySelector("#ktp");
  const ktpError = form.querySelector("#ktpError");

  form.addEventListener("submit", function (e) {
    let valid = true;
    if (passwordError) passwordError.textContent = "";
    if (ktpError) ktpError.textContent = "";

    // Cek konfirmasi password
    if (
      password &&
      confirmPassword &&
      password.value !== confirmPassword.value
    ) {
      passwordError.textContent = "Password tidak cocok!";
      valid = false;
    }

    // Cek format KTP (hanya untuk pemilik kos)
    if (ktpInput) {
      const allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;
      if (!allowedExtensions.exec(ktpInput.value)) {
        ktpError.textContent = "Format file KTP harus JPG/PNG!";
        valid = false;
      }
    }

    if (!valid) {
      e.preventDefault();
    }
  });
}

// Panggil untuk masing-masing form
validateForm("ownerForm");
validateForm("renterForm");
