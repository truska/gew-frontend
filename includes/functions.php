<!-- START functions.php ww-->
<?php

function loadPrefs($conn) {
	static $prefs = null;

	if (is_array($prefs)) {
		return $prefs;
	}

	$cmsPrefsPath = __DIR__ . '/lib/cms_prefs.php';
	if (file_exists($cmsPrefsPath)) {
		require_once $cmsPrefsPath;
	}

	$prefs = [];
	if ($conn instanceof mysqli) {
		$queryprefs = false;
		$table = 'cms_preferences';
		$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'cms_preferences'");
		if ($tableCheck instanceof mysqli_result && mysqli_fetch_row($tableCheck)) {
			$columns = [];
			$columnQuery = mysqli_query($conn, "SHOW COLUMNS FROM `{$table}`");
			if ($columnQuery instanceof mysqli_result) {
				while ($columnRow = mysqli_fetch_assoc($columnQuery)) {
					$field = (string) ($columnRow['Field'] ?? '');
					if ($field !== '') {
						$columns[$field] = true;
					}
				}
			}

			$sql = "SELECT `name`, `value` FROM `{$table}`";
			$where = [];
			if (isset($columns['archived'])) {
				$where[] = "`archived` = 0";
			}
			if ($where !== []) {
				$sql .= " WHERE " . implode(' AND ', $where);
			}

			$order = [];
			if (isset($columns['sort'])) {
				$order[] = "`sort` ASC";
			}
			if (isset($columns['id'])) {
				$order[] = "`id` ASC";
			}
			if ($order !== []) {
				$sql .= " ORDER BY " . implode(', ', $order);
			}

			$queryprefs = mysqli_query($conn, $sql);
		}

		if ($queryprefs instanceof mysqli_result) {
			while ($rowprefs = mysqli_fetch_assoc($queryprefs)) {
				$name = (string) ($rowprefs['name'] ?? '');
				if ($name === '') {
					continue;
				}
				$prefs[$name] = $rowprefs['value'] ?? '';
			}
		}
	}

	if ($prefs === [] && function_exists('cms_preferences_table')) {
		global $pdo, $DB_OK;

		$table = cms_preferences_table();
		if ($table && !empty($DB_OK) && ($pdo instanceof PDO)) {
			try {
				$columnsStmt = $pdo->query("SHOW COLUMNS FROM `{$table}`");
				$columns = [];
				foreach (($columnsStmt ? $columnsStmt->fetchAll(PDO::FETCH_ASSOC) : []) as $columnRow) {
					$field = (string) ($columnRow['Field'] ?? '');
					if ($field !== '') {
						$columns[$field] = true;
					}
				}

				$sql = "SELECT `name`, `value` FROM `{$table}`";
				$where = [];
				if (isset($columns['archived'])) {
					$where[] = "`archived` = 0";
				}
				if ($where !== []) {
					$sql .= " WHERE " . implode(' AND ', $where);
				}

				$order = [];
				if (isset($columns['sort'])) {
					$order[] = "`sort` ASC";
				}
				if (isset($columns['id'])) {
					$order[] = "`id` ASC";
				}
				if ($order !== []) {
					$sql .= " ORDER BY " . implode(', ', $order);
				}

				$stmt = $pdo->query($sql);
				$rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
				foreach ($rows as $row) {
					$name = (string) ($row['name'] ?? '');
					if ($name === '') {
						continue;
					}
					$prefs[$name] = $row['value'] ?? '';
				}
			} catch (Throwable $e) {
				$prefs = [];
			}
		}
	}

	return $prefs;
	}
?>
<!-- End loadprefs -->
<?php
function normalizeBrandDisplay($value) {
	$value = (string) $value;
	if ($value === '') {
		return $value;
	}

	return preg_replace('/(?<!@)\b(?:MS (?:Tradewood|Timber)|MSTradewood)\b(?!@|\.com\b)/i', 'MS TRADEWOOD', $value);
}

function getCompanyName($prefs) {
		return normalizeBrandDisplay(prefValue($prefs, 'prefCompanyName'));
	}
function getSiteName($prefs) {
			return prefValue($prefs, 'prefSiteName');
	}

function prefValue($prefs, $key, $default = '') {
	$value = null;

	if (function_exists('cms_pref')) {
		$cmsValue = cms_pref($key, null);
		if ($cmsValue !== null && $cmsValue !== '') {
			$value = $cmsValue;
		}
	}

	if (($value === null || $value === '') && is_array($prefs) && array_key_exists($key, $prefs) && $prefs[$key] !== null && $prefs[$key] !== '') {
		$value = $prefs[$key];
	}

	if ($value === null) {
		$value = $default;
	}

	if (is_string($value)) {
		return trim($value);
	}

	return $value;
}

function prefAddressParts($prefs, $prefix = 'pref') {
	$keys = [
		$prefix . 'Address1',
		$prefix . 'Address2',
		$prefix . 'Address3',
		$prefix . 'Town',
		$prefix . 'County',
		$prefix . 'Country',
		$prefix . 'Postcode',
	];
	$parts = [];

	foreach ($keys as $key) {
		$value = prefValue($prefs, $key, '');
		if ($value !== '') {
			$parts[] = $value;
		}
	}

	return $parts;
}

function  getAddressLong($prefs) {
		return implode(', ', prefAddressParts($prefs));  
	}
function  getAddressShort($prefs) {
		return implode("<br>", prefAddressParts($prefs));  
	}
