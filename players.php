<?php

require 'db.php';

$stmt = $dbh->prepare('SELECT playerid, name, COUNT(playerid) AS cnt FROM games_players
JOIN players ON players.id = playerid
WHERE playerid > 1
GROUP BY playerid

ORDER BY cnt DESC');
$stmt->execute();

$i = 1;
foreach ($stmt as $row) {
	if ($row['cnt'] < 20) {
		break;
	}
	echo ($i++) . '. ' . $row['name'] . ' ' . $row['cnt']. '<br />';
}