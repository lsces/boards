{strip}
{if $comments_style eq 'threaded' and $comment.level}
	<div style="margin-left:20px">
{else}
	<div style="margin-left:0px">
{/if}
<div class="body" id="comment_{$comment.content_id|escape}">

	{if $gBitUser->getPreference('boards_show_avatars','y') == 'y'}
		<div class="userinfo">
			{if $comment.user_id == $smarty.const.ANONYMOUS_USER_ID}
				<strong>{$comment.anon_name|escape}</strong>
			{else}
				<strong>{displayname hash=$comment}</strong>
				<br />
				{if $comment.user_id != $smarty.const.ANONYMOUS_USER_ID and !empty($comment.user_avatar_url)}
					<div><a href="{$comment.user_url}"><img src="{$comment.user_avatar_url}" class="img-responsive" alt="Profile Picture" /></a></div>
				{/if}
				<div class="date">Joined: {$comment.registration_date|bit_short_date}</div>
			{/if}
		</div>

	{/if}

	<div class="wrapper{if $gBitUser->getPreference('boards_show_avatars','y') == 'y'} showavatar{/if}{if $smarty.request.comments_style eq 'threaded'} indent{$comment.level}{/if}">
		{if !$post_is_preview}
			<div class="floaticon">
				{if $print_page ne 'y' and $comment.deleted==0 }
					{if !$topic_locked and $board->hasPostCommentsPermission()}
						<a href="{$comments_return_url}&amp;post_comment_reply_id={$comment.content_id}&amp;post_comment_request=1#editcomments" rel="nofollow">{biticon ipackage="icons" iname="internet-group-chat"  ipackage="icons"  iexplain="Reply to this Post" iforce="icon"}</a>
					{/if}
					{if !$topic_locked and $board->hasPostCommentsPermission()}
						<a href="{$comments_return_url}&amp;post_comment_reply_id={$comment.content_id}&amp;post_comment_request=1&amp;quote=y#editcomments" rel="nofollow">{biticon ipackage="icons" iname="internet-group-chat" iexplain="Reply with Quote to this Post" iforce="icon"}</a>
					{/if}
					{if $comment.is_editable or $gContent->hasUserPermission('p_liberty_edit_comments')}
						<a href="{$comments_return_url}&amp;post_comment_id={$comment.comment_id}&amp;post_comment_request=1#editcomments" rel="nofollow">{biticon ipackage="icons" iname="edit" ipackage="icons" iexplain="Edit" iforce="icon"}</a>
					{/if}
					{if $board->hasUserPermission( 'p_liberty_admin_comments' )}
						<a href="{$comments_return_url}&amp;delete_comment_id={$comment.comment_id}" rel="nofollow">{biticon ipackage="icons" iname="user-trash" ipackage="icons" iexplain="Remove" iforce="icon"}</a>
					{/if}
					{if $board->hasUpdatePermission() and (($comment.user_id<0 and $comment.is_approved==0)or$comment.user_id>=0) and !$comment.is_warned}
						{if $comment.user_id<0 and $comment.is_approved==0}
							<a title="Approve this post" href="{$smarty.const.BOARDS_PKG_URL}view_topic_inc.php?t={$thread->mRootId}&amp;action=1&amp;comment_id={$comment.comment_id}">
								{biticon ipackage="icons" iname="list-add"  ipackage="icons"  iexplain="Approve Post" iforce="icon"}
							</a>

							<a title="Reject this post" href="{$smarty.const.BOARDS_PKG_URL}view_topic_inc.php?t={$thread->mRootId}&amp;action=2&amp;comment_id={$comment.comment_id}">
								{biticon ipackage="icons" iname="list-remove"  ipackage="icons"  iexplain="Reject Post" iforce="icon"}
							</a>
						{elseif !$comment.is_warned and $comment.user_id>=0}
							<a onclick="return BitBoards.warn( 'warn_block_{$comment.comment_id|escape:"url"}', this )" title="Warn the poster about this post" href="{$smarty.const.BOARDS_PKG_URL}view_topic_inc.php?t={$thread->mRootId}&amp;action=3&amp;comment_id={$comment.comment_id}">
								{biticon ipackage="icons" iname="dialog-warning"  ipackage="icons"  iexplain="Warn Post" iforce="icon"}
							</a>

							<div class="warn_block" style="display:none;" id="warn_block_{$comment.comment_id|escape:"url"}">
								{form action="`$thread_mInfo.display_url`"}
									<input type="hidden" value="3" name="action" />
									<input type="hidden" value="{$thread_mInfo.th_thread_id}" name="t" />
									<input type="hidden" value="{$comment.comment_id}" name="comment_id" />
									<textarea style="vertical-align: top;" rows="3" cols="10" name="warning_message" onclick="this.value=''; this.innerHTML=''; this.onclick=null;">
										Enter Warning Message
									</textarea>
									<input type="submit" class="btn btn-default" value="Warn" />
								{/form}
							</div>
						{/if}
					{/if}
				{/if}<!-- end print_page -->
			</div><!-- end .floaticon -->
		{/if}
		<div class="header">
			{if $comment.title neq ""}<h2>{$comment.title|escape}</h2>{/if}
			<span class="date">
				{if $gBitUser->getPreference('boards_show_avatars','y') != 'y'}
					Posted by: {if $comment.user_id < 0}{$comment.unreg_uname|escape}{else}{displayname hash=$comment}{/if}, 
				{else}
					Posted: 
				{/if}
				{$comment.created|reltime}
				{if $board->hasAdminPermission() and $comment.last_modified != $comment.created}
					<em> Last modified:
					{if $comment.user_id < 0}
						{$comment.unreg_uname|escape}
					{else}
						{displayname user=$comment.modifier_user user_id=$comment.modifier_user_id real_name=$comment.modifier_real_name}
					{/if}, {$comment.last_modified|reltime} </em>
				{/if}
			</span>
		</div><!-- end .header -->

		{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='comment' serviceHash=$comment}

		{if $gBitUser->isRegistered() and $comment.is_warned}
			{assign var=comment_id value=$comment.comment_id}
			<div class="warning">
				<a onclick="return BitBoards.warn('warned_message_{$comment.comment_id|escape:"url"}', '{$smarty.const.BOARDS_PKG_URL}ajax.php?req=10&amp;comment_id={$comment_id}', this)"
				   href="{$thread_mInfo.display_url}&amp;warning[{$comment_id}]={if empty($warnings.$comment_id)}show{else}hide{/if}"
				>{biticon ipackage="icons" iname="dialog-warning" iexplain="Warned Post"}</a>
				<div id="warned_message_{$comment.comment_id|escape:"url"}" style="{if empty($warnings.$comment_id)}display:none{/if}">
					{if !empty($warnings.$comment_id)}{$comment.warned_message}{/if}
				</div>
			</div>
		{/if}

		<div class="content">
			{$comment.parsed_data}
			{if $gBitSystem->isFeatureActive( 'comments_allow_attachments' )}
				{include file="bitpackage:liberty/list_comment_files_inc.tpl" storageHash=$comment.storage}
			{/if}
		</div><!-- end .content -->
	</div><!-- end .wrapper -->
	<div class="clear"><!-- --></div>
</div> <!-- end .body -->


<div class="signature"> </div>

{if $comment.children}
	<div id="comment_{$comment.content_id}_children">
		{foreach key=key item=item from=$comment.children}
			{include file="bitpackage:boards/post_display.tpl" comment=$item}
		{/foreach}
	</div>
{/if}
<div id="comment_{$comment.content_id}_footer"></div>

</div><!-- end .left margin -->
{/strip}
