<?php
require 'db.php';

$league_ids = array(
	'A' => 1,
	'B' => 2,
	'C' => 3,
	'N' => 4
);

// SEASON 0 FILLING 
$games = 0;
$pgames = array();
$ranking = array();
$pplaces = array();
$gpd = array();
$banz = array(
	'7e3cbd39f9334ff0d3c28aa01008cc52', // fake xkime
	md5('jinxflow'),
	md5('Urameshi'),
	md5('NIGGER<3'),
	md5('geodude3'),
	'463e56f4c1e1f3b0da35d14c1629005e', // sexkime
	md5('L?onardo'),
	md5('RAPHEAL'),
	md5('Splinter'),
	md5('splinter'),
	'cde1fc5c6b1b751f9b09bb057fc76b40', // the
	md5('emofag'),
	md5('buttfuck'),
	md5('Kaneeeda'),
	md5('mohammed'),
	md5('?rophet'),
	md5('youS'),
	md5('myD'),
	'0e0ec921dc42859d8e9d09f3cc9065b5', // fox
	'bb0c78681f10218df83ba378160abba7', // falco
	md5('Putin'),
	md5('It'),
	md5('Slam'),
	md5('Jam'),
	md5('kazooie')
	
);

$date_start = strtotime('2013-01-01 00:00:00');
$date_end = strtotime('2013-02-28 23:59:00');

//$date_end = strtotime('2012-11-02 23:59:00');

/*
$stmt = $dbh->prepare('INSERT INTO seasons (id, start, end) VALUES (0, :from, :to)');
$stmt->bindParam(':from', $date_start);
$stmt->bindParam(':to', $date_end);

if (!$stmt->execute()) {
	echo 'season insert error';
	print_r($dbh->errorInfo());
	die();	
}

$sid = $dbh->lastInsertId();

$sid = 2;

$padding = 30;
$coef = 0.25;

$stmt = $dbh->prepare('INSERT INTO seasons_leagues (seasonid, leagueid, padding, coef) VALUES (:sid, :lid, :padding, :coef)');

$stmt->bindParam(':sid', $sid);

$stmt->bindParam(':lid', $league_ids['N']); // N
$stmt->bindParam(':padding', $padding);
$stmt->bindParam(':coef', $coef);
$stmt->execute();

$stmt->bindParam(':lid', $league_ids['C']); // C
$stmt->execute();

$padding = 20;
$stmt->bindParam(':lid', $league_ids['B']); // B
$stmt->execute();

$padding = 10;
$stmt->bindParam(':lid', $league_ids['A']); // A
$stmt->execute();

die();

include('lgs_01.php');

$stmt = $dbh->prepare('SELECT id, name FROM players WHERE name = :name');
$stmt->bindParam(':name', $plname);

$stmti = $dbh->prepare('INSERT INTO players_league (playerid, leagueid, start) VALUES (:playerid, :leagueid, :start)');
$stmti->bindParam(':start', $date_start);
$stmti->bindParam(':playerid', $pid);
$stmti->bindParam(':leagueid', $lid);

foreach ($leagues as $md5 => $plyr) {
	$plname = $plyr['name'];
	$stmt->execute();
	
	if ($stmt->rowCount() > 0) {
		$player = $stmt->fetch();
		
		echo $player['id'] . ' ' . $plyr['league'] . '<br />';
		$pid = $player['id'];
		$lid = $league_ids[$plyr['league']];
		
		$stmti->execute();
	}
}

die();
*/

$sid = 2;

$paths = array('http://a.pqrs.se/tenhou/gamerecords2/all/sca');
$lobbies = array(
	'L7447',
	'C5929'
);

