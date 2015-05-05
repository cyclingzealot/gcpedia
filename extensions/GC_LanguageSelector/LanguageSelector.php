<?php
/**
 * LanguageSelector extension - language selector on every page, also for visitors
 *
 * Features:
 *  * Automatic detection of the language to use for anonymous visitors
 *  * Ads selector for preferred language to every page (also works for anons)
 *
 * This can be combined with Polyglot and MultiLang to provide more internationalization support.
 *
 *
 * @package MediaWiki
 * @subpackage Extensions
 * @author Daniel Kinzler, brightbyte.de
 * @copyright © 2007 Daniel Kinzler
 * @licence GNU General Public Licence 2.0 or later
 */

if( !defined( 'MEDIAWIKI' ) ) {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	die( 1 );
}

$wgExtensionCredits['other'][] = array( 
	'name'           => 'Language Selector', 
	'author'         => 'Daniel Kinzler', 
	'url'            => 'http://mediawiki.org/wiki/Extension:LanguageSelector',
	'svn-date' => '$LastChangedDate: 2008-05-06 11:59:58 +0000 (Tue, 06 May 2008) $',
	'svn-revision' => '$LastChangedRevision: 34306 $',
	'description'    => 'language selector on every page, also for visitors',
	'descriptionmsg' => 'languageselector-desc',
);

define( 'LANGUAGE_SELECTOR_USE_CONTENT_LANG',    0 ); #no detection
define( 'LANGUAGE_SELECTOR_PREFER_CONTENT_LANG', 1 ); #use content language if accepted by the client
define( 'LANGUAGE_SELECTOR_PREFER_CLIENT_LANG',  2 ); #use language most preferred by the client

/**
* Language detection mode for anonymous visitors.
* Possible values:
* * LANGUAGE_SELECTOR_USE_CONTENT_LANG - use the $wgLanguageCode setting (default content language)
* * LANGUAGE_SELECTOR_PREFER_CONTENT_LANG - use the $wgLanguageCode setting, if accepted by the client
* * LANGUAGE_SELECTOR_USE_CONTENT_LANG - use the client's preferred language, if in $wgLanguageSelectorLanguages
*/
$wgLanguageSelectorDetectLanguage = LANGUAGE_SELECTOR_PREFER_CLIENT_LANG; 

/**
* Languages to offer in the language selector. Per default, this includes all languages MediaWiki knows
* about by virtue of $wgLanguageNames. A shorter list may be more usable, though.
* If the Polyglot extension is installed, $wgPolyglotLanguages is used as fallback.
*/
$wgLanguageSelectorLanguages = NULL;

define( 'LANGUAGE_SELECTOR_MANUAL',    0 ); #don't place anywhere
define( 'LANGUAGE_SELECTOR_AT_TOP_OF_TEXT', 1 ); #put at the top of page content
define( 'LANGUAGE_SELECTOR_IN_TOOLBOX',  2 ); #put into toolbox
define( 'LANGUAGE_SELECTOR_AS_PORTLET', 3 ); #as portlet
define( 'LANGUAGE_SELECTOR_INTO_SITENOTICE', 11); #put after sitenotice text
define( 'LANGUAGE_SELECTOR_INTO_TITLE', 12); #put after title text
define( 'LANGUAGE_SELECTOR_INTO_SUBTITLE', 13); #put after subtitle text
define( 'LANGUAGE_SELECTOR_INTO_CATLINKS', 14); #put after catlinks text
// GCPEDIA CHANGE: able to add to sidebar
define( 'LANGUAGE_SELECTOR_AT_TOP_OF_SIDEBAR', 15); #put at the top of the sidebar 
define( 'LANGUAGE_SELECTOR_AT_TOP_OF_MAINPAGE', 16); #put at the top of the mainpage

$wgLanguageSelectorLocation;

$wgLanguageSelectorLocation = LANGUAGE_SELECTOR_AT_TOP_OF_TEXT;

///// hook it up /////////////////////////////////////////////////////
$wgHooks['AbortNewAccount'][] = 'wfLanguageSelectorAbortNewAccount'; //abuse hook to inject default language option //FIXME: doesn't quite work it seems :(

$wgExtensionFunctions[] = "wfLanguageSelectorExtension";

$wgLanguageSelectorRequestedLanguage = NULL;


$dir = dirname(__FILE__) . '/';
$wgExtensionMessagesFiles['LanguageSelector'] = $dir . 'LanguageSelector.i18n.php';

function wgLanguageSelectorSetHook() {
	global $wgParser;
	$wgParser->setHook('languageselector', 'wfLanguageSelectorTag' );
	return true;
}

