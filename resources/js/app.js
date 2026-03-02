import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
	const tables = document.querySelectorAll('main table');

	tables.forEach((table) => {
		const parent = table.parentElement;
		if (!parent) {
			return;
		}

		const alreadyWrapped =
			parent.classList.contains('overflow-x-auto') ||
			parent.classList.contains('table-responsive');

		if (alreadyWrapped) {
			return;
		}

		const wrapper = document.createElement('div');
		wrapper.className = 'table-responsive';
		parent.insertBefore(wrapper, table);
		wrapper.appendChild(table);
	});
});
