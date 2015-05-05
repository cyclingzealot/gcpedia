<?php
/**
 * Disclaimer - use with EmailUpdate extension
 * 
 * 
 */

if ( !defined('MEDIAWIKI') ) {
        echo <<<EOT
To install this extension, put the following line in LocalSettings.php:
require_once("$IP/extensions/GCPEDIAdisclaimer/disclaimer.php");
EOT;
        exit( 1 );
}

$wgHooks['BeforePageDisplay'][] = 'showDisclaimer';

///Function to compare current date and date last accepted.
/**
 *
 * @param $lastdate date string
 *
 * @return bool
 * 
 * @author Ilia Salem
 */
function lastaccept( $lastdate ) {
	
	$ltime = explode( '.', $lastdate );
	
	if(count($ltime)>1)
	{
		$lasttime = $ltime[2] * 365 + $ltime[1] * 30 + $ltime[0];
		$now = explode( '.', date("d.m.y"));
		$nowtime = $now[2] * 365 + $now[1] * 30 + $now[0];
	
		global $wgDisclaimReset;
	
		$lasttime += $wgDisclaimReset;
	
		return ( $nowtime >= $lasttime );
	}
	else
	{
		return true;
	}	
}

///Create the disclaimer output page
/**
 * 
 * @return bool returns true on completion
 * 
 * @author Ilia Salem
 */
