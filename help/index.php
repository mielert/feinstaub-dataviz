<html>
    <head>
        <title>Hilfe zum Feinstaubdiagramm</title>
        <meta charset="utf-8"/>
        <meta http-equiv="cache-control" content="max-age=0" />
        <meta http-equiv="cache-control" content="no-cache" />
        <meta http-equiv="expires" content="0" />
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <meta http-equiv="pragma" content="no-cache" />
		<script src="/feinstaub/js/jquery.min.js" type="text/javascript"></script>
		<script src="/feinstaub/js/jquery-ui.min.js" type="text/javascript"></script>
		<link rel="stylesheet" href="/feinstaub/js/jquery-ui.min.css" type="text/css" media="all">
		<link rel="stylesheet" href="/feinstaub/styles.css" type="text/css" media="all">
		<script>
		$( function() {
		  $( "#accordion" ).accordion({
			collapsible: true,
			heightStyle: "content"
		  });
		} );
		</script>
    </head>
    <body id="help">
		<div id="accordion">
			<h3>Feinstaub in Stuttgart</h3>
			<div>
				<p>Regelmäßig herrscht in Stuttgart <a href="https://www.stuttgart.de/feinstaubalarm/" target="_blank">Feinstaubalarm</a>. Dieser wird ausgelöst, wenn zu erwarten ist, dass die Luftbelastung durch <a href="https://de.wikipedia.org/wiki/Feinstaub" target="_blank">Feinstaub</a> (speziell die Partikelgröße PM10) aufgrund einer austauscharmen Wetterlage die gesetzliche Schwelle von 50 Mikrogramm pro Kubikmeter Luft (µg/m³) überschreitet. An maximal 35 Tagen pro Jahr darf dieser Wert die Schwelle überschreiten. Stuttgart, als dreckigste Stadt Deutschlands, erreicht die 35 Tage regelmäßig.</p>
				<p>Da die Stadt Stuttgart (bzw. die zuständige <a href="http://www.mnz.lubw.baden-wuerttemberg.de/messwerte/aktuell/statDEBW013.htm" target="_blank">Landesanstalt für Umwelt, Messungen und Naturschutz Baden-Württemberg, LUBW</a>) nur an wenigen Stellen misst, haben Bürgerinnen und Bürger im Rahmen eines Projekts von <a href="http://codefor.de/stuttgart/" target="_blank">OK Lab Stuttgart bzw. Code For Stuttgart</a> eigene Sensoren aufgestellt.</p>
				<p>Diese Sensoren schicken ihre Messdaten an <a href="http://luftdaten.info" target="_blank">luftdaten.info</a>.</p>
			</div>
			<h3>Was sehe ich hier?</h3>
			<div>
