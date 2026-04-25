/* ═══════════════════════════════════════════
   main.js — Portfolio fotográfico
   Funciones: Nav, Lightbox, Filtros, Formulario, Reveal
═══════════════════════════════════════════ */

// ── Año en footer
document.getElementById('year').textContent = new Date().getFullYear();

// ── NAV: scroll & mobile toggle
const navbar = document.getElementById('navbar');
const navToggle = document.querySelector('.nav-toggle');
const navLinks = document.querySelector('.nav-links');

window.addEventListener('scroll', () => {
  navbar.classList.toggle('scrolled', window.scrollY > 40);
}, { passive: true });

navToggle.addEventListener('click', () => {
  navLinks.classList.toggle('open');
  // Animación hamburger → X
  const spans = navToggle.querySelectorAll('span');
  if (navLinks.classList.contains('open')) {
    spans[0].style.transform = 'rotate(45deg) translate(4.5px, 4.5px)';
    spans[1].style.opacity = '0';
    spans[2].style.transform = 'rotate(-45deg) translate(4.5px, -4.5px)';
  } else {
    spans.forEach(s => { s.style.transform = ''; s.style.opacity = ''; });
  }
});

// Cerrar menú al hacer click en un link
navLinks.querySelectorAll('a').forEach(a => {
  a.addEventListener('click', () => {
    navLinks.classList.remove('open');
    navToggle.querySelectorAll('span').forEach(s => { s.style.transform = ''; s.style.opacity = ''; });
  });
});

// ── PORTFOLIO FILTERS
const filterBtns = document.querySelectorAll('.filter-btn');
const portfolioItems = document.querySelectorAll('.portfolio-item');

filterBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    filterBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const filter = btn.dataset.filter;
    portfolioItems.forEach(item => {
      if (filter === 'all' || item.dataset.cat === filter) {
        item.classList.remove('hidden');
      } else {
        item.classList.add('hidden');
      }
    });
  });
});

// ── LIGHTBOX
const lightbox = document.getElementById('lightbox');
const lbImg = document.getElementById('lb-img');
const lbClose = document.getElementById('lb-close');
const lbPrev = document.getElementById('lb-prev');
const lbNext = document.getElementById('lb-next');

let currentIdx = 0;
let visibleItems = [];

function openLightbox(idx) {
  visibleItems = [...document.querySelectorAll('.portfolio-item:not(.hidden)')];
  currentIdx = idx;
  showLbImage(currentIdx);
  lightbox.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeLightbox() {
  lightbox.classList.remove('open');
  document.body.style.overflow = '';
  lbImg.src = '';
}

function showLbImage(idx) {
  const item = visibleItems[idx];
  if (!item) return;
  const src = item.dataset.full || item.querySelector('img')?.src;
  if (src) lbImg.src = src;
  lbPrev.style.opacity = idx === 0 ? '0.3' : '1';
  lbNext.style.opacity = idx === visibleItems.length - 1 ? '0.3' : '1';
}

// Click en items
portfolioItems.forEach((item, i) => {
  item.addEventListener('click', () => {
    const visible = [...document.querySelectorAll('.portfolio-item:not(.hidden)')];
    const idx = visible.indexOf(item);
    openLightbox(idx);
  });
});

lbClose.addEventListener('click', closeLightbox);
lightbox.addEventListener('click', e => { if (e.target === lightbox || e.target === document.getElementById('lb-img-wrap')) closeLightbox(); });

lbPrev.addEventListener('click', e => {
  e.stopPropagation();
  if (currentIdx > 0) { currentIdx--; showLbImage(currentIdx); }
});
lbNext.addEventListener('click', e => {
  e.stopPropagation();
  if (currentIdx < visibleItems.length - 1) { currentIdx++; showLbImage(currentIdx); }
});

// Teclado
document.addEventListener('keydown', e => {
  if (!lightbox.classList.contains('open')) return;
  if (e.key === 'Escape') closeLightbox();
  if (e.key === 'ArrowLeft' && currentIdx > 0) { currentIdx--; showLbImage(currentIdx); }
  if (e.key === 'ArrowRight' && currentIdx < visibleItems.length - 1) { currentIdx++; showLbImage(currentIdx); }
});

// ── PROTECCIÓN DE IMÁGENES
document.addEventListener('contextmenu', e => e.preventDefault());

// ── SCROLL REVEAL
const revealEls = document.querySelectorAll(
  '#about .about-grid, #services .service-card, #portfolio .portfolio-item, #contact .contact-info, #contact .contact-form, .section-header'
);
revealEls.forEach(el => el.classList.add('reveal'));

const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

revealEls.forEach(el => revealObserver.observe(el));

// Stagger para service cards
document.querySelectorAll('.service-card').forEach((card, i) => {
  card.style.transitionDelay = `${i * 80}ms`;
});

// ── FORMULARIO DE CONTACTO
// ============================================================
// CONFIGURA: URL de script PHP en Hostinger
// ============================================================
const FORM_ENDPOINT = 'https://guillermoworks.com/mail.php'; // 

const contactForm = document.getElementById('contactForm');
const submitBtn = document.getElementById('submitBtn');
const btnText = submitBtn.querySelector('.btn-text');
const btnLoading = submitBtn.querySelector('.btn-loading');
const formMsg = document.getElementById('formMsg');

contactForm.addEventListener('submit', async (e) => {
  e.preventDefault();

  // Validación básica
  const nombre = contactForm.nombre.value.trim();
  const email = contactForm.email.value.trim();
  const mensaje = contactForm.mensaje.value.trim();

  if (!nombre || !email || !mensaje) {
    showMsg('Por favor llena los campos requeridos (*)', 'error');
    return;
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    showMsg('El email no es válido', 'error');
    return;
  }

  // Loading state
  btnText.hidden = true;
  btnLoading.hidden = false;
  submitBtn.disabled = true;

  const formData = {
    nombre,
    email,
    telefono: contactForm.telefono.value.trim(),
    servicio: contactForm.servicio.value,
    mensaje,
  };

  try {
    const res = await fetch(FORM_ENDPOINT, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData),
    });

    if (res.ok) {
      showMsg('¡Mensaje enviado! Te respondo pronto 📸', 'success');
      contactForm.reset();
    } else {
      throw new Error('Server error');
    }
  } catch (err) {
    showMsg('Hubo un error. Escríbeme directamente a tu@email.com', 'error');
  } finally {
    btnText.hidden = false;
    btnLoading.hidden = true;
    submitBtn.disabled = false;
  }
});

function showMsg(text, type) {
  formMsg.textContent = text;
  formMsg.className = `form-feedback ${type}`;
  formMsg.hidden = false;
  setTimeout(() => { formMsg.hidden = true; }, 5000);
}