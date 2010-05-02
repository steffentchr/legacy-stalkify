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

// Asked for list of playlists
$res = db_query("select * from stalkify_playlists where spotify_uri is not null and lastfm_username = ".db_quote($username)." order by playlist_id");
if ($res->numRows()==0) {
  // No list yet for consumption yet...
  $res2 = db_query("select * from stalkify_playlists where lastfm_username = ".db_quote($username));
  if ($res2->numRows()==0) {
    // Check that the user exists and then create
    $url = "http://ws.audioscrobbler.com/2.0/?api_key=0f9d58ba56bfa4bd4b24ba62b9568615&method=user.getrecenttracks&user=" . $username;
    $ch = curl_init($url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $_x = curl_exec($ch); 
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
      db_query("insert into stalkify_playlists (lastfm_username, playlist_name, feed_type, update_interval) values (".db_quote($username).", 'Stalkify: '||".db_quote($username)."||' / live', 'recent', '1 minute'::interval)");
      db_query("insert into stalkify_playlists (lastfm_username, playlist_name, feed_type) values (".db_quote($username).", 'Stalkify: '||".db_quote($username)."||' / all-time', 'toptracks-overall')");
      db_query("insert into stalkify_playlists (lastfm_username, playlist_name, feed_type) values (".db_quote($username).", 'Stalkify: '||".db_quote($username)."||' / this week', 'toptracks-7day')");
      db_query("insert into stalkify_playlists (lastfm_username, playlist_name, feed_type) values (".db_quote($username).", 'Stalkify: '||".db_quote($username)."||' / 3 months', 'toptracks-3month')");
      db_query("insert into stalkify_playlists (lastfm_username, playlist_name, feed_type) values (".db_quote($username).", 'Stalkify: '||".db_quote($username)."||' / 6 months', 'toptracks-6month')");
      db_query("insert into stalkify_playlists (lastfm_username, playlist_name, feed_type) values (".db_quote($username).", 'Stalkify: '||".db_quote($username)."||' / this year', 'toptracks-12month')");
      Header('Location: /'.$username);
    } else {
      Header("Location: /welcome?msg=Wow.+That+is+not+a+real+Last.fm+user");
    }
  } else {
    // Still waiting to be created in Spotify
    include('stillwaiting.php');
  }
} else {
  // Show lists!
  include('header.php');
  include('showlists.php');
  include('footer.php');
}

?>


