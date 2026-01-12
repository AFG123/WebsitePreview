// Mobile menu toggle
function toggleMobileMenu() {
  const navLinks = document.querySelector('.nav-links');
  navLinks.classList.toggle('active');
}

// Dropdown toggle
function toggleDropdown(event) {
  event.preventDefault();
  const dropdown = event.target.closest('.dropdown');
  dropdown.classList.toggle('active');

  
  
  // Close other dropdowns
  document.querySelectorAll('.dropdown').forEach(d => {
    if (d !== dropdown) {
      d.classList.remove('active');
    }
  });
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
  if (!event.target.closest('.dropdown')) {
    document.querySelectorAll('.dropdown').forEach(dropdown => {
      dropdown.classList.remove('active');
    });
  }
});

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
  const navLinks = document.querySelector('.nav-links');
  const mobileToggle = document.querySelector('.mobile-menu-toggle');
  
  if (!event.target.closest('.nav-links') && !event.target.closest('.mobile-menu-toggle')) {
    navLinks.classList.remove('active');
  }
});

// Hero slideshow
let currentSlide = 0;
const slides = document.querySelectorAll('.hero-slide');

function showSlide(index) {
  slides.forEach((slide, i) => {
    slide.classList.toggle('active', i === index);
  });
}

function nextSlide() {
  currentSlide = (currentSlide + 1) % slides.length;
  showSlide(currentSlide);
}

// Auto-advance slides every 5 seconds
if (slides.length > 0) {
  setInterval(nextSlide, 5000);
}

// Horizontal card scrolling
function scrollCards(sectionId, direction) {
  const scrollContainer = document.getElementById(`${sectionId}-scroll`);
  const cardWidth = 280 + 32; // card width + gap
  const scrollAmount = cardWidth * 2; // scroll 2 cards at a time
  
  if (direction === 'left') {
    scrollContainer.scrollBy({
      left: -scrollAmount,
      behavior: 'smooth'
    });
  } else {
    scrollContainer.scrollBy({
      left: scrollAmount,
      behavior: 'smooth'
    });
  }
}

// Touch/swipe support for mobile
function addTouchSupport() {
  const scrollContainers = document.querySelectorAll('.card-scroll-wrapper');
  
  scrollContainers.forEach(container => {
    let isDown = false;
    let startX;
    let scrollLeft;

    container.addEventListener('mousedown', (e) => {
      isDown = true;
      container.style.cursor = 'grabbing';
      startX = e.pageX - container.offsetLeft;
      scrollLeft = container.scrollLeft;
    });

    container.addEventListener('mouseleave', () => {
      isDown = false;
      container.style.cursor = 'grab';
    });

    container.addEventListener('mouseup', () => {
      isDown = false;
      container.style.cursor = 'grab';
    });

    container.addEventListener('mousemove', (e) => {
      if (!isDown) return;
      e.preventDefault();
      const x = e.pageX - container.offsetLeft;
      const walk = (x - startX) * 2;
      container.scrollLeft = scrollLeft - walk;
    });

    // Touch events for mobile
    container.addEventListener('touchstart', (e) => {
      startX = e.touches[0].pageX - container.offsetLeft;
      scrollLeft = container.scrollLeft;
    });

    container.addEventListener('touchmove', (e) => {
      const x = e.touches[0].pageX - container.offsetLeft;
      const walk = (x - startX) * 2;
      container.scrollLeft = scrollLeft - walk;
    });
  });
}

// Card click handler
function openCardDetails(cardId) {
  window.location.href = `design-details.html?cardId=${cardId}`;
}

// Smooth scrolling for anchor links
function smoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });
}

// Update scroll button visibility
function updateScrollButtons() {
  const scrollContainers = document.querySelectorAll('.card-scroll-wrapper');
  
  scrollContainers.forEach(container => {
    const leftBtn = container.parentElement.querySelector('.scroll-btn-left');
    const rightBtn = container.parentElement.querySelector('.scroll-btn-right');
    
    if (leftBtn && rightBtn) {
      container.addEventListener('scroll', () => {
        // Show/hide left button
        leftBtn.style.opacity = container.scrollLeft > 0 ? '1' : '0.5';
        
        // Show/hide right button
        const maxScroll = container.scrollWidth - container.clientWidth;
        rightBtn.style.opacity = container.scrollLeft < maxScroll - 10 ? '1' : '0.5';
      });
      
      // Initial state
      leftBtn.style.opacity = '0.5';
      rightBtn.style.opacity = container.scrollWidth > container.clientWidth ? '1' : '0.5';
    }
  });
}

// Form submission handler
function handleFormSubmission() {
  const contactForm = document.querySelector('#contact-form');
  if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Get form data
      const formData = new FormData(contactForm);
      const name = formData.get('name') || document.getElementById('name').value;
      const email = formData.get('email') || document.getElementById('email').value;
      const phone = formData.get('phone') || document.getElementById('phone').value;
      const message = formData.get('message') || document.getElementById('message').value;
      
      // Create WhatsApp message
      const whatsappMessage = `Hi! I'm interested in your wedding invitation services.

Name: ${name}
Email: ${email}
Phone: ${phone}

Message: ${message}

Please get back to me with more information about your services and pricing.

Thank you!`;
      
      // Redirect to WhatsApp
      const whatsappURL = `https://wa.me/6366329292?text=${encodeURIComponent(whatsappMessage)}`;
      window.open(whatsappURL, '_blank');
      
      // Show success message
      alert('Thank you for your interest! You will be redirected to WhatsApp to complete your inquiry.');
    });
  }
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  smoothScroll();
  addTouchSupport();
  updateScrollButtons();
  handleFormSubmission();
  
  // Add grab cursor to scrollable containers
  document.querySelectorAll('.card-scroll-wrapper').forEach(container => {
    container.style.cursor = 'grab';
  });
});

// Handle window resize
window.addEventListener('resize', function() {
  updateScrollButtons();
});

// Intersection Observer for animations
function addScrollAnimations() {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, observerOptions);

  // Observe cards for fade-in animation
  document.querySelectorAll('.card').forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(card);
  });
}

// Add scroll animations after page load
window.addEventListener('load', addScrollAnimations);
