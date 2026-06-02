/**
 * Kaufnebenkosten- & Finanzierungsrechner
 * Vanilla JS, no jQuery dependency.
 */
(function () {
	'use strict';

	if (typeof dbwFinanceCalc === 'undefined') return;

	var data = dbwFinanceCalc;
	var i18n = data.i18n;
	var kaufpreis = parseFloat(data.kaufpreis) || 0;
	if (kaufpreis <= 0) return;

	// PLZ prefix → Bundesland mapping (first 1-2 digits)
	var PLZ_MAP = {
		'01': 'Sachsen', '02': 'Sachsen', '03': 'Brandenburg', '04': 'Sachsen',
		'06': 'Sachsen-Anhalt', '07': 'Thueringen', '08': 'Sachsen', '09': 'Sachsen',
		'10': 'Berlin', '12': 'Berlin', '13': 'Berlin', '14': 'Brandenburg',
		'15': 'Brandenburg', '16': 'Brandenburg', '17': 'Mecklenburg-Vorpommern',
		'18': 'Mecklenburg-Vorpommern', '19': 'Mecklenburg-Vorpommern',
		'20': 'Hamburg', '21': 'Niedersachsen', '22': 'Hamburg', '23': 'Schleswig-Holstein',
		'24': 'Schleswig-Holstein', '25': 'Schleswig-Holstein', '26': 'Niedersachsen',
		'27': 'Niedersachsen', '28': 'Bremen', '29': 'Niedersachsen',
		'30': 'Niedersachsen', '31': 'Niedersachsen', '32': 'Nordrhein-Westfalen',
		'33': 'Nordrhein-Westfalen', '34': 'Hessen', '35': 'Hessen',
		'36': 'Hessen', '37': 'Niedersachsen', '38': 'Niedersachsen', '39': 'Sachsen-Anhalt',
		'40': 'Nordrhein-Westfalen', '41': 'Nordrhein-Westfalen', '42': 'Nordrhein-Westfalen',
		'44': 'Nordrhein-Westfalen', '45': 'Nordrhein-Westfalen', '46': 'Nordrhein-Westfalen',
		'47': 'Nordrhein-Westfalen', '48': 'Nordrhein-Westfalen', '49': 'Niedersachsen',
		'50': 'Nordrhein-Westfalen', '51': 'Nordrhein-Westfalen', '52': 'Nordrhein-Westfalen',
		'53': 'Nordrhein-Westfalen', '54': 'Rheinland-Pfalz', '55': 'Rheinland-Pfalz',
		'56': 'Rheinland-Pfalz', '57': 'Nordrhein-Westfalen', '58': 'Nordrhein-Westfalen',
		'59': 'Nordrhein-Westfalen',
		'60': 'Hessen', '61': 'Hessen', '63': 'Hessen', '64': 'Hessen',
		'65': 'Hessen', '66': 'Saarland', '67': 'Rheinland-Pfalz',
		'68': 'Baden-Wuerttemberg', '69': 'Baden-Wuerttemberg',
		'70': 'Baden-Wuerttemberg', '71': 'Baden-Wuerttemberg', '72': 'Baden-Wuerttemberg',
		'73': 'Baden-Wuerttemberg', '74': 'Baden-Wuerttemberg', '75': 'Baden-Wuerttemberg',
		'76': 'Baden-Wuerttemberg', '77': 'Baden-Wuerttemberg', '78': 'Baden-Wuerttemberg',
		'79': 'Baden-Wuerttemberg',
		'80': 'Bayern', '81': 'Bayern', '82': 'Bayern', '83': 'Bayern',
		'84': 'Bayern', '85': 'Bayern', '86': 'Bayern', '87': 'Bayern',
		'88': 'Baden-Wuerttemberg', '89': 'Baden-Wuerttemberg',
		'90': 'Bayern', '91': 'Bayern', '92': 'Bayern', '93': 'Bayern',
		'94': 'Bayern', '95': 'Bayern', '96': 'Bayern', '97': 'Bayern',
		'98': 'Thueringen', '99': 'Thueringen'
	};

	// Grunderwerbsteuer rates by Bundesland
	var GEST_RATES = {
		'Baden-Wuerttemberg': 5.0,
		'Bayern': 3.5,
		'Berlin': 6.0,
		'Brandenburg': 6.5,
		'Bremen': 5.0,
		'Hamburg': 5.5,
		'Hessen': 6.0,
		'Mecklenburg-Vorpommern': 6.0,
		'Niedersachsen': 5.0,
		'Nordrhein-Westfalen': 6.5,
		'Rheinland-Pfalz': 5.0,
		'Saarland': 6.5,
		'Sachsen': 5.5,
		'Sachsen-Anhalt': 5.0,
		'Schleswig-Holstein': 6.5,
		'Thueringen': 5.0
	};

	// Display names with proper Umlaute
	var BUNDESLAND_DISPLAY = {
		'Baden-Wuerttemberg': 'Baden-Württemberg',
		'Mecklenburg-Vorpommern': 'Mecklenburg-Vorpommern',
		'Thueringen': 'Thüringen'
	};

	function getBundesland(plz) {
		var prefix = String(plz).substring(0, 2);
		return PLZ_MAP[prefix] || null;
	}

	function getGestRate(bundesland) {
		return bundesland ? (GEST_RATES[bundesland] || 5.0) : 5.0;
	}

	function getBundeslandDisplay(bundesland) {
		return BUNDESLAND_DISPLAY[bundesland] || bundesland;
	}

	function parseProvisionPercent(raw) {
		if (!raw) return 0;
		var match = String(raw).replace(',', '.').match(/([\d.]+)\s*%/);
		return match ? parseFloat(match[1]) : 0;
	}

	function formatCurrency(value) {
		return value.toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' \u20AC';
	}

	function formatPercent(value) {
		return value.toLocaleString('de-DE', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) + ' %';
	}

	// Element helper
	function el(id) {
		return document.getElementById(id);
	}

	function init() {
		var container = el('dbw-finance-calculator');
		if (!container) return;

		// Bundesland & rates
		var bundesland = getBundesland(data.plz);
		var gestRate = getGestRate(bundesland);
		var provisionPercent = parseProvisionPercent(data.provision);

		// Nebenkosten
		var gestAmount = kaufpreis * gestRate / 100;
		var notarAmount = kaufpreis * 1.5 / 100;
		var grundbuchAmount = kaufpreis * 0.5 / 100;
		var provisionAmount = kaufpreis * provisionPercent / 100;
		var gesamtkosten = kaufpreis + gestAmount + notarAmount + grundbuchAmount + provisionAmount;

		// Set labels from i18n
		el('dbw-calc-headline').textContent = i18n.headline;
		el('dbw-calc-label-kaufpreis').textContent = i18n.kaufpreis;
		el('dbw-calc-label-gest').textContent = i18n.grunderwerbsteuer;
		el('dbw-calc-label-notar').textContent = i18n.notarkosten;
		el('dbw-calc-label-grundbuch').textContent = i18n.grundbuchamt;
		el('dbw-calc-label-provision').textContent = i18n.maklerprovision;
		el('dbw-calc-label-gesamt').textContent = i18n.gesamtkosten;
		el('dbw-calc-label-ek').textContent = i18n.eigenkapital;
		el('dbw-calc-label-zins').textContent = i18n.zinssatz;
		el('dbw-calc-label-tilgung').textContent = i18n.tilgung;
		el('dbw-calc-label-darlehen').textContent = i18n.darlehenssumme;
		el('dbw-calc-label-rate').textContent = i18n.monatliche_rate;
		el('dbw-calc-label-zinskosten').textContent = i18n.zinskosten_10j;
		el('dbw-calc-fin-headline').textContent = i18n.finanzierung;
		el('dbw-calc-hinweis').textContent = i18n.hinweis;

		// GeSt detail
		if (bundesland) {
			el('dbw-calc-gest-detail').textContent = '(' + formatPercent(gestRate) + ' — ' + getBundeslandDisplay(bundesland) + ')';
		} else {
			el('dbw-calc-gest-detail').textContent = '(' + formatPercent(gestRate) + ' — ' + i18n.bundesland_unknown + ')';
		}

		// Provision detail & row visibility
		var provisionRow = el('dbw-calc-provision-row');
		if (provisionPercent > 0) {
			el('dbw-calc-provision-detail').textContent = '(' + formatPercent(provisionPercent) + ')';
			el('dbw-calc-provision').textContent = formatCurrency(provisionAmount);
		} else {
			provisionRow.hidden = true;
		}

		// Fill Nebenkosten table
		el('dbw-calc-kaufpreis').textContent = formatCurrency(kaufpreis);
		el('dbw-calc-gest').textContent = formatCurrency(gestAmount);
		el('dbw-calc-notar').textContent = formatCurrency(notarAmount);
		el('dbw-calc-grundbuch').textContent = formatCurrency(grundbuchAmount);
		el('dbw-calc-gesamt').textContent = formatCurrency(gesamtkosten);

		// Eigenkapital slider setup
		var ekSlider = el('dbw-calc-eigenkapital');
		ekSlider.max = Math.round(gesamtkosten);
		ekSlider.value = Math.round(gesamtkosten * 0.2);

		// Financing calculation
		function calculate() {
			var eigenkapital = parseFloat(ekSlider.value);
			var zinssatz = parseFloat(el('dbw-calc-zinssatz').value);
			var tilgung = parseFloat(el('dbw-calc-tilgung').value);

			var darlehen = Math.max(0, gesamtkosten - eigenkapital);
			var monatsrate = darlehen * (zinssatz + tilgung) / 100 / 12;

			// Simplified 10-year interest: sum monthly interest with declining balance
			var restschuld = darlehen;
			var totalZins = 0;
			for (var monat = 0; monat < 120; monat++) {
				var monatsZins = restschuld * zinssatz / 100 / 12;
				var monatsTilgung = monatsrate - monatsZins;
				if (monatsTilgung < 0) monatsTilgung = 0;
				totalZins += monatsZins;
				restschuld -= monatsTilgung;
				if (restschuld <= 0) {
					restschuld = 0;
					break;
				}
			}

			// Update outputs
			el('dbw-calc-ek-output').textContent = formatCurrency(eigenkapital);
			el('dbw-calc-zins-output').textContent = formatPercent(zinssatz);
			el('dbw-calc-tilgung-output').textContent = formatPercent(tilgung);

			el('dbw-calc-darlehen').textContent = formatCurrency(darlehen);
			el('dbw-calc-rate').textContent = formatCurrency(monatsrate);
			el('dbw-calc-zinskosten').textContent = formatCurrency(totalZins);

			// Update CSS custom property for slider fill
			updateSliderFill(ekSlider);
			updateSliderFill(el('dbw-calc-zinssatz'));
			updateSliderFill(el('dbw-calc-tilgung'));
		}

		function updateSliderFill(slider) {
			var min = parseFloat(slider.min);
			var max = parseFloat(slider.max);
			var val = parseFloat(slider.value);
			var percent = ((val - min) / (max - min)) * 100;
			slider.style.setProperty('--fill', percent + '%');
		}

		// Bind events
		var sliders = [ekSlider, el('dbw-calc-zinssatz'), el('dbw-calc-tilgung')];
		sliders.forEach(function (slider) {
			slider.addEventListener('input', calculate);
		});

		// Initial calculation
		calculate();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
