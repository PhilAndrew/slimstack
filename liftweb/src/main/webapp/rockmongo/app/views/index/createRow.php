<h3><?php render_navigation($db,$collection,false); ?> &raquo; <?php hm("createrow"); ?></h3>

<?php if (isset($error)):?> 
<p class="error"><?php h($error);?></p>
<?php endif; ?>
<?php if (isset($message)):?> 
<p class="message"><?php h($message);?></p>
<script language="javascript">
window.parent.frames["left"].location.reload();
</script>
<?php endif; ?>

<form method="post">
<?php hm("data"); ?>:<br/>
<textarea rows="35" cols="70" name="data"><?php echo x("data") ?></textarea><br/>
<input type="submit" value="<?php hm("save"); ?>"/>
</form>

<?php hm("validarray"); ?>
<blockquote>
<pre>
array (
	'value1' => 1,
	'value2' => 2,
	...
);
</pre>
</blockquote>