import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

const menuToggle = document.querySelector('[data-menu-toggle]');
const menu = document.querySelector('[data-menu]');

menuToggle?.addEventListener('click', () => {
	menu?.classList.toggle('is-open');
	menuToggle.classList.toggle('is-open');
});

document.querySelectorAll('.filter').forEach((filter) => {
	filter.addEventListener('click', () => {
		document.querySelectorAll('.filter').forEach((item) => item.classList.remove('is-selected'));
		filter.classList.add('is-selected');
		const category = filter.dataset.filter;
		document.querySelectorAll('.service-card').forEach((card) => {
			card.hidden = category !== 'all' && card.dataset.category !== category;
		});
	});
});

document.querySelectorAll('[data-hero-carousel]').forEach((carousel) => {
	const slides = [...carousel.querySelectorAll('[data-carousel-slide]')];
	const indicators = [...carousel.querySelectorAll('[data-carousel-to]')];
	const captions = [...carousel.querySelectorAll('[data-carousel-caption]')];
	const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
	let activeIndex = 0;
	let timer;

	const showSlide = (index) => {
		activeIndex = (index + slides.length) % slides.length;
		slides.forEach((slide, slideIndex) => {
			const isActive = slideIndex === activeIndex;
			slide.classList.toggle('is-active', isActive);
			slide.setAttribute('aria-hidden', String(!isActive));
		});
		captions.forEach((caption) => {
			caption.classList.toggle('is-active', Number(caption.dataset.carouselCaption) === activeIndex);
		});
		indicators.forEach((indicator, indicatorIndex) => {
			const isActive = indicatorIndex === activeIndex;
			indicator.classList.toggle('is-active', isActive);
			indicator.setAttribute('aria-current', String(isActive));
		});
	};

	const stopAutoplay = () => window.clearInterval(timer);
	const startAutoplay = () => {
		stopAutoplay();
		if (!reducedMotion.matches && slides.length > 1) {
			timer = window.setInterval(() => showSlide(activeIndex + 1), 5000);
		}
	};

	carousel.querySelector('[data-carousel-previous]')?.addEventListener('click', () => {
		showSlide(activeIndex - 1);
		startAutoplay();
	});
	carousel.querySelector('[data-carousel-next]')?.addEventListener('click', () => {
		showSlide(activeIndex + 1);
		startAutoplay();
	});
	indicators.forEach((indicator) => {
		indicator.addEventListener('click', () => {
			showSlide(Number(indicator.dataset.carouselTo));
			startAutoplay();
		});
	});
	carousel.addEventListener('mouseenter', stopAutoplay);
	carousel.addEventListener('mouseleave', startAutoplay);
	carousel.addEventListener('focusin', stopAutoplay);
	carousel.addEventListener('focusout', (event) => {
		if (!carousel.contains(event.relatedTarget)) startAutoplay();
	});
	reducedMotion.addEventListener('change', startAutoplay);
	startAutoplay();
});

document.querySelector('[data-appointment-form]')?.addEventListener('submit', (event) => {
	event.preventDefault();
	event.currentTarget.querySelector('[data-form-success]').classList.add('is-visible');
	event.currentTarget.reset();
});
