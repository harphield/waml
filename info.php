<pre>This is Yazphier's working copy of the WAML webpage

Things done: * done, - todo, ? dunno
------------------------------------
>> Users
  - possibility to merge users, probably create a new table of 'users' that
    can be linked to multiple 'players' and let stats and such be of users instead of players..

>> League (standings)
  * content gathered from db.
  * content precalculated by cron
  * link to player info
  * 'full list' for a season
  - thropies
 
>> Games list
  * content gathered from db on the fly
  - timestamp missing
  - nr of games missing
  - possible for admin to ban games
  ? games not belonging to seasons
  ? linking to showgame
  ? link to player info

>> Player info
  * list leagues
  * list games
  * admin can ban player and add/del player from leagues
  - list stats (graphs?)
  - thropies

>> Leagues List
  * List Seasons and leagues and their properies
  * Admin can add/del/alter leagues
  * mass add players from 1 league to another based on criteria
  * stuff that would be recalced is deleted from db
  - start cron after del/alterations
  ? some values checks

>> Admin
 - login
 - special messages can be sent as pre tags (looks nice)
 - remake ban as other stuff is made, is guess
 ? some entryfields appearance

>> Cron
  * Scans logs from arcturus
  * Checks if last log checked have been altered
  * Automatically allocate games to seasons
  * Calculate gamevalues
  * Calculate playerscores for league standings
  - Admin request for cron to run
  - Handle when log has previously been worked on, but has changed hash
  - make it produce logs
  ? add it to real cron

>> Graphs
  - well, kinda everything

>> Thropies
  - well, add the support, no spec page for this

>> Games
  - should be able to ban specific games
  - add/remove games to season even if not in timespam
    this might screw some stuff up thou.
  - add option to add more types of games and lobbies

>> Database
  - reorganize and clean (again)
  - make it mysql compatible

>> Bugs
  - When banning NoName season 3, scores was not updated properly, deleting all games in yseasongames solved it for now.., problem from before doing the actual ban
</pre>
