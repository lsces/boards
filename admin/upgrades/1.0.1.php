<?php
/**
 * @version $Header$
 */
global $gBitInstaller;

$infoHash = [
	'package'      => BOARDS_PKG_NAME,
	'version'      => str_replace( '.php', '', basename( __FILE__ )),
	'description'  => "Add boards sections and positioning.",
	'post_upgrade' => null,
];
$gBitInstaller->registerPackageUpgrade( $infoHash, [

[ 'DATADICT' => [
	[ 'CREATE' => [
		'boards_sections' => "
			section_id I4 PRIMARY,
			section_title C(255)
		",
	]],
	// insert new column
	[ 'ALTER' => [
		'boards' => [
			'section_id' => [ '`section_id`', 'I4' ],
			'pos' => [ '`pos`', 'I4' ],
	], ]],
	[ 'CREATEINDEX' => [
		'boards_sections_idx'       => [ 'boards', 'section_id', [] ],
	]],
	[ 'CREATESEQUENCE' => [
		'boards_sections_id_seq',
	]],
]],

[ 'PHP' => '
// Is package installed and enabled
global $gBitSystem;

$gBitSystem->verifyPackage( "boards" );

require_once( BOARDS_PKG_CLASS_PATH."BitBoardTopic.php");

$oTopic = new BitBoardTopic();

// get a list of all the bad records
$list_query = "SELECT bt.*
            FROM `".BIT_DB_PREFIX."boards_topics` bt 
            INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id`= bt.`parent_id` ) 
            WHERE lc.content_type_guid != ?";

$bind_vars = array( "bitcomment" );

$max_records = 99999;

$map_errors = $oTopic->mDb->query( $list_query, $bind_vars, $max_records );

// fix everything 
// transaction will save us if something goes bad
$oTopic->StartTrans();

// expunge all the bad records we just got a list of
$expunge_query = "DELETE FROM `".BIT_DB_PREFIX."boards_topics` 
                    WHERE `parent_id` IN 
                    ( SELECT bt.`parent_id` 
                        FROM `".BIT_DB_PREFIX."boards_topics` bt 
                        INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id`= bt.`parent_id` ) 
                        WHERE lc.content_type_guid != ? )";
$oTopic->mDb->query( $expunge_query, $bind_vars );

// repopulate the records with the proper parent_id value
while( $topic = $map_errors->fetchRow() ) {
    $store_hash = $topic;
    $comment_query = "SELECT lcom.`content_id` FROM `".BIT_DB_PREFIX."liberty_comments` lcom WHERE lcom.`comment_id` = ?";
    // if the mapping isnt totally screwed up the parent id should work as a comment_id
    if( $comment_content_id = $oTopic->mDb->getOne( $comment_query, array( $topic["parent_id"] ) ) ){
        // just to be doublely safe, make sure the record doesnt already exist in the table
        if( !$oTopic->mDb->getOne( "SELECT parent_id FROM boards_topics WHERE parent_id = ?", $comment_content_id ) ){
            $store_hash["parent_id"] = $comment_content_id;
            // reinsert the topic
            if( $result = $oTopic->mDb->associateInsert( "boards_topics", $store_hash ) ){
                echo "Table boards_topic mapping repaired for topic/comment content id:" . $comment_content_id . "<br />";
            }
        }else{
            echo "Duplicate record for topic/comment content id:" . $comment_content_id . ", insertion ignored <br />";
        }
    }
}

$oTopic->CompleteTrans();
' ],

]);
?>