function wfLanguageSelectorExtension() {
	wfLoadExtensionMessages( 'LanguageSelector' );
	global $wgLanguageSelectorLanguages, $wgLanguageSelectorDetectLanguage, $wgLanguageSelectorRequestedLanguage, $wgLanguageSelectorLocation;
	global $wgUser, $wgLang, $wgRequest, $wgCookiePrefix, $wgCookiePath, $wgOut, $wgJsMimeType, $wgHooks;

	if ( defined( 'MW_SUPPORTS_PARSERFIRSTCALLINIT' ) ) {
		$wgHooks['ParserFirstCallInit'][] = 'wgLanguageSelectorSetHook';
	} else {
		wgLanguageSelectorSetHook();
	}

	if ( $wgLanguageSelectorLanguages === NULL ) {
		$wgLanguageSelectorLanguages = @$GLOBALS['wgPolyglotLanguages'];
	}
	
	if ( $wgLanguageSelectorLanguages === NULL ) {
		$wgLanguageSelectorLanguages = array_keys( Language::getLanguageNames( true ) );
	}

	$setlang = $wgRequest->getVal('setlang');
	if ($setlang && !in_array($setlang, $wgLanguageSelectorLanguages)) $setlang = NULL; //ignore invalid

	if ($setlang) {
		setcookie($wgCookiePrefix.'LanguageSelectorLanguage', $setlang, 0, $wgCookiePath);
		$wgLanguageSelectorRequestedLanguage = $setlang;
	}
	else {
		$wgLanguageSelectorRequestedLanguage = @$_COOKIE[$wgCookiePrefix.'LanguageSelectorLanguage'];
	}

	if ( !$wgUser->isAnon() && $setlang ) {
		if ($setlang != $wgUser->getOption('language')) {
			$wgUser->setOption('language', $wgLanguageSelectorRequestedLanguage);
			$wgUser->saveSettings();
		}
	}

	if ( $wgUser->isAnon() && !$wgRequest->getVal( 'uselang' )) {

		//NOTE: we need this for anons, so squids don't get confused.
		//      but something is still wrong with caching...
		header('Vary: Cookie', false); //hrm, this is pretty BAD.
		header('Vary: Accept-Language', false);
		 
		if ( $wgLanguageSelectorRequestedLanguage || $wgLanguageSelectorDetectLanguage != LANGUAGE_SELECTOR_USE_CONTENT_LANG ) {

			if (!class_exists('StubAutoLang')) {
				class StubAutoLang extends StubObject {
					function __construct() {
						parent::__construct( 'wgLang' );
					}
				
					function __call( $name, $args ) {
						return $this->_call( $name, $args );
					}
				
					//partially copied from StubObject.php. There should be a better way...
					function _newObject() {
						global $wgContLanguageCode, $wgContLang, $wgLanguageSelectorDetectLanguage, $wgLanguageSelectorRequestedLanguage;
	
						$code = $wgLanguageSelectorRequestedLanguage;
						if (!$code) $code = wfLanguageSelectorDetectLanguage($wgLanguageSelectorDetectLanguage);
				
						if( $code == $wgContLanguageCode ) {
							return $wgContLang;
						} else {
							$obj = Language::factory( $code );
							return $obj;
						}
					}
				}
			}
	
			$wgLang = new StubAutoLang;
		}
	}
	
	global $wgUser;
	
	
	// GCPEDIA CHANGE: added  && ( $wgUser->mRealName == "" || !(isset($wgUser->mRealName))) to hide if a user is logged in
	if ($wgLanguageSelectorLocation != LANGUAGE_SELECTOR_MANUAL && ( $wgUser->mRealName == "" || !(isset($wgUser->mRealName)))) {
		switch($wgLanguageSelectorLocation) {
			case LANGUAGE_SELECTOR_AT_TOP_OF_TEXT: $wgHooks['BeforePageDisplay'][] = 'wfLanguageSelectorBeforePageDisplay'; break;
			case LANGUAGE_SELECTOR_IN_TOOLBOX: $wgHooks['MonoBookTemplateToolboxEnd'][] = 'wfLanguageSelectorSkinHook'; break;
			// GCPEDIA CHANGE: able to add to sidebar now
			case LANGUAGE_SELECTOR_AT_TOP_OF_SIDEBAR: $wgHooks['BeforeSideBar'][] = 'wfLanguageSelectorBeforeSidebarDisplay'; break;
			case LANGUAGE_SELECTOR_AT_TOP_OF_MAINPAGE: $wgHooks['BeforePageContent'][] = 'gcLanguageSelectorBeforePageDisplay';
			default:
				$wgHooks['SkinTemplateOutputPageBeforeExec'][] = 'wfLanguageSelectorSkinTemplateOutputPageBeforeExec'; break;
		}
	}

	$wgOut->addScript('<script type="'.$wgJsMimeType.'">
		addOnloadHook(function() { 
			var i = 1;
			while ( true ) {
				var btn = document.getElementById("languageselector-commit-"+i);
				var sel = document.getElementById("languageselector-select-"+i);
				var idx = i;

				if (!btn) break;

				btn.style.display = "none";
				sel.onchange = function() { this.parentNode.submit(); };

				i++;
			}
		});
	</script>');
}

// GCPEDIA CHANGE: new function to handle output for sidebar hook
function wfLanguageSelectorBeforeSidebarDisplay($thing) {
	//print "<div style='padding: 0px; border: 1px solid;'>".$html."</div>";
	
	global $pageLanguageVariants;
	$pageLanguageVariants = $thing;
	
	?>
		<div style="font-weight: bold; text-align: center; width: 150px;">Language of Choice</div>
		<div style="width: 145px; border: 1px solid #aaa; text-align: center; padding: 20px 0px 20px 0px; margin-left: 5px; background-color: #ddd;">
	<?php	echo wfLanguageSelectorHTML();	?>
		</div>
		
	<?php
	
	return true;
}

function gcLanguageSelectorBeforePageDisplay( $thing )
{	
	global $pageLanguageVariants;
	$pageLanguageVariants = $thing;
	
	$html = wfLanguageSelectorHTML();
	echo $html;
	return true;
}

function wfLanguageSelectorBeforePageDisplay( &$out ) {
	$html = wfLanguageSelectorHTML();
	$out->mBodytext = $html . $out->mBodytext;
	return true;
}

function wfLanguageSelectorSkinHook( &$out ) {
	$html = wfLanguageSelectorHTML();
	print $html;
	return true;
}

function wfLanguageSelectorTag($input, $args) {
	$style = @$args['style'];
	$class = @$args['class'];
	$selectorstyle = @$args['selectorstyle'];
	$buttonstyle = @$args['buttonstyle'];

	if ($style) $style = htmlspecialchars($style);
	if ($class) $class = htmlspecialchars($class);
	if ($selectorstyle) $selectorstyle = htmlspecialchars($selectorstyle);
	if ($buttonstyle) $buttonstyle = htmlspecialchars($buttonstyle);

	return wfLanguageSelectorHTML( $style, $class, $selectorstyle, $buttonstyle );
}

function wfLanguageSelectorSkinTemplateOutputPageBeforeExec( &$skin, &$tpl ) {
	global $wgLanguageSelectorLocation, $wgLanguageSelectorLanguages;
	global $wgLang, $wgContLang, $wgTitle;

	if ($wgLanguageSelectorLocation == LANGUAGE_SELECTOR_AS_PORTLET) {
		$code = $wgLang->getCode();
		$lines = array();
		foreach ($wgLanguageSelectorLanguages as $ln) {
			$lines[] = array(
				$href = $wgTitle->getFullURL( 'setlang=' . $ln ),
				'text' => $wgContLang->getLanguageName($ln),
				'href' => $href,
				'id' => 'n-languageselector',
				'active' => ($ln == $code),
			);
		}
		
		$tpl->data['sidebar']['languageselector'] = $lines;
		return true;
	}

	$key = NULL;

	switch($wgLanguageSelectorLocation) {
		case LANGUAGE_SELECTOR_INTO_SITENOTICE: $key = 'sitenotice'; break;
		case LANGUAGE_SELECTOR_INTO_TITLE: $key = 'title'; break;
		case LANGUAGE_SELECTOR_INTO_SUBTITLE: $key = 'subtitle'; break;
		case LANGUAGE_SELECTOR_INTO_CATLINKS: $key = 'catlinks'; break;
	}
	
	if ($key) {
		$html = wfLanguageSelectorHTML();
		$tpl->set( $key, $tpl->data[ $key ] . $html );
	}

	return true;
}

function wfLanguageSelectorDetectLanguage($mode) {
	global $wgContLang, $wgLanguageSelectorLanguages;
	
	$contLang = $wgContLang->getCode();
	
	if (!$mode || $mode == LANGUAGE_SELECTOR_USE_CONTENT_LANG) {
		return $contLang;
	}

	/**
	* get accepted languages from Accept-Languages
	* HTTP header.
	*/
	$l= @$_SERVER["HTTP_ACCEPT_LANGUAGE"];
	
	if (empty($l)) return $contLang;
	
	$l= split(',',$l);
	
	/**
	* normalize accepted languages
	*/
	$languages= array();
	foreach ($l as $lan) {
		$lan= trim($lan);
		
		$idx= strpos($lan,';');
		if ($idx !== false) {
			#FIXME: qualifiers are ignored, order is relevant!
			$lan= substr($lan,0,$idx);
			$lan= trim($lan);
		}

		$languages[]= $lan;
		
		$idx= strpos($lan,'-');
		if ($idx !== false) {
			$lan= substr($lan,0,$idx);
			$languages[]= $lan;
		}
	}

	/**
	* see if the content language is accepted by the 
	* client.
	*/
	if ( ($mode == LANGUAGE_SELECTOR_PREFER_CONTENT_LANG) 
		&& in_array($contLang,$languages) ) {
		return $contLang;
	}
	
	/**
	* look for a language that is acceptable to the client
	* and known to the wiki.
	*/
	foreach($wgLanguageSelectorLanguages as $code) {
		/**
		* TODO: only accept languages for which an implementation exists.
		*       this is disabled, because it's slow. Note that this code is
		*       executed for every page request!
		*/
		/*
		global $IP;
		$langfile="$IP/languages/Language".str_replace('-', '_', ucfirst($code)).".php";
		if(!file_exists($langfile)) {
			continue;
		}
		*/
		
		if (in_array($code,$languages)) {
			return $code;
		}
	}
		
	return $contLang;
}

function wfLanguageSelectorAbortNewAccount( &$u ) { //FIXME: doesn't quite work it seems :(
	global $wgUser;

	//inherit language;
	//if $wgUser->isAnon, this means remembering what the user selected
	//otherwise, it would mean inheriting the language from the user creating the account.
	if ($wgUser->isAnon()) {
		$u->setOption('language', $wgUser->getOption('language')); 
	}

	return true;
}

function wfLanguageSelectorHTML( $style = NULL, $class = NULL, $selectorstyle = NULL, $buttonstyle = NULL) {
	global $wgLanguageSelectorLanguages, $wgTitle, $wgLang, $wgContLang, $wgScript, $pageLanguageVariants;

	$output;
	
	static $id = 0;
	$id += 1;

	$code = $wgLang->getCode();

	//$output = "<script type='text/javascript'> function chgLang(lang) { if (lang.options[lang.selectedIndex].value != 'none') window.location = lang.options[lang.selectedIndex].value; } </script>";
	
	/*$html = '';
	$html .= Xml::openElement('span', array('id' => 'languageselector-box-'.$id, 'class' => 'languageselector ' . $class, 'style' => $style ));
	$html .= Xml::openElement('form', array('name' => 'languageselector-form-'.$id, 'id' => 'languageselector-form-'.$id, 'method' => 'get', 'action' => $wgScript, 'style' => 'display:inline;'));
	$html .= Xml::hidden( 'title', $wgTitle->getPrefixedDBKey() );
	$html .= Xml::openElement('select', array('name' => 'setlang', 'id' => 'languageselector-select-'.$id, 'style' => $selectorstyle));*/
		
	//$output .= "<form><select id='langSelect' onchange='chgLang(this)'><option value='none'>select language</option>";

	$output = "<br /><style type='text/css'> div .langLink { width: 60px; padding: 2px; margin: 2px; margin-top:6px; margin-bottom:8px; border: 1px solid #000; float: center; text-align: center; }
					div .langLinks { float: left;} 
										div .langBox { font-size: 1.0em; width: 100%; padding: 1px; padding-bottom: 15px; margin: 0px; border: 1px double #999; position:fixed;}</style><div class='langBox'><div class='langLinks'>";
		
	foreach ($wgLanguageSelectorLanguages as $ln) 
	{
		//$html .= Xml::option($wgContLang->getLanguageName($ln), $ln, $ln == $code);
		$url = "";
		
		/*foreach ($pageLanguageVariants as $altLang)
		{
			if ($wgContLang->getLanguageName($ln) == $altLang['text'])
			{
				$url = $altLang['href']."&setlang=".$ln;
			}
		}*/
		if ($url == "")
			$url = "/Charles/GCPEDIA/index.php?title=".$wgTitle."&setlang=".$ln;
		
// GCPEDIA CHANGE - Use the following lines of code if variables loaed in reverse 

 		if ( $ln == 'fr' )
  			$output .= "<div class='langLink' style='position:relative; bottom:33px'>";
  		else
  			$output .= "<div class='langLink' style='position:relative; top:45px'>";

		
		if ($ln == $code) $output .= "<a style='font-weight: bold;' lang='fr' xml:lang='$ln' href=\"".$url."\">".$wgContLang->getLanguageName($ln)."</a></div>";
		else $output .= "<a lang='en' xml:lang='$ln' href=\"".$url."\">".$wgContLang->getLanguageName($ln)."</a></div>";
    }

  $output .= '</div>'.'<img src="./images/GCPEDIA_Language_Logo.png" alt="Choose your interface Language | Sélectionnez la langue de votre choix" style="float:left; position:absolute;" />'.'</div>';

	return $output;
}
	



