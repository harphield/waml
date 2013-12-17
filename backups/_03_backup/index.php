<?php$aPages = array(	'a' => 's03_a.php',	'b' => 's03_b.php',	'c' => 's03_c.php',	'n' => 's03_n.php',	2 => 'graphs.php',	1 => 'statsfull.php',	3 => 'statsranked_00.php',	4 => 'gameslist.php',	5 => 's01_a.php',	6 => 's01_b.php',	7 => 's01_c.php',	8 => 's01_n.php',	9 => 's02_a.php',	10 => 's02_b.php',	11 => 's02_c.php',	12 => 's02_n.php'	);$aTitles = array(	'a' => 'A league',	'b' => 'B league',	'c' => 'C league',	'n' => 'New players',	1 => 'Complete ranking',	2 => 'Graphs',	3 => 'Season 0 ranking',	4 => 'Games list',	5 => 'Season 1 League A',	6 => 'Season 1 League B',	7 => 'Season 1 League C',	8 => 'Season 1 League N',	9 => 'Season 2 League A',	10 => 'Season 2 League B',	11 => 'Season 2 League C',	12 => 'Season 2 League N'	);$iPage = isset($_GET['p']) ? $_GET['p'] : 1;?><!DOCTYPE html><html lang="en-us">	<head>		<title>WAML ranking<?php echo (isset($aTitles[$iPage]) ? ' | ' . $aTitles[$iPage] : ''); ?></title>		<meta charset="utf-8" />		<meta name="viewport" content="width=device-width" />		<meta name="description" content="World Amateur Mahjong League ranking page" />		<link href="css/bootstrap.min.css" rel="stylesheet" media="screen" />		<link href="css/bootstrap-responsive.min.css" rel="stylesheet" media="screen" />				<script src="http://code.jquery.com/jquery-latest.js"></script>		<script src="js/bootstrap.min.js"></script>		<script src="js/highcharts.js" type="text/javascript"></script>			<script type="text/javascript" src="js/jquery.tablesorter.js"></script> 				<script type="text/javascript">		  $(function() {			$('#plsearch').val('').keyup(function() {				player = $(this).val();								if (player != '') {					$('.table tbody tr td').attr('style', '');					$('.table tbody tr').each(function() {						names = $(this).find('strong');						found = false;						for (i = 0; i < names.length; i++) {							if ($(names[i]).html().indexOf(player) === 0) {								found = true;								$(names[i]).parent().attr('style', 'background-color: #FFE019;');								break;							}						}												if (!found) {							$(this).hide();						} else {							$(this).show();						}					});				} else {					$('.table tbody tr').show();					$('.table tbody tr td').attr('style', '');				}			});						$('.plsearchform').submit(function(e) {				e.preventDefault();			});						$('#only_ranked').change(function() {				if ($(this).is(':checked')) {					$('.table tbody tr').not('.ranked').hide();				} else {					$('.table tbody tr').show();				}								i = 1;				$('.table tbody tr td:first-child').each(function() {					if ($(this).parent().is(':visible')) {						$(this).html(i++);					}				});			});		  });						  var _gaq = _gaq || [];		  _gaq.push(['_setAccount', 'UA-4431621-4']);		  _gaq.push(['_trackPageview']);		  (function() {			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);		  })();		</script>						<style type="text/css">			table.table.table-striped.table-condensed.tablesorter th:nth-child(5) {				text-align: center;			}					table.table.table-striped.table-condensed tr.ranked td:nth-child(4) {				background-color: #35D4A4;			}						table.table.table-striped.table-condensed tr td.league_A,			table.table.table-striped.table-condensed tr td.league_B,			table.table.table-striped.table-condensed tr td.league_C,			table.table.table-striped.table-condensed tr td.league_N {				text-align: center;				font-weight: bold;			}						table.table.table-striped.table-condensed tr td.league_A {				background-color: #339966;			}			table.table.table-striped.table-condensed tr td.league_B {				background-color: #ff9900;			}			table.table.table-striped.table-condensed tr td.league_C {				background-color: #ff6600;			}						table.tablesorter th {				cursor: pointer;			}						.mainbody {				margin-top: 10px;			}						td span.league {				padding-left: 5px;				padding-right: 5px;				margin-left: 5px;				border: 1px solid #000;				text-decoration: none;			}						span.league.l_A {				background-color: #339966;			}			span.league.l_B {				background-color: #ff9900;			}			span.league.l_C {				background-color: #ff6600;			}						.table-striped tbody tr td.ban { 				background-color: #FF3333;				text-decoration: line-through;			}						.table-striped tbody tr.zerogames td {				color: gray;				background-color: silver;			}					</style>	</head>	<body>		<div class="navbar navbar-inverse navbar-static-top">			<div class="navbar-inner">				<div class="container">					<a class="brand" href="index.php">WAML Ranking (Season 3)</a>					<ul class="nav">						<li class="<?php echo ($iPage == 'a' ? 'active' : ''); ?>"><a href="index.php?p=a">A</a></li>						<li class="<?php echo ($iPage == 'b' ? 'active' : ''); ?>"><a href="index.php?p=b">B</a></li>						<li class="<?php echo ($iPage == 'c' ? 'active' : ''); ?>"><a href="index.php?p=c">C</a></li>						<li class="<?php echo ($iPage == 'n' ? 'active' : ''); ?>"><a href="index.php?p=n">N</a></li>						<li class="divider-vertical"></li>						<li class="<?php echo ($iPage == 1 ? 'active' : ''); ?>"><a href="index.php?p=1">Full</a></li>						<li class="<?php echo ($iPage == 4 ? 'active' : ''); ?>"><a href="index.php?p=4">Games</a></li>						<li class="<?php echo ($iPage == 2 ? 'active' : ''); ?>"><a href="index.php?p=2">Graphs</a></li>					</ul>					<ul class="nav pull-right">						<li class="divider-vertical"></li>						<li class="dropdown <?php echo ($iPage == 3 ? 'active' : ''); ?>">							<a class="dropdown-toggle" data-toggle="dropdown" href="#">								History								<b class="caret"></b>							</a>							<ul class="dropdown-menu">								<li><a href="index.php?p=3">Season 0</a></li>								<li><a href="index.php?p=5">Season 1 A</a></li>								<li><a href="index.php?p=6">Season 1 B</a></li>								<li><a href="index.php?p=7">Season 1 C</a></li>								<li><a href="index.php?p=8">Season 1 N</a></li>								<li><a href="index.php?p=9">Season 2 A</a></li>								<li><a href="index.php?p=10">Season 2 B</a></li>								<li><a href="index.php?p=11">Season 2 C</a></li>								<li><a href="index.php?p=12">Season 2 N</a></li>															</ul>						</li>						<li class="dropdown">							<a class="dropdown-toggle" data-toggle="dropdown" href="#">								Links								<b class="caret"></b>							</a>							<ul class="dropdown-menu">								<li><a href="http://www.mahjong-league.com">Blog</a></li>								<li><a href="http://tenhou.net/0/?L7447">7447 lobby</a></li>								<li><a href="http://tenhou.net/0/?59295224">Secure lobby</a></li>								<li><a href="irc://irc.rizon.net/osamuko">#osamuko</a></li>								<li><a href="http://www.twitch.tv/kirowind">Kirowind Stream</a></li>							</ul>													</li>											</ul>				</div>			</div>		</div>		<div class="container mainbody">			<div class="row">				<div class="span12">				<p>				Season 3 start!				</p>				<hr />								<?php					// games list filter					if ($iPage == 4) {					?>						<div style="overflow: hidden;">							<form class="form-inline pull-right plsearchform">								<fieldset>																		<input type="text" placeholder="Find player..." id="plsearch" />								</fieldset>							</form>						</div>					<?php					}										@include $aPages[$iPage];				?>				</div>						</div>		</div>				<script type="text/javascript">		$(document).ready(function() { 			$("table.tablesorter").tablesorter({				sortInitialOrder: "desc"			}); 		}); 		</script>	</body></html>