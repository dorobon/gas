import './bootstrap';

const THEME_STORAGE_KEY = 'gas-theme';
const root = document.documentElement;
const themeColorMeta = document.querySelector('meta[name="theme-color"]');
const darkPreference = window.matchMedia('(prefers-color-scheme: dark)');

window.gasFormatPrice = (value) => {
	const parsed = Number(value);

	if (Number.isNaN(parsed)) {
		return 'Sin dato';
	}

	return `${new Intl.NumberFormat('es-ES', {
		minimumFractionDigits: 3,
		maximumFractionDigits: 3,
	}).format(parsed)} €/l`;
};

const getStoredTheme = () => {
	try {
		return localStorage.getItem(THEME_STORAGE_KEY);
	} catch {
		return null;
	}
};

const getPreferredTheme = () => {
	const storedTheme = getStoredTheme();

	if (storedTheme === 'light' || storedTheme === 'dark') {
		return storedTheme;
	}

	return darkPreference.matches ? 'dark' : 'light';
};

const getThemeColor = (theme) => (theme === 'dark' ? '#07111f' : '#f4f7fb');

const applyTheme = (theme) => {
	root.classList.toggle('dark', theme === 'dark');
	root.dataset.theme = theme;
	root.style.colorScheme = theme;

	if (themeColorMeta) {
		themeColorMeta.setAttribute('content', getThemeColor(theme));
	}

	document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
		button.setAttribute('aria-pressed', String(theme === 'dark'));

		const icon = button.querySelector('[data-theme-icon]');
		const label = button.querySelector('[data-theme-label]');

		if (icon) {
			icon.textContent = theme === 'dark' ? '☀️' : '🌙';
		}

		if (label) {
			label.textContent = theme === 'dark' ? 'Modo claro' : 'Modo oscuro';
		}
	});

	document.dispatchEvent(new CustomEvent('gas:theme-change', { detail: { theme } }));
};

const persistTheme = (theme) => {
	try {
		localStorage.setItem(THEME_STORAGE_KEY, theme);
	} catch {
		// Ignorado: el sitio funciona igualmente sin persistencia.
	}
};

const initThemeToggle = () => {
	document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
		button.addEventListener('click', () => {
			const nextTheme = root.classList.contains('dark') ? 'light' : 'dark';

			persistTheme(nextTheme);
			applyTheme(nextTheme);
		});
	});
};

const initBarHeights = () => {
	document.querySelectorAll('[data-bar-height]').forEach((element) => {
		element.style.height = `${element.dataset.barHeight}px`;
	});
};

let chartLoader;
let chartInstances = [];

const loadChart = async () => {
	if (!chartLoader) {
		chartLoader = import('chart.js/auto');
	}

	return chartLoader;
};

const getChartTheme = () => {
	const styles = getComputedStyle(root);

	return {
		muted: styles.getPropertyValue('--muted').trim() || '#64748b',
		grid: styles.getPropertyValue('--chart-grid').trim() || 'rgba(100, 116, 139, 0.2)',
		tooltipBg: styles.getPropertyValue('--chart-tooltip-bg').trim() || '#0f172a',
		tooltipText: styles.getPropertyValue('--text-strong').trim() || '#f8fafc',
	};
};

const destroyCharts = () => {
	chartInstances.forEach((chart) => chart.destroy());
	chartInstances = [];
};

const initCharts = async () => {
	const canvases = [...document.querySelectorAll('canvas[data-gas-chart]')];

	if (!canvases.length) {
		return;
	}

	const { default: Chart } = await loadChart();
	const chartTheme = getChartTheme();

	destroyCharts();

	canvases.forEach((canvas) => {
		const dataNode = document.getElementById(canvas.dataset.gasChart || '');

		if (!dataNode) {
			return;
		}

		let chartData;

		try {
			chartData = JSON.parse(dataNode.textContent || '{}');
		} catch {
			return;
		}

		const instance = new Chart(canvas.getContext('2d'), {
			type: 'line',
			data: chartData,
			options: {
				responsive: true,
				maintainAspectRatio: false,
				interaction: {
					mode: 'index',
					intersect: false,
				},
				plugins: {
					legend: {
						labels: {
							color: chartTheme.muted,
							usePointStyle: true,
							pointStyle: 'circle',
						},
					},
					tooltip: {
						backgroundColor: chartTheme.tooltipBg,
						titleColor: chartTheme.tooltipText,
						bodyColor: chartTheme.tooltipText,
						callbacks: {
							label: (context) => `${context.dataset.label}: ${window.gasFormatPrice(context.parsed.y)}`,
						},
					},
				},
				scales: {
					x: {
						ticks: { color: chartTheme.muted },
						grid: { color: chartTheme.grid },
					},
					y: {
						ticks: {
							color: chartTheme.muted,
							callback: (value) => window.gasFormatPrice(value),
						},
						grid: { color: chartTheme.grid },
					},
				},
			},
		});

		chartInstances.push(instance);
	});
};

document.addEventListener('DOMContentLoaded', () => {
	applyTheme(getPreferredTheme());
	initThemeToggle();
	initBarHeights();
	initCharts();
});

darkPreference.addEventListener('change', (event) => {
	const storedTheme = getStoredTheme();

	if (storedTheme !== 'light' && storedTheme !== 'dark') {
		applyTheme(event.matches ? 'dark' : 'light');
	}
});

document.addEventListener('gas:theme-change', () => {
	if (chartInstances.length) {
		initCharts();
	}
});
