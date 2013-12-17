<?php	// save initial leagues of players to a file	$games = 0;	$pgames = array();	$ranking = array();	$pplaces = array();	$gpd = array();		$date_start = strtotime('2012-11-01 00:00:00');	$date_end = strtotime('2012-12-31 23:59:00');	$path = 'http://arcturus.su/tenhou/gamerecords/7447/sca';		for ($i = $date_start; $i <= (time() + 8*60*60 < $date_end ? time() + 8*60*60 : $date_end); $i += 24*60*60) {		$day = date('Ymd', $i);		$f = fopen($path . $day . '.log', 'rb');				if ($f) {			if (!isset($gpd[$i])) {				$gpd[$i] = 0;			}						while ($line = fgets($f)) {				$ar = explode('|', $line);				$scores = end($ar);								$gametype = $ar[2];				// only kuitan ari and kuitan ari fast allowed				if (md5(trim($gametype)) != '21670224953821f8a0096236de5b13a7' && md5(trim($gametype)) != '38283f7700a3c76ede5e54b0ea4d23d7') {					continue;				}								$peoplez = explode(' ', $scores);								$game = array();				foreach ($peoplez as $person) {					if (empty($person)) {						continue;					}										$sc = explode('(', $person);										$points = substr( trim($sc[1]), 0, strlen(trim($sc[1])) - 1);										$game[$sc[0]] = (int) $points;				}								// removing sanma				if (count($game) < 4) {					continue;				}								$coef = 1.0; // 0 nonames				$pos = 1;				foreach ($game as $player => $points) {					if ($player == 'NoName') {						$coef -= 0.2;					} else if (!array_key_exists($player, $ranking)) {						$ranking[$player] = ($points + 30) * $coef;						$pgames[$player] = 1;						$pplaces[$player] = array(							1 => ($pos == 1 ? 1 : 0),							2 => ($pos == 2 ? 1 : 0),							3 => ($pos == 3 ? 1 : 0),							4 => ($pos == 4 ? 1 : 0),							'avg' => $pos						);					} else {						$ranking[$player] += ($points + 30) * $coef;						$pgames[$player]++;						$pplaces[$player][$pos]++;						$pplaces[$player]['avg'] += $pos;					}										$pos++;				}								$games++;				$gpd[$i]++;			}					fclose($f);		} else {			echo 'error';			break;		}	}		asort($ranking);	$ranking = array_reverse($ranking);				$body = "<?php		$leagues = array(\n	";	$rankedbody = $body;		// I need number of ranked players first	$rankedplayers = 0;	foreach ($ranking as $player => $points) {		if ($pgames[$player] >= 20) {			$rankedplayers++;		}	}		$i = 1;	$iranked = 1;	$leaguebase = $rankedplayers / 15;	$leagueA = round($leaguebase * 4);	$leagueB = $leagueA + round($leaguebase * 5);	$leagueC = $leagueB + round($leaguebase * 6);		foreach ($ranking as $player => $points) {		$league = '-';		if ($pgames[$player] >= 20) {			if ($iranked <= $leagueA) {				$league = 'A';			} else if ($iranked <= $leagueB) {				$league = 'B';			} else if ($iranked <= $leagueC) {				$league = 'C';			}			$iranked++;						$rankedbody .= '"' . md5($player) . '" => array("name" => "' . $player . '", "league" => "' . $league . '"), // ' . $player . "\n";		}	}			$rankedbody .= '); ?>';	$f = fopen('lgs.php', 'w');		if ($f) {		fwrite($f, $rankedbody);				fclose($f);			} else {		echo 'error';	}	?>