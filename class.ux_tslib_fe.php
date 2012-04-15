<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2009 Juergen Furrer (juergen.furrer@gmail.com)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is 
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
* 
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/** 
 * objective: Shows the defined error page of the given language if the requested page or file could not be found
 * @author	Juergen Furrer <juergen.furrer@gmail.com>
 */

require_once(PATH_t3lib."class.t3lib_extmgm.php");

require_once(PATH_tslib.'class.tslib_content.php');

class ux_tslib_fe extends tslib_fe
{
	/**
	 * @var array
	 */
	var $typo3_conf_var_404 = array();

	/**
	 * @var array
	 */
	var $typo3_conf_var_realurl = array();

	/**
	 * @var array
	 */
	var $cObj = null;

	/**
	 * Page not found handler.
	 * 
	 * @return void
	 */
	function pageNotFoundHandler()
	{
		if (t3lib_div::_GP('tx_error404multilingual') || ! is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['error_404_multilingual'])) {
			// If the defined errorpage does not exist, the normal errorpage is shown...
			$urlcontent = parent::pageNotFoundHandler($code, $header, $reason);
		} else {
			// create the cObject (to create links by pid)
			//$this->cObj = $this->getCObj();
			// extract the domain
			$host = t3lib_div::getIndpEnv('HTTP_HOST');
			$uri = $this->getUri(t3lib_div::getIndpEnv('REQUEST_URI'));
			list($script, $option) = explode("?", $uri);

			// define config for error_404_multilingual
			$this->typo3_conf_var_404 = $this->getConfiguratio($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['error_404_multilingual'], $host);
			if (! is_array($this->typo3_conf_var_404)) {
				// set the default
				$this->typo3_conf_var_404 = array(
					'errorPage' => '404',
					'redirects' => array(),
					'mail' => '',
					'mailOnRedirect' => false,
					'mailOn404' => false,
					'stringConversion' => 'none',
				);
			}

			// define config for realurl
			$this->typo3_conf_var_realurl = $this->getConfiguratio($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'], $host);

			// removes all leading slashes in array
			if (count($this->typo3_conf_var_404['redirects']) > 0) {
				$redirects = array();
				foreach ($this->typo3_conf_var_404['redirects'] as $key => $val) {
					$redirects[$this->getUri($key)] = $this->getUri($val);
				}
				$this->typo3_conf_var_404['redirects'] = $redirects;
			}

			// fallback if typo3_conf_var_404 not an array
			if (! is_array($this->typo3_conf_var_404['redirects'])) {
				$this->typo3_conf_var_404['redirects'] = array();
			}

			// First element will be the host
			$url_array = array();
			$url_array[] = $host;
			if (is_array($this->typo3_conf_var_404['redirects']) && array_key_exists($uri, $this->typo3_conf_var_404['redirects'])) {
				// There is a redirect defined for this request URI, so the value is taken
				$url_array[] = $this->typo3_conf_var_404['redirects'][$uri];
				$send_mail = $this->typo3_conf_var_404['mailOnRedirect'];
			} elseif (is_array($this->typo3_conf_var_404['redirects']) && array_key_exists($script, $this->typo3_conf_var_404['redirects'])) {
				// There is a redirect defined for this script, so the value is taken
				$url_array[] = $this->typo3_conf_var_404['redirects'][$script];
				$send_mail = $this->typo3_conf_var_404['mailOnRedirect'];
			} else {
				// Normaly no alternative is defined, so the 404 site will be taken
				// extract the language
				$reg = array();
				preg_match("/^\/([a-zA-Z]*)\/(.*)/", t3lib_div::getIndpEnv('REQUEST_URI'), $reg);
				$lang = $reg[1];
				// define the page name
				$errorpage = $this->typo3_conf_var_404['errorPage'];
				if (! $errorpage) {
					$errorpage = $this->typo3_conf_var_realurl['404page'];
				}
				$errorpage = ($errorpage == '' ? '404' : $errorpage);
				if (is_array($this->typo3_conf_var_realurl['preVars'][0]['valueMap']) && array_key_exists($lang, $this->typo3_conf_var_realurl['preVars'][0]['valueMap'])) {
					$url_array[] = $lang;
				} elseif ($this->typo3_conf_var_realurl['preVars'][0]['valueDefault']) {
					$url_array[] = $this->typo3_conf_var_realurl['preVars'][0]['valueDefault'];
				}
				$url_array[] = $errorpage;
				$send_mail = $this->typo3_conf_var_404['mailOn404'];
			}
			//send the email
			$mailAddress = $this->getMail();
			if ($mailAddress && $send_mail) {
				$subject = 'ERROR: "'.t3lib_div::getIndpEnv('HTTP_HOST').t3lib_div::getIndpEnv('REQUEST_URI').'" was not found on the server';
				$body = "The requested URL \n\t".t3lib_div::getIndpEnv('HTTP_HOST').t3lib_div::getIndpEnv('REQUEST_URI')."\nwas not found.\n\n REFERER:     ".t3lib_div::getIndpEnv('HTTP_REFERER')."\n REMOTE_ADDR: ".t3lib_div::getIndpEnv('REMOTE_ADDR')."\n USER_AGENT:  ".t3lib_div::getIndpEnv('HTTP_USER_AGENT')."\n\nThis Email was automaticly created by the Typo3-Extension error_404_multilingual.";
				@mail ($mailAddress, $subject, $body);
			}
			$url = "http://". implode("/", $url_array);
			// header 404
			$error_header = $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_statheader'];
			$error_header = ($error_header ? $error_header : "HTTP/1.0 404 Not Found");
			header($error_header);
			if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlUse']) {
				// Open url by curl
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER , false);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, 'tx_error404multilingual=1');
				//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 5);
				if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyTunnel']) {
					curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyTunnel']);
				}
				if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyServer']) {
					curl_setopt($ch, CURLOPT_PROXY, $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyServer']);
				}
				if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyUserPass']) {
					curl_setopt($ch, CURLOPT_PROXYUSERPWD, $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyUserPass']); 
				}
				$urlcontent = curl_exec($ch);
				curl_close($ch);
			} else {
				// Open url by fopen
				set_time_limit(5);
				$urlcontent = file_get_contents($url.'?tx_error404multilingual=1');
			}
		}

		switch ($this->typo3_conf_var_404['stringConversion']) {
			case 'utf8_encode' : {
				echo utf8_encode($urlcontent);
				break;
			}
			case 'utf8_decode' : {
				echo utf8_decode($urlcontent);
				break;
			}
			default : {
				echo $urlcontent;
				break;
			}
		}
	}

	/**
	 * Returns the URI without leading slashes
	 * 
	 * @param string $url
	 * @return string
	 */
	function getUri($url="")
	{
		if (preg_match("/^\/(.*)/i", $url, $reg)) {
			return $reg[1];
		}
		return $url;
	}

	/**
	 * Return the configured Email-address
	 * 
	 * @return string
	 */
	function getMail()
	{
		$mail = '';
		if ($this->typo3_conf_var_404['mail']) {
			$mail = $this->typo3_conf_var_404['mail'];
		} else {
			$mail = $GLOBALS['TYPO3_CONF_VARS']['BE']['warning_email_addr'];
		}
		return $mail;
	}

	/**
	 * Return the cObject
	 * 
	 * @return object
	 */
	function getCObj($id=0)
	{
		$TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');
		$GLOBALS['TSFE'] = new $TSFEclassName($TYPO3_CONF_VARS, $id, '0', 1, '', '','','');
		$GLOBALS['TSFE']->connectToMySQL();
		$GLOBALS['TSFE']->initFEuser();
		$GLOBALS['TSFE']->fetch_the_id();
		$GLOBALS['TSFE']->getPageAndRootline();
		$GLOBALS['TSFE']->initTemplate();
		$GLOBALS['TSFE']->tmpl->getFileName_backPath = PATH_site;
		$GLOBALS['TSFE']->forceTemplateParsing = 1;
		$GLOBALS['TSFE']->getConfigArray();
		$cObj = t3lib_div::makeInstance('tslib_cObj');
		$cObj->start(array(), '');

		return $cObj;
	}

	/**
	 * Returns the related configuration
	 * 
	 * @param $array array
	 * @param $key string
	 * @return string
	 */
	function getConfiguratio($array=array(), $key='_DEFAULT')
	{
		if (is_array($array) && array_key_exists($key, $array)) {
			$domain_key = $key;
		} else {
			$domain_key = '_DEFAULT';
		}
		if (is_array($array[$domain_key])) {
			return $array[$domain_key];
		} else {
			return $array[$array[$domain_key]];
		}
	}
}
?>
