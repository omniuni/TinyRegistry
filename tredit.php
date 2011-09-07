<?php
include('tr.php');
$TR = new tinyRegistry;
$MESSAGES = new tinyRegistry;
$MESSAGES->open('__messages');
if(isset($_REQUEST['addmessage'])){$MESSAGES->push(microtime(true), sqlite_escape_string(stripslashes($_REQUEST['addmessage'])));}

if(isset($_GET['REGDROP'])){
	$MESSAGES->push(microtime(true), 'Drop Registry '.stripslashes($_GET['REGDROP']));
	$TR->registrydrop(sqlite_escape_string(stripslashes($_GET['REGDROP'])));
	if(stripslashes($_GET['REGDROP']) == '__messages'){
		$MESSAGES = null; $MESSAGES = new tinyRegistry;
		$MESSAGES->open('__messages');
		$MESSAGES->push(microtime(true), 'Messages Cleared.');
		$_GET['REG'] = '__messages';
	}
}
if(isset($_GET['REG'])){
	$MESSAGES->push(microtime(true), 'Open Registry '.stripslashes($_GET['REG']));
	$TR->open(sqlite_escape_string(stripslashes($_GET['REG'])));
}
if(isset($_POST['RPUSHKEY'])){
	$TR->push(stripslashes($_POST['RPUSHKEY']), stripslashes($_POST['RPUSHVAL']));
	$MESSAGES->push(microtime(true), 'Registry ['.stripslashes($_GET['REG']).'] push: '.stripslashes($_POST['RPUSHKEY']).','.stripslashes($_POST['RPUSHVAL']));
}
if(isset($_GET['DEL'])){
	$TR->pull(false, stripslashes($_GET['DEL']));
	$MESSAGES->push(microtime(true), 'Delete '.stripslashes($_GET['DEL']).' from registry ['.stripslashes($_GET['REG']).']');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<title>TINY REGISTRY EDITOR</title>

<style>
	body{
		font-size: 11pt;
		font-family: monospace;
	}
	a{
		text-decoration: none;
		color: #006;
	}
	a:hover{
		color: #800;
	}
	input{
		background-color: transparent;
		border: none;
		border-bottom: 1pt solid black;
		font-family: monospace;
	}
	input[type="submit"]{
		border: none;
	}
	input:focus{
		outline: none;
	}
	form{
		border: 1pt dashed black;
		margin: 0;
		padding: 2pt;
		width: 100%;
	}
	.leftcol{
		display: block;
		float: left;
		width: 25%;
		margin-right: 2%;
	}
	.rightcol{
		display: block;
		float: left;
		width: 70%;
		padding-left: 2%;
		border-left: 2px solid black;
		min-height: 400px;
	}
	.regentries{
		width: 100%;
		border-collapse: collapse;
		border: 1pt solid black;
	}
	.regentries td{
		border: 1pt solid black;
		padding: 2pt;
	}
</style>

</head>

<body>

<div class="leftcol">
<strong>Registries</strong>
	<form action="?" method="GET" style="text-align: center;">
	<input name="REG" style="width: 80%;"/>
	<input type="hidden" name="addmessage" value="Submitted registry form." />
	<input type="submit" value="+" />
	</form>
<dl>
<dt>SEL?|DEL| NAME</dt>
<?php
$registries = $TR->registrylist();
foreach($registries as $registry){
if(stripslashes($_GET['REG']) == $registry['name']){$selchar = '&rarr;';}else{$selchar = '&nbsp;';}
echo('<dt>['.$selchar.'] | <a href="?REGDROP='.$registry['name'].'">&times;</a>
 | <a href="?REG='.$registry['name'].'">'.$registry['name'].'</a></dt>
');
}
?>
</dl>


</div>

<div class="rightcol">
<strong>Registry Entries</strong><br/>
	<form action="?REG=<?php echo(stripslashes($_GET['REG'])); ?>" method="POST" style="text-align: center; width: 99.4%; margin-bottom: 6pt;">
	K: <input name="RPUSHKEY" style="width: 25%;"/>
	&nbsp;V: <input name="RPUSHVAL" style="width: 57%;"/>
	<input type="hidden" name="addmessage" value="Adding new Registry Entry to <?php echo($_GET['REG']); ?>" />
	<input type="submit" value="+" />
	</form>
<table class="regentries">
<tr style="background-color: #ddd;"><td style="width: 5%;">ID</td><td style="width: 30%;">KEY</td><td>VALUE</td><td style="width: 1em;"></td></tr>
<?php
if(!isset($_GET['REG'])){
echo('NO REGISTRY SELECTED.');
}else{
echo($TR->pull("<tr><td>%i%</td><td>%k%</td><td>%v%</td><td style=\"text-align: center;\"><a href=\"?REG={$_GET['REG']}&amp;DEL=%i%\">&times;</a></td></tr>"));
}
/*
$TR->open('test1');
$TR->push('key1', 'value1',false);
$TR->push('key2', 'value1',true);
$TR->push('lastedit', time(), true);
print_r($TR->pull('%i% ; %k% ; %v% <br/>'));
$TR->push('key2', 'value2');
$TR->pull(false, '3');
print_r($TR->pull('%i% ; %k% ; %v% <br/>'));
*/
?>
</table>

</div>

</body>
</html>