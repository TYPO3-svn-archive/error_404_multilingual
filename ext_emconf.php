<?php

########################################################################
# Extension Manager/Repository config file for ext "error_404_multilingual".
#
# Auto generated 02-08-2011 23:44
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Error 404 multilingual',
	'description' => 'Shows the defined error page of the given language if the requested page or file could not be found',
	'category' => 'fe',
	'shy' => 0,
	'version' => '0.3.0',
	'dependencies' => 'realurl',
	'conflicts' => 'error_404_handling',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Juergen Furrer',
	'author_email' => 'juergen.furrer@gmail.com',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'realurl' => '',
			'php' => '5.0.0-0.0.0',
			'typo3' => '4.0.0-4.99.999',
		),
		'conflicts' => array(
			'error_404_handling' => '',
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:4:{s:21:"class.ux_tslib_fe.php";s:4:"900f";s:12:"ext_icon.gif";s:4:"b4a8";s:17:"ext_localconf.php";s:4:"4fc0";s:14:"doc/manual.sxw";s:4:"75c1";}',
	'suggests' => array(
	),
);

?>