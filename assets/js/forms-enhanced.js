// forms-enhanced.js - Untuk efek interaktif pada form

document.addEventListener("DOMContentLoaded", function () {
  // Password toggle visibility
  const passwordToggles = document.querySelectorAll(".password-toggle");
  passwordToggles.forEach((toggle) => {
    toggle.addEventListener("click", function () {
      const input = this.previousElementSibling;
      const icon = this.querySelector("i");

      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
      } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
      }
    });
  });

  // Password strength indicator
  const passwordInputs = document.querySelectorAll('input[type="password"]');
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
          label.innerHTML = `<i class="fas fa-check-circle"></i> ${files[0].name}`;
        } else {
          label.innerHTML = `<i class="fas fa-check-circle"></i> ${files.length} file dipilih`;
        }
        label.style.borderColor = "#00C851";
        label.style.color = "#00C851";
      }
    });
  });

  // Form validation with visual feedback
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
        }
      });

      if (!isValid) {
        e.preventDefault();

        // Show error message
        const errorDiv = document.createElement("div");
        errorDiv.className = "error";
        errorDiv.textContent = "Harap isi semua field yang diperlukan";
        errorDiv.style.marginTop = "15px";

        const existingError = this.querySelector(".error:last-child");
        if (existingError) {
          existingError.remove();
        }

        this.appendChild(errorDiv);
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

  // Step form navigation
  const nextButtons = document.querySelectorAll(".next-step");
  const prevButtons = document.querySelectorAll(".prev-step");

  nextButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const currentStep = this.closest(".form-section");
      const nextStep = currentStep.nextElementSibling;

      if (nextStep && nextStep.classList.contains("form-section")) {
        currentStep.style.display = "none";
        nextStep.style.display = "block";

        // Update progress steps
        const steps = document.querySelectorAll(".step");
        steps.forEach((step) => step.classList.remove("active"));
        nextStep.querySelector(".step")?.classList.add("active");
      }
    });
  });

  prevButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const currentStep = this.closest(".form-section");
      const prevStep = currentStep.previousElementSibling;

      if (prevStep && prevStep.classList.contains("form-section")) {
        currentStep.style.display = "none";
        prevStep.style.display = "block";

        // Update progress steps
        const steps = document.querySelectorAll(".step");
        steps.forEach((step) => step.classList.remove("active"));
        prevStep.querySelector(".step")?.classList.add("active");
      }
    });
  });
});