function showDisclaimer() {
	global $wgUser, $wgTitle, $wgLang, $wgOut, $wgScriptPath;
	
	$userName = $wgUser->getName();
	$timeout = true;
	$dbw = wfGetDB( DB_MASTER );
	
	# select user from table
	$queryString = "SELECT * FROM `accepted` WHERE username = '$userName'";
	$result = $dbw->query($queryString);
	
	 try{
		$row = $dbw->fetchRow( $result );
		if(count($row)>1)
		{
			$timeout = lastaccept( $row[1] );
		}
	} catch( Exception $e ) {
		$row = array(0);
		# create table
		require_once('dbdisclaimer.php');
	}

if( $wgUser->isLoggedIn() ) {
	if ( $timeout and $wgTitle != 'Special:UserLogin' and $wgTitle != 'GCPEDIA:Terms and conditions of use' and $wgTitle != 'Help:Code of Conduct' and $wgTitle != 'GCPEDIA:Privacy' and $wgTitle != 'Special:ConfirmEmail' and $wgTitle != 'GCPEDIA:Conditions d\'utilisation') {
	
	# get current page URL before we change it in disclaimerHead
	$fullURL = $wgTitle->escapeFullURL();
	
	# create <head>
	$dhead = new disclaimerHead;
	$dhead->execute();
	
	?>
	
	<body style ="background-color:white;">
			
			<script src="<?php echo $wgScriptPath ?>/load.php?debug=false&amp;lang=en&amp;modules=startup&amp;only=scripts&amp;skin=monobook&amp;*" type="text/javascript"></script>
<script type="text/javascript">if ( window.mediaWiki ) {
	mediaWiki.config.set({"wgCanonicalNamespace": "Special", "wgCanonicalSpecialPageName": "Userlogin", "wgNamespaceNumber": -1, "wgPageName": "Special:UserLogin", "wgTitle": "UserLogin", "wgAction": "view", "wgArticleId": 0, "wgIsArticle": false, "wgUserName": null, "wgUserGroups": ["*"], "wgCurRevisionId": 0, "wgCategories": [], "wgBreakFrames": false, "wgRestrictionCreate": [], "wgSearchNamespaces": [0], "wgFCKEditorDir": "extensions/WYSIWYG/ckeditor/", "wgFCKEditorExtDir": "extensions/WYSIWYG", "wgFCKEditorToolbarSet": "Wiki", "wgFCKEditorHeight": "0", "wgCKeditorMagicWords": {"wikitags": ["nowiki", "includeonly", "onlyinclude", "noinclude", "tagcloud", "video", "wikiflv", "videoflash", "multilang", "poll", "gc_poll", "accesscontrol", "section", "dpl", "dynamicpagelist", "pre", "gallery", "inputbox", "ref", "references", "imagemap", "languageselector"], "magicwords": ["NOTOC", "FORCETOC", "TOC", "NOEDITSECTION", "NEWSECTIONLINK", "NONEWSECTIONLINK", "NOCONTENTCONVERT", "NOCC", "NOTITLECONVERT", "NOTC", "NOGALLERY", "INDEX", "NOINDEX", "STATICREDIRECT", "NOGALLERY", "HIDDENCAT", "START", "END"], "datevars": ["CURRENTYEAR", "CURRENTMONTH", "CURRENTMONTHNAME", "CURRENTMONTHNAMEGEN", "CURRENTMONTHABBREV", "CURRENTDAY", "CURRENTDAY2", "CURRENTDOW", "CURRENTDAYNAME", "CURRENTTIME", "CURRENTHOUR", "CURRENTWEEK", "CURRENTTIMESTAMP"], "wikivars": ["SITENAME", "SERVER", "SERVERNAME", "DIRMARK", "SCRIPTPATH", "CURRENTVERSION", "CONTENTLANG", "REVISIONID", "REVISIONDAY", "REVISIONDAY2", "REVISIONMONTH", "REVISIONYEAR", "REVISIONTIMESTAMP", "REVISIONUSER", "FULLPAGENAME", "PAGENAME", "BASEPAGENAME", "SUBPAGENAME", "SUBJECTPAGENAME", "TALKPAGENAME", "NAMESPACE", "ARTICLESPACE", "TALKSPACE"], "parserhooks": ["lc", "lcfirst", "uc", "ucfirst", "formatnum", "#dateformat", "padleft", "padright", "plural", "grammar", "#language", "int", "#tag", "#css", "#dpl", "#dplnum", "#dplchapter", "#dplmatrix", "#int", "#ns", "#nse", "#urlencode", "#lcfirst", "#ucfirst", "#lc", "#uc", "#localurl", "#localurle", "#fullurl", "#fullurle", "#formatnum", "#grammar", "#gender", "#plural", "#numberofpages", "#numberofusers", "#numberofactiveusers", "#numberofarticles", "#numberoffiles", "#numberofadmins", "#numberingroup", "#numberofedits", "#numberofviews", "#padleft", "#padright", "#anchorencode", "#special", "#defaultsort", "#filepath", "#pagesincategory", "#pagesize", "#protectionlevel", "#namespace", "#namespacee", "#talkspace", "#talkspacee", "#subjectspace", "#subjectspacee", "#pagename", "#pagenamee", "#fullpagename", "#fullpagenamee", "#basepagename", "#basepagenamee", "#subpagename", "#subpagenamee", "#talkpagename", "#talkpagenamee", "#subjectpagename", "#subjectpagenamee", "#formatdate", "#displaytitle", "#widget", "#if", "#ifeq", "#switch", "#ifexist", "#ifexpr", "#iferror", "#expr", "#time", "#timel", "#rel2abs", "#titleparts"]}, "wgCKeditorUseBuildin4Extensions": []});
}
</script>
<script type="text/javascript">if ( window.mediaWiki ) {
	mediaWiki.loader.load(["mediawiki.util", "mediawiki.legacy.wikibits", "mediawiki.legacy.ajax", "mediawiki.legacy.mwsuggest"]);
	mediaWiki.loader.go();
}
</script>

<script type="text/javascript">
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
	</script>

<script src="<?php echo $wgScriptPath ?>/load.php?debug=false&amp;lang=en&amp;modules=site&amp;only=scripts&amp;skin=monobook&amp;*" type="text/javascript"></script>
<script type="text/javascript">if ( window.mediaWiki ) {
	mediaWiki.user.options.set({"ccmeonemails":0,"cols":80,"contextchars":50,"contextlines":5,"date":"default","diffonly":0,"disablemail":0,"disablesuggest":0,"editfont":"default","editondblclick":0,"editsection":1,"editsectiononrightclick":0,"enotifminoredits":0,"enotifrevealaddr":0,"enotifusertalkpages":1,"enotifwatchlistpages":0,"extendwatchlist":0,"externaldiff":0,"externaleditor":0,"fancysig":0,"forceeditsummary":0,"gender":"unknown","hideminor":0,"hidepatrolled":0,"highlightbroken":1,"imagesize":2,"justify":0,"math":1,"minordefault":0,"newpageshidepatrolled":0,"nocache":0,"noconvertlink":0,"norollbackdiff":0,"numberheadings":0,"previewonfirst":0,"previewontop":1,"quickbar":1,"rcdays":7,"rclimit":50,"rememberpassword":0,"rows":25,"searchlimit":20,"showhiddencats":0,"showjumplinks":1,"shownumberswatching":1,"showtoc":1,"showtoolbar":1,"skin":"monobook","stubthreshold":0,"thumbsize":2,"underline":2,"uselivepreview":0,"usenewrc":0,"watchcreations":0,"watchdefault":0,"watchdeletion":0,
	"watchlistdays":3,"watchlisthideanons":0,"watchlisthidebots":0,"watchlisthideliu":0,"watchlisthideminor":0,"watchlisthideown":0,"watchlisthidepatrolled":0,"watchmoves":0,"wllimit":250,"riched_use_toggle":1,"riched_start_disabled":1,"riched_use_popup":1,"riched_toggle_remember_state":1,"variant":"en","language":"en","searchNs0":true,"searchNs1":false,"searchNs2":false,"searchNs3":false,"searchNs4":false,"searchNs5":false,"searchNs6":false,"searchNs7":false,"searchNs8":false,"searchNs9":false,"searchNs10":false,"searchNs11":false,"searchNs12":false,"searchNs13":false,"searchNs14":false,"searchNs15":false,"searchNs100":false,"searchNs101":false,"searchNs102":false,"searchNs103":false,"searchNs274":false,"searchNs275":false});;mediaWiki.loader.state({"user.options":"ready"});
}
</script>
	
		<div id="globalWrapper">
			<form>
				<div style="margin:auto;
					border:3px solid #000;
					min-width:512px;
					max-width:1024px;
					width:70%;
					position:relative;
					top:40px;
					background-color:white;
					padding: 5px 20px 8px 20px;
					/* for IE */
					filter:alpha(opacity=90);
					/* CSS3 standard */
					opacity:0.9;
					color:#000;
					
					">
					<?php ?>
					<h2 st/yle="font-size:133%; background-color: #ac1a2f; color: white; padding-left:7px;"><?php if ( $wgLang->getCode() == 'en' ) echo "Welcome"; else echo "Bienvenu";?>, <?php if ( $wgUser->getRealName() != '' ) echo $wgUser->getRealName(); else echo $wgUser->getName(); ?></h1>
					<ul style="float: right;">
						<li> <a href='<?php echo $fullURL . "?setlang="
						. ( ( $wgLang->getCode() == 'en' ) ? fr : en ) . "'>"
						. wfMsg( 'emailupdate-language' ); ?></a> </li>
					</ul>
					<br />
					<!-- Instructions -->
					<p style="padding-left:7px;"> <?php echo wfMsg( 'emailupdate-instruc' ); ?> </p>
					
					<br />
					
					<!-- Step 1 -->
					<p> 
						<b> <?php echo ( ( $wgLang->getCode() == 'en' ) ? 'Step 1' : 'Étape 1' ) ; ?> </b> 
						<br />
						<?php echo emailCheck(); ?>
					</p>
					
					<div style="clear:both;"></div>
					<br /> <br /> <hr />
					
					<!-- Step 2 -->
					<p>
						<b><?php echo ( ( $wgLang->getCode() == 'en' ) ? 'Step 2' : 'Étape 2' ) ; ?></b> 
						<br /> <br />
						<?php if( $wgLang->getCode() == 'en' ) echo "As a registered user, you are requested to acknowledge that you have read and agree to the terms and conditions of GCPEDIA use."; else echo "À titre d'usager inscrit, vous devez confirmer que vous avez lu et que vous acceptez les conditions d'utilisation de GCPEDIA.";?>
					</p>
					
					<ul>
						<li> <a href = '<?php if( $wgLang->getCode() == 'en' ) echo "./GCPEDIA:Terms_and_conditions_of_use"; else echo "./GCPEDIA:Conditions_d%27utilisation"; ?>' target='_blank'><?php if ( $wgLang->getCode() == 'en' ) echo "Terms and conditions of use"; else echo "Conditions d'utilisation"; ?> </a> </li>
					</ul>
					
					<input name='accept' id='accept' type='checkbox' onchange="getChecked()" /> <label for='accept'> <?php if( $wgLang->getCode() == 'en' ) echo "I have read, understood, and agree to the terms and conditions of use."; else echo "J'ai lu, je comprends et j'accepte les conditions d'utilisation.";?> </label>
					<br />
					
					<!-- Submit -->
					<input style='margin: 4px 0 3px 9px;' name='proceed' type='button' id='proceed'
						value="<?php if ( $wgLang->getCode() == 'en' ) echo 'proceed'; else echo 'procéder'; ?> "
						disabled='disabled' onclick='sajax_do_call( "Addaccepted", ["<?php echo str_ireplace( "'", "-*-", $wgUser->getName() ); ?>"], function( strin ) { location.reload(true); });'
					/>
					
					<p style="text-align: right;"> <?php echo wfMsg('emailupdate-help'); ?> </p>
					
				</div>
			</form>
		</div>
	</body>
</html>
	<?php
	exit; //kill from rendering the actual page
	}
}

	return true;
}

