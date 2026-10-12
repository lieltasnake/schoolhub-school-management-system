const sidebar = document.querySelector('#school-sidebar');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const sidebarClose = document.querySelector('[data-sidebar-close]');

function closeSidebar() {
	sidebar?.classList.remove('is-open');
	sidebarToggle?.setAttribute('aria-expanded', 'false');
	document.body.classList.remove('navigation-open');
}

sidebarToggle?.addEventListener('click', () => {
	const isOpen = sidebar?.classList.toggle('is-open') ?? false;
	sidebarToggle.setAttribute('aria-expanded', String(isOpen));
	document.body.classList.toggle('navigation-open', isOpen);
});

sidebarClose?.addEventListener('click', closeSidebar);

document.addEventListener('keydown', (event) => {
	if (event.key === 'Escape') {
		closeSidebar();
	}
});

sidebar?.querySelectorAll('.nav-link').forEach((link) => {
	link.addEventListener('click', closeSidebar);
});

document.querySelector('.branch-switcher select')?.addEventListener('change', (event) => {
	event.currentTarget.form?.requestSubmit();
});
