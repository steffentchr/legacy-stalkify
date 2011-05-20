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
$prettyname["lovedtracks"] = "loved tracks";
$prettydesc["lovedtracks"] = "Track loved on last.fm";
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

<?php if(0) { ?>
<!-- Neighbours according to Last.fm -->
<div id="neighbours" class="smallmeta listen-in" style="display:none; margin:0; padding-top:10px;">
  <div>Just like @<?=$username?>?</div>
  <ul id="neighboursContainer" style="font-size:12px;"></ul>
</div>
<script>
  stalkify.lastfm.getNeighbours('<?=$username?>', function(u){
      var i = 0;
      var html = '';
      while (i<24&&i<u.length) {
          html += '<a href="/'+u[i].name+'">@'+u[i].name+'</a> ';
          i++;
      }     
      $('#neighboursContainer').html(html);
      $('#neighbours').css({display:''});
  });
</script>
<?php } ?>

<!-- Top artists according to Last.fm -->
<div id="artists" style="display:none; padding-top:10px;">
  <h4>Who is @<?=$username?> listening to?</h4>
  <ul id="artistsContainer" style="font-size:12px;"></ul>
</div>
<script>
    stalkify.lastfm.getTopArtists('<?=$username?>', function(a){
        var i = 0;
        var html = '';
        while (i<48&&i<a.length) {
            var src = (a[i].image&&a[i].image.length&&a[i].image[2]['#text'] ? stalkify.lastfm.squareImage(a[i].image[2]['#text']) : "/void.gif");
            html += '<li class="lastfmartist" rel="'+a[i].name+'"><img width="126" height="126" src="'+src+'" /><div class="lastfmartist-meta"><div class="lastfmartist-name">'+a[i].name+'</div><br/><div class="lastfmartist-playcount">'+a[i].playcount+' plays</div></div></li> ';
            i++;
        }     
        $('#artistsContainer').html(html);
        $('#artists').css({display:''});

        $('.lastfmartist').click(function(){
            var t = $(this);
            t.find('img').css({opacity:'.2'});
            stalkify.spotify.searchArtist(t.attr('rel'), function(artists){
                if(artists&&artists.length) {
                    location.href=artists[0].href;
                } else {
                    alert("Bad stalker! Couldn't find this artist on Spotify.");
                }
                t.find('img').css({opacity:'1'});
              });
          });
        
    });
</script>
