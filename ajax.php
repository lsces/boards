<?php
/**
 * AJAX Function Call Stuff
 *
 * reqs:
 *   1 - list all boards
 *   2 - switch lock state on a given topic
 *   3 - switch sticky state on a given topic
 * @package boards
 * @subpackage functions
 */

/**
 * required setup
 */
require_once '../kernel/includes/setup_inc.php';

// Is package installed and enabled
$gBitSystem->verifyPackage( 'boards' );

// Now check permissions to access this page
$gBitSystem->verifyPermission( 'p_boards_read' );

function ajax_nice_error($errno, $errstr, $errfile, $errline) {
	$errortype =  [
	E_ERROR => [
	'desc'=>"Error",
	'ignore'=>false, ],
	E_WARNING => [
	'desc'=> "Warning",
	'ignore'=>false, ],
	E_PARSE => [
	'desc'=> "Parsing Error",
	'ignore'=>false, ],
	E_NOTICE => [
	'desc'=> "Notice",
	'ignore'=>true, ],
	E_CORE_ERROR => [
	'desc'=> "Core Error",
	'ignore'=>false, ],
	E_CORE_WARNING => [
	'desc'=> "Core Warning",
	'ignore'=>false, ],
	E_COMPILE_ERROR => [
	'desc'=> "Compile Error",
	'ignore'=>false, ],
	E_COMPILE_WARNING => [
	'desc'=> "Compile Warning",
	'ignore'=>false, ],
	E_USER_ERROR => [
	'desc'=> "User Error",
	'ignore'=>false, ],
	E_USER_WARNING => [
	'desc'=> "User Warning",
	'ignore'=>false, ],
	E_USER_NOTICE => [
	'desc'=> "User Notice",
	'ignore'=>false, ],
	E_STRICT => [
	'desc'=> "Runtime Notice",
	'ignore'=>true, ],
	];
	// set of errors for which a var trace will be saved

	if(!$errortype[$errno]['ignore']) {
		$l = ob_get_level();
		if ($l>0) {
			$body = ob_get_contents();
			ob_end_clean();
		}
		static $sent=false;
		if (!$sent) {
			header("HTTP/1.0 500 Internal Server Error");
			echo "<h1>PHP Exception</h1>";
			$sent=true;
		}
		$str= "<br />\n<b>{$errortype[$errno]['desc']}</b>: $errstr in <b>$errfile</b> on line <b>$errline</b>\n<br />\n";
		echo $str;//. "<pre>". htmlspecialchars(var_export($vars,true))."</pre>";
		if ($l>0) {
			ob_start();
			echo $body;
		}
	}
}

set_error_handler("ajax_nice_error");

switch ($_GET['req']) {
	case 10:
		require_once( BOARDS_PKG_CLASS_PATH.'BitBoardPost.php' );
		$comment = new BitBoardPost($_GET['comment_id']);
		$comment->loadMetaData();
		if (@$comment->verifyId($comment->mCommentId)) {
			print $comment->mInfo['warned_message'];
		} else {
			trigger_error(var_export($comment->mErrors,true ));
		}
		break;
	default:
		break;
}
?>
