# VerbrauchsAlarm
Das Modul dient dazu einen unnatürlich Verbrauch festzustellen. Es reagiert auf eine Zählervariable und schaltet unter bestimmten Bedingungen einen Alarm.
Es gibt zwei Zustandsvariablen.  
Einen Großverbrauch-Zustand, welcher schaltet wenn ein einstellbarer Grenzwert überschritten wird.
Einen Kleinverbrauch-Zustand, welcher in 7 Stufen hochtickt wenn über längeren Zeitraum einen eingestellter Grenzwert (z.B. tropfender Wasserhahn) überschritten wird.
Das Intervall für beide Kontrollen kann über die Konfiguration eingestellt werden.
Ein Alarm, welche schaltet wenn der Großverbrauch-Zustand auf Alarm geschaltet ist, oder der Kleinverbrauch-Alarm einen eingestellten Wert überschreitet. 

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Auswahl der Zählervariable
* Groß-/Kleinverbrauch Timer in Minuten einstellbar
* Groß-/Kleinverbrauch Grenzwert einstellbar
* 7 Stufen Anzeige für Kleinverbrauch
* Alarmanzeige bei Großverbrauch

### 2. Vorraussetzungen

- IP-Symcon ab Version 6.0

### 3. Software-Installation

* Über den Module Store das Modul Verbraucher-Alarm installieren.
* Alternativ über das Module Control folgende URL hinzufügen:
`https://github.com/symcon/VerbrauchsAlarm`

### 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' kann das 'VerbrauchsAlarm'-Modul mithilfe des Schnellfilters gefunden werden.  
	- Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

__Konfigurationsseite__:

Name                     | Beschreibung
------------------------ | ---------------------------------
Zählervariable           | Variable, welche den Zählerwert wiedergibt.
Alarmsauslöser           | __Standard: 6__ Kleinverbrauchsstufe bei dem der Alarm ausgelöst wird.
Kleinverbrauch Intervall | __Standard: 1min__ Zeitintervall in dem kontrolliert wird, ob der Verbrauch zu hoch ist ist.
Kleinverbrauch Grenzwert | __Standart: 0__ Grenzwert bei dem der Zustand geändert wird. Ist dieser nicht gesetzt wird nicht darauf geprüft.
Großverbrauch Intervall  | __Standard: 5min__ Zeitintervall in dem kontrolliert wird, ob der Verbrauch zu hoch ist.
Großverbrauch Grenzwert  | __Standart: 0__ Grenzwert bei dem der Zustand geändert wird.Ist dieser nicht gesetzt wird nicht darauf geprüft.

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

#### Statusvariablen

Name                   | Typ     | Beschreibung
---------------------- | ------- | ----------------
Alarmbenachrichtigung  | Boolean | Alarm, wenn Großverbrauch geschaltet oder Kleinverbrauch den Wert überschreitet. 
Kleinverbrauch Zustand | Integer | 7 Stufenanzeige für den Stand den Alarmlevels.
Großverbrauch Zustand  | Boolean | Alarm ob der Durchfluss zu hoch ist. 

##### Profile:

Bezeichnung        | Beschreibung
------------------ | -----------------
VBA.LeakLevel      | Profil für Kleinverbrauch - 7 Alarmstufen mit verschiedenen Symbolen und Farbanzeigen
VBA.ThresholdValue | Profil für Klein-/Großverbrauch Grenzwert

Aufschlüsselung VBA.LeakLevel
Stufe                   | Wert 
----------------------- | ----------
Keine Aktivität         | 0
Alles im grünen Bereich | 1
Normale Aktivität       | 2
Erhöte Aktivität        | 3
Unnormale Aktivität     | 4
Vor-Alarm               | 5 
Alarm                   | 6 

### 6. WebFront

Über das WebFront können die Grenzwerte eingestellt werden.  
Es wird zusätzlich angezeigt, ob ein Alarm vorliegt oder nicht.

### 7. PHP-Befehlsreferenz

`boolean VBA_CheckAlert(integer $InstanzID, string $BorderValue, string $OldValue);`
Kontrolliert innerhalb des VerbraucherAlarms mit der InstanzID $InstanzID ob ein Grenzwert überschritten wurde und setzt die Alarmvariablen  
Die Funktion liefert keinerlei Rückgabewert.  
`VBA_CheckAlert(12345, "SmallUserThreashold", "SmallUserBuffer");`

`void VBA_UpdateSuffix(int $meterID);`
Führt ein UpdateFormfield() für den Suffix und Anzahl der Nachkommastellen der Grenzwerte aus. 
Die Funktion liefert keinerlei Rückgabewert
`VBA_UpdateSuffix(12345);`
