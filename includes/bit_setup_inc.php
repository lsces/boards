<?php
use \Bitweaver\Boards\BitBoard;
use \Bitweaver\Liberty\LibertyComment;
use \Bitweaver\BitMailer;

global $gBitSystem, $gBitThemes;

$pRegisterHash = [
	'package_name' => 'boards',
	'package_path' => dirname( dirname( __FILE__ ) ).'/',
	'homeable' => true,
];

// fix to quieten down VS Code which can't see the dynamic creation of these ...
define( 'BOARDS_PKG_NAME', $pRegisterHash['package_name'] );
define( 'BOARDS_PKG_URL', BIT_ROOT_URL . basename( $pRegisterHash['package_path'] ) . '/' );
define( 'BOARDS_PKG_PATH', BIT_ROOT_PATH . basename( $pRegisterHash['package_path'] ) . '/' );
define( 'BOARDS_PKG_INCLUDE_PATH', BIT_ROOT_PATH . basename( $pRegisterHash['package_path'] ) . '/includes/');
define( 'BOARDS_PKG_CLASS_PATH', BIT_ROOT_PATH . basename( $pRegisterHash['package_path'] ) . '/includes/classes/');
define( 'BOARDS_PKG_ADMIN_PATH', BIT_ROOT_PATH . basename( $pRegisterHash['package_path'] ) . '/admin/');
$gBitSystem->registerPackage( $pRegisterHash );

if( $gBitSystem->isPackageActive( 'boards' ) && $gBitUser->hasPermission( 'p_boards_read' )) {
	$menuHash = [
		'package_name'  => BOARDS_PKG_NAME,
		'index_url'     => BOARDS_PKG_URL.'index.php',
		'menu_template' => 'bitpackage:boards/menu_boards.tpl',
	];
	$gBitSystem->registerAppMenu( $menuHash );

	$registerArray = [
		'content_display_function' => 'boards_content_display',
		'content_preview_function' => 'boards_content_edit',
		'content_edit_function' => 'boards_content_edit',
		'content_store_function' => 'boards_content_store',
		'content_verify_function' => 'boards_content_verify',
		'content_expunge_function' => 'boards_content_expunge',
		'comment_store_function'		=> 'boards_comment_store',
//		'content_view_tpl' => 'bitpackage:boards/service_view_boards.tpl',
		'content_icon_tpl' => 'bitpackage:boards/boards_service_icons.tpl',
		'content_list_sql_function' => 'boards_content_list_sql',
	];

	if ( !$gBitSystem->isFeatureActive( 'boards_hide_edit_tpl' ) &&
		 !$gBitSystem->isFeatureActive( 'boards_link_by_pigeonholes' ) ) {
		$registerArray['content_edit_mini_tpl'] = 'bitpackage:boards/boards_edit_mini_inc.tpl';
	}

	$gLibertySystem->registerService( LIBERTY_SERVICE_FORUMS, BOARDS_PKG_NAME, $registerArray );

	function boards_get_topic_comment( $pThreadForwardSequence ) {
		return (int) ( substr( $pThreadForwardSequence, 0, 9 ) );
	}

	$gBitThemes->loadCss(BOARDS_PKG_PATH.'styles/boards.css');

	/**
	 * load up moderation in case we are using modcomments
	 * we need to include its bit_setup_inc incase comments gets loaded first
	 */
	if( file_exists(BIT_ROOT_PATH.'moderation/bit_setup_inc.php') ) {
		require_once BIT_ROOT_PATH.'moderation/bit_setup_inc.php';
		global $gModerationSystem;

		if( $gBitSystem->isPackageActive( 'moderation' ) ) {

			// Register our event handler
			$gModerationSystem->registerModerationObserver(BOARDS_PKG_NAME, 'modcomments', 'board_comments_moderation');
			$gModerationSystem->registerModerationObserver(BOARDS_PKG_NAME, 'liberty', 'board_comments_moderation');

			// And define the function we use to handle the observation.
			function board_comments_moderation($pModerationInfo) {
				if( $pModerationInfo['type'] == 'comment_post' ) {
					$storeComment = new LibertyComment( null, $pModerationInfo['content_id'] );
					$storeComment->load();
					$comments_return_url = '';
					$root_id = $storeComment->mInfo['root_id'];
					global $gContent;
					$board = new BitBoard(null, $root_id);
					$board->load();
					$boardSync = $board->getPreference('board_sync_list_address');
					$code = $storeComment->getPreference('board_confirm_code');
					$approved = $board->getPreference('boards_mailing_list_password');
					// Possible race. Did we beat the cron?
					if( empty($code) ) {
						require_once BOARDS_PKG_INCLUDE_PATH.'admin/boardsync_inc.php';
						// Try to pick up the message!
						board_sync_run(true);
					}
					if( !empty($code) && !empty($boardSync) && !empty($approved) ) {
						$boardSync = str_replace('@', '-request@', $boardSync);
						$code = 'confirm '.$code;
						require_once KERNEL_PKG_CLASS_PATH.'BitMailer.php';
						$mailer = new BitMailer();

						if( $pModerationInfo['last_status'] == MODERATION_DELETE ) {
							// Send a reject message
							$mailer->sendEmail($code, '', $boardSync,
											   [ 'sender' =>
													  BitBoard::getBoardSyncInbox(), ], );
						} else {
							// Send an accept message
							$mailer->sendEmail($code, '', $boardSync,
											   ['sender' =>
													 BitBoard::getBoardSyncInbox(),
													 'x_headers' =>
													 [ 'Approved' =>
															$approved, ], ], );
						}
					}
				}
			}
		}
	}
}