function  getAddressList($prefs) {
		$prefsAddressList = '';
		foreach (prefAddressParts($prefs) as $part) {
			$prefsAddressList = $prefsAddressList . "<li>" . $part . "</li>";
		}

		return $prefsAddressList;  
	}
function  getAddressShortList($prefs) {
			$prefsAddressShortList = '';

			$prefsAddressShortList = $prefsAddressShortList  . "<li><strong>Head Office</strong></li>" ;
		foreach (prefAddressParts($prefs) as $part) {
			$prefsAddressShortList = $prefsAddressShortList . "<li>" . $part . "</li>";
		}

		return $prefsAddressShortList;  
	}
function  getAddressNameShortList($prefs) {
			$prefsAddressNameShortList = '';

			if (prefValue($prefs, 'prefCompanyName') !== ''){$prefsAddressNameShortList = $prefsAddressNameShortList  . "<li>" . prefValue($prefs, 'prefCompanyName') . "</li>" ;}
		foreach (prefAddressParts($prefs) as $part) {
			$prefsAddressNameShortList = $prefsAddressNameShortList . "<li>" . $part . "</li>";
		}

		return $prefsAddressNameShortList;  
	}

function getAddessLong($prefs) {
		return getAddressLong($prefs);
	}

function getEmail($prefs) {
		return prefValue($prefs, 'prefEmail');
	}
function getTel1($prefs) {
		return prefValue($prefs, 'prefTel1');
	}
function getTel1Int($prefs) {
		return getTel1Display($prefs);
	}
function getTel2($prefs) {
		return prefValue($prefs, 'prefTel2');
	}
function getTel2Int($prefs) {
		return getTel2Display($prefs);
	}
function getFax($prefs) {
		return prefValue($prefs, 'prefFax');
	}
function getGoogleMap($prefs) {
		return prefValue($prefs, 'prefGoogleMap');
	}
function getLogo($prefs) {
		return "filestore/images/logos/" . prefValue($prefs, 'prefLogo');
	}
function getTagline($prefs) {
		return prefValue($prefs, 'prefTagline');
	}

function getTelData($prefs, $telKey = 'prefTel1', $intCodeKey = 'prefTelIntCode') {
	$display = prefValue($prefs, $telKey, '');
	$intl = prefValue($prefs, $intCodeKey, '');
	$displayTrim = ltrim($display);
	$isInternational = ($displayTrim !== '' && $displayTrim[0] === '+');
	$dial = '';
	$displayIntl = $display;

	if ($display !== '') {
		$dial = preg_replace('/[^0-9+]/', '', $display);
		if (!$isInternational && $intl !== '') {
			$trimmedLocal = ltrim($display);
			if ($trimmedLocal !== '' && $trimmedLocal[0] === '0') {
				$trimmedLocal = substr($trimmedLocal, 1);
			}
			$displayIntl = rtrim($intl) . ' ' . ltrim($trimmedLocal);
			$dial = preg_replace('/[^0-9+]/', '', $intl . $trimmedLocal);
		}
	}

	return [
		'display' => trim($displayIntl),
		'dial' => $dial,
	];
}

function getTel1Display($prefs) {
		$data = getTelData($prefs, 'prefTel1');
		return $data['display'];
	}

function getTel1Dial($prefs) {
		$data = getTelData($prefs, 'prefTel1');
		return $data['dial'];
	}

function getTel2Display($prefs) {
		$data = getTelData($prefs, 'prefTel2');
		return $data['display'];
	}

function getTel2Dial($prefs) {
		$data = getTelData($prefs, 'prefTel2');
		return $data['dial'];
	}
?>
<!-- End loadprefs specifics -->
<?php
// Shop Preferences
function loadShopPrefs($conn) {
	$selectshopprefs = "SELECT `name`, `value` FROM `preferences_shop` ORDER BY `prefCat` ";
	$queryshopprefs = mysqli_query($conn,$selectshopprefs);
	while ($rowshopprefs = mysqli_fetch_assoc($queryshopprefs) )
		{
			$prefshop[$rowshopprefs["name"]] = $rowshopprefs["value"];
		}
		return $prefshop;
	}
// ??? Is this needed
function getPrefix($prefshop) {
		return $prefshop['prefFile'];
	}
//

function loadShopText($conn) {
	$selectshoptext = "SELECT `name`, `text` FROM `shoptext` ORDER BY `id` ";
	$queryshoptext = mysqli_query($conn,$selectshoptext);
	while ($rowshoptext = mysqli_fetch_assoc($queryshoptext) )
		{
			$preftext[$rowshoptext["name"]] = $rowshoptext["text"];
		}
		return $preftext;
	}

?>
<!-- End shop -->
<?php

// Shop Modules
function loadModulePrefs($conn) {
	$selectmoduleprefs = "SELECT `name`, `value` FROM `preferences_modules` ORDER BY `prefCat` ";
	$querymoduleprefs = mysqli_query($conn,$selectmoduleprefs);
	while ($rowmoduleprefs = mysqli_fetch_assoc($querymoduleprefs) )
		{
			$prefmodule[$rowmoduleprefs["name"]] = $rowmoduleprefs["value"];
		}
		return $prefmodule;
	}
?>
<!-- End shop mudules -->
<?php
/* General Functions*/

function valid_email($email) {
  return filter_var((string)$email, FILTER_VALIDATE_EMAIL) !== false;
}

function flipdate($dt, $seperator_in, $seperator_out)
	{
	return implode($seperator_out, array_reverse(explode($seperator_in, $dt)));
	}
?>

<!-- END functions.php -->
