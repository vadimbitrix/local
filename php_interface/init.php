<?php
function d($data,$trace = false,$vd = false,$die = false){
    $dTrace = debug_backtrace();
    global $USER;
	if($USER->IsAdmin()){
    echo"<pre>=================================<br>";
    ($trace)?print_r($dTrace):'';
    echo "file: ".$dTrace[0]['file'];
    echo"<br>";
    echo "line: ".$dTrace[0]['line'];
    echo"<br>";
    ($vd)?var_dump($data):print_r($data);
    echo"<br>=================================</pre>";
    ($die)?die():'';
	}
}

$eventManager = \Bitrix\Main\EventManager::getInstance();

// фикс удаление тегов svg в виз. редакторе битрикс
$eventManager->addEventHandler(
	'fileman',
	'OnBeforeHTMLEditorScriptRuns',
	'OnBeforeHTMLEditorScriptRunsHandler'
);
function OnBeforeHTMLEditorScriptRunsHandler() {
	\CJSCore::RegisterExt('citfact_html_edit', [
		'js' => [
			'/local/js/bx_edit_fix.js',
		]
	]);
	\CJSCore::Init(array('citfact_html_edit'));
}
$eventManager->addEventHandler(
	"main",
	"OnEpilog",
	"My404PageInSiteStyle"
);
function My404PageInSiteStyle()
{
	if(defined('ERROR_404') && ERROR_404 == 'Y')
	{
		global $APPLICATION;
		$APPLICATION->RestartBuffer();
		include $_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/header.php';
		include $_SERVER['DOCUMENT_ROOT'].'/404.php';
		include $_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/footer.php';
	}
}
