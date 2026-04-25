@once
<script>
    (() => {
        if (window.__responsiveTableCardsInitialized) {
            return;
        }

        window.__responsiveTableCardsInitialized = true;

        const mobileQuery = window.matchMedia('(max-width: 767px)');

        const escapeHtml = (value) => {
            const element = document.createElement('div');
            element.textContent = value ?? '';
            return element.innerHTML;
        };

        const buildCard = (headers, row) => {
            const card = document.createElement('article');
            card.className = 'rounded-2xl border border-gray-200 bg-white p-4 shadow-sm';

            const cells = Array.from(row.querySelectorAll('td'));
            const titleCell = cells[0];

            if (titleCell) {
                const title = document.createElement('div');
                title.className = 'mb-3 flex items-start justify-between gap-3';
                title.innerHTML = `
                    <div class="min-w-0 text-sm font-semibold text-gray-900">
                        ${titleCell.innerHTML}
                    </div>
                    <div class="shrink-0 rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-500">
                        Item
                    </div>
                `;
                card.appendChild(title);
            }

            const details = document.createElement('dl');
            details.className = 'space-y-3';

            cells.forEach((cell, index) => {
                if (index === 0) {
                    return;
                }

                const label = headers[index] ? escapeHtml(headers[index]) : `Kolom ${index + 1}`;
                const item = document.createElement('div');
                item.className = 'grid gap-1 border-t border-gray-100 pt-3 sm:grid-cols-3 sm:gap-4';
                item.innerHTML = `
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 sm:pt-1">${label}</dt>
                    <dd class="text-sm text-gray-900 sm:col-span-2">${cell.innerHTML}</dd>
                `;
                details.appendChild(item);
            });

            if (cells.length === 1) {
                const onlyCell = document.createElement('div');
                onlyCell.className = 'text-sm text-gray-900';
                onlyCell.innerHTML = titleCell ? titleCell.innerHTML : '';
                card.appendChild(onlyCell);
            } else {
                card.appendChild(details);
            }

            return card;
        };

        const syncResponsiveTables = () => {
            document.querySelectorAll('[data-responsive-table]').forEach((container) => {
                const table = container.querySelector('[data-responsive-table-table]');
                const mobileContainer = container.querySelector('[data-responsive-table-mobile]');

                if (!table || !mobileContainer) {
                    return;
                }

                if (!mobileQuery.matches) {
                    table.classList.remove('hidden');
                    mobileContainer.classList.add('hidden');
                    mobileContainer.innerHTML = '';
                    return;
                }

                const headerCells = Array.from(table.querySelectorAll('thead th')).map((header) => header.textContent.trim());
                const rows = Array.from(table.querySelectorAll('tbody tr'));

                mobileContainer.innerHTML = '';
                rows.forEach((row) => {
                    if (row.hasAttribute('data-no-mobile-card')) {
                        return;
                    }

                    mobileContainer.appendChild(buildCard(headerCells, row));
                });

                table.classList.add('hidden');
                mobileContainer.classList.remove('hidden');
            });
        };

        document.addEventListener('DOMContentLoaded', syncResponsiveTables);
        mobileQuery.addEventListener('change', syncResponsiveTables);
    })();
</script>
@endonce