for ($i = $date_start; $i <= (time() + 8*60*60 < $date_end ? time() + 8*60*60 : $date_end); $i += 24*60*60) {
	$day = date('Ymd', $i);
	foreach ($paths as $path) {
		$f = gzopen($path . $day . '.log.gz', 'rb');
		if ($f) {
			if (!isset($gpd[$i])) {
				$gpd[$i] = 0;
			}
			
			while ($line = gzgets($f)) {
				$ar = explode('|', $line);
				$scores = end($ar);
				
				$lobby = trim($ar[0]);
				if (!in_array($lobby, $lobbies)) {
					continue;
				}				
				
				$gametype = trim($ar[2]);
				// only kuitan ari and kuitan ari fast allowed
				if (md5($gametype) != '21670224953821f8a0096236de5b13a7' && md5($gametype) != '38283f7700a3c76ede5e54b0ea4d23d7') {
					continue;
				}
				
				$gametime = date('Y-m-d ' . trim($ar[1]) . ':00', $i);
				echo $line .' ' . $gametime . '<br />';
				//continue;
				
				$peoplez = explode(' ', $scores);
				
				$game = array();
				$game_value = 0;	
				$coef = 1.0; // 0 nonames
				$nonames = 0;
				foreach ($peoplez as $person) {
					if (empty($person)) {
						continue;
					}
					
					$sc = explode('(', $person);
					
					$points = substr( trim($sc[count($sc) - 1]), 0, strlen(trim($sc[count($sc) - 1])) - 1);
					array_pop($sc);
					$pname = implode('(', $sc);
											
					if ($pname == 'NoName') {
						$game[$pname . '_' . $nonames++] = (int) $points;

						$coef -= 0.2;
						$game_value += 0.15;
					} else {
						$game[$pname] = (int) $points;
						$league = $leagues[md5($pname)]['league'];
						if ($league == 'A') {
							$game_value += 0.25;
						} else if ($league == 'B') {
							$game_value += 0.2;
						} else {
							$game_value += 0.15;
						}
					}						
				}
				
				// removing sanma
				if (count($game) < 4) {
					echo $line;
					die();
					continue;
				}
				
				$pos = 1;
				$currgame = array(
					'players' => array(),
					'date' => $i,
					'lobby' => $lobby
				);
				foreach ($game as $player => $points) {
					if (strpos($player, 'NoName', 0) === 0) {						
						$league = null;						
					} else if (!array_key_exists($player, $ranking) && !in_array(md5($player), $banz)) {
						
						// The "value" of the game is 0.25 * A player + 0.20 * B player + 0.15 * C player + 0.15 * scrub
						// the offset is +10 for A, +20 for B, +30 for C and scrub
						// and -20% per NoName (on value, before offset, per NoName)
						$league = $leagues[md5($player)]['league'];
						if ($league == 'A') {
							$offset = 10;
						} else if ($league == 'B') {
							$offset = 20;
						} else {
							$offset = 30;
						}
						
						$ranking[$player] = ($points * $game_value * $coef) + $offset;
						//echo $player . ' ' . $league . '<br />';
						
						$pgames[$player] = 1;
						$pplaces[$player] = array(
							1 => ($pos == 1 ? 1 : 0),
							2 => ($pos == 2 ? 1 : 0),
							3 => ($pos == 3 ? 1 : 0),
							4 => ($pos == 4 ? 1 : 0),
							'avg' => $pos
						);
					} else if (!in_array(md5($player), $banz)) {
						$league = $leagues[md5($player)]['league'];
						if ($league == 'A') {
							$offset = 10;
						} else if ($league == 'B') {
							$offset = 20;
						} else {
							$offset = 30;
						}					
					
						$ranking[$player] += ($points * $game_value * $coef) + $offset;
						//echo $player . ' ' . $league . '<br />';
						
						$pgames[$player]++;
						$pplaces[$player][$pos]++;
						$pplaces[$player]['avg'] += $pos;
					} else {
						$league = 'N';
					}
					
					$league = (isset($league) ? $league : 'N');
					$currgame['players'][] = array(
						'name' => ((strpos($player, 'NoName', 0) === 0) ? 'NoName' : $player),
						'league' => $league,
						'points' => ((!in_array(md5($player), $banz) && (strpos($player, 'NoName', 0) !== 0)) ? $points : 'X'),
						'banned' => in_array(md5($player), $banz)
					);
					
					$pos++;
				}
				
				$gameslist[] = $currgame;
				
				$stmt = $dbh->prepare('INSERT INTO games (id, gametime, lobby, season) VALUES (0, :when, :lobby, :season)');
				$stmt->bindParam(':when', strtotime($gametime));
				$stmt->bindParam(':lobby', $currgame['lobby']);
				$stmt->bindParam(':season', $sid);
				if ($stmt->execute()) {						
					$gid = $dbh->lastInsertId();
				} else {
					echo 'game insert error';
					die();
				}
				
				$stmt = $dbh->prepare('INSERT INTO games_players (gameid, playerid, real_points, placement) VALUES (:gid, :pid, :rpoints, :place)');
				$stmt->bindParam(':gid', $gid);
				$place = 1;
				foreach ($currgame['players'] as $player) {
					if (strpos($player, 'NoName', 0) === 0) {
						$pid = 1; // 1 is NONAME
					} else if (!isset($loaded_players[$player['name']])) { 
						$pst = $dbh->prepare('SELECT id FROM players WHERE name = :name');
						$pst->bindParam(':name', $player['name']);
						$pst->execute();
						
						$row = $pst->fetch();
						
						if (!isset($row) || empty($row)) {
							// new
							$pst = $dbh->prepare('INSERT INTO players (id, name, banned) VALUES (0, :name, :ban)');
							$pst->bindParam(':name', $player['name']);
							$pst->bindParam(':ban', $player['banned']);
							
							if ($pst->execute()) {
								$pid = $dbh->lastInsertId();
								$loaded_players[$player['name']] = $pid;
							} else {
								echo 'player insert error';
								die();
							}
						} else {
							$pid = $row['id'];
							$loaded_players[$player['name']] = $pid;
						}
					} else {
						$pid = $loaded_players[$player['name']];
					}
				
					$stmt->bindParam(':pid', $pid);
					$stmt->bindParam(':rpoints', $player['points']);
					$stmt->bindParam(':place', $place);
					if (!$stmt->execute()) { 
						echo 'game_player insert error';
						die();
					}
					
					$place++;
				}
				
				$games++;
				$gpd[$i]++;
			}
		
			gzclose($f);

		} else {
			echo 'error';
			break;
		}
	}
}