///Function to validate and update the users email address + create the HTML form
/**
 *
 * @return string - HTML form
 * 
 * @author Matthew April
 */
function emailCheck() {
	global $wgRequest, $wgUser, $wgTitle, $wgEnableEmail, $wgEmailAuthentication;
	
	# init vars
	$currentEmail = $wgUser->getEmail();
	$action = $wgTitle->escapeLocalUrl();
	$emailOption = $wgRequest->getVal( 'EmailUpdate' );
	$err = "";
	$out = "";
	$confmessage = "";	//confirmation email notification message (blank if not sent)
	
	# radio option to update email
	if( $emailOption == "outdated" ) {
		
		if( $wgEnableEmail ) {
			
			$newEmail = $wgRequest->getText( 'newEmail' );
			$newEmail = trim($newEmail);
			$oldEmail = $currentEmail;
			$oldEmail = trim($oldEmail);
			
			# returns true on success
			$validEmail = User::isValidEmailAddr( $newEmail );
			
			if( $validEmail === true && $newEmail != $oldEmail ) {
				
				# set new email + invalidate it
				$wgUser->setEmail( $newEmail );
				
				# update var for form output
				$currentEmail = $newEmail;
				
				if( $wgEmailAuthentication ) {
					
					$wgUser->invalidateEmail();
					# send confirmation email
					$result = $wgUser->sendConfirmationMail();
					
					if( WikiError::isError( $result ) ) { //test me
						$err = wfMsg( 'mailerror', htmlspecialchars( $result->getMessage() ) );
					} else {
						$confmessage = wfMsg( 'emailupdate-confirm' );
					}
				}
				
			} else {
				# get error
				if( $newEmail == $oldEmail ) {
					$err = wfMsg('emailupdate-duplicate-error');
				} else {
					$err = wfMsg('invalidemailaddress');
				}
			}
			
			if( $oldEmail != $newEmail ) {
				wfRunHooks( 'PrefsEmailAudit', array( $wgUser, $oldEmail, $newEmail ) );
			}
		}
	}
	
	# form output
	$out .= "<div style='float: left; width: 40%; border-right: 1px solid black; clear:none; margin-right: 7px; padding: 3px 7px 5px;'>". wfMsg('emailupdate-current') . " <b>$currentEmail</b> <br/><br />
			<input type='checkbox' name='emailcheck' id='emailcheck' onchange='getChecked()' /> <label for='emailcheck'>" . wfMsg('emailupdate-confirm-address') . "</label><br /></div>";
	
	$out .= "<div style='float: left; width:55%; margin-bottom:1px;><form method='POST' action='$action'>
				<input type='hidden' id='outdateEmail' name='EmailUpdate' value='outdated' CHECKED>
				<label for='newEmail'>" . wfMsg('emailupdate-update') . "
				<input type='text' name='newEmail' size='30' value='' /></label>
					<br/>
					<br/>
				<input type='submit' name='submit' value='". wfMsg('emailupdate-submit') ."' /> <div style='color:green;'>$confmessage</div> <div class='error'> $err </div>
				
			</form> </div>";
	
	return $out;		
}


