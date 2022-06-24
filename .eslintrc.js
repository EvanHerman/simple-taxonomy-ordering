module.exports = {
	"extends": "eslint:recommended",
	"env": {
		"browser": true,
		"es2021": true
	},
	"ignorePatterns": ["lib/js/*.min.js"],
	"globals": {
		"lityScriptData": true,
		"jQuery": true
	},
	"parserOptions": {
		"ecmaVersion": "latest"
	},
	"rules": {
		"no-extra-boolean-cast": "off"
	}
}
