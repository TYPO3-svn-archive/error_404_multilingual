<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TYPO3_CONF_VARS["FE"]["XCLASS"]["tslib/class.tslib_fe.php"] = t3lib_extMgm::extPath($_EXTKEY)."class.ux_tslib_fe.php";
?>