import 'lenis/dist/lenis.css';
import $ from 'jquery';
import Lenis from 'lenis';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import '../css/cekirdex-landing.css';

window.$ = window.jQuery = $;
gsap.registerPlugin(ScrollTrigger);

const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const isMobile = window.matchMedia('(max-width: 820px)').matches;

document.body.classList.add('ck-landing-active');

if (!prefersReduced) {
  const lenis = new Lenis({ duration: 1.08, smoothWheel: true, touchMultiplier: isMobile ? 1.2 : 1.5 });
  lenis.on('scroll', ScrollTrigger.update);
  gsap.ticker.add((time) => lenis.raf(time * 1000));
  gsap.ticker.lagSmoothing(0);
}

function setupReveal() {
  const nodes = document.querySelectorAll('.ck-reveal');
  if (!nodes.length) return;
  if (prefersReduced || isMobile) {
    nodes.forEach((n) => { n.style.opacity = '1'; n.style.transform = 'none'; });
    return;
  }
  nodes.forEach((el) => {
    gsap.fromTo(el, { opacity: 0, y: 20 }, {
      opacity: 1, y: 0, duration: 0.65, ease: 'power2.out',
      scrollTrigger: { trigger: el, start: 'top 88%', toggleActions: 'play none none reverse' },
    });
  });
}

function setupHeroParallax() {
  const hero = document.querySelector('#ck-hero');
  const bg = document.querySelector('[data-ck-hero-bg]');
  const beans = document.querySelectorAll('.ck-hero-bean');
  if (!hero || !bg || prefersReduced) return;

  gsap.to(bg, {
    yPercent: 8,
    scale: 1.05,
    ease: 'none',
    scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: 0.7 },
  });

  beans.forEach((bean, i) => {
    gsap.to(bean, {
      y: '+=12', x: i % 2 ? '+=5' : '-=5', rotate: i % 2 ? 6 : -6,
      duration: 2.4 + i * 0.2, repeat: -1, yoyo: true, ease: 'sine.inOut',
    });
  });
}

function setupPhoneFlow() {
  const root = document.querySelector('[data-ck-phone-root]');
  if (!root) return;
  const screens = root.querySelectorAll('[data-ck-phone-screen]');
  if (screens.length < 2) return;

  if (prefersReduced || isMobile) {
    screens.forEach((s, i) => s.style.opacity = i === 0 ? '1' : '0');
    return;
  }

  const tl = gsap.timeline({
    scrollTrigger: { trigger: root, start: 'top 75%', end: '+=1200', scrub: 0.6 },
  });

  screens.forEach((s, i) => {
    if (i === 0) return;
    tl.to(screens[i - 1], { opacity: 0, duration: 0.35 }, i * 0.85)
      .to(s, { opacity: 1, duration: 0.35 }, '<');
  });
}

function setupTilt() {
  if (prefersReduced || isMobile) return;
  const cards = document.querySelectorAll('[data-ck-tilt]');
  cards.forEach((card) => {
    card.addEventListener('mousemove', (e) => {
      const r = card.getBoundingClientRect();
      const x = (e.clientX - r.left) / r.width - 0.5;
      const y = (e.clientY - r.top) / r.height - 0.5;
      gsap.to(card, { rotateY: x * 7, rotateX: -y * 7, transformPerspective: 900, duration: 0.25 });
    });
    card.addEventListener('mouseleave', () => gsap.to(card, { rotateY: 0, rotateX: 0, duration: 0.4 }));
  });
}

$(() => {
  setupReveal();
  setupHeroParallax();
  setupPhoneFlow();
  setupTilt();
  ScrollTrigger.refresh();
});
