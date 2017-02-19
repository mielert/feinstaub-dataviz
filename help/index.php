<html>
    <head>
        <title>Hilfe zum Feinstaubdiagramm</title>
        <meta charset="utf-8"/>
        <meta http-equiv="cache-control" content="max-age=86400" />
		<script src="../js/jquery.min.js" type="text/javascript"></script>
		<script src="../js/jquery-ui.min.js" type="text/javascript"></script>
		<link rel="stylesheet" href="../js/jquery-ui.min.css" type="text/css" media="all">
		<link rel="stylesheet" href="../styles.css" type="text/css" media="all">
		<script>
		$( function() {
		  $( "#accordion" ).accordion({
			collapsible: true,
			heightStyle: "content",
			active: 1
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
				<p>Im Diagramm werden die Messdaten, die die einzelnen Sensoren zu luftdaten.info geschickt haben, mathematisch und grafisch aufbereitet und mit offiziellen Daten der LUBW überlagert. Dabei werden nur Sensoren bedacht, die einerseits im Stadtgebiet aufgestellt sind und andererseits von einem bestimmten Typ sind (SDS011 des Herstellers Nova). Die Daten von luftdaten.info als <a href="https://de.wikipedia.org/wiki/Median" target="_blank">Median</a> jeweils einer Stunde (durchgezogene Linie) und als 24-Stunden-Mittel (gleitender Mittelwert) aus diesem Median dargestellt (gestrichelte Linie). Mit dem 24-Stunden-Mittel soll eine Vergleichbarkeit mit den offiziellen Werten sicherstellen werden.</p>
				<p>Wenn die Streuung eingeblendet ist, zeigt die helle Fläche die komplette Streubreite der Messungen. Etwas dunkler ist der Bereich gehalten, in dem sich die mittleren 50% aller Messwerte befinden.</p>
				<p>Die Zahlen, die zu sehen sind, wenn sich die Maus über dem Diagramm befindet, sind folgendermaßen zu interpretieren:<br/>
				Beispiel: PM10: 65.5 µg/m³ (35.7 [S211] - 97.6 [S231]); ganz unten: 15.11., 07:17, 135 Werte<br/>
				Interpretation: Für die Partikelgröße PM10 wurden im Median in Stuttgart durch das Projekt 65,5 Mikrogramm pro Kubikmeter Luft gemessen. Dies wurde aus 135 Werten ermittelt, die im Mittel am 15. November 2016 um 7:17 Uhr erhoben wurden und sich von 35,7 µg/m³ beim Sensor 211 bis 97,6 µg/m³ beim Sensor 231 erstreckten.</p>
<?php } ?>
<?php if($_GET["context"]=="map") { ?>
				<p>Die Karte zeigt beim Laden die aktuelle Belastung in Stuttgart mit PM10-Feinstaubpartikeln. Die Flächen sind die 23 Stuttgarter Stadtbezirke, die Kreise stellen die momentan aktiven Sensoren dar. In den einzelnen Bezirken wird der Median aus den Sensorwerten des jeweiligen Bezirks gebildet. Eingefärbt sind die Bezirke und Sensoren nach dem Air Quality Index.</p>
				<p>Über die Einstellungen können Karte und Sensoren anhand einer anderen Farbskala eingefärbt und die Datengrundlage geändert werden.</p>
				<p>Befindet sich der Mauszeiger über einem Bezirk, werden dessen Werte angezeigt. Über einem Sensor tauchen zusätzlich dessen Werte auf. Ein Klick auf einen Sensor öffnet weitere Auswertungen auf einem anderen Server.</p>
<?php } ?>
<?php if($_GET["context"]=="districts") { ?>
				<p>Die Kurven zeigen PM10-Feinstaubmessungen aus den Stuttgarter Stadtbezirken, die im Rahmen eines Projekts vom OK Lab Stuttgart vorgenommen wurden.</p>
				<p>Jede Kurve zeigt an jeder Stelle die über die vergangenen 24 Stunden geglättete Werte aus dem entsprechenden Bezirk. Dafür wird jeweils der Median zwischen den aktiven Sensoren und anschließend das arithmetische Mittel über 24 Stunden gebildet (gleitender Mittelwert).</p>
				<p>Die eingeblendete Karte zeigt die Feinstaubbelastung in den jeweiligen Stadtbezirken zu einem bestimmten Zeitpunkt.</p>
				<p>Das Diagramm reagiert auf Mausbewegungen, kann vergrößert und verschoben werden.</p>
<?php } ?>
			</div>
			<h3>Was bedeuten PM10 und PM2.5?</h3>
			<div>
				<p>PM10 und PM2.5 sind Einteilungen für die Größe von Feinstaubpartikeln. Dabei gibt es keine Trennschärfe. Bei PM10 werden "Partikel mit einem aerodynamischen Durchmesser von weniger als 1 µm ... vollständig einbezogen, bei größeren Partikeln wird ein gewisser Prozentsatz gewertet, der mit zunehmender Partikelgröße abnimmt und bei ca. 15 µm schließlich 0 % erreicht." (Wikipedia). PM2.5 bezieht sich auf lungengängige Partikel, d.h. Feinstaubteilchen können in die Lungenbläschen eindringen und gelangen somit in den Blutkreislauf. Seine "Definition ist analog zu PM10, allerdings ist die Gewichtungsfunktion wesentlich steiler (100 % Gewichtung < 0,5 µm; 0 % Gewichtung > 3,5 µm; 50 % Gewichtung bei ca. 2,5 µm)." (Wikipedia)</p>
			</div>
			<h3>Was bedeuten die Farben in den Karten?</h3>
			<div>
				<p>Die Farben in den Karten sind einzelnen Luftwerten zugeordnet. Sie geben an, wie gut die Luft ist.</p>
				<p>Bisher kommt eine eigene Farbskala und der Air Quality Index (AQI) in der US-amerikainschen Version zum Einsatz. In Planung ist die Implementierung des Kurzzeit-Luftqualitätsindex LuQx der Landesanstalt. Hier fehlen allerdings Angaben zur Beurteilung von PM2.5-Partikeln.</p>
				<p><strong>Eigene Farbskala Grün-Rot-Pink</strong><br/>Die Farbskala besteht aus einem kontinuierlichen Farbverlauf von 0 µg/m<sup>3</sup>(Grün) bis 50 µg/m<sup>3</sup> (Rot) und einem weiteren kontinuierlichen Farbverlauf von 50 µg/m<sup>3</sup>(Rot) bis 200 µg/m<sup>3</sup> (Pink).</p>
				<p><strong>Air Quality Index (USA)</strong>
<table style="text-align:left;font-size: 100%;width: 100%;">
<tbody><tr align="center">
<td><strong>PM<sub>2.5</sub> (µg/m<sup>3</sup>)</strong></td>
<td><strong>PM<sub>10</sub> (µg/m<sup>3</sup>)</strong></td>
<td><strong>AQI</strong></td>
</tr>
<tr align="center">
<td>0.0-12.0</td>
<td>0-54</td>
<td style="background:#00e400;">gut</td>
</tr>
<tr align="center">
<td>12.1-35.4</td>
<td>55-154</td>
<td style="background:#ff0;">befriedigend</td>
</tr>
<tr align="center">
<td>35.5-55.4</td>
<td>155-254</td>
<td style="background:#ff7e00;">ungesund für empfindliche Personen</td>
</tr>
<tr align="center">
<td>55.5-150.4</td>
<td>255-354</td>
<td style="background:#f00; color:#fff;">ungesund</td>
</tr>
<tr align="center">
<td>150.5-250.4</td>
<td>355-424</td>
<td style="background:#99004c; color:#fff;">sehr ungesund</td>
</tr>
<tr align="center">
<td>> 250.5</td>
<td>> 425</td>
<td style="background:#7e0023; color:#fff;">gesundheitsgefährdend</td>
</tr>
</tbody></table>
				Angaben gelten jeweils für ein 24-Stunden-Mittel.<br/>
				Quelle: <a href="https://en.wikipedia.org/wiki/Air_quality_index#Computing_the_AQI" target="_blank">Wikipedia</a>
				</p>
				<p><strong>Kurzzeit-Luftqualitätsindex LuQx</strong>
<table style="text-align:left;font-size: 100%;width: 100%;">
<tr align="center">
<td><strong>PM<sub>10</sub> (µg/m<sup>3</sup>)</strong></td>
<td><strong>Bewertung</strong></td></tr><tbody>
<tr align="center">
<td>0 - 10</td>
<td style="background-color: #3499ff">sehr gut</td></tr>
<tr align="center">
<td>&gt;&nbsp;10 - 20</td>
<h3>Was bedeuten PM10 und PM2.5?</h3>
<div>

PM10 und PM2.5 sind Einteilungen für die Größe von Feinstaubpartikeln. Dabei gibt es keine Trennschärfe. Bei PM10 werden "Partikel mit einem aerodynamischen Durchmesser von weniger als 1 µm ... vollständig einbezogen, bei größeren Partikeln wird ein gewisser Prozentsatz gewertet, der mit zunehmender Partikelgröße abnimmt und bei ca. 15 µm schließlich 0 % erreicht." (<a href="https://de.wikipedia.org/wiki/Feinstaub" target="_blank">Wikipedia</a>). PM2.5 bezieht sich auf lungengängige Partikel, d.h. Feinstaubteilchen können in die Lungenbläschen eindringen und gelangen somit in den Blutkreislauf. Seine "Definition ist analog zu PM10, allerdings ist die Gewichtungsfunktion wesentlich steiler (100 % Gewichtung &lt; 0,5 µm; 0 % Gewichtung &gt; 3,5 µm; 50 % Gewichtung bei ca. 2,5 µm)." (<a href="https://de.wikipedia.org/wiki/Feinstaub" target="_blank">Wikipedia</a>)

</div>
<h3>Was bedeuten die Farben in den Karten?</h3>
<div>

Die Farben in den Karten sind einzelnen Luftwerten zugeordnet. Sie geben an, wie gut die Luft ist.

Bisher kommt eine eigene Farbskala und der Air Quality Index (AQI) in der US-amerikainschen Version zum Einsatz. In Planung ist die Implementierung des Kurzzeit-Luftqualitätsindex LuQx der Landesanstalt. Hier fehlen allerdings Angaben zur Beurteilung von PM2.5-Partikeln.

<strong>Eigene Farbskala Grün-Rot-Pink</strong>
Die Farbskala besteht aus einem kontinuierlichen Farbverlauf von 0 µg/m<sup>3</sup>(Grün) bis 50 µg/m<sup>3</sup> (Rot) und einem weiteren kontinuierlichen Farbverlauf von 50 µg/m<sup>3</sup>(Rot) bis 200 µg/m<sup>3</sup> (Pink).

<strong>Air Quality Index (USA)</strong>
<table style="text-align: left; font-size: 100%; width: 100%;">
<tbody>
<tr align="center">
<td><strong>PM<sub>2.5</sub> (µg/m<sup>3</sup>)</strong></td>
<td><strong>PM<sub>10</sub> (µg/m<sup>3</sup>)</strong></td>
<td><strong>AQI</strong></td>
</tr>
<tr align="center">
<td>0.0-12.0</td>
<td>0-54</td>
<td style="background: #00e400;">gut</td>
</tr>
<tr align="center">
<td>12.1-35.4</td>
<td>55-154</td>
<td style="background: #ff0;">befriedigend</td>
</tr>
<tr align="center">
<td>35.5-55.4</td>
<td>155-254</td>
<td style="background: #ff7e00;">ungesund für empfindliche Personen</td>
</tr>
<tr align="center">
<td>55.5-150.4</td>
<td>255-354</td>
<td style="background: #f00; color: #fff;">ungesund</td>
</tr>
<tr align="center">
<td>150.5-250.4</td>
<td>355-424</td>
<td style="background: #99004c; color: #fff;">sehr ungesund</td>
</tr>
<tr align="center">
<td>&gt; 250.5</td>
<td>&gt; 425</td>
<td style="background: #7e0023; color: #fff;">gesundheitsgefährdend</td>
</tr>
</tbody>
</table>
Angaben gelten jeweils für ein 24-Stunden-Mittel.
Quelle: <a href="https://en.wikipedia.org/wiki/Air_quality_index#Computing_the_AQI" target="_blank">Wikipedia</a>

<strong>Kurzzeit-Luftqualitätsindex LuQx</strong>
<table style="text-align: left; font-size: 100%; width: 100%;">
<tbody>
<tr align="center">
<td><strong>PM<sub>10</sub> (µg/m<sup>3</sup>)</strong></td>
<td><strong>Bewertung</strong></td>
</tr>
</tbody>
<tbody>
<tr align="center">
<td>0 - 10</td>
<td style="background-color: #3499ff;">sehr gut</td>
</tr>
<tr align="center">
<td>&gt; 10 - 20</td>
<td style="background-color: #67ccff;">gut</td>
</tr>
<tr align="center">
<td>&gt; 20 - 35</td>
<td style="background-color: #99ffff;">befriedigend</td>
</tr>
<tr align="center">
<td>&gt; 35 - 50</td>
<td style="background-color: #ffff99;">ausreichend</td>
</tr>
<tr align="center">
<td>&gt; 50 - 100</td>
<td style="background-color: #ff9934;">schlecht</td>
</tr>
<tr align="center">
<td>&gt; 100</td>
<td style="background-color: #ff3434;">sehr schlecht</td>
</tr>
</tbody>
</table>
Angaben gelten jeweils für ein 24-Stunden-Mittel.
Quelle: <a href="http://www4.lubw.baden-wuerttemberg.de/servlet/is/20152/" target="_blank">LUBW</a>

</div>
<h3>Sind die Messwerte mit denen der Landesanstalt für Umwelt, Messungen und Naturschutz Baden-Württemberg (LUBW) vergleichbar?</h3>
<div>

Ja, die über 24 Stunden gemittelten PM10-Werte sind mit denen der Landesanstalt vergleichbar.

Zu beachten ist unbedingt, dass die Sensoren des Projekts weder geeicht noch irgendwie kalibriert sind. Insbesondre starke Ausschläge einzelner Sensoren sind mit extremer Vorsicht zu genießen. Auch scheint die Luftfeuchtigkeit einen Einfluss zu haben (siehe <a href="https://feinstaub-stuttgart.info/2017/01/fehlerquellen-beim-messen-von-feinstaub/" target="_blank">Fehlerquellen beim Messen von Feinstaub</a>).

</div>
<h3>Was bedeuten die Werte für mich?</h3>
<div>

Die Werte sind schwer zu beurteilen. Wir brauchen alle noch Erfahrung mit ihrer Interpretation.

</div>
<h3>Was sind das für Sensoren und wie funktionieren sie?</h3>
<div>

Die SDS011-Sensoren des Herstellers Nova saugen Luft ein. Mittels eines Laserstrahls wird dann die Lichtstreuung ermittelt, die Rückschlüsse auf die Partikelkonzentration zulassen. Gesteuert wird der Sensor über einen W-Lan-Chip (NodeMCU), der die Daten auch gleich an verschiedene Server schickt.
<h3>Ich will bei mir zuhause oder am Arbeitsplatz eigene Messungen vornehmen. Wie komme ich an einen Feinstaubsensor?</h3>
Eine ausführliche Anleitung findet sich unter <a href="http://luftdaten.info/feinstaubsensor-bauen/" target="_blank">luftdaten.info</a>. Immer wieder werden im <a href="http://shackspace.de" target="_blank">Shackspace</a> gemeinsam Sensoren zusammengebaut. Wende Dich am besten an <a href="mailto:jan.lutz@buero-fuer-gestalten.de">Jan Lutz</a>.

</div>
<h3>Ich habe Verbesserungsvorschläge für die Diagramme. Wie kann ich mich einbringen?</h3>
<div>

Der <a href="https://github.com/mielert/feinstaub-dataviz" target="_blank">Code der Diagramme</a> liegt komplett auf Github. Dort findet sich auch eine <a href="https://github.com/mielert/feinstaub-dataviz/issues" target="_blank">Möglichkeit, Fehler und Anregungen zu hinterlassen</a>. Allerdings ist dafür ein Account bei Github notwendig. Es hilft aber auch eine E-Mail an <a href="mailto:fritz.mielert@gmx.de">fritz.mielert@gmx.de</a> weiter.

</div>
<h3>Darf ich die Visualisierungen auf anderen Websites benutzen?</h3>
<div>

Ja, kein Problem. Die Diagramme (<a href="https://feinstaub-stuttgart.info/dataviz/chart" target="_blank">https://feinstaub-stuttgart.info/dataviz/chart</a> &amp; <a href="https://feinstaub-stuttgart.info/dataviz/districts" target="_blank">https://feinstaub-stuttgart.info/dataviz/districts</a>) und die Karte (<a href="https://feinstaub-stuttgart.info/dataviz/map" target="_blank">https://feinstaub-stuttgart.info/dataviz/map</a>) dürfen auf Websites eingebettet werden. Ich bitte um Nachricht, wenn dies auf Websites passieren soll, von denen größere Zugriffszahlen zu erwarten sind.

</div>
<h3>Datenherkunft</h3>
<div>

Die gezeigten Daten werden mit freundlicher Genehmigung der <a href="http://www.mnz.lubw.baden-wuerttemberg.de/" target="_blank">Landesanstalt für Umwelt, Messungen und Naturschutz Baden-Württemberg (LUBW)</a> und <a href="http://luftdaten.info" target="_blank">luftdaten.info</a> hier gezeigt und mittels PHP und Javascript (inklusive der grandiosen Tools <a href="https://d3js.org" target="_blank">d3</a>, <a href="http://openlayers.org/" target="_blank">OpenLayers</a>, <a href="https://jquery.com/" target="_blank">jQuery</a> &amp; <a href="http://jqueryui.com/" target="_blank">jQuery UI</a>) aufbereitet.

</div>
<h3>Ich will selber Daten visualisieren. Wie finde ich einen Einstieg?</h3>
<div>

Die erste Frage, die sich stellt, ist die nach dem gewünschten Ergebnis. Soll eine Karte, ein Liniendiagramm oder eine ganz andere Darstellung entstehen? Was mit d3 möglich ist, können Sie den <a href="https://github.com/d3/d3/wiki/Gallery" target="_blank">Beispielen</a> entnehmen. Auch für OpenLayers gibt es eine, leider nicht ganz so nett aufbereitete, <a href="http://openlayers.org/en/latest/examples/" target="_blank">Beispielsammlung</a>. Haben Sie diese Entscheidung gefällt, greifen Sie sich am besten in einem ersten Schritt die bei mir auf dem Server vorhandenen Daten ab und experimentieren mit diesen. Hierfür suchen Sie entweder im Quellcode meiner Visualisierungen nach "<a href="https://de.wikipedia.org/wiki/JavaScript_Object_Notation" target="_blank">JSON</a>" (Geodaten) oder "<a href="https://de.wikipedia.org/wiki/CSV_(Dateiformat)" target="_blank">TSV</a>" (tabellarische Daten).

<strong>Momentan sind folgende Dateien verfügbar:</strong>
<table style="text-align:left;font-size: 100%;width: 100%;">
<tbody>
<tr>
<td><strong>Datei</strong></td>
<td><strong>Beschreibung</strong></td>
<td style="text-align: center;"><strong>Nutzung</strong></td>
</tr>
<tr>
<td><a href="/dataviz/data/chronological_city_1.tsv" target="_blank">chronological_city_1.tsv</a></td>
<td>Destillat der OK Lab-Messwerte aus Stuttgart seit September 2016</td>
<td style="text-align: center;"> keine</td>
</tr>
<tr>
<td><a href="/dataviz/data/chronological_city_1_week.tsv" target="_blank">chronological_city_1_week.tsv</a></td>
<td>Destillat der OK Lab-Messwerte aus Stuttgart der vergangenen sieben Tage</td>
<td style="text-align: center;">Chart</td>
</tr>
<tr>
<td><a href="/dataviz/data/chronological_data_lubw.tsv" target="_blank">chronological_data_lubw.tsv</a></td>
<td>Daten der LUBW-Sensoren am Neckartor und Bad Cannstatt seit Beginn meiner Aufzeichnung</td>
<td style="text-align: center;">Chart &amp; Districts</td>
</tr>
<tr>
<td><a href="/dataviz/data/chronological_districts_v2_complete.tsv" target="_blank">chronological_districts_v2_complete.tsv</a></td>
<td>Feinstaub-Messwerte auf Bezirksebene seit September 2016</td>
<td style="text-align: center;">keine</td>
</tr>
<tr>
<td><a href="/dataviz/data/chronological_districts_v2_simple.tsv" target="_blank">chronological_districts_v2_simple.tsv</a></td>
<td>Feinstaub-Messwerte auf Bezirksebene seit September 2016 (reduziert auf 24h-Mittelwert PM10)</td>
<td style="text-align: center;">Districts</td>
</tr>
<tr>
<td><a href="/dataviz/data/stuttgart_districts_v2.json" target="_blank">stuttgart_districts_v2.json</a></td>
<td>Geodaten Stuttgarter Bezirke inkl. aktueller Mess- und sonstiger Daten des OK Lab</td>
<td style="text-align: center;">Map</td>
</tr>
<tr>
<td><a href="/dataviz/data/stuttgart_districts.json" target="_blank">stuttgart_districts.json</a></td>
<td>Geodaten Stuttgarter Bezirke</td>
<td style="text-align: center;">Districts</td>
</tr>
<tr>
<td><a href="/dataviz/data/stuttgart_sensors_v2.json" target="_blank">stuttgart_sensors_v2.json</a></td>
<td>Geodaten Stuttgarter OK Lab-Sensoren inkl. aktueller Messdaten</td>
<td style="text-align: center;">Map</td>
</tr>
</tbody>
</table>
</div>
<h3>Wie werden die Daten aufbereitet?</h3>
<div><img class="alignleft wp-image-109 size-thumbnail" src="https://feinstaub-stuttgart.info/wp-content/uploads/2017/02/flow_chart_cropped-150x150.png" width="150" height="150" />Ich lese regelmäßig von <a href="http://api.luftdaten.info" target="_blank">luftdaten.info</a> und <a href="http://mnz.lubw.baden-wuerttemberg.de" target="_blank">mnz.lubw.baden-wuerttemberg.de</a> Daten ein und schreibe diese in MySQL-Tabellen. Die Rohdaten umfassten Mitte Februar 2017 etwa 12 Millionen Zeilen. Von dort aus werden sie in mehreren Schritten durch Zuordnung zu Regionen, Mittelwert- und Medianbildungen aufbereitet und schließlich als tsv- und json-Dateien für die Diagramme ausgegeben. Ein grobes Flussdiagramm findet sich unter <a href="/dataviz/flow_chart.pdf" target="_blank">/dataviz/flow-chart.pdf</a>.</div>
<h3>Datenschutz &amp; Impressum</h3>
<div>

Ich speichere nur das, was der Webserver automatisch erledigt. Hier gibt's weder Cookies noch irgendwelche eingebetteten Dateien über die Dritte das Nutzungsverhalten aufzeichnen könnten. Dies bedingt allerdings ziemliche Ladezeiten.Natürlich übernehme ich keine Verantwortung für die Korrektheit meine Darstellungen und auch nicht für Links.Die Visualisierungen sind <a href="https://creativecommons.org/licenses/by/4.0/deed.de" target="_blank">Creative Commons - Namensnennung (CC-BY)</a> lizensiert.

Kontaktmöglichkeiten &amp; co. finden Sie im <a href="https://feinstaub-stuttgart.info/impressum/" target="_blank">ausführlichen Impressum</a>.

</div>
		</div>
    </body>
</html>