<?php if($_GET["context"]=="chart") { ?>
				<p>Im Diagramm werden die Messdaten, die die einzelnen Sensoren zu luftdaten.info geschickt haben, mathematisch und grafisch aufbereitet und mit offiziellen Daten der LUBW überlagert. Dabei werden nur Sensoren bedacht, die einerseits etwa im Stadtgebiet aufgestellt sind (näherungsweise wurde ein Rechteck um Stuttgart gelegt: Lat: 48.6-48.9, Lon: 8.5-9.5) und andererseits von einem bestimmten Typ sind (SDS011 des Herstellers Nova). Die Daten von luftdaten.info werden seit 17.11.2016 pro Sensor über jeweils fünf Minuten arithmetisch gemittelt (davor fand dieser Schritt nicht statt). Anschließend wird der <a href="https://de.wikipedia.org/wiki/Median" target="_blank">Median</a> gebildet (durchgezogene Linie). Hieraus wiederum wird ein arithmetisches Mittel der vergangenen 24 Stunden generiert (gestrichelte Linie), das eine Vergleichbarkeit mit den offiziellen Werten sicherstellen soll.</p>
				<p>Wenn die Streuung eingeblendet ist, zeigt die helle Fläche die komplette Streubreite der Messungen. Etwas dunkler ist der Bereich gehalten, in dem sich die mittleren 50% aller Messwerte befinden.</p>
				<p>Die Zahlen, die zu sehen sind, wenn sich die Maus über dem Diagramm befindet, sind folgendermaßen zu interpretieren:<br/>
				Beispiel: PM10: 65.5 µg/m³ (35.7 [S211] - 97.6 [S231]); ganz unten: 15.11., 07:17, 135 Werte<br/>
				Interpretation: Für die Partikelgröße PM10 wurden im Median in Stuttgart durch das Projekt 65,5 Mikrogramm pro Kubikmeter Luft gemessen. Dies wurde aus 135 Werten ermittelt, die im Mittel am 15. November 2016 um 7:17 Uhr erhoben wurden und sich von 35,7 µg/m³ beim Sensor 211 bis 97,6 µg/m³ beim Sensor 231 erstreckten. <!--Der Sensor 211 befindet sich am nord-westlichen Rand von Leonberg, der Sensor 231 in Bad Cannstatt.--></p>
<?php } ?>
<?php if($_GET["context"]=="map") { ?>
				<p>Abschnitt fehlt noch.</p>
<?php } ?>
<?php if($_GET["context"]=="districts") { ?>
				<p>Die Kurven zeigen P10-Feinstaubmessungen aus den Stuttgarter Stadtbezirken, die im Rahmen eines Projekts vom OK Lab Stuttgart vorgenommen wurden.</p>
				<p>Jede Kurve zeigt an jeder Stelle die über die vergangenen 24 Stunden geglättete Werte aus dem entsprechenden Bezirk. Dafür wird der Median zwischen den aktiven Sensoren und anschließend das arithmetische Mittel gebildet.</p>
<?php } ?>
			</div>
			<h3>Sind die Messwerte mit denen der Landesanstalt vergleichbar?</h3>
			<div>
				<p>Ja, die über 24 Stunden gemittelten P10-Werte sind mit denen der Landesanstalt vergleichbar.</p>
				<p>Zu beachten ist unbedingt, dass die Sensoren des Projekts weder geeicht noch irgendwie kalibriert sind. Insbesondre Ausschläge in der Streuung sind mit extremer Vorsicht zu genießen.</p>
			</div>
			<h3>Was bedeuten die Werte für mich?</h3>
			<div>
				<p>Die Werte sind schwer zu beurteilen. Wir brauchen alle noch Erfahrung mit ihrer Interpretation.</p>
			</div>
			<h3>Was sind das für Sensoren, wie funktionieren sie und wie bekomme ich einen?</h3>
			<div>
				<p>Die SDS011-Sensoren des Herstellers Nova saugen Luft ein. Mittels eines Laserstrahls wird dann die Lichtstreuung ermittelt, die Rückschlüsse auf die Partikelkonzentration zulassen. Gesteuert wird der Sensor über einen W-Lan-Chip (NodeMCU), der die Daten auch gleich an verschiedene Server schickt.</p>
				<p>Immer wieder werden im <a href="http://shackspace.de" target="_blank">Shackspace</a> Sensoren zusammengebaut. Wende Dich am besten an <a href="mailto:jan.lutz@buero-fuer-gestalten.de">Jan Lutz</a>.</p>
			</div>
			<h3>Ich habe einen Vorschlag zur Darstellung</h3>
			<div>
				<p>Gerne per E-Mail an <a href="mailto:fritz.mielert@gmx.de">fritz.mielert@gmx.de</a>.</p>
			</div>
			<h3>Darf ich die Visualisierungen auf anderen Websites benutzen?</h3>
			<div>
				<p>Ja, kein Problem. Die Diagramme (<a href="https://fritzmielert.de/feinstaub/chart" target="_blank">https://fritzmielert.de/feinstaub/chart</a> & <a href="https://fritzmielert.de/feinstaub/districts" target="_blank">https://fritzmielert.de/feinstaub/districts</a>) und die Karte (<a href="https://fritzmielert.de/feinstaub/map" target="_blank">https://fritzmielert.de/feinstaub/map</a>) dürfen auf Websites eingebettet werden. Ich bitte um Nachricht, wenn dies auf Websites passieren soll, von denen größere Zugriffszahlen zu erwarten sind.</p>
			</div>
			<h3>Datenherkunft, -schutz & Impressum</h3>
			<div>
				<p>Die gezeigten Daten werden mit freundlicher Genehmigung der <a href="http://www.mnz.lubw.baden-wuerttemberg.de/" target="_blank">Landesanstalt für Umwelt, Messungen und Naturschutz Baden-Württemberg</a> und <a href="http://luftdaten.info" target="_blank">luftdaten.info</a> hier gezeigt und mittels PHP und Javascript (inklusive der grandiosen Tools <a href="https://d3js.org"target="_blank">d3</a>, <a href="http://openlayers.org/" target="_blank">OpenLayers</a>, <a href="https://jquery.com/" target="_blank">jQuery</a> & <a href="http://jqueryui.com/" target="_blank">jQuery UI</a>) aufbereitet.</p>
				<p>Ich speichere nur das, was der Webserver automatisch erledigt. Hier gibt's weder Cookies noch (ab Version 2.4.0 des Diagramms) irgendwelche eingebetteten Dateien über die Dritte das Nutzungsverhalten aufzeichnen könnten. Dies bedingt allerdings ziemliche Ladezeiten.</p>
				<p>Natürlich übernehme ich keine Verantwortung für die Korrektheit meine Darstellungen und auch nicht für Links.</p>
				<p>Die Visualisierungen sind Creative Commons - Namensnennung (CC-BY) lizensiert.</p>
			</div>
		</div>
        <!--<div>
            <h2>Feinstaub in Stuttgart</h2>
            <h2>Was sehe ich hier?</h2>
            <h2>Sind die Messwerte mit denen der Landesanstalt vergleichbar?</h2>
            <h2>Warum sind die Graphen einzelner Sensoren so zerhackt?</h2>
            <p>Ich sammle deren Daten noch nicht allzu lange. Für die Vergangenheit greife ich auf die Daten von Zeitpunkten zurück, an denen diese Sensoren das Maximum lieferten. Dazwischen gibt's leider Lücken.</p>
            <p>Auch für vollständigere Graphen der Landesanstalt fehlen mir Daten. So liegen mir die Messwerte für die Station Neckartor erst seit dem 21.11.2016 vor und klammern jeweils Werte zwischen 21 Uhr und 6 Uhr aus.</p>
            <h2>Was bedeuten die Werte für mich?</h2>
            <p>Die Werte sind schwer zu beurteilen. Wir brauchen alle noch Erfahrung mit ihrer Interpretation.</p>
            <h2>Was zeigt die Rangliste genau?</h2>
            <p>Die Rangliste zeigt, welcher Sensor während der vergangenen sieben Tage wie häufig den Spitzenwert lieferte.</p>
            <p>Sensor 50, der absolute Spitzenreiter, sitzt in Wangen in direkter Nachbarschaft zur dortigen Baustelle von Stuttgart 21. Wahrscheinlich kommen die hohen Werte hiervon.</p>
            <p>Sensor 217 hockt im Wohngebiet der Friedenskiche (oberhalb des Neckartors).</p>
            <p>Sensor 231 ist in einer ruhigen Wohngegend in Cannstatt montiert. Wie er es auf Platz 3 schafft, ist ein Rätsel. Da aber Platz vier ebenfalls von einem Sensor aus Cannstatt belegt wird, scheint es nicht unbedingt an einem fehlerhaften Sensor zu liegen.</p>
        </div>-->
    </body>
</html>
