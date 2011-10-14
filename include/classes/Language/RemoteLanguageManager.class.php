<?php
/************************************************************************/
/* AContent                                                             */
/************************************************************************/
/* Copyright (c) 2010                                                   */
/* Inclusive Design Institute                                           */
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/

/**
* RemoteLanguageManager
* Class for managing available languages as Language Objects.
* @access	public
* @author	Joel Kronenberg
* @see		Language.class.php
* @package	Language
*/

require_once(TR_INCLUDE_PATH.'classes/Language/LanguageParser.class.php');
require_once(TR_INCLUDE_PATH.'classes/Language/LanguagesParser.class.php');

class RemoteLanguageManager extends LanguageManager {

	function RemoteLanguageManager() {
		$version = str_replace('.','_',VERSION);
		$language_xml = @file_get_contents('http://update.atutor.ca.ca/acontent/languages/'.$version.'/languages.xml');
		if ($language_xml === FALSE) {
			// fix for bug #2896
			$language_xml = @file_get_contents('http://update.atutor.ca/acontent/languages/1_5_3/languages.xml');
		}
		if ($language_xml !== FALSE) {

			$languageParser = new LanguagesParser();
			$languageParser->parse($language_xml);

			$this->numLanguages = $languageParser->getNumLanguages();

			for ($i = 0; $i < $this->numLanguages; $i++) {
				$thisLanguage = new Language($languageParser->getLanguage($i));

				$this->availableLanguages[$thisLanguage->getCode()][$thisLanguage->getCharacterSet()] =& $thisLanguage;
			}
		} else {
			$this->numLanguages = 0;
			$this->availableLanguages = array();
		}
	}

	// public
	function fetchLanguage($language_code, $filename) {
		$version = str_replace('.','_',VERSION);

		$language_pack = @file_get_contents('http://update.atutor.ca/transformable/languages/' . $version . '/transformable_' . $version . '_' . $language_code . '.zip');

		if ($language_pack) {
			$fp = fopen($filename, 'wb+');
			fwrite($fp, $language_pack, strlen($language_pack));

			return TRUE;
		}
		return FALSE;
	}

	function import($language_code) {
		$filename = tempnam(TR_CONTENT_DIR . 'import', $language_code);
		if ($this->fetchLanguage($language_code, $filename)) {
			parent::import($filename);
		}
	}
}

?>