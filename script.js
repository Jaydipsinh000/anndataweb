document.addEventListener("DOMContentLoaded", () => {
  const regForm = document.getElementById("farmerForm");
  if (regForm) {
    regForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const name = document.getElementById("name").value.trim();
      const village = document.getElementById("village").value.trim();
      const mobile = document.getElementById("mobile").value.trim();
      const messageEl = document.getElementById("message");
      const lang = document.documentElement.lang; // 'gu', 'hi', or 'en'

      const mobilePattern = /^[0-9]{10}$/;

      if (!mobilePattern.test(mobile)) {
        messageEl.style.color = "red";
        if (lang === 'gu') {
          messageEl.textContent = "મોબાઇલ નંબર માત્ર 10 અંકોનો અને અંકવાળો જ હોવો જોઈએ!";
        } else if (lang === 'hi') {
          messageEl.textContent = "मोबाइल नंबर केवल 10 अंकों का और केवल अंकों वाला होना चाहिए!";
        } else {
          messageEl.textContent = "Mobile number must be exactly 10 digits and contain only numbers!";
        }
        return;
      }

      let allFarmers = JSON.parse(localStorage.getItem("farmers")) || [];
      allFarmers.push({ name, village, mobile });
      localStorage.setItem("farmers", JSON.stringify(allFarmers));
      regForm.reset();
      messageEl.style.color = "green";
      if (lang === 'gu') {
        messageEl.textContent = "તમારી નોંધણી સફળતાપૂર્વક થઈ ગઈ છે!";
      } else if (lang === 'hi') {
        messageEl.textContent = "आपका पंजीकरण सफलतापूर्वक हो गया है!";
      } else {
        messageEl.textContent = "Your registration was successful!";
      }
    });
  }

  const contactForm = document.getElementById("contactForm");
  if (contactForm) {
    contactForm.addEventListener("submit", function (e) {
      e.preventDefault();
      contactForm.reset();
      const contactSuccess = document.getElementById("contact-success");
      const lang = document.documentElement.lang;

      if (lang === 'gu') {
        contactSuccess.textContent = "તમારો સંદેશ મોકલાઈ ગયો છે!";
      } else if (lang === 'hi') {
        contactSuccess.textContent = "आपका संदेश भेज दिया गया है!";
      } else {
        contactSuccess.textContent = "Your message has been sent!";
      }
    });
  }


  showSlide(currentSlide);

  // Close mobile menu on link click
  const navLinks = document.querySelectorAll('.navbar a');
  navLinks.forEach(link => {
    link.addEventListener('click', () => {
      const nav = document.getElementById("nav-links");
      const hamburger = document.getElementById("hamburger");
      const overlay = document.getElementById("menu-overlay");
      if (window.innerWidth <= 768) {
        if (nav) nav.classList.remove("show");
        if (hamburger) hamburger.classList.remove("active");
        if (overlay) overlay.classList.remove("show");
      }
    });
  });

  // Hero section background slideshow
  const heroSection = document.getElementById('hero-section');
  const bgImages = [
    '../images/bg1.jpg',
    '../images/bg2.jpg',
    '../images/bg3.jpg'
  ];
  let bgIndex = 0;
  if (heroSection) {
    setInterval(() => {
      bgIndex = (bgIndex + 1) % bgImages.length;
      heroSection.style.backgroundImage = `linear-gradient(rgba(0,0,0,0.5),rgba(0,0,0,0.5)), url('${bgImages[bgIndex]}')`;
    }, 4000);
  }
});

let currentSlide = 0;
function showSlide(index) {
  const slides = document.querySelectorAll('.slide');
  if (!slides.length) return;
  slides.forEach((slide, i) => {
    slide.classList.toggle('active', i === index);
  });
}
function prevSlide() {
  const slides = document.querySelectorAll('.slide');
  currentSlide = (currentSlide - 1 + slides.length) % slides.length;
  showSlide(currentSlide);
}
function nextSlide() {
  const slides = document.querySelectorAll('.slide');
  currentSlide = (currentSlide + 1) % slides.length;
  showSlide(currentSlide);
}

function toggleMenu() {
  const nav = document.getElementById("nav-links");
  const hamburger = document.getElementById("hamburger");
  const overlay = document.getElementById("menu-overlay");
  if (nav) nav.classList.toggle("show");
  if (hamburger) hamburger.classList.toggle("active");
  if (overlay) overlay.classList.toggle("show");
}

function learnMore() {
  alert("આ વેબસાઇટ હજુ વિકાસ હેઠળ છે.");
}

// Highlight current page link in nav
document.addEventListener("DOMContentLoaded", () => {
  const currentPage = window.location.pathname.split("/").pop(); // e.g. "index.html"
  const navLinks = document.querySelectorAll(".navbar a");

  navLinks.forEach(link => {
    if (link.getAttribute("href") === currentPage) {
      link.classList.add("active");
    }
  });
});
