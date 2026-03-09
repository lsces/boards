{* $Header$ *}
{strip}
<div class="floaticon">{bithelp}</div>

<div class="admin boards">
	{if $preview}
		<h2>Preview {$gContent->getTitle()|escape}</h2>
		<div class="preview">
			{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='nav' serviceHash=$gContent->mInfo}
				<div class="display boards">
					<div class="header">
						<h1>{$gContent->getTitle()|escape|default:"Board"}</h1>
					</div><!-- end .header -->

					<div class="body">
						<div class="content">
							{$gContent->mInfo.parsed_data}
						</div><!-- end .content -->
					</div><!-- end .body -->
				</div><!-- end .boards -->
			{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='view' serviceHash=$gContent->mInfo}
		</div>
	{/if}

	<div class="header">
		<h1>
			{if $gContent->isValid()}
				Edit {$gContent->getTitle()|escape}
			{else}
				Create New Board
			{/if}
		</h1>
	</div>

	<div class="body">
		{form enctype="multipart/form-data" id="editbitboardform"}
		{jstabs}
			{jstab title=$leg}
				<input type="hidden" name="bitboard[board_id]" value="{$gContent->mInfo.board_id}" />

				<div class="form-group">
					{formlabel label="Title" for="title"}
					{forminput}
						<input type="text" class="form-control" maxlength="200" name="bitboard[title]" id="title" value="{$gContent->getTitle()|escape}" />
					{/forminput}
				</div>

				{textarea name="bitboard[edit]" edit=$gContent->mInfo.data}

				{* any simple service edit options *}
				{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_edit_mini_tpl"}
			{/jstab}

			{include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_edit_tab_tpl"}
		{/jstabs}

		<div class="form-group submit">
			<input type="submit" class="btn btn-primary" name="save_bitboard" value="Save" /> <input type="submit" class="btn btn-default" name="preview" value="Preview" />
		</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .boards -->

{/strip}
