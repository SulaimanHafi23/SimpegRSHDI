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

	const logoutForms = document.querySelectorAll('form[action$="/logout"]');

	if (window.__logoutSwalHandlerBound) {
		return;
	}

	logoutForms.forEach((form) => {
		form.addEventListener('submit', (event) => {
			if (form.dataset.logoutConfirmed === 'true') {
				return;
			}

			event.preventDefault();

			if (window.Swal) {
				window.Swal.fire({
					title: 'Logout sekarang?',
					text: 'Sesi Anda akan diakhiri.',
					icon: 'question',
					showCancelButton: true,
					confirmButtonText: 'Ya, logout',
					cancelButtonText: 'Batal',
					reverseButtons: true,
				}).then((result) => {
					if (result.isConfirmed) {
						form.dataset.logoutConfirmed = 'true';
						form.submit();
					}
				});
				return;
			}

			const confirmed = window.confirm('Anda yakin ingin logout?');
			if (confirmed) {
				form.dataset.logoutConfirmed = 'true';
				form.submit();
			}
		});
	});

	window.__logoutSwalHandlerBound = true;
});
