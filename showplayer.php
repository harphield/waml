<?phprequire_once 'db.php';@require_once 'security.php';$player = $_GET['add'];//$season = $_GET['season']; if (!is_numeric($player)) {    $player = 903; //Yazphier}if ($isadmin) {   if (isset($_POST['type'])) {    $add = false;    $rem = false;    if ($_POST['type'] == 'moveleague') {      $rem=true; $add=true;    } elseif ($_POST['type'] == 'delleague') {      $rem=true;    } elseif ($_POST['type'] == 'addleague') {      $add=true;    }    // this due to restriction, i cannot move a player :/    // first have to delete    if ($rem) {      if(isset($_POST['fromlid'])) {	$fromlid = $_POST['fromlid'];	if (!is_numeric($fromlid)) {echo 'error fromlid';return;}	// first remove the games, then the score, then the entry	$stmt = $dbh->prepare("DELETE from yseasongames WHERE (sid,gid) in (SELECT sid,gid from ygames_players  natural join yseasonplayers natural join yseasongames natural join yleagues where lid=:fromlid AND pid=:pid)");	$stmt->bindParam(':fromlid', $lid);	$stmt->bindParam(':pid', $pid);	$stmt->execute();	echo $stmt->rowCount() . ' deleted games (will be readded by cron)<BR>';	$stmt = $dbh->prepare("DELETE from yseasonscore WHERE lid=:fromlid AND pid=:pid");	$stmt->bindParam(':fromlid', $fromlid);	$stmt->bindParam(':pid', $player);	$stmt->execute();	echo $stmt->rowCount() . ' deleted scores<BR>';	$stmt = $dbh->prepare("DELETE from yseasonplayers WHERE lid=:fromlid AND pid=:pid");	$stmt->bindParam(':fromlid', $fromlid);	$stmt->bindParam(':pid', $player);	$stmt->execute();	echo $stmt->rowCount() . ' deleted players<BR>';      }    }    if ($add) {      if(isset($_POST['tolid'])) {	$tolid = $_POST['tolid'];	if (!is_numeric($tolid)) {echo 'error tolid';return;}	$stmt = $dbh->prepare("INSERT INTO yseasonplayers (lid,pid) VALUES (:tolid,:pid)");	$stmt->bindParam(':tolid', $tolid);	$stmt->bindParam(':pid', $player);	$stmt->execute();	echo $stmt->rowCount() . ' added players<BR>If no dublicates during same season cron will handle this just fine<BR>';      }    }  }?>  <script type="text/javascript">    $(function() {      $('#moveleague').change(function() {	if ($(this).is(':checked')) {	  $('#formmoveleague').show();	} else {	  $('#formmoveleague').hide();	}      });      $('#addleague').change(function() {	if ($(this).is(':checked')) {	  $('#formaddleague').show();	} else {	  $('#formaddleague').hide();	}      });      $('#delleague').change(function() {	if ($(this).is(':checked')) {	  $('#formdelleague').show();	} else {	  $('#formdelleague').hide();	}      });    });  </script><?php }/*SELECT (SELECT name from yplayers where pid=f.pid) as name1,  f.real_points as points1,  (SELECT name from yplayers where pid=s.pid) as name2,  s.real_points as points2,  (SELECT name from yplayers where pid=t.pid) as name3,  t.real_points as points3,  (SELECT name from yplayers where pid=l.pid) as name4,  l.real_points as points4,  g.gid FROM   (SELECT gid,pid,real_points FROM ygames_players WHERE placement=1) AS fJOIN (SELECT gid,pid,real_points FROM ygames_players WHERE placement=2) AS sJOIN (SELECT gid,pid,real_points FROM ygames_players WHERE placement=3) AS tJOIN (SELECT gid,pid,real_points FROM ygames_players WHERE placement=4) AS lNATURAL RIGHT JOIN (select gid,gametime from ygames natural join yseasongames natural join yleagues where lid=1) AS gON t.gid=g.gid ON g.gid=s.gid ON f.gid=g.gid;*//*SELECT (SELECT name FROM yplayers WHERE pid=f.pid) AS name1,  f.real_points AS points1,  (SELECT name FROM yplayers WHERE pid=s.pid) AS name2,  s.real_points AS points2,  (SELECT name FROM yplayers WHERE pid=t.pid) AS name3,  t.real_points AS points3,  (SELECT name FROM yplayers WHERE pid=l.pid) AS name4,  l.real_points AS points4,  (SELECT CASE WHEN banned THEN 'banned' ELSE lshort END FROM yleagues NATURAL JOIN yseasonplayers WHERE sid=g.sid AND pid=f.pid) AS league1,  (SELECT CASE WHEN banned THEN 'banned' ELSE lshort END FROM yleagues NATURAL JOIN yseasonplayers WHERE sid=g.sid AND pid=s.pid) AS league2,  (SELECT CASE WHEN banned THEN 'banned' ELSE lshort END FROM yleagues NATURAL JOIN yseasonplayers WHERE sid=g.sid AND pid=t.pid) AS league3,  (SELECT CASE WHEN banned THEN 'banned' ELSE lshort END FROM yleagues NATURAL JOIN yseasonplayers WHERE sid=g.sid AND pid=l.pid) AS league4,  g.gid, to_char(g.gametime,'YYYY-MM-DD HH24:MI') AS gametime, g.value  FROM (SELECT gid,pid,real_points FROM ygames_players WHERE placement=1) AS f    JOIN (SELECT gid,pid,real_points FROM ygames_players WHERE placement=2) AS s    JOIN (SELECT gid,pid,real_points FROM ygames_players WHERE placement=3) AS t    JOIN (SELECT gid,pid,real_points FROM ygames_players WHERE placement=4) AS l    NATURAL RIGHT JOIN (select sid,gid,gametime,value FROM ygames NATURAL JOIN yseasongames    NATURAL JOIN yleagues WHERE lid=:league) AS g ON t.gid=g.gid ON g.gid=s.gid ON f.gid=g.gid      ORDER BY gid");//AND scgames >= 20//to_char(g.gametime,'YYYY/MM/DD') AS gametime  */$stmt = $dbh->prepare("SELECT sshort, lshort, lname, lid, banned, name FROM yleagues NATURAL JOIN yseasonplayers NATURAL JOIN yplayers NATURAL JOIN yseasons WHERE pid=:player");$stmt->bindParam(':player', $player);$stmt->execute();if ($stmt->rowCount() > 0) {	$leagues = $stmt->fetchAll();}?> for <?php echo htmlspecialchars($leagues[0]['name']) ?><table class="table table-striped table-condensed">  <thead><tr>    <th>League</th>    <th>Banned</th>  </tr></thead>  <tbody><?php foreach ($leagues as $league) { ?>    <tr><td><span class="league l_<?php echo $league['sshort'];?>"><?php echo $league['sshort'];?></span><span class="league l_<?php echo $league['lshort'];?>"><?php echo $league['lshort'];?></span>	<a href="index.php?p=<?php echo $league[lid]+100?>"><?php echo $league['lname'];?></a></td>      <td><?php echo $league['banned'] ? 'true' : 'false';?></td><?php if ($isadmin) echo "<td><a href='index.php?p=ban&i=". $player .'&l=' . $league['lid'] . "'>toggle ban</a></td>";?>    </tr><?php } ?>  </tbody></table><?php if ($isadmin) { $stmt = $dbh->prepare("SELECT lname, lid FROM yleagues WHERE lid NOT IN (SELECT lid FROM yseasonplayers WHERE pid=:player)");$stmt->bindParam(':player', $player);$stmt->execute();if ($stmt->rowCount() > 0) {	$allleagues = $stmt->fetchAll();}?><div align="right"><input type="checkbox" id="moveleague"> Move <input type="checkbox" id="addleague"> Add to League <input type="checkbox" id="delleague"> Delete from League</div><P><P><div id="formmoveleague" hidden><form action="index.php?p=pl&i=<?php echo $player ?>" method="post">  Move from     <select name="fromlid"><?php foreach ($leagues as $league) { ?>      <option value="<?php echo $league['lid'] ?>"><?php echo $league['lname']?></option><?php } ?>    </select> to league     <select name="tolid"><?php foreach ($allleagues as $league) { ?>      <option value="<?php echo $league['lid'] ?>"><?php echo $league['lname']?></option><?php } ?>    </select>    <input name="type" value="moveleague" hidden>    <input type="submit" value="submit"></form></div><P><div id="formaddleague" hidden><form action="index.php?p=pl&i=<?php echo $player ?>" method="post">  Add player to      <select name="tolid"><?php foreach ($allleagues as $league) { ?>      <option value="<?php echo $league['lid'] ?>"><?php echo $league['lname']?></option><?php } ?>    </select>    <input name="type" value="addleague" hidden>    <input type="submit" value="submit"></form></div><P><div id="formdelleague" hidden><form action="index.php?p=pl&i=<?php echo $player ?>" method="post">  Delete player from     <select name="fromlid"><?php foreach ($leagues as $league) { ?>      <option value="<?php echo $league['lid'] ?>"><?php echo $league['lname']?></option><?php } ?>    </select>    <input name="type" value="delleague" hidden>    <input type="submit" value="submit"></form></div><P><?php }$stmt = $dbh->prepare("SELECT (SELECT name FROM yplayers WHERE pid=f.pid) AS name1,  f.real_points AS points1,  (SELECT name FROM yplayers WHERE pid=s.pid) AS name2,  s.real_points AS points2,  (SELECT name FROM yplayers WHERE pid=t.pid) AS name3,  t.real_points AS points3,  (SELECT name FROM yplayers WHERE pid=l.pid) AS name4,  l.real_points AS points4,  (SELECT CASE WHEN banned THEN 'banned' ELSE lshort END FROM yleagues NATURAL JOIN yseasonplayers WHERE sid=g.sid AND pid=f.pid) AS league1,  (SELECT CASE WHEN banned THEN 'banned' ELSE lshort END FROM yleagues NATURAL JOIN yseasonplayers WHERE sid=g.sid AND pid=s.pid) AS league2,  (SELECT CASE WHEN banned THEN 'banned' ELSE lshort END FROM yleagues NATURAL JOIN yseasonplayers WHERE sid=g.sid AND pid=t.pid) AS league3,  (SELECT CASE WHEN banned THEN 'banned' ELSE lshort END FROM yleagues NATURAL JOIN yseasonplayers WHERE sid=g.sid AND pid=l.pid) AS league4,  g.gid, to_char(g.gametime,'YYYY-MM-DD HH24:MI') AS gametime, g.value,  (SELECT sshort FROM yseasons WHERE sid=g.sid) AS sshort  FROM (SELECT gid,pid,real_points FROM ygames_players WHERE placement=1) AS f    JOIN (SELECT gid,pid,real_points FROM ygames_players WHERE placement=2) AS s    JOIN (SELECT gid,pid,real_points FROM ygames_players WHERE placement=3) AS t    JOIN (SELECT gid,pid,real_points FROM ygames_players WHERE placement=4) AS l    NATURAL RIGHT JOIN (SELECT sid,gid,gametime,value FROM ygames NATURAL LEFT JOIN yseasongames WHERE gid IN (SELECT gid FROM ygames_players WHERE pid=:player)) AS g ON t.gid=g.gid ON g.gid=s.gid ON f.gid=g.gid      ORDER BY gid DESC");//  (SELECT CASE WHEN (g.sid NOT NULL) THEN (SELECT sshort FROM yseasons WHERE sid=g.sid) ELSE '-' END) AS sshort$stmt->bindParam(':player', $player);$stmt->execute();if ($stmt->rowCount() > 0) {	$games = $stmt->fetchAll();}?><table class="table table-striped table-condensed">				<thead>					<tr>						<th>Date</th>						<th>1st</th>						<th>2nd</th>						<th>3rd</th>						<th>4th</th>						<th>Value</th>					</tr>				</thead>				<tbody><?php foreach ($games as $game) { ?>				  <tr><td><span class="league l_<?php echo $game['sshort'];?>"><?php echo $game['sshort'];?></span><?php echo $game['gametime']; ?></td>				    <td<?php if ($game['league1']=='banned') {					 echo ' class="ban"'; $game['league1']='X'; }				      ?>><span class="league l_<?php echo $game['league1'];?>"><?php echo $game['league1'];?></span>				      <strong><?php echo htmlspecialchars($game['name1']);?></strong> (<?php echo $game['points1'];?>)</td>				    <td<?php if ($game['league2']=='banned') {					 echo ' class="ban"'; $game['league2']='X'; }				      ?>><span class="league l_<?php echo $game['league2'];?>"><?php echo $game['league2'];?></span>				      <strong><?php echo htmlspecialchars($game['name2']);?></strong> (<?php echo $game['points2'];?>)</td>				    <td<?php if ($game['league3']=='banned') {					 echo ' class="ban"'; $game['league3']='X'; }				      ?>><span class="league l_<?php echo $game['league3'];?>"><?php echo $game['league3'];?></span>				      <strong><?php echo htmlspecialchars($game['name3']);?></strong> (<?php echo $game['points3'];?>)</td>				    <td<?php if ($game['league4']=='banned') {					 echo ' class="ban"'; $game['league4']='X'; }				      ?>><span class="league l_<?php echo $game['league4'];?>"><?php echo $game['league4'];?></span>				      <strong><?php echo htmlspecialchars($game['name4']);?></strong> (<?php echo $game['points4'];?>)</td>				    <td><?php echo $game['value']; ?></td>				  </td></tr><?php }/*					2013/06/30</td><td class=""><span class="league l_N">N</span> <strong>NoName</strong> (X)</td><td class=""><span class="league l_N">N</span> <strong>テルテル</strong> (35.72)</td><td class=""><span class="league l_B">B</span> <strong>kosutasu</strong> (9.6)</td><td class=""><span class="league l_N">N</span> <strong>Sponde</strong> (11.28)</td></tr>*/?></tbody></table>