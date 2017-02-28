<?php
/**
*
* Kontaktformular [Deutsch — Du]
*
* @package language
* @version $Id: kontakt.php$
* @copyright (c) 2011 phpBB.de
* @license Not for redistribution
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'KONTAKT_TITLE' => 'Kontaktformular',
	'KONTAKT_DISABLED' => 'Das Kontaktformular ist deaktiviert.',
	'KONTAKT_RULES' => '<strong>Achtung</strong>: Bitte dieses Formular für folgende Anfragen <strong>nicht</strong> benutzen:<br>
						<ul>
							<li>Supportanfragen aller Art - diese ausnahmslos im Forum stellen</li>
							<li>Regelverstöße von anderen Benutzern - hierzu bitte die Funktion "Beitrag melden" benutzen</li>
							<li>Fehler auf der Website phpBB.de (ausgenommen Sicherheitslücken) bitte im <a href="/community/viewtopic.php?f=19&t=239117">Sammel-Thread für Fehler in phpBB.de 3.1</a> melden</li>
							<li>Fehler in den deutschen Sprachdateien bitte im Thema <a href="/community/viewtopic.php?f=73&t=148593">Fehler in den deutschen Sprachpaketen</a> melden</li>
						</ul>',
	'KONTAKT_REPLY_LINK' => "_________________________\n" .
							"[url=%s]Diese Anfrage beantworten[/url]",
	'RETURN_UEBER_UNS'	=> '%sZurück zum Bereich "Über uns"%s',
	'KONTAKT_STORED'	=> 'Deine Kontaktanfrage wurde an das phpBB.de-Team weitergeleitet. Wir werden diese so schnell wie möglich bearbeiten und dir dann die Antwort per privater Nachricht (PN) zusenden.',
	'SUBTITLE_NEW' => 'Kontakt mit dem phpBB.de-Team aufnehmen',
	'SUBTITLE_REPLY' => 'Rückfrage zu einer vorigen Anfrage',
	'KONTAKT_USE_LOGIN_REQ' => 'Um Kontakt mit dem Team aufzunehmen, musst du eingeloggt sein',
	'NOT_AUTH_MOD_KONTAKT' => 'Du hast nicht die Berechtigung "Kann Kontaktformular-Anfragen beantworten"',
	'REPLIED' => 'Beantwortet',
	'KONTAKT_REPLY' => 'Kontaktformular-Anfrage beantworten',
	'KONTAKT_REPLY_CONFIRM' => 'Beim Beantworten der Anfrage wird dem Anfragenden Benutzer die Antwort per PN zugeschickt sowie eine Antwort im Thema erstellt.',
	'KONTAKT_REPLIED'  => 'Die Antwort wurde dem Benutzer zugeschickt und im Forum gepostet',
	'KONTAKT_REPLIED_ANONYMOUS'  => 'Die Antwort wurde im Forum gepostet (es wurde <strong>keine</strong> PN verschickt)',
	'KONTAKT_NOT_REPLIED' => 'Die Anfrage wurde nicht beantwortet',
	'KONTAKT_REPLIED_POST_SUBJECT' => 'Anfrage beantwortet',
	'KONTAKT_REPLIED_POST_TEXT' => 'Die Anfrage des Benutzers (Beitrag #%2$s) wurde beantwortet:[quote]%1$s[/quote]',
	'KONTAKT_REPLIED_ANONYMOUS_POST_SUBJECT' => 'Anfrage bearbeitet',
	'KONTAKT_REPLIED_ANONYMOUS_POST_TEXT' => 'Die Anfrage des Benutzers (Beitrag #%2$s) wurde bearbeitet:[quote]%1$s[/quote]Es wurde [b]keine[/b] PN verschickt, da der Autor der Anfrage der Gast-Benutzer ist',
	'KONTAKT_PM_REPLY_SUBJECT' => 'Re: Deine Anfrage an das phpBB.de-Team',
	'KONTAKT_PM_REPLY_TEXT' => 'Deine Anfrage an das phpBB.de-Team "%s"
								[quote]%s[/quote]
								wurde wie folgt beantwortet:
								[quote]%s[/quote]
								Falls deine Anfrage damit nicht vollständig beantwortet wurde, benutze bitte den folgenden Link, um eine Rückfrage zu starten: %s',
	'CANNOT_HANDLE_TWICE'	=> 'Die Anfrage kann nicht mehrfach beantwortet werden!',
	'REF_TOPIC' => 'Referenz-Anfrage',
	'INVALID_REF_TOPIC' => 'Ungültige Referenzanfrage',
	'REKLAMATION_POST_MESSAGE' => 'Hallo phpBB.de-Team,

die Sperrung/Löschung des Beitrages [url=%s]%s[/url] war unberechtigt.

Grund:
',
	'AUTHOR_ANONYMOUS_WARNING' => '<strong>Achtung:</strong> Der Autor der Anfrage ist der Gast-Benutzer. Die hier eingegebene Antwort wird daher nur im internen Thema gepostet und keine PN verschickt!',
));
