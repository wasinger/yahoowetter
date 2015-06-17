<?php
/*
	Configuration: copy ../config/config.dist.php to ../config/config.php
	and change the values
*/
// Read configuration from ../config/config.php
if (file_exists(__DIR__ . '/../config/config.php')) {
	require_once __DIR__ . '/../config/config.php';
} else {
	// default configuration
	require_once __DIR__ . '/../config/config.dist.php';
}


$libpath = __DIR__ .'/../lib/';

if (isset($_GET['lang']) && $_GET['lang'] == 'en') $lang = 'en';
else $lang = 'de';

$cachefile = __DIR__ . '/../cache/cache.'.$lang.'.html';

function send_headers($moddate) {
	header('Last-Modified: ' . date('r', $moddate));
	header('Cache-Control: public, max-age=600, s-maxage=600');
	header('Expires: ' . date('r', time() + 600));
}
function getHeaders() {
	$headers = array();
	foreach ($_SERVER as $k => $v)
	{
		if (substr($k, 0, 5) == "HTTP_")
		{
			$k = str_replace('_', ' ', substr($k, 5));
			$k = str_replace(' ', '-', ucwords(strtolower($k)));
			$headers[$k] = $v;
		}
	}
	return $headers;
}
function getIfModifiedSince() {
	$headers = getHeaders();
	if (isset($headers['If-Modified-Since']) && $headers['If-Modified-Since']) {
		return strtotime($headers['If-Modified-Since']);
	}
	return null;
}

if (file_exists($cachefile) && (time() - filemtime($cachefile) < 600)) {
	if (getIfModifiedSince() >= filemtime($cachefile)) {
		header('HTTP/1.0 304 Not Modified');
		exit;
	}
	send_headers(filemtime($cachefile));
	readfile($cachefile);
	exit;
}

require_once $libpath.'wa72WeatherData.php';
require_once $libpath.'wa72WeatherSourceYahoo.php';
require_once $libpath.'wa72WeatherDataTranslatorGerman.php';
if (isset($config_proxy)) wa72WeatherSourceYahoo::$proxy = $config_proxy;
if (isset($config_location)) wa72WeatherSourceYahoo::$default_location = $config_location;
if ($lang == 'de') {
	wa72WeatherData::setTranslatorClass('wa72WeatherDataTranslatorGerman');
}

$w = wa72WeatherSourceYahoo::getWeatherData();

// if getWeather() was not successful it returns null
// in this case, return $cachefile (if it exists) or nothing
if (!$w instanceof wa72WeatherData) {
	if (file_exists($cachefile)) {
		send_headers(filemtime($cachefile));
		readfile($cachefile);
	}
	else header("HTTP/1.0 503 Service Unavailable");
	exit;
}
$path = dirname($_SERVER['PHP_SELF']);
if ($path == '/') $path = '';
if ($path == '.') $path = '';
$pixgif = $path . '/pix.gif';
wa72WeatherSourceYahoo::$icon_url = $path . '/wicons.png';

function tr($string) {
	global $lang, $translations;
	if (isset($translations[$lang]) && isset($translations[$lang][$string])) {
		return $translations[$lang][$string];
	}
	else return $string;
}
$translations['en'] = array(
	'Wetterdaten' => ' Weather Data',
	'Feuchte' => 'Humidity',
    'SA' => 'Sunrise',
	'SU' => 'Sunset',
	'Vorhersage' => 'Forecast',
	'Datum' => 'Date',
	'Wetter' => 'Weather',
	'Höchsttemperatur' => 'High',
	'Tiefsttemperatur' => 'Low',
	'Quelle' => 'Source'
);

ob_start();
?>

<div class="wetterblock">
	<div class="wetterblock_basic">
		<div style="<?php echo wa72WeatherSourceYahoo::getIconCssCode($w->code) ?> float: left;">
			<img src="<?php echo $pixgif; ?>" style="width: 61px; height: 34px;"
				alt="<?php echo wa72WeatherData::tr($w->text) ?>"
				title="<?php echo wa72WeatherData::tr($w->text) ?>" />
		</div>
		<div class="temperature">
		<?php echo $w->getTemperature('°') ?>
		</div>
		<div class="wind">
		<?php echo $w->getWindDirectionPointOfCompass() ?>
		<?php echo $w->getWindSpeed() ?>
			km/h
		</div>
	</div>
	<div class="wetterblock_extended">
		<table class="wetterdaten">
			<caption>
				<?php echo tr('Wetterdaten') ?>
				<?php echo $w->getDatetime('d.m. H:i')?>
			</caption>
			<tr>
				<th><?php echo tr('Feuchte') ?>:</th>
				<td><?php echo $w->humidity ?>%</td>
			</tr>
			<tr>
				<th><?php echo tr('SA') ?>:</th>
				<td><?php echo $w->getSunrise() ?></td>
			</tr>
			<tr>
				<th><?php echo tr('SU') ?>:</th>
				<td><?php echo $w->getSunset() ?></td>
			</tr>
		</table>
		<table class="wettervorhersage">
			<caption><?php echo tr('Vorhersage') ?>:</caption>
			<thead>
				<tr>
					<th><?php echo tr('Datum') ?></th>
					<th><?php echo tr('Wetter') ?></th>
					<th><?php echo tr('Höchsttemperatur') ?></th>
					<th><?php echo tr('Tiefsttemperatur') ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($w->forecasts as $fc) :?>
				<tr>
					<td><?php echo $fc->getDate() ?></td>
					<td><div style="<?php echo wa72WeatherSourceYahoo::getIconCssCode($fc->code) ?>">
							<img src="<?php echo $pixgif; ?>"
								style="width: 61px; height: 34px;"
								alt="<?php echo wa72WeatherData::tr($fc->text) ?>"
								title="<?php echo wa72WeatherData::tr($fc->text) ?>" />
						</div></td>
					<td class="temp_high"><?php echo $fc->high ?>°C</td>
					<td class="temp_low"><?php echo $fc->low ?>°C</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p>
		<a href="https://www.yahoo.com/?ilc=401" target="_blank"><img src="<?php echo $path ?>/purple.png" width="134" height="29"/></a>
		</p>
	</div>
</div>
<?php 
send_headers(time());
$cont = ob_get_flush();
file_put_contents($cachefile, $cont);
