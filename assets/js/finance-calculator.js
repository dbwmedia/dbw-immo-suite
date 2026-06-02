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

	// Configurable rates from backend settings
	var NOTAR_RATE = parseFloat(data.notarPercent) || 1.5;
	var GRUNDBUCH_RATE = parseFloat(data.grundbuchPercent) || 0.5;
	var DEFAULT_ZINSSATZ = parseFloat(data.defaultZinssatz) || 3.5;
	var DEFAULT_TILGUNG = parseFloat(data.defaultTilgung) || 2.0;
	var GEST_OVERRIDE = parseFloat(data.gestOverride) || 0;

	// PLZ prefix -> Bundesland mapping (first 2 digits)
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

	var GEST_RATES = {
		'Baden-Wuerttemberg': 5.0, 'Bayern': 3.5, 'Berlin': 6.0,
		'Brandenburg': 6.5, 'Bremen': 5.0, 'Hamburg': 5.5, 'Hessen': 6.0,
		'Mecklenburg-Vorpommern': 6.0, 'Niedersachsen': 5.0,
		'Nordrhein-Westfalen': 6.5, 'Rheinland-Pfalz': 5.0, 'Saarland': 6.5,
		'Sachsen': 5.5, 'Sachsen-Anhalt': 5.0, 'Schleswig-Holstein': 6.5,
		'Thueringen': 5.0
	};

	var BUNDESLAND_DISPLAY = {
		'Baden-Wuerttemberg': 'Baden-W\u00FCrttemberg',
		'Thueringen': 'Th\u00FCringen'
	};

	function getBundesland(plz) {
		return PLZ_MAP[String(plz).substring(0, 2)] || null;
	}

	function getGestRate(bundesland) {
		if (GEST_OVERRIDE > 0) return GEST_OVERRIDE;
		return bundesland ? (GEST_RATES[bundesland] || 5.0) : 5.0;
	}

	function getBundeslandDisplay(bl) {
		return BUNDESLAND_DISPLAY[bl] || bl;
	}

	function parseProvisionPercent(raw) {
		if (!raw) return { value: 0, display: '' };
		var str = String(raw);
		var match = str.replace(',', '.').match(/([\d.]+)\s*%/);
		if (!match) return { value: 0, display: '' };
		// Keep original display string (e.g. "3,57 %") from the raw input
		var displayMatch = str.match(/([\d,.]+)\s*%/);
		var display = displayMatch ? displayMatch[1].replace('.', ',') + '\u00A0%' : fmtPct(parseFloat(match[1]));
		return { value: parseFloat(match[1]), display: display };
	}

	function fmtCur(v) {
		return v.toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' \u20AC';
	}

	function fmtPct(v) {
		return v.toLocaleString('de-DE', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) + '\u00A0%';
	}

	function el(id) {
		return document.getElementById(id);
	}

	function init() {
		var container = el('dbw-finance-calculator');
		if (!container) return;

		var bundesland = getBundesland(data.plz);
		var gestRate = getGestRate(bundesland);
		var provision = parseProvisionPercent(data.provision);

		// Nebenkosten
		var gestAmount = kaufpreis * gestRate / 100;
		var notarAmount = kaufpreis * NOTAR_RATE / 100;
		var grundbuchAmount = kaufpreis * GRUNDBUCH_RATE / 100;
		var provisionAmount = kaufpreis * provision.value / 100;
		var gesamtkosten = kaufpreis + gestAmount + notarAmount + grundbuchAmount + provisionAmount;

		// Labels
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

		// Detail percentages
		if (bundesland) {
			el('dbw-calc-gest-detail').textContent = '(' + fmtPct(gestRate) + ' \u2014 ' + getBundeslandDisplay(bundesland) + ')';
		} else if (GEST_OVERRIDE > 0) {
			el('dbw-calc-gest-detail').textContent = '(' + fmtPct(gestRate) + ')';
		} else {
			el('dbw-calc-gest-detail').textContent = '(' + fmtPct(gestRate) + ' \u2014 ' + i18n.bundesland_unknown + ')';
		}
		el('dbw-calc-notar-detail').textContent = '(' + fmtPct(NOTAR_RATE) + ')';
		el('dbw-calc-grundbuch-detail').textContent = '(' + fmtPct(GRUNDBUCH_RATE) + ')';

		// Provision
		var provisionRow = el('dbw-calc-provision-row');
		if (provision.value > 0) {
			el('dbw-calc-provision-detail').textContent = '(' + provision.display + ')';
			el('dbw-calc-provision').textContent = fmtCur(provisionAmount);
		} else {
			provisionRow.hidden = true;
		}

		// Fill values
		el('dbw-calc-kaufpreis').textContent = fmtCur(kaufpreis);
		el('dbw-calc-gest').textContent = fmtCur(gestAmount);
		el('dbw-calc-notar').textContent = fmtCur(notarAmount);
		el('dbw-calc-grundbuch').textContent = fmtCur(grundbuchAmount);
		el('dbw-calc-gesamt').textContent = fmtCur(gesamtkosten);

		// Slider setup with backend defaults
		var ekSlider = el('dbw-calc-eigenkapital');
		var zinsSlider = el('dbw-calc-zinssatz');
		var tilgungSlider = el('dbw-calc-tilgung');

		ekSlider.max = Math.round(gesamtkosten);
		ekSlider.value = Math.round(gesamtkosten * 0.2);
		zinsSlider.value = DEFAULT_ZINSSATZ;
		tilgungSlider.value = DEFAULT_TILGUNG;

		function calculate() {
			var eigenkapital = parseFloat(ekSlider.value);
			var zinssatz = parseFloat(zinsSlider.value);
			var tilgung = parseFloat(tilgungSlider.value);

			var darlehen = Math.max(0, gesamtkosten - eigenkapital);
			var monatsrate = darlehen * (zinssatz + tilgung) / 100 / 12;

			// 10-year amortization
			var restschuld = darlehen;
			var totalZins = 0;
			for (var m = 0; m < 120; m++) {
				var mZins = restschuld * zinssatz / 100 / 12;
				var mTilg = monatsrate - mZins;
				if (mTilg < 0) mTilg = 0;
				totalZins += mZins;
				restschuld -= mTilg;
				if (restschuld <= 0) break;
			}

			el('dbw-calc-ek-output').textContent = fmtCur(eigenkapital);
			el('dbw-calc-zins-output').textContent = fmtPct(zinssatz);
			el('dbw-calc-tilgung-output').textContent = fmtPct(tilgung);

			el('dbw-calc-darlehen').textContent = fmtCur(darlehen);
			el('dbw-calc-rate').textContent = fmtCur(monatsrate);
			el('dbw-calc-zinskosten').textContent = fmtCur(totalZins);

			updateFill(ekSlider);
			updateFill(zinsSlider);
			updateFill(tilgungSlider);
		}

		function updateFill(s) {
			var pct = ((s.value - s.min) / (s.max - s.min)) * 100;
			s.style.setProperty('--fill', pct + '%');
		}

		[ekSlider, zinsSlider, tilgungSlider].forEach(function (s) {
			s.addEventListener('input', calculate);
		});

		calculate();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
