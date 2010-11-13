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
  Tell us a <a href="http://last.fm">Last.fm</a> username. We'll give you back <a href="http://www.spotify.com">Spotify</a> playlists with live updated recent tracks, user favorites, similar profiles and direct links to artists.
</div>


<div class="smallmeta listen-in">Listen in:
<?
$res = db_query("select pl.lastfm_username from stalkify_tracks tr, stalkify_playlists pl where pl.playlist_id=tr.playlist_id and tr.spotify_uri is not null and tr.processed_p is true and tr.creation_date>now()-'10 minutes'::interval group by pl.lastfm_username order by random() limit 20");
while ($res->fetchInto($row)) {
  ?>
  <a href="/<?=$row['lastfm_username']?>">@<?=$row['lastfm_username']?></a>
  <?
}
?>
</div>

<? require('footer.php'); ?>
