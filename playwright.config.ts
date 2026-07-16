import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
	testDir: './tests',
	fullyParallel: false,
	workers: 1,
	reporter: [['html', { open: 'never' }], ['list']],
	use: {
		baseURL: process.env.PNS_BASE_URL || 'http://localhost:10008',
		screenshot: 'only-on-failure',
		trace: 'retain-on-failure',
	},
	projects: [
		{
			name: 'desktop',
			use: {
				...devices['Desktop Chrome'],
				viewport: { width: 1440, height: 1000 },
			},
		},
		{
			name: 'tablet',
			grepInvert: /@desktop-only/,
			use: {
				...devices['Desktop Chrome'],
				viewport: { width: 900, height: 1000 },
			},
		},
		{
			name: 'mobile',
			grepInvert: /@desktop-only/,
			use: {
				...devices['Desktop Chrome'],
				isMobile: true,
				viewport: { width: 390, height: 900 },
			},
		},
	],
});
