// forms-enhanced.js - DIPERBAIKI (rename file)
document.addEventListener("DOMContentLoaded", function () {
  // Password toggle visibility
  const passwordToggles = document.querySelectorAll(
    ".password-toggle, .toggle-password"
  );
  passwordToggles.forEach((toggle) => {
    toggle.addEventListener("click", function () {
      let input;

      // Cari input password yang terkait
      if (
        this.previousElementSibling &&
        this.previousElementSibling.type === "password"
      ) {
        input = this.previousElementSibling;
      } else if (this.getAttribute("data-target")) {
        input = document.getElementById(this.getAttribute("data-target"));
      } else {
        input = this.parentElement.querySelector(
          'input[type="password"], input[type="text"]'
        );
      }

      if (!input) return;

      const icon = this.querySelector("i");

      if (input.type === "password") {
        input.type = "text";
        if (icon) {
          icon.classList.remove("fa-eye");
          icon.classList.add("fa-eye-slash");
        }
        this.setAttribute("aria-label", "Sembunyikan password");
      } else {
        input.type = "password";
        if (icon) {
          icon.classList.remove("fa-eye-slash");
          icon.classList.add("fa-eye");
        }
        this.setAttribute("aria-label", "Tampilkan password");
      }
    });
  });

  // Password strength indicator (jika ada)
  const passwordInputs = document.querySelectorAll(
    'input[type="password"]#password'
  );
  passwordInputs.forEach((input) => {
    input.addEventListener("input", function () {
      const strengthBar =
        this.nextElementSibling?.querySelector(".strength-bar");
      if (!strengthBar) return;

      const password = this.value;
      let strength = 0;

      if (password.length >= 8) strength++;
      if (/[A-Z]/.test(password)) strength++;
      if (/[0-9]/.test(password)) strength++;
      if (/[^A-Za-z0-9]/.test(password)) strength++;

      const width = strength * 25;
      let color = "#ff4444"; // red

      if (strength === 2) color = "#ffbb33"; // orange
      if (strength === 3) color = "#00C851"; // green
      if (strength === 4) color = "#00C851"; // green

      strengthBar.style.width = `${width}%`;
      strengthBar.style.background = color;
    });
  });

  // File input preview
  const fileInputs = document.querySelectorAll('input[type="file"]');
  fileInputs.forEach((input) => {
    input.addEventListener("change", function () {
      const label = this.nextElementSibling;
      const files = this.files;

      if (files.length > 0) {
        if (files.length === 1) {
          if (label) {
            label.innerHTML = `<i class="fas fa-check-circle"></i> ${files[0].name}`;
          }
        } else {
          if (label) {
            label.innerHTML = `<i class="fas fa-check-circle"></i> ${files.length} file dipilih`;
          }
        }
        if (label) {
          label.style.borderColor = "#00C851";
          label.style.color = "#00C851";
        }
      }
    });
  });

  // Form validation dengan visual feedback
  const forms = document.querySelectorAll("form");
  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      const requiredInputs = this.querySelectorAll("[required]");
      let isValid = true;

      requiredInputs.forEach((input) => {
        if (!input.value.trim()) {
          input.style.borderColor = "#ff4444";
          input.style.boxShadow = "0 0 0 3px rgba(255, 68, 68, 0.2)";
          isValid = false;

          // Add error animation
          input.classList.add("error-shake");
          setTimeout(() => {
            input.classList.remove("error-shake");
          }, 500);

          // Scroll ke input error
          if (isValid === false) {
            input.scrollIntoView({ behavior: "smooth", block: "center" });
          }
        } else {
          input.style.borderColor = "";
          input.style.boxShadow = "";
        }
      });

      if (!isValid) {
        e.preventDefault();

        // Show error message
        const errorDiv = document.createElement("div");
        errorDiv.className = "error";
        errorDiv.textContent = "Harap isi semua field yang diperlukan";
        errorDiv.style.marginTop = "15px";
        errorDiv.style.padding = "10px";
        errorDiv.style.background = "#ffeaea";
        errorDiv.style.color = "#d32f2f";
        errorDiv.style.borderRadius = "5px";

        const existingError = this.querySelector(".error:last-child");
        if (existingError) {
          existingError.remove();
        }

        this.prepend(errorDiv);

        // Scroll ke error
        errorDiv.scrollIntoView({ behavior: "smooth", block: "center" });
      }
    });
  });

  // Input focus effects
  const formInputs = document.querySelectorAll("input, textarea, select");
  formInputs.forEach((input) => {
    input.addEventListener("focus", function () {
      this.parentElement.classList.add("focused");
    });

    input.addEventListener("blur", function () {
      if (!this.value) {
        this.parentElement.classList.remove("focused");
      }
    });
  });

  // Error shake animation
  const style = document.createElement("style");
  style.textContent = `
        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .error-shake {
            animation: errorShake 0.5s ease-in-out;
        }
        
        .form-group.focused label {
            color: #0A2C4F;
            font-weight: bold;
        }
        
        .form-group.focused .form-input {
            border-color: #18a0fb;
            box-shadow: 0 0 0 3px rgba(24, 160, 251, 0.1);
        }
    `;
  document.head.appendChild(style);
});
