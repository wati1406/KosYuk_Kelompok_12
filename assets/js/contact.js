const form = document.getElementById("contactForm");
const formMessage = document.getElementById("formMessage");

form.addEventListener("submit", function (e) {
  e.preventDefault();

  // Ambil data form
  const name = form.name.value.trim();
  const email = form.email.value.trim();
  const subject = form.subject.value.trim();
  const message = form.message.value.trim();

  // Validasi
  if (!name || !email || !subject || !message) {
    showMessage("Semua kolom wajib diisi!", "error");
    return;
  }

  // Validasi email
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailPattern.test(email)) {
    showMessage("Format email tidak valid!", "error");
    return;
  }

  // Simulasi pengiriman
  showMessage(
    "Pesan berhasil dikirim! Tim kami akan menghubungi Anda dalam 1-2 jam kerja.",
    "success"
  );

  // Reset form setelah 3 detik
  setTimeout(() => {
    form.reset();
    formMessage.style.display = "none";
    formMessage.className = "";
  }, 4000);
});

function showMessage(text, type) {
  formMessage.textContent = text;
  formMessage.className = `show ${type}`;
  formMessage.style.display = "block";

  // Auto hide setelah 4 detik untuk pesan sukses
  if (type === "success") {
    setTimeout(() => {
      formMessage.style.display = "none";
      formMessage.className = "";
    }, 4000);
  }
}
