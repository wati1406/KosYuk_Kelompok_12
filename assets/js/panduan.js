// ============================================
// PANDUAN.JS - INTERACTIVITY FOR GUIDANCE PAGE
// ============================================

document.addEventListener("DOMContentLoaded", function () {
  console.log("Panduan CariKos - JavaScript loaded successfully!");

  // ============================================
  // FAQ ACCORDION FUNCTIONALITY
  // ============================================

  const faqItems = document.querySelectorAll(".faq-premium-item");
  console.log("Found " + faqItems.length + " FAQ items");

  // Set SEMUA FAQ items sebagai TERTUTUP by default
  faqItems.forEach((item) => {
    item.classList.remove("active");
    const content = item.querySelector(".faq-premium-content");
    const toggleIcon = item.querySelector(".faq-toggle i");

    if (content) {
      content.style.maxHeight = null;
      content.style.opacity = "0";
      content.style.padding = "0 30px";
    }

    if (toggleIcon) {
      toggleIcon.className = "fas fa-chevron-right";
      toggleIcon.style.transform = "rotate(0deg)";
    }
  });

  // Add click event to each FAQ item
  faqItems.forEach((item) => {
    const header = item.querySelector(".faq-premium-header");
    const content = item.querySelector(".faq-premium-content");
    const toggleBtn = header?.querySelector(".faq-toggle");
    const toggleIcon = toggleBtn?.querySelector("i");

    if (!header || !content || !toggleBtn || !toggleIcon) {
      console.warn("FAQ item missing required elements:", item);
      return;
    }

    header.addEventListener("click", (e) => {
      e.stopPropagation();
      const isActive = item.classList.contains("active");
      const isClickOnToggle = e.target.closest(".faq-toggle");

      if (!isClickOnToggle) {
        toggleFAQItem(item, !isActive);
      }
    });

    // Separate handler for toggle button
    toggleBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      const isActive = item.classList.contains("active");
      toggleFAQItem(item, !isActive);
    });

    // Add hover effect to entire FAQ item
    item.addEventListener("mouseenter", function () {
      if (!this.classList.contains("active")) {
        this.style.boxShadow = "var(--shadow-md)";
      }
    });

    item.addEventListener("mouseleave", function () {
      if (!this.classList.contains("active")) {
        this.style.boxShadow = "var(--shadow-sm)";
      }
    });
  });

  // Function to toggle FAQ item
  function toggleFAQItem(item, open) {
    const content = item.querySelector(".faq-premium-content");
    const toggleIcon = item.querySelector(".faq-toggle i");

    // Close all other FAQ items
    faqItems.forEach((otherItem) => {
      if (otherItem !== item && otherItem.classList.contains("active")) {
        otherItem.classList.remove("active");
        const otherContent = otherItem.querySelector(".faq-premium-content");
        const otherIcon = otherItem.querySelector(".faq-toggle i");

        if (otherContent) {
          otherContent.style.maxHeight = null;
          otherContent.style.opacity = "0";
          otherContent.style.padding = "0 30px";
        }

        if (otherIcon) {
          otherIcon.className = "fas fa-chevron-right";
          otherIcon.style.transform = "rotate(0deg)";
        }

        // Reset styles
        otherItem.style.boxShadow = "var(--shadow-sm)";
      }
    });

    // Toggle current item
    if (open) {
      item.classList.add("active");

      if (content) {
        const scrollHeight = content.scrollHeight;
        content.style.maxHeight = scrollHeight + "px";
        content.style.opacity = "1";
        content.style.padding = "0 30px 25px";
      }

      // Update toggle icon
      if (toggleIcon) {
        toggleIcon.className = "fas fa-chevron-down";
        toggleIcon.style.transform = "rotate(180deg)";
      }

      // Update box shadow for active state
      item.style.boxShadow = "var(--shadow-lg)";

      // Scroll into view if needed
      setTimeout(() => {
        const rect = item.getBoundingClientRect();
        if (rect.top < 100 || rect.bottom > window.innerHeight - 100) {
          item.scrollIntoView({
            behavior: "smooth",
            block: "center",
          });
        }
      }, 100);

      const faqTitle = item.querySelector("h3");
      if (faqTitle) {
        console.log("Opened FAQ: " + faqTitle.textContent);
      }
    } else {
      item.classList.remove("active");

      if (content) {
        content.style.maxHeight = null;
        content.style.opacity = "0";
        content.style.padding = "0 30px";
      }

      if (toggleIcon) {
        toggleIcon.className = "fas fa-chevron-right";
        toggleIcon.style.transform = "rotate(0deg)";
      }

      // Reset box shadow
      item.style.boxShadow = "var(--shadow-sm)";
    }
  }

  // ============================================
  // VIDEO PLAY BUTTON INTERACTION
  // ============================================

  const premiumPlayBtn = document.querySelector(".premium-play-btn");

  if (premiumPlayBtn) {
    const playBtnInner = premiumPlayBtn.querySelector(".play-btn-inner");
    const playBtnRings = premiumPlayBtn.querySelectorAll(".play-btn-ring");

    // Click event for video play button
    premiumPlayBtn.addEventListener("click", function () {
      // Visual feedback
      if (playBtnInner) {
        playBtnInner.style.transform = "translate(-50%, -50%) scale(0.9)";

        // Enhance ring animation
        playBtnRings.forEach((ring) => {
          ring.style.animation = "ringPulse 1s ease-in-out infinite";
          ring.style.borderWidth = "3px";
          ring.style.borderColor = "rgba(13, 110, 253, 0.5)";
        });

        // Show notification message
        setTimeout(() => {
          showVideoNotification();

          // Reset button animation
          playBtnInner.style.transform = "translate(-50%, -50%) scale(1.1)";
          playBtnInner.style.boxShadow = "0 0 50px rgba(13, 110, 253, 0.7)";

          // Reset rings after notification
          setTimeout(() => {
            playBtnInner.style.transform = "translate(-50%, -50%) scale(1)";
            playBtnInner.style.boxShadow = "0 8px 32px rgba(13, 110, 253, 0.4)";

            playBtnRings.forEach((ring) => {
              ring.style.animation = "";
              ring.style.borderWidth = "";
              ring.style.borderColor = "";
            });
          }, 1500);
        }, 150);
      }
    });

    // Hover effects for play button
    premiumPlayBtn.addEventListener("mouseenter", function () {
      if (playBtnInner) {
        playBtnInner.style.transform = "translate(-50%, -50%) scale(1.05)";
        playBtnInner.style.boxShadow = "0 0 30px rgba(13, 110, 253, 0.5)";
      }

      // Speed up ring animation on hover
      playBtnRings.forEach((ring) => {
        ring.style.animationDuration = "1.5s";
      });
    });

    premiumPlayBtn.addEventListener("mouseleave", function () {
      if (playBtnInner) {
        playBtnInner.style.transform = "translate(-50%, -50%) scale(1)";
        playBtnInner.style.boxShadow = "0 8px 32px rgba(13, 110, 253, 0.4)";
      }

      // Reset ring animation speed
      playBtnRings.forEach((ring) => {
        ring.style.animationDuration = "2s";
      });
    });
  }

  // Function to show video notification
  function showVideoNotification() {
    // Create notification element
    const notification = document.createElement("div");
    notification.className = "video-notification";
    notification.innerHTML =
      '<div class="notification-content">' +
      '<i class="fas fa-video"></i>' +
      '<div class="notification-text">' +
      "<h4>Video Tutorial Akan Segera Hadir!</h4>" +
      "<p>Video panduan lengkap CariKos sedang dalam produksi. Kami akan memberitahu Anda ketika video tersedia.</p>" +
      "</div>" +
      '<button class="notification-close"><i class="fas fa-times"></i></button>' +
      "</div>";

    // Add styles
    notification.style.cssText =
      "position: fixed;" +
      "top: 20px;" +
      "right: 20px;" +
      "background: white;" +
      "border-radius: 12px;" +
      "box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);" +
      "z-index: 1000;" +
      "animation: slideIn 0.5s ease-out;" +
      "max-width: 400px;" +
      "overflow: hidden;" +
      "border-left: 4px solid #0d6efd;";

    // Add notification to body
    document.body.appendChild(notification);

    // Add close button functionality
    const closeBtn = notification.querySelector(".notification-close");
    closeBtn.addEventListener("click", function () {
      notification.style.animation = "slideOut 0.5s ease-in forwards";
      setTimeout(() => {
        if (notification.parentNode) {
          notification.parentNode.removeChild(notification);
        }
      }, 500);
    });

    // Auto remove after 5 seconds
    setTimeout(() => {
      if (notification.parentNode) {
        notification.style.animation = "slideOut 0.5s ease-in forwards";
        setTimeout(() => {
          if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
          }
        }, 500);
      }
    }, 5000);

    // Add CSS animations
    if (!document.querySelector("#notification-styles")) {
      const style = document.createElement("style");
      style.id = "notification-styles";
      style.textContent =
        "@keyframes slideIn {" +
        "    from {" +
        "        transform: translateX(100%);" +
        "        opacity: 0;" +
        "    }" +
        "    to {" +
        "        transform: translateX(0);" +
        "        opacity: 1;" +
        "    }" +
        "}" +
        "@keyframes slideOut {" +
        "    from {" +
        "        transform: translateX(0);" +
        "        opacity: 1;" +
        "    }" +
        "    to {" +
        "        transform: translateX(100%);" +
        "        opacity: 0;" +
        "    }" +
        "}" +
        ".notification-content {" +
        "    display: flex;" +
        "    align-items: flex-start;" +
        "    padding: 20px;" +
        "    gap: 15px;" +
        "}" +
        ".notification-content i.fa-video {" +
        "    color: #0d6efd;" +
        "    font-size: 1.5rem;" +
        "    margin-top: 3px;" +
        "}" +
        ".notification-text h4 {" +
        "    margin: 0 0 8px 0;" +
        "    color: #333;" +
        "    font-size: 1.1rem;" +
        "}" +
        ".notification-text p {" +
        "    margin: 0;" +
        "    color: #666;" +
        "    font-size: 0.9rem;" +
        "    line-height: 1.5;" +
        "}" +
        ".notification-close {" +
        "    background: none;" +
        "    border: none;" +
        "    color: #999;" +
        "    cursor: pointer;" +
        "    padding: 5px;" +
        "    margin-left: auto;" +
        "    align-self: flex-start;" +
        "}" +
        ".notification-close:hover {" +
        "    color: #333;" +
        "}";
      document.head.appendChild(style);
    }
  }

  // ============================================
  // SCROLL ANIMATIONS
  // ============================================

  const sections = document.querySelectorAll("section");
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  };

  const sectionObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "1";
        entry.target.style.transform = "translateY(0)";
      }
    });
  }, observerOptions);

  // Initialize sections with fade-in effect
  sections.forEach((section, index) => {
    section.style.opacity = "0";
    section.style.transform = "translateY(30px)";
    section.style.transition =
      "opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1) " +
      index * 0.2 +
      "s, transform 0.8s cubic-bezier(0.4, 0, 0.2, 1) " +
      index * 0.2 +
      "s";
    sectionObserver.observe(section);
  });

  // ============================================
  // RESPONSIVE BEHAVIOR
  // ============================================

  // Handle window resize
  let resizeTimeout;
  window.addEventListener("resize", function () {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function () {
      // Recalculate FAQ content heights
      faqItems.forEach((item) => {
        if (item.classList.contains("active")) {
          const content = item.querySelector(".faq-premium-content");
          if (content) {
            // Temporarily remove max-height to get actual scrollHeight
            content.style.maxHeight = null;
            const scrollHeight = content.scrollHeight;
            content.style.maxHeight = scrollHeight + "px";
          }
        }
      });
    }, 250);
  });

  // ============================================
  // ADDITIONAL ENHANCEMENTS
  // ============================================

  // Add loading animation for page elements
  setTimeout(() => {
    document.body.classList.add("page-loaded");
  }, 100);

  // Keyboard accessibility for FAQ
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      // Close all FAQ items on Escape
      faqItems.forEach((item) => {
        if (item.classList.contains("active")) {
          toggleFAQItem(item, false);
        }
      });
    }

    // Navigate FAQ with arrow keys
    if (e.key === "ArrowDown" || e.key === "ArrowUp") {
      e.preventDefault();
      const activeIndex = Array.from(faqItems).findIndex((item) =>
        item.classList.contains("active")
      );

      if (activeIndex !== -1) {
        let nextIndex;
        if (e.key === "ArrowDown") {
          nextIndex = (activeIndex + 1) % faqItems.length;
        } else {
          nextIndex = (activeIndex - 1 + faqItems.length) % faqItems.length;
        }

        // Close current and open next
        toggleFAQItem(faqItems[activeIndex], false);
        setTimeout(() => {
          toggleFAQItem(faqItems[nextIndex], true);
        }, 300);
      } else if (faqItems.length > 0) {
        // If no FAQ is open, open the first one on ArrowDown
        if (e.key === "ArrowDown") {
          toggleFAQItem(faqItems[0], true);
        }
      }
    }
  });

  // Log initialization
  console.log("Panduan page initialized successfully!");
});