///diclaimerHead class
/**
 * creates the head element for the page.
 *
 *
 * @author Ilia Salem
 */

class disclaimerHead extends QuickTemplate {
	var $skin;
	
	function execute() {
		global $wgRequest, $wgLang, $wgTitle, $wgScriptPath;
		
		$title = Title::newFromText('GCPEDIA Disclaimer');
		$wgTitle = $title;
		
		//$this->skin = $skin = $this->data['skin'];
		$action = $wgRequest->getText( 'action' );

		//suppress warnings to prevent notices about missing indexes in $this->data
		wfSuppressWarnings();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="<?php $this->text('xhtmldefaultnamespace') ?>" <?php 
	foreach($this->data['xhtmlnamespaces'] as $tag => $ns) {
		?>xmlns:<?php echo "{$tag}=\"{$ns}\" ";
	} ?>xml:lang="<?php $this->text('lang') ?>" lang="<?php $this->text('lang') ?>" dir="<?php $this->text('dir') ?>">
	<head>
	

		<title>GCPEDIA Disclaimer</title>

	
		<meta http-equiv="Content-Type" content="<?php $this->text('mimetype') ?>; charset=<?php $this->text('charset') ?>" />
		<?php $this->html('headlinks') ?>
		<title><?php $this->text('pagetitle') ?></title>
		<style type="text/css" media="screen, projection">/*<![CDATA[*/
			@import "<?php $this->text('stylepath') ?>/common/shared.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";
			@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/main.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";
		/*]]>*/</style>
		<link rel="stylesheet" type="text/css" <?php if(empty($this->data['printable']) ) { ?>media="print"<?php } ?> href="<?php $this->text('printcss') ?>?<?php echo $GLOBALS['wgStyleVersion'] ?>" />
		<?php if( in_array( 'IE50', $skin->cssfiles ) ) { ?><!--[if lt IE 5.5000]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE50Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
		<?php } if( in_array( 'IE55', $skin->cssfiles ) ) { ?><!--[if IE 5.5000]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE55Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
		<?php } if( in_array( 'IE60', $skin->cssfiles ) ) { ?><!--[if IE 6]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE60Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
		<?php } if( in_array( 'IE70', $skin->cssfiles ) ) { ?><!--[if IE 7]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE70Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
		<?php } ?><!--[if lt IE 7]><?php if( in_array( 'IE', $skin->cssfiles ) ) { ?><script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath') ?>/common/IEFixes.js?<?php echo $GLOBALS['wgStyleVersion'] ?>"></script>
		<?php } ?><meta http-equiv="imagetoolbar" content="no" /><![endif]-->
		<link rel="stylesheet" href="<?php echo $wgScriptPath; ?>/skins/common/shared.css?270" media="screen" />
		<link rel="stylesheet" href="<?php echo $wgScriptPath; ?>/skins/monobook/main.css?270" media="screen" />

                
		<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath' ) ?>/common/wikibits.js?<?php echo $GLOBALS['wgStyleVersion'] ?>"><!-- wikibits js --></script>
		<!-- Head Scripts -->
<?php $this->html('headscripts') ?>
<?php	if($this->data['jsvarurl']) { ?>
		<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('jsvarurl') ?>"><!-- site js --></script>
<?php	} ?>
<?php	if($this->data['pagecss']) { ?>
		<style type="text/css"><?php $this->html('pagecss') ?></style>
<?php	}
		if($this->data['usercss']) { ?>
		<style type="text/css"><?php $this->html('usercss') ?></style>
<?php	}
		if($this->data['userjs']) { ?>
		<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('userjs' ) ?>"></script>
<?php	}
		if($this->data['userjsprev']) { ?>
		<script type="<?php $this->text('jsmimetype') ?>"><?php $this->html('userjsprev') ?></script>
<?php	}
		if($this->data['trackbackhtml']) print $this->data['trackbackhtml']; ?>
<!-- Head Scripts -->
<script type="text/javascript">
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
		
		function getChecked() {
			if( document.getElementById('accept').checked == true && document.getElementById('emailcheck').checked == true ) { 
				document.getElementById('proceed').disabled=0
			} else { 
				document.getElementById('proceed').disabled=1
			}
		}
	</script>
	<script src="<?php echo $wgScriptPath; ?>/skins/common/ajax.js?270"></script>
	<script src="<?php echo $wgScriptPath; ?>/skins/common/ajaxwatch.js?270"></script>
	<script src="<?php echo $wgScriptPath; ?>/skins/common/mwsuggest.js?270"></script>
	<script src="<?php echo $wgScriptPath; ?>/skins/common/rightclickedit.js?270"></script>
	<script type="text/javascript" src="<?php echo $wgScriptPath; ?>/index.php?title=-&amp;action=raw&amp;smaxage=0&amp;gen=js&amp;useskin=monobook"><!-- site js --></script>
	</head>
			
	<?php
	}
	
}

?>