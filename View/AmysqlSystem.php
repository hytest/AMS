<?php include('AmysqlHeader.php'); ?>

<script src="View/js/language/list.js"></script>
<?php if(isset($_POST['submit'])) { ?>
<script src="View/js/language/<?php echo $SystemConfig -> language;?>.js"></script>
<script>
parent.parent._L = <?php echo json_encode($SystemConfig -> language);?>;
parent.parent.L = L;
</script>
<?php }?>

<body id="body">
<span id="h1">{js}L.SysSet{/js}</span>

<form action="index.php?c=index&a=AmysqlSystem" method="POST" id="AmysqlSystemContent">
	<div class="block">
	<h1>» {js}L.SysRelevant{/js}</h1>
	<p>{js}L.SysDefaultLanguage{/js}
	<span id="LanguageBlock">
	</span>
	</p>
	<p>{js}L.SysNoticeLogout{/js}<input type="checkbox" name="LogoutNotice" <?php if($SystemConfig -> LogoutNotice) echo 'checked=""';?> /></p>
	</div>

	<div class="block">
	<h1>» {js}L.SysSqlEditRelevant{/js}</h1>
	<p>{js}L.SysSqlEditCapital{/js} <input type="checkbox" name="SqlUppercase" <?php if($SystemConfig -> SqlUppercase) echo 'checked=""';?> /></p>
	<p>{js}L.SysSqlEditBold{/js} <input type="checkbox" name="SqlBold" <?php if($SystemConfig -> SqlBold) echo 'checked=""';?> /></p>
	<p>{js}L.SysSqlEditEnter{/js} <input type="text" name="SqlLine" style="width:40px;" value="<?php echo $SystemConfig -> SqlLine;?>" /></p>
	</div>

	<div class="block">
	<h1>» {js}L.SysTableRelevant{/js} </h1>
	<p>{js}L.SysTableResultLimit{/js} <input type="text" name="TableDataLine" style="width:40px;" value="<?php echo $SystemConfig -> TableDataLine;?>" /> &nbsp;
	<i>(LIMIE <?php echo $SystemConfig -> TableDataLine;?>) </i></p>
	<p>{js}L.SysTableShowIndex{/js} <input type="checkbox" name="TableShowIndex" <?php if($SystemConfig -> TableShowIndex) echo 'checked=""';?> /></p>
	</div>
	
	<?php if(isset($_POST['submit'])) { ?> <p id="time"><i>UpTime: <?php echo $SystemConfig -> time;?></i></p> <?php }?>
	<p><input type="submit" value="{js}L.SysSave{/js}" id="submit" name="submit" /></p>
</form>
<script>
var body = G('body');
C(body, 'In', JsValue(body.innerHTML));

LSelectData = [];
for (var k in LanguageList )
	LSelectData.push(LanguageList[k]['text'] + '|' + k);
LSelectDom = CreatesSelect(LSelectData);
LSelectDom.name = 'language';
LSelectDom.value = parent.parent._L;
C(G('LanguageBlock'), 'In', LSelectDom);

</script>
</body>
</html>