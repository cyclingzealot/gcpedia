<!--[if !IE]><!--> <script type="text/javascript" src="skins/common/jquery.min.js"></script> <!--<![endif]-->
<?php
/**
 * @addtogroup Templates
 */
 
	
if( !defined( 'MEDIAWIKI' ) ) die( -1 );


require_once( 'includes/SkinTemplate.php' );

/**
 * HTML template for Special:Userlogin form
 * @addtogroup Templates
 * Template uses ajax calls to access the contents of "..\includes\templates\GCpedia AJAX\AJAXtest.php"
 */
class customUserlogin extends QuickTemplate {
	function execute() {
		if( $this->data['message'] ) {
?>
	<div class="<?php $this->text('messagetype') ?>box">
		<?php if ( $this->data['messagetype'] == 'error' ) { ?>
			<strong><?php $this->msg( 'loginerror' )?></strong><br />
		<?php } ?>
		<?php $this->html('message') ?>
	</div>
	<div class="visualClear"></div>
<?php } ?>

<div id="loginstart"><?php $this->msgWiki( 'loginstart' ); ?></div>
<div id="userloginForm">

<form name="userlogin" method="post" action="<?php $this->text('action') ?>">
	<h2><?php $this->msg('login') ?></h2>
	<p id="userloginlink"><?php $this->html('link') ?></p>
	<?php $this->html('header'); /* pre-table point for form plugins... */ ?>
	<div id="userloginprompt"><?php  $this->msgWiki('loginprompt') ?></div>
	<?php if( $this->haveData( 'languages' ) ) { ?><div id="languagelinks"><p><?php $this->html( 'languages' ); ?></p></div><?php } ?>
	<table>
		<tr>
			<td class="mw-label"><label for='wpName1'><?php $this->msg('yourname') ?></label></td>
			<td class="mw-input">
				<?php
			echo Html::input( 'wpName', $this->data['name'], 'text', array(
				'class' => 'loginText',
				'id' => 'wpName1',
				'tabindex' => '1',
				'size' => '20',
				'required'
				# Can't do + array( 'autofocus' ) because + for arrays in PHP
				# only works right for associative arrays!  Thanks, PHP.
			) + ( $this->data['name'] ? array() : array( 'autofocus' => '' ) ) ); ?>

			</td>
		</tr>
		<tr>
			<td class="mw-label"><label for='wpPassword1'><?php $this->msg('yourpassword') ?></label></td>
			<td class="mw-input">
				<?php
			echo Html::input( 'wpPassword', null, 'password', array(
				'class' => 'loginPassword',
				'id' => 'wpPassword1',
				'tabindex' => '2',
				'size' => '20'
			) + ( $this->data['name'] ? array( 'autofocus' ) : array() ) ); ?>

			</td>
		</tr>
	<?php if( isset( $this->data['usedomain'] ) && $this->data['usedomain'] ) {
		$doms = "";
		foreach( $this->data['domainnames'] as $dom ) {
			$doms .= "<option>" . htmlspecialchars( $dom ) . "</option>";
		}
	?>
		<tr id="mw-user-domain-section">
			<td class="mw-label"><?php $this->msg( 'yourdomainname' ) ?></td>
			<td class="mw-input">
				<select name="wpDomain" value="<?php $this->text( 'domain' ) ?>"
					tabindex="3">
					<?php echo $doms ?>
				</select>
			</td>
		</tr>
	<?php }

	if( $this->haveData( 'extrafields' ) ) {
		echo $this->data['extrafields'];
	}

	if( $this->data['canremember'] ) { ?>
		<tr>
			<td></td>
			<td class="mw-input">
				<?php
				global $wgCookieExpiration, $wgLang;
				echo Xml::checkLabel(
					wfMsgExt( 'remembermypassword', 'parsemag', $wgLang->formatNum( ceil( $wgCookieExpiration / ( 3600 * 24 ) ) ) ),
					'wpRemember',
					'wpRemember',
					$this->data['remember'],
					array( 'tabindex' => '8' )
				)
				?>
			</td>
		</tr>
<?php } ?>
<?php if( $this->data['cansecurelogin'] ) { ?>
		<tr>
			<td></td>
			<td class="mw-input">
			<?php
			echo Xml::checkLabel(
				wfMsg( 'securelogin-stick-https' ),
				'wpStickHTTPS',
				'wpStickHTTPS',
				$this->data['stickHTTPS'],
				array( 'tabindex' => '9' )
			);
		?>
			</td>
		</tr>
<?php } ?>
		<tr>
			<td></td>
			<td class="mw-submit">
				<?php
		echo Html::input( 'wpLoginAttempt', wfMsg( 'login' ), 'submit', array(
			'id' => 'wpLoginAttempt',
			'tabindex' => '9'
		) );
		if ( $this->data['useemail'] && $this->data['canreset'] ) {
			if( $this->data['resetlink'] === true ){
				echo '&#160;';
				echo Linker::link(
					SpecialPage::getTitleFor( 'PasswordReset' ),
					wfMessage( 'userlogin-resetlink' )
				);
			} elseif( $this->data['resetlink'] === null ) {
				echo '&#160;';
				echo Html::input( 'wpMailmypassword', wfMsg( 'mailmypassword' ), 'submit', array(
					'id' => 'wpMailmypassword',
					'tabindex' => '10'
				) );
			}
		} ?>

			</td>
		</tr>
	</table>

	
<?php if( $this->haveData( 'uselang' ) ) { ?><input type="hidden" name="uselang" value="<?php $this->text( 'uselang' ); ?>" /><?php } ?>
<?php if( $this->haveData( 'token' ) ) { ?><input type="hidden" name="wpLoginToken" value="<?php $this->text( 'token' ); ?>" /><?php } ?>
</form>
<br /><br />
<div id="forgotname">
				<strong><?php $this->msg('forgotusername'); ?></strong>
				<br />
				<?php $this->msg('forgotusername-text1'); ?>
				<br /><br />
				<?php $this->msg('forgotusername-text2'); ?>
				
				<br />
				
				<label for='byEmail'> <?php $this->msg('enteremail') ?> </label> <input type='text' name='byEmail' id='byEmail' size='28' />
				<input type='button' value='<?php $this->msg('findusername') ?>' name='findUser' id='findUser'
				onclick="sajax_do_call( 'findUsername', [byEmail.value] , function( strin ) { document.getElementById('wpName1').value = strin.responseText;});
				"
				/>
</div>

</div>
<div id="loginend"><?php $this->html( 'loginend' ); ?></div>

				
<?php

	}
}
?>