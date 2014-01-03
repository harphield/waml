<?php
require 'db.php';
date_default_timezone_set('Asia/Tokyo');
$verbose = false;

// check database what day has latest been updated
// recheck that day, if has changes, update
// do so for all days until current day.
// for a maximum of 10 days,
// to prevent massive calls to arcturus

/*
  db readlogs
  id, date, log-hash, nr of saved games
  SERIAL, DATE, CHAR(8), SMALLINT

CREATE TABLE IF NOT EXISTS ygames (
  gid SERIAL,
  gametime timestamp NOT NULL,
  lobby VARCHAR(10) NOT NULL,
  PRIMARY KEY  (gid)
);


CREATE TABLE IF NOT EXISTS ygames_players (
  gid int NOT NULL,
  pid int NOT NULL,
  real_points decimal(10,0) NOT NULL,
  placement smallint NOT NULL
);
//,UNIQUE (gid, pid)

CREATE TABLE IF NOT EXISTS yplayers (
  pid SERIAL,
  name varchar(40) NOT NULL,
  PRIMARY KEY  (pid)
);

*/ 

// legend for nonverbose mode
if (!$verbose) {
  echo '<p>legend: * : game, N : new player, . : player, ! : game already submitted';
}

// clean db part games etc.
if (false) {
//  $stmt = $dbh->prepare("DELETE from yplayers"); $stmt->execute();
  $stmt = $dbh->prepare("DELETE from ygames"); $stmt->execute();
  $stmt = $dbh->prepare("DELETE from readlogs"); $stmt->execute();
  $stmt = $dbh->prepare("DELETE from ygames_players"); $stmt->execute();
}

$stmt = $dbh->prepare("select to_char(date,'YYYYMMDD') as date, hash, nrgames from readlogs ORDER BY date DESC LIMIT 1");
$stmt->execute();

if ($stmt->rowcount() == 0) {
// just creating a date for now if none exist in db
  $readlogs = array(
    'date' => '', // 20131001',
    'hash' => '', // '587611c6',
    'nrgames' => 0
  );
  $days = array('20121001');
} else {
  $readlogs = $stmt->fetch();
//  $days = array(strtotime($readlogs['date']));
  $days = array($readlogs['date']);
}
//var_dump($readlogs);
// this should be gathered from db
$path = 'http://arcturus.su/tenhou/gamerecords2/all/sca';
//$path = 'sca';
$lobbies = array(
	'L7447',
	'C5929'
);
//$gametypes = 


// create an array with dates to work with
// allow 1 hour for file to pop up
//while ((end($days) < time()-1*60*60) & (count($days) < 2)) array_push($days, end($days)+24*60*60);
while ((strtotime(end($days)) < time()-25*60*60) && (count($days) < 4)) array_push($days, date('Ymd',strtotime(end($days))+24*60*60));

// populate the database
foreach ($days as $day) {
//  $day = date('Ymd', $day);
  echo '<P> [' . date('Y-m-d H:i:sP',time()) . '] Scanning logs: ' . $day . '<BR>';

  $f = @gzopen($path . $day . '.log.gz', 'rb');
  if ($f) {
    // test if file has been altered
    $hash = hash_file('crc32',$path . $day . '.log.gz');
    // cancel this day if hash is equal
    if (($hash == $readlogs['hash']) && ($day == $readlogs['date'])) {
      echo 'hash equal(' . $hash . ') games: '. $readlogs['nrgames'];
      continue;
    } else {
      if ($hash == $readlogs['hash']) echo 'hash unequal (' . $hash . ') vs (' . $readlogs['hash'] . ') - ' . $readlogs['nrgames'] . ' old games <B>WARNING, code not done</B> (this is normal if first run)<BR>';
      //we should delete games, or skip games or something
      //running on the assumption that only new games are added at the bottom of the file
    }

    // scan file for games to add and update db
    $nrgames = 0;
    while($line = trim(gzgets($f))) {
      // ar:	0 lobby
      //	1 time?
      //	2 gametype
      //	3 result (name(+val)name(+val)etc..
      $ar = explode(' | ', $line);
      $scores = end($ar);

      //abort if not correct lobby
      $lobby = $ar[0];
      if (!in_array($lobby, $lobbies)) continue;

      $gametype = $ar[2]; //used to have trims, should not be needed
      // only kuitan ari and kuitan ari fast allowed
      // should also be 
      if (md5($gametype) != '21670224953821f8a0096236de5b13a7' && md5($gametype) != '38283f7700a3c76ede5e54b0ea4d23d7') continue;

      $gametime = $day .  ' '. $ar[1] . ':00';

      if ($verbose) echo $line .' ' . $gametime . '<br />';
      else echo '*';

      // spec check if we're looking at a record already been checked before
      $nrgames++;
      if (($day == $readlogs['date']) && ($nrgames <= $readlogs['nrgames'])) {
	if ($verbose) echo 'Game already submitted<BR>';
	else echo '!';
	continue;
      }

      // just put the game in the db, no need to care about NoNames, leagues whatever.
      $stmt = $dbh->prepare('INSERT INTO ygames (gametime, lobby) VALUES (:when, :lobby)');

      $stmt->bindParam(':when', $gametime);
      $stmt->bindParam(':lobby', $lobby);
      if ($stmt->execute()) $gid = $dbh->lastInsertId('ygames_gid_seq');
      else {
	echo '<BR>game insert error';
	continue;
//	  die();
      }
      $players = explode(' ', $scores);

      $place = 1;
      foreach ($players as $player) {
	if (empty($player)) {echo "ERROR: empty player found\n"; continue;}
	$sc = explode('(', $player); // name(pts)
	$score = intval(substr(end($sc),0,-1));
	array_pop($sc); // remove the score, implode again if a '(' in the name
	$pname = implode('(',$sc);

	$stmt = $dbh->prepare('INSERT INTO yplayers (name) SELECT :name::text WHERE NOT EXISTS(SELECT pid FROM yplayers WHERE name = :name)');
	$stmt->bindParam(':name', $pname);//, PDO::PARAM_STR, 20);
	if (!$stmt->execute()) {
	  echo '<BR>player insert error';
	  continue;
//	    die();
	}
	if ($stmt->rowcount() > 0)
	  if ($verbose) echo 'New player ';
	  else echo 'N';

	$stmt = $dbh->prepare('INSERT INTO ygames_players (gid, pid, real_points, placement) VALUES (:gid, (SELECT pid FROM yplayers WHERE name = :name), :rpoints, :place)');
	$stmt->bindParam(':gid', $gid);
	$stmt->bindParam(':name', $pname);
	$stmt->bindParam(':place', $place);
	$stmt->bindParam(':rpoints', $score);
	if (!$stmt->execute()) {
	  echo '<BR>games_players insert error';
	  continue;
	}

	if ($verbose) echo $pname .' - '. $score . "<br>";
	else echo '.';
	$place++;
      }
    } 

    // update readlogs
    if ($day == $readlogs['date']) $stmt = $dbh->prepare('UPDATE readlogs SET hash=:hash, nrgames=:nrgames WHERE date = :date');
    else $stmt = $dbh->prepare('INSERT INTO readlogs (date, hash, nrgames) VALUES (:date,:hash,:nrgames)');
    $stmt->bindParam(':date', $day);
    $stmt->bindParam(':hash', $hash);
    $stmt->bindParam(':nrgames', $nrgames);
    if (!$stmt->execute()) {
      echo '<BR>update logs failed';
      continue;
    }    
    gzclose($f);
  } else echo 'Open file failed';
}

// search if there has been changes affecting any leagues etc.
/*
CREATE TABLE IF NOT EXISTS yseasons (
  sid serial NOT NULL,
  startdate DATE NOT NULL,
  enddate DATE NOT NULL,
  defaultleague INT DEFAULT NULL,
  sname VARCHAR(20) DEFAULT NULL,
  sshort varchar(2) DEFAULT NULL,
  seupdated TIMESTAMP DEFAULT NULL,
  PRIMARY KEY  (sid)
); 

CREATE TABLE IF NOT EXISTS yseasongames (
  sid integer NOT NULL,
  gid integer NOT NULL,
  gbanned BOOL DEFAULT FALSE,
  value decimal(10,3) DEFAULT NULL,
  updated TIMESTAMP DEFAULT NULL,
  PRIMARY KEY  (sid,gid)
);

CREATE TABLE IF NOT EXISTS yleagues (
  sid integer NOT NULL,
  lid serial NOT NULL,
  lname varchar(20) NOT NULL,
  lshort varchar(2),
  padding decimal(10,0) NOT NULL,
  coef decimal(10,2) NOT NULL,
  PRIMARY KEY  (lid) 
);

CREATE TABLE IF NOT EXISTS yseasonplayers (
  lid integer NOT NULL,
  pid integer NOT NULL,
  banned BOOL DEFAULT FALSE,
  PRIMARY KEY  (lid,pid) 
);

CREATE TABLE IF NOT EXISTS yseasonscore (
  lid integer NOT NULL,
  pid integer NOT NULL,
  scpoints decimal(10,2) NOT NULL,
  scgames integer NOT NULL,
  scfirst integer NOT NULL,
  scsecond integer NOT NULL,
  scthird integer NOT NULL,
  scfourth integer NOT NULL,
  scupdated timestamp default now(),
  PRIMARY KEY  (lid,pid) 
);
 */
//var_dump($days);
//echo end($days) . $days[count($days)-1];
//$stmt = $dbh->prepare("select sid from yseasons where (startdate,enddate) OVERLAPS (:stdate::date,:endate::date)");
//$stmt->bindParam(':stdate', $days[0]);
//$stmt->bindParam(':endate', $days[count($days)-1]);
//$stmt->execute();

//if ($stmt->rowcount() == 0) echo 'no seasons';
//else $seasons = $stmt->fetchall(); 

//populate yseasongames
echo '<P>[' . date('Y-m-d H:i:sP',time()) . '] Scanning seasons and leagues<BR>';
$stmt = $dbh->prepare("INSERT INTO yseasongames (sid,gid) (SELECT sid,gid FROM ygames, yseasons WHERE (startdate,enddate) OVERLAPS (gametime,gametime) AND (gid,sid) NOT IN(SELECT gid,sid FROM yseasongames))");
$stmt->execute();

if ($stmt->rowcount() == 0) echo 'no updated games';
else echo $stmt->rowcount() . ' games added allocated into seasons, ';

  // if games has been added, so might players
  $stmt = $dbh->prepare("INSERT INTO yseasonplayers (lid, pid) (
    SELECT DISTINCT defaultleague,pid from yseasongames NATURAL JOIN ygames_players NATURAL JOIN yseasons
      WHERE (sid,pid) NOT IN(SELECT sid,pid FROM yseasonplayers NATURAL JOIN yleagues) AND defaultleague IS NOT NULL)");
//    SELECT DISTINCT lid, pid from yseasongames NATURAL JOIN ygames_players NATURAL JOIN yleagues WHERE (lid,pid) NOT IN(SELECT lid,pid FROM yseasonplayers))");
  @$stmt->execute();
  echo $stmt->rowcount() . ' new players<BR>';
//      SELECT lid,pid FROM yleagues, yplayers WHERE (startdate,enddate) OVERLAPS (gametime,gametime) AND (gid,sid) NOT IN(SELECT gid,sid FROM yseasongames))");
//SELECT DISTINCT lid, pid from yseasongames natural join ygames_players natural join yleagues;
  //$stmt->execute();

  // calculate gamevalue, sum(players league coef)*(1-sum(nonames&banz)*0.2)
  // unsure if banz should be added...
