document.addEventListener("DOMContentLoaded", function () {
  // ========= FIX HEADER POSITIONING =========
  const header = document.querySelector("header");
  if (header) {
    header.style.position = "fixed";
    header.style.top = "0";
    header.style.left = "0";
    header.style.right = "0";
    header.style.zIndex = "1000";
    header.style.background = "rgba(255, 255, 255, 0.98)";
    header.style.backdropFilter = "blur(20px)";
    header.style.boxShadow = "0 5px 20px rgba(0, 0, 0, 0.1)";
    header.style.borderBottom = "1px solid rgba(10, 44, 79, 0.1)";

    const adminBanner = document.querySelector(".admin-banner");
    if (adminBanner) {
      header.style.top = "45px";
    }

    window.addEventListener("scroll", function () {
      if (window.scrollY > 10) {
        header.style.background = "rgba(255, 255, 255, 0.98)";
        header.style.boxShadow = "0 8px 30px rgba(0, 0, 0, 0.12)";
      } else {
        header.style.background = "rgba(255, 255, 255, 0.95)";
        header.style.boxShadow = "0 5px 20px rgba(0, 0, 0, 0.1)";
      }
    });
  }

  // ========= SLIDER FUNCTIONALITY =========
  let currentSlide = 0;
  const slides = document.querySelectorAll(".slide");
  const slideInterval = 4000;

  if (slides.length > 0) {
    function showSlide(index) {
      slides.forEach((slide) => slide.classList.remove("active"));
      slides[index].classList.add("active");
      currentSlide = index;
    }

    function nextSlide() {
      let nextIndex = (currentSlide + 1) % slides.length;
      showSlide(nextIndex);
    }

    let slideTimer = setInterval(nextSlide, slideInterval);

    const slider = document.querySelector(".hero-slider");
    if (slider) {
      slider.addEventListener("mouseenter", () => {
        clearInterval(slideTimer);
      });

      slider.addEventListener("mouseleave", () => {
        slideTimer = setInterval(nextSlide, slideInterval);
      });
    }
  }

  // ========= DETAIL BUTTONS =========
  document.querySelectorAll(".detail-btn").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.stopPropagation();
      const kosId = this.dataset.id;
      window.location.href = "detail_kos.php?id=" + kosId;
    });
  });

  // ========= CLICK ON DAFTAR CARD =========
  document.querySelectorAll(".daftar-card").forEach((card) => {
    card.addEventListener("click", function (e) {
      const kosId = this.dataset.id;
      window.location.href = "detail_kos.php?id=" + kosId;
    });
  });
});

// Smooth scroll untuk anchor links
document.addEventListener("DOMContentLoaded", function () {
  // Tangani klik pada anchor links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();

      const href = this.getAttribute("href");

      // Jika href hanya "#" atau anchor kosong, abaikan
      if (href === "#" || href === "") return;

      // Cari elemen target
      const targetElement = document.querySelector(href);

      if (targetElement) {
        // Smooth scroll ke elemen target
        window.scrollTo({
          top: targetElement.offsetTop - 80, // Offset untuk header
          behavior: "smooth",
        });

        // Tambah highlight efek (opsional)
        targetElement.style.transition = "background-color 0.3s";
        targetElement.style.backgroundColor = "#f8f9fa";

        setTimeout(() => {
          targetElement.style.backgroundColor = "";
        }, 1000);
      }
    });
  });
});
