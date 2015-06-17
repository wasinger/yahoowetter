PHP-Bibliothek zur Anzeige von Wetterdaten von Yahoo
====================================================

Das Script holt Wetterdaten für einen konfigurierbaren Ort über die 
[Yahoo Wetter API](https://developer.yahoo.com/weather/documentation.html)
und zeigt sie als HTML-Daten an.

Das Verzeichnis `web` muss über den Webserver erreichbar sein, z.B. unter
http://beispiel.de/wetter. Das Skript `index.php` in diesem Verzeichnis gibt 
dann die Wetterdaten aus. Es ist dazu gedacht, per AJAX-Request in andere
HTML-Seiten eingebunden zu werden.

Die Konfiguration (z.B. für welchen Ort die Daten abgerufen werden sollen)
erfolgt in `config/config.php`. Einfach die mitgelieferte Datei
`config.dist.php` nach `config.php` kopieren und die Werte ändern.

Das Script `index.php` ist aber eigentlich auch nur eine Beispieldatei - die
Hauptfunktionen werden durch die Klassen im `lib`-Verzeichnis ausgeführt.
So können die Wetterdaten in beliebige PHP-Scripte eingebunden werden.