<html>
 <head>
 <title>RockMongo</title>
 <script language="javascript" src="js/jquery-1.4.2.min.js"></script>
 <style type="text/css">
* {font-size:12px; font-family:'Courier New', Arial}
body {margin:0; padding:0}
a { text-decoration:none; color:#004499; line-height:1.5 }

.manual, .server-menu {
  float:right;
  margin-right:100px;
  margin-top:0px;
  background-color:#eee;
  border-left:1px #ccc solid;
  border-top:1px #ccc solid;
  border-right:2px #ccc solid;
  border-bottom:2px #ccc solid;
  padding-left:3px;
  position:absolute;
  display:none;
  width:100px;
}
</style>
<script language="javascript">
/** show manual links **/
function setManualPosition(className, x, y) {
	if ($(className).is(":visible")) {
		$(className).hide();
	}
	else {
		window.setTimeout(function () {
			$(className).show();
			$(className).css("left", x);
			$(className).css("top", y)
		}, 100);
		$(className).find("a").click(function () {
			hideMenus();
		});
	}
}
 
/** hide menus **/
function hideMenus() {
	$(".manual").hide();
	$(".server-menu").hide();
}
</script>
 </head>
<body>
<!-- top bar -->
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td colspan="3" height="20">
			<iframe src="<?php echo $topUrl;?>" name="top" width="100%" frameborder="no" height="20" marginheight="0" scrolling="no"></iframe>
		</td>
	</tr>
	<tr>
		<td valign="top" width="19%" style="border-right:1px #ccc solid" bgcolor="#eeefff">
			<!-- left bar -->
			<iframe src="<?php echo $leftUrl;?>" name="left" width="100%" height="100%" frameborder="no" scrolling="auto" marginheight="0"></iframe>
		</td>
		<td valign="top" width="80%" style="padding-left:10px">
			<!-- right bar -->
			<iframe src="<?php echo $rightUrl;?>" name="right" width="100%" height="100%" frameborder="no" marginheight="0" scrolling="auto"></iframe>
		</td>
	</tr>
</table>

<!-- quick links -->
<div class="manual">
	<a href="http://www.mongodb.org/display/DOCS/Advanced+Queries" target="_blank"><?php hm("querying"); ?></a><br/>
	<a href="http://www.mongodb.org/display/DOCS/Updating" target="_blank"><?php hm("updating"); ?></a><br/>
	<a href="http://www.mongodb.org/display/DOCS/List+of+Database+Commands" target="_blank"><?php hm("commands"); ?></a><br/>
	<a href="http://api.mongodb.org/js/" target="_blank"><?php hm("jsapi"); ?></a><br/>
	<a href="http://www.php.net/manual/en/book.mongo.php" target="_blank"><?php hm("phpmongo"); ?></a>
</div>

<!-- menu when "Tools" clicked -->
<div class="server-menu" style="width:120px">
	<a href="<?php h(url("server")); ?>" target="right"><?php hm("server"); ?></a><br/>
	<a href="<?php h(url("status")); ?>" target="right"><?php hm("status"); ?></a> <br/>
	<a href="<?php h(url("databases")); ?>" target="right"><?php hm("databases"); ?></a> <a href="<?php h(url("createDatabase")); ?>" target="right" title="Create new Database">[+]</a> <br/>
	<a href="<?php h(url("processlist")); ?>" target="right"><?php hm("processlist"); ?></a> <br/>
	<a href="<?php h(url("command")); ?>" target="right"><?php hm("command"); ?></a> <br/>
	<a href="<?php h(url("execute")); ?>" target="right"><?php hm("execute"); ?></a> <br/>
	<a href="<?php h(url("replication")); ?>" target="right"><?php hm("master_slave"); ?></a> 
</div>

</body>
</html>