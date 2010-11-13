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
db_conn.set_isolation_level(0)
print "Begun matching"

### 2. MATCH TRACKS FROM DATA ALREADY IN DATABASE ###
cur = db_conn.cursor()
print ("Running match from database -- looing for songs on spotify")
sys.stdout.flush()
cur.execute("select tr.track_id, trmatch.spotify_uri from stalkify_tracks tr, stalkify_tracks trmatch where tr.spotify_uri is null and tr.processed_p is false and trmatch.spotify_uri is not null and tr.artist=trmatch.artist and tr.name=trmatch.name group by tr.track_id, trmatch.spotify_uri")
totaltracks = 0
for row in cur.fetchall():
    totaltracks = totaltracks + 1
    track_id = row[0]
    spotify_uri = row[1]
    cur.execute("update stalkify_tracks set spotify_uri=%s where track_id = %s", (spotify_uri, track_id))
    print ("%s: Queued %s, %s from database" % (totaltracks, spotify_uri, track_id))
    sys.stdout.flush()

print ("Matched %s tracks from db" % (totaltracks, ))
sys.stdout.flush()
db_conn.commit()
cur.close()

### 2a. MATCH NOT-FOUND TRACKS FROM DATA ALREADY IN DATABASE ###
cur = db_conn.cursor()
print ("Running match from database -- looking for songs not found on spotify")
sys.stdout.flush()
cur.execute("select tr.track_id from stalkify_tracks tr, stalkify_tracks trmatch where tr.spotify_uri is null and tr.processed_p is false and trmatch.spotify_uri is null and trmatch.processed_p is true and tr.artist=trmatch.artist and tr.name=trmatch.name group by tr.track_id")
totaltracks = 0
for row in cur.fetchall():
    totaltracks = totaltracks + 1
    track_id = row[0]
    cur.execute("update stalkify_tracks set processed_p=true, processed_date=now() where track_id = %s", (track_id, ))
    print ("%s: Queued %s not on spotify according to database" % (totaltracks, track_id))
    sys.stdout.flush()

print ("Matched %s tracks not on spotify from db" % (totaltracks, ))
sys.stdout.flush()
db_conn.commit()
cur.close()


### 3. MATCH REMAINING TRACKS ON SPOTIFY AND UPDATE WITH SPOTIFY_URI ###
cur = db_conn.cursor()
cur.execute("select track_id, name, artist from stalkify_tracks where spotify_uri is null and processed_p is false order by track_id limit 1000")
totaltracks = 0
hittracks = 0
missedtracks = 0
errortracks = 0
for row in cur.fetchall():
    totaltracks = totaltracks + 1
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
            print ("%s: Queued %s - %s (%s): %s - %s" % (totaltracks, artist, name, spotify_uri, spotimeta_artist, spotimeta_name))
            sys.stdout.flush()
            hittracks = hittracks + 1
        else: 
            # We won't be able to do more for these tracks
            cur.execute("update stalkify_tracks set processed_p=true, processed_date=now() where track_id = %s", (track_id, ))
            missedtracks = missedtracks + 1
    except:
        cur.execute("update stalkify_tracks set processed_p=true, processed_date=now() where track_id = %s", (track_id, ))
        errortracks = errortracks + 1


    db_conn.commit()
    time.sleep(0.2)

print ("Hit %s, missed %s and errored %s" % (hittracks, missedtracks, errortracks))
sys.stdout.flush()


cur.close()

db_conn.close()
    





