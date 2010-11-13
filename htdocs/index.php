<?

require('include.php');
db_open();


$stubs = explode("/", $_SERVER['REQUEST_URI']);
if ( $stubs[1]=="" || $stubs[1]=="index") {
  Header('Location: /welcome');
  exit();
}
$username = $stubs[1];


if (sizeof($stubs)>2) {
  // Asked for a specific playlist
  $playlist = $stubs[2];

  $res = db_query("select * from stalkify_playlists where lastfm_username = ".db_quote($username)." and feed_type = ".db_quote($playlist)." and spotify_uri is not null");
  if ($res->numRows()==0) {
    Header('Location: /'.$username);
  } else {
    $res->fetchInto($row);
    $playlist_id = $row['playlist_id'];
    db_query("update stalkify_playlists set num_opens=num_opens+1 where playlist_id = " . db_quote($playlist_id)); 
    $url = $row['spotify_uri'];
    //preg_match("/spotify:user:([^:]+):([^:]+):([^:]+)/", $row['spotify_uri'], $matches);
    //$url = "http://open.spotify.com/user/".$matches[1]."/".$matches[2]."/".$matches[3];
    Header('Location: '.$url);
  }
}

$res = db_query("select * from stalkify_playlists where lastfm_username = ".db_quote($username));
if ($res->numRows()==0) {
  // Check that the user exists and then create
  $url = "http://www.last.fm/user/" . $username;
  $ch = curl_init($url); 
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $_x = curl_exec($ch); 
  if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
    db_query("insert into stalkify_playlists (lastfm_username, playlist_name, feed_type, update_interval) values (".db_quote($username).", '@'||".db_quote($username)."||' / live', 'recent', '10 minutes'::interval)");
    db_query("insert into stalkify_playlists (lastfm_username, playlist_name, feed_type, last_updated) values (".db_quote($username).", '@'||".db_quote($username)."||' / all-time', 'toptracks-overall', now()-'30 days'::interval+'2 minutes'::interval)");
    db_query("insert into stalkify_playlists (lastfm_username, playlist_name, feed_type, last_updated) values (".db_quote($username).", '@'||".db_quote($username)."||' / this week', 'toptracks-7day', now()-'2 days'::interval+'5 minutes'::interval)");
    db_query("insert into stalkify_playlists (lastfm_username, playlist_name, feed_type, last_updated) values (".db_quote($username).", '@'||".db_quote($username)."||' / 3 months', 'toptracks-3month', now()-'15 days'::interval+'8 minutes'::interval)");
    db_query("insert into stalkify_playlists (lastfm_username, playlist_name, feed_type, last_updated) values (".db_quote($username).", '@'||".db_quote($username)."||' / 6 months', 'toptracks-6month', now()-'30 days'::interval+'12 minutes'::interval)");
    db_query("insert into stalkify_playlists (lastfm_username, playlist_name, feed_type, last_updated) values (".db_quote($username).", '@'||".db_quote($username)."||' / this year', 'toptracks-12month', now()-'30 days'::interval+'15 minutes'::interval)");
    db_query("insert into stalkify_playlists (lastfm_username, playlist_name, feed_type, last_updated) values (".db_quote($username).", '@'||".db_quote($username)."||' / loved tracks', 'lovedtracks', now()-'1 days'::interval+'15 minutes'::interval)");
    Header('Location: /'.$username);
  } else {
    Header("Location: /welcome?msg=Wow.+That+is+not+a+real+Last.fm+user");
  }
} else {
  // Show lists!
  include('header.php');
  include('showlists.php');
  include('footer.php');
}

?>


