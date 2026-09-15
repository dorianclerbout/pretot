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

document.querySelector('[data-appointment-form]')?.addEventListener('submit', (event) => {
	event.preventDefault();
	event.currentTarget.querySelector('[data-form-success]').classList.add('is-visible');
	event.currentTarget.reset();
});
