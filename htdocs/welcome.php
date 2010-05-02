<? 
require('header.php');
require('include.php');
db_open();
?>

<?
if( isset($_GET['msg']) ) { 
   ?>
   <div class="bigmessage">
     <?= $_GET['msg'] ?>
   </div>
   <?
}
?>

<form onsubmit="location.href = '/' + this.lastfm_username.value; return(false);">
   <input type="text" name="lastfm_username" />
   <input type="submit" value="Stalkify" />
</form>
<div class="smallmeta">
  Tell us a <a href="http://last.fm">Last.fm</a> username. We'll give you back some <a href="http://www.spotify.com">Spotify</a> playlists with recent tracks and user favorites.
</div>


<div class="smallmeta listen-in">Listen in:
<?
$res = db_query("select pl.lastfm_username, tr.playlist_id, max(tr.creation_date) as m from stalkify_tracks tr, stalkify_playlists pl where pl.feed_type = 'recent' and pl.playlist_id = tr.playlist_id group by pl.lastfm_username, tr.playlist_id order by m desc limit 10");
while ($res->fetchInto($row)) {
  ?>
  <a href="/<?=$row['lastfm_username']?>">@<?=$row['lastfm_username']?></a>
  <?
}
?>
</div>

<? require('footer.php'); ?>
