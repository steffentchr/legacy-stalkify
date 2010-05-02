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
    preg_match("/spotify:user:([^:]+):([^:]+):([^:]+)/", $row['spotify_uri'], $matches);
    $url = "http://open.spotify.com/user/".$matches[1]."/".$matches[2]."/".$matches[3];
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
    db_query("insert into stalkify_playlists (lastfm_username, playlist_name, feed_type, update_interval) values (".db_quote($username).", '@'||".db_quote($username)."||' / live', 'recent', '1 minute'::interval)");
    db_query("insert into stalkify_playlists (lastfm_username, playlist_name, feed_type, last_updated) values (".db_quote($username).", '@'||".db_quote($username)."||' / all-time', 'toptracks-overall', now()-'1 day'::interval+'2 minute'::interval)");
    db_query("insert into stalkify_playlists (lastfm_username, playlist_name, feed_type, last_updated) values (".db_quote($username).", '@'||".db_quote($username)."||' / this week', 'toptracks-7day', now()-'1 day'::interval+'4 minutes'::interval)");
    db_query("insert into stalkify_playlists (lastfm_username, playlist_name, feed_type, last_updated) values (".db_quote($username).", '@'||".db_quote($username)."||' / 3 months', 'toptracks-3month', now()-'1 day'::interval+'6 minutes'::interval)");
    db_query("insert into stalkify_playlists (lastfm_username, playlist_name, feed_type, last_updated) values (".db_quote($username).", '@'||".db_quote($username)."||' / 6 months', 'toptracks-6month', now()-'1 day'::interval+'8 minutes'::interval)");
    db_query("insert into stalkify_playlists (lastfm_username, playlist_name, feed_type, last_updated) values (".db_quote($username).", '@'||".db_quote($username)."||' / this year', 'toptracks-12month', now()-'1 day'::interval+'10 minutes'::interval)");
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


