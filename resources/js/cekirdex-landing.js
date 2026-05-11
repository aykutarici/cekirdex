import '../css/cekirdex-landing.css';

if (!document.body.classList.contains('ck-landing-active')) {
  document.body.classList.add('ck-landing-active');
}

/** Hafif scroll reveal (Framer Motion yerine; React bağımlılığı yok) */
function initReveal() {
  const nodes = document.querySelectorAll('.ck-reveal:not(.ck-reveal--visible)');
  if (!nodes.length || !('IntersectionObserver' in window)) {
    nodes.forEach((el) => el.classList.add('ck-reveal--visible'));
    return;
  }

  const io = new IntersectionObserver(
    (entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('ck-reveal--visible');
        observer.unobserve(entry.target);
      });
    },
    { threshold: 0.08, rootMargin: '0px 0px -32px 0px' },
  );

  nodes.forEach((el) => io.observe(el));
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initReveal);
} else {
  initReveal();
}
