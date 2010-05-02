#!/usr/bin/env ruby

require 'rubygems'
require 'postgres'
require 'greenstripes'
require 'config'

# Connect to Spotify
session = GreenStripes::Session.new(SPOTIFY_APPKEY, 'GreenStripes', 'tmp', 'tmp')
session.login(SPOTIFY_USERNAME, SPOTIFY_PASSWORD)
session.process_events until session.connection_state == GreenStripes::ConnectionState::LOGGED_IN

pc = session.playlist_container()

# Connect to pgsql
conn = PGconn.connect("localhost",5432,"","",DB_NAME,DB_USERNAME,DB_PASSWORD)


# Sync playlists
results = conn.query("select playlist_id, playlist_name from stalkify_playlists pl where spotify_uri is null and exists (select 1 from stalkify_tracks tr where tr.playlist_id = pl.playlist_id)")
results.each do |row|
  playlist_id = row[0]
  playlist_name = row[1]

  p = pc.add_new_playlist(playlist_name)
  uri = GreenStripes::Link.new(p).to_s()
  conn.query("update stalkify_playlists set spotify_uri = '" + uri + "' where playlist_id = " + playlist_id)
  printf "Created playlist '%s' with uri '%s'\n", p.name(), uri
end

# Index existing playlists
playlists = {}
index = 0
while index<pc.num_playlists()
  pl = pc.playlist(index)
  uri = GreenStripes::Link.new(pl).to_s()
  playlists[uri] = pl
  #printf "Indexed playlist '%s' with uri '%s'\n", pl.name(), uri
  index +=1
end 

# Clear some playlists
results = conn.query("select playlist_id, spotify_uri from stalkify_playlists pl where pl.feed_type <> 'recent' and spotify_uri is not null and exists (select 1 from stalkify_tracks tr where tr.playlist_id=pl.playlist_id and tr.spotify_uri is not null and tr.processed_p is false)")
results.each do |row|
  playlist_id = row[0]
  playlist_uri = row[1]
  
  p = playlists[playlist_uri]
  if p then
    printf "Cleared content on '%s' with uri '%s'\n", p.name(), playlist_uri
    while p.num_tracks()>0
      # this is a method hacked into greenstripes
      p.remove_first_track()
    end
  end
end





# Sync tracks
results = conn.query("select tr.track_id, pl.spotify_uri, tr.spotify_uri from stalkify_playlists pl, stalkify_tracks tr where tr.playlist_id=pl.playlist_id and tr.spotify_uri is not null and tr.processed_p is false order by track_id asc")
results.each do |row|
  track_id = row[0]
  playlist_uri = row[1]
  track_uri = row[2]
  
  p = playlists[playlist_uri]
  if p then
    t = GreenStripes::Link.new(track_uri).to_track()
    printf "Added track %s (%s) to %s (%s)\n", t.name(), track_uri, p.name(), playlist_uri
    tr = [t];
    p.add_tracks(tr, 1, p.num_tracks())
    conn.query("update stalkify_tracks set processed_p=true, processed_date=now() where track_id = " + track_id)
  end
end

conn.close()

session.logout
session.process_events until session.connection_state == GreenStripes::ConnectionState::LOGGED_OUT
