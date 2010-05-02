<?
$prettyname["recent"] = "live";
$prettydesc["recent"] = "Recent plays &minus; from Spotify, iTunes, Winamp and anything else that speaks Last.fm";
$prettyname["toptracks-overall"] = "all-time favs";
$prettydesc["toptracks-overall"] = "All-time top tracks ";
$prettyname["toptracks-7day"] = "this week's favs";
$prettydesc["toptracks-7day"] = "Top tracks from the past seven days ";
$prettyname["toptracks-3month"] = "past three months";
$prettydesc["toptracks-3month"] = "Top tracks from the past three months ";
$prettyname["toptracks-6month"] = "past six months";
$prettydesc["toptracks-6month"] = "Top tracks from the past six months ";
$prettyname["toptracks-12month"] = "this year";
$prettydesc["toptracks-12month"] = "Top tracks from the past year ";
?>

<h2><a href="http://last.fm/user/<?=$username?>">@<?=$username?></a></h2>
<table class="playlist-list">

<?
$res = db_query("select pl.feed_type, (case when pl.spotify_uri is null or not exists (select 1 from stalkify_tracks tr where tr.playlist_id = pl.playlist_id and tr.spotify_uri is not null) then 'none' when exists (select 1 from stalkify_tracks tr where tr.playlist_id = pl.playlist_id and tr.processed_p is false) then 'processing' else 'done' end) as status from stalkify_playlists pl where pl.lastfm_username=".db_quote($username)." order by playlist_id");
while ($res->fetchInto($row)) {
  $t = $row['feed_type'];
  $s = $row['status'];
  $n = $prettyname[$t];
    $d = $prettydesc[$t];
    ?>
  <tr <? if ($s=='none') { ?>class="discrete"<? } ?>>
    <td class="playlist-list-name">
    <h3>
    <? if ($s=='none') { ?>
      <img src="/indicator.gif" />
      <?=$n?> *
    <? } else { ?>
      <? if ($s=='processing') { ?>
        <img src="/indicator.gif" />
      <? } ?>
        <a target="_new" href="/<?=$username?>/<?=$t?>"><?=$n?></a> *
    <? } ?>
    </h3>
  </td>
  <td class="playlist-list-tilde">~&nbsp;</td>
  <td class="playlist-list-desc"><?=$d?></td>
  </tr>
<?
}
?>

</table>
