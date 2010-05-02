#!/usr/bin/env python
import sys
import traceback
import time

import pylast
import spotimeta
import psycopg2
import config

last_network = pylast.get_lastfm_network(api_key = config.LASTFM_API_KEY)
last_network.disable_caching()

db_conn = psycopg2.connect(config.DB_CONN_STRING)

### 1. IMPORT TRACK FROM LAST.FM ###
cur = db_conn.cursor()
cur.execute("select playlist_id, lastfm_username, feed_type from stalkify_playlists where (last_updated is null or last_updated+update_interval<now())")
for row in cur.fetchall():
    playlist_id = row[0]
    lastfm_username = row[1]
    feed_type = row[2]

    last_user = pylast.User(lastfm_username, last_network);

    if feed_type == "recent":
        # Get most recent addition
        cur.execute("select name, artist from stalkify_tracks where playlist_id = %s order by track_id desc limit 1", (playlist_id, ))
        if cur.rowcount>0:
            r = cur.fetchone()
            recent_name = r[0]
            recent_artist = r[1]
        else:
            recent_name = ""
            recent_artist = ""
            
        print ("Most recent for %s is %s - %s" % (lastfm_username, recent_name, recent_artist))

        # Get data from last.fm
        tracks = last_user.get_recent_tracks(100)
        addtracks = []
        for played_track in tracks:
            t = played_track['track']
            name = t.get_title().encode('utf-8')
            artist = t.get_artist().get_name().encode('utf-8')

            # Already added?
            if artist == recent_artist and name == recent_name:
                print "Matched recent track, breaking"
                break

            addtracks.append(played_track)

        addtracks.reverse()
        for played_track in addtracks:
            t = played_track['track']
            name = t.get_title().encode('utf-8')
            artist = t.get_artist().get_name().encode('utf-8')
            print ("Recent track for %s: %s - %s" % (lastfm_username, name, artist))

            # Add the row
            cur.execute("insert into stalkify_tracks (playlist_id, name, artist) values (%s, %s, %s)", (playlist_id, name, artist))
    else:
        # Clear previous lists
        cur.execute("delete from stalkify_tracks where playlist_id = %s", (playlist_id, ))

        # Get data from last.fm
        if feed_type == "toptracks-7day":
            period = pylast.PERIOD_7DAYS
        elif feed_type == "toptracks-3month":
            period = pylast.PERIOD_3MONTHS
        elif feed_type == "toptracks-6month":
            period = pylast.PERIOD_6MONTHS 
        elif feed_type == "toptracks-12month":
            period = pylast.PERIOD_12MONTHS 
        else:
            period = pylast.PERIOD_OVERALL
 

        for track in last_user.get_top_tracks(period):
            t = track['item']
            name = t.get_title().encode('utf-8')
            artist = t.get_artist().get_name().encode('utf-8')

            # Add the row
            cur.execute("insert into stalkify_tracks (playlist_id, name, artist) values (%s, %s, %s)", (playlist_id, name, artist))

    cur.execute("update stalkify_playlists set last_updated = now() where playlist_id = %s", (playlist_id, ))

cur.close()
db_conn.commit()


### 2. MATCH TO TRACKS ON SPOTIFY AND UPDATE WITH SPOTIFY_URI ###
cur = db_conn.cursor()
cur.execute("select track_id, name, artist from stalkify_tracks where spotify_uri is null and processed_p is false")
for row in cur.fetchall():
    try:
        track_id = row[0]
        name = row[1].decode('utf-8')
        artist = row[2].decode('utf-8')
        
        search = spotimeta.search_track("%s %s" % (name, artist))
        if search["total_results"]>0:
            # Save the spotify_uri; then the sync script will carry on the good word
            spotify_uri = search["result"][0]["href"]
            cur.execute("update stalkify_tracks set spotify_uri=%s where track_id = %s", (spotify_uri, track_id))
            spotimeta_artist = search["result"][0]["artist"]["name"]
            spotimeta_name = search["result"][0]["name"]
            print ("Queued %s - %s (%s): %s - %s" % (artist, name, spotify_uri, spotimeta_artist, spotimeta_name))
        else: 
            # We won't be able to do more for these tracks
            cur.execute("update stalkify_tracks set processed_p=true, processed_date=now() where track_id = %s", (track_id, ))
    except:
        do = "nothing"


    time.sleep(0.3)


cur.close()
db_conn.commit()

db_conn.close()
    