//should do: but having banz coef = empty for unknown reason... , reason, multiple banned fields
//  select sum(coef) from yleagues natural right join yseasonplayers natural right join (ygames_players natural join yseasongames) group by gid;

  $stmt = $dbh->prepare("UPDATE yseasongames AS y SET value=joint.value,updated=now() FROM 
    (SELECT sid,gid,sum(coef)*(1-sum(banned::int)*.2) AS value FROM yleagues NATURAL JOIN yseasonplayers NATURAL JOIN
    ygames_players NATURAL JOIN yseasongames GROUP BY sid,gid) AS joint WHERE y.sid=joint.sid AND y.gid=joint.gid AND y.value IS NULL");
//INSERT INTO yseasonplayers (lid, pid) (SELECT DISTINCT lid, pid from yseasongames NATURAL JOIN ygames_players NATURAL JOIN yleagues WHERE (lid,pid) NOT IN(SELECT lid,pid FROM yseasonplayers))");
  $stmt->execute();
  echo $stmt->rowcount() . ' updated gamevalues<BR>';

  $stmt = $dbh->prepare("UPDATE yseasonscore AS y SET
    (scpoints,scgames,scfirst,scsecond,scthird,scfourth,scupdated) =
    (j.score,j.numbergames,j.first,j.second,j.third,j.fourth, now()) FROM
      (SELECT lid,pid, sum(real_points*value+padding) AS score, 
	count(real_points) AS numbergames, 
	sum((placement=1)::int) AS first,  sum((placement=2)::int) AS second,
	sum((placement=3)::int) AS third,   sum((placement=4)::int) AS fourth
	  FROM yleagues NATURAL JOIN yseasonplayers NATURAL JOIN ygames_players NATURAL JOIN yseasongames
	WHERE (lid,pid) IN (SELECT lid,pid FROM yseasonscore NATURAL JOIN yseasongames NATURAL JOIN ygames_players NATURAL JOIN yleagues WHERE scupdated < updated)
	  AND NOT gbanned GROUP BY lid,pid) AS j WHERE y.lid=j.lid AND y.pid=j.pid");
// forgot join yleagues to not updated (link sid and lid)
  $stmt->execute();
  echo $stmt->rowcount() . ' scores updated - ';

  $stmt = $dbh->prepare("INSERT INTO yseasonscore (lid,pid,scpoints,scgames,scfirst,scsecond,scthird,scfourth,scupdated)
    (SELECT lid,pid, sum(real_points*value+padding) AS score, 
    count(real_points) AS numbergames, 
    sum((placement=1)::int) AS first,  sum((placement=2)::int) AS second,
    sum((placement=3)::int) AS third,   sum((placement=4)::int) AS fourth, now()
    FROM yleagues NATURAL JOIN yseasonplayers NATURAL JOIN ygames_players NATURAL JOIN yseasongames
    WHERE (lid,pid) NOT IN (SELECT lid,pid FROM yseasonscore) AND NOT gbanned AND NOT banned
    GROUP BY lid,pid)");
  $stmt->execute();
  echo $stmt->rowcount() . ' new entries<BR>';

//INSERT INTO yseasongames (sid,gid) (SELECT gid,sid FROM ygames, yseasons WHERE (startdate,enddate) OVERLAPS (gametime,gametime) AND NOT EXISTS (SELECT sid,gid FROM yseasongames));

//insert into yseasongames (sid,gid) values (select gid,sid from ygames, yseasons where (startdate,enddate) OVERLAPS (gametime,gametime));
//select gid,gametime,sid from ygames, yseasons where (startdate,enddate) OVERLAPS (gametime,gametime);
// select distinct gid will return all season who have hits...
//echo date('Ymd', $seasons[1]['start']) . '-' .date('Ymd', $seasons[1]['end']);
//var_dump($seasons);


// score difference, reason:
// I calced coef as sum(coef)-sum(noname&banned)*0.2 (no banned players in season00 as far as i know)
// U calced coef as sum(coef)-sum(nonames placed above player)*0.2

// ppl getting 1 more game in my season 0:
// コイコイ
// セイヨウウスユキ
// Akagi5 x2 
//    ur db gives same value: select count(*) from games_players where playerid=110 and gameid in (select id from games where season=1)
// Daisushi
// Felgrand
// 科学者
// Eiqchi
// トマホーク
// harph
// Nozomi
// Ashj
// Pikokola
// sabmyf
// 宮星
// Malachus
// heha
// Thursday x2
// yuyukos
// Slaix223

/* manual movement of ppl to leagues season 1;
 league A=5,B=4,C=3;
INSERT into yseasonplayers (lid,pid) (select 5,pid from yseasonscore natural join yplayers where lid=1 and scgames >=20 ORDER BY scpoints DESC LIMIT 31);
INSERT into yseasonplayers (lid,pid) (select 4,pid from yseasonscore natural join yplayers where lid=1 and scgames >=20 ORDER BY scpoints DESC LIMIT 70-31 OFFSET 31);
INSERT into yseasonplayers (lid,pid) (select 3,pid from yseasonscore natural join yplayers where lid=1 and scgames >=20 ORDER BY scpoints DESC OFFSET 70);

// restriction dissallows below command, must insert and then delete :(
// UPDATE yseasonplayers set lid=5 where pid=(select pid from yplayers where name='Dorage');

INSERT INTO yseasonplayers (lid,pid) (select 5,pid from yplayers where name='Dorage');
DELETE FROM yseasonplayers where (lid,pid)=(select 4,pid from yplayers where name='Dorage');
DELETE FROM yseasonscore where (lid,pid)=(select 4,pid from yplayers where name='Dorage');
INSERT INTO yseasonplayers (lid,pid) (select 4,pid from yplayers where name='bebop55');
DELETE FROM yseasonplayers where (lid,pid)=(select 3,pid from yplayers where name='bebop55');
DELETE FROM yseasonscore where (lid,pid)=(select 3,pid from yplayers where name='bebop55');
OPS!!
need to recalc all values involving the above persons,
I just recalced all values to be easy, this will be needed for when ppl get banned etc.. too
UPDATE yseasongames AS y SET value=joint.value,updated=now() FROM 
    (SELECT sid,gid,sum(coef)-sum(banned::int)*.2 AS value FROM yleagues NATURAL JOIN yseasonplayers NATURAL JOIN
    ygames_players NATURAL JOIN yseasongames GROUP BY sid,gid) AS joint WHERE y.sid=joint.sid AND y.gid=joint.gid ;

//leaguespec: might be too iterative(?)
UPDATE yseasongames AS y SET value=joint.value,updated=now() FROM
SELECT sid,gid,sum(coef)-sum(banned::int)*.2 AS value FROM yleagues NATURAL JOIN yseasonplayers NATURAL JOIN
    ygames_players NATURAL JOIN yseasongames WHERE (sid,gid) in (SELECT sid,gid from ygames_players  natural join yseasonplayers natural join yseasongames natural join yleagues where lid=7) GROUP BY sid,gid) AS joint WHERE y.sid=joint.sid AND y.gid=joint.gid ;

//player and leaguespec:
UPDATE yseasongames AS y SET value=joint.value,updated=now() FROM
SELECT sid,gid,sum(coef)-sum(banned::int)*.2 AS value FROM yleagues NATURAL JOIN yseasonplayers NATURAL JOIN
    ygames_players NATURAL JOIN yseasongames WHERE (sid,gid) in (SELECT sid,gid from ygames_players  natural join yseasonplayers natural join yseasongames natural join yleagues where pid=2 lid=7) GROUP BY sid,gid) AS joint WHERE y.sid=joint.sid AND y.gid=joint.gid ;
*/
?>