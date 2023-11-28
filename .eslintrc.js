module.exports = {
	extends: ['@10up/eslint-config/node', '@10up/eslint-config/jest'],
	rules: {
		'global-require': 'off',
		'import/no-dynamic-require': 'off',
		'no-process-exit': 'off',
	},
	globals: {
		module: true,
		process: true,
		jquery: true,
		wp: true,
		ajaxurl: true,
	},
};
