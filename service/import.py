#!/usr/bin/env python
import sys
import traceback
import time

import pylast
import spotimeta
import psycopg2
import config

#print ("Start A")
#sys.stdout.flush()

last_network = pylast.get_lastfm_network(api_key = config.LASTFM_API_KEY)
#print ("Start B")
#sys.stdout.flush()
last_network.disable_caching()
#print ("Start C")
#sys.stdout.flush()

db_conn = psycopg2.connect(config.DB_CONN_STRING)

#print ("Start D")
#sys.stdout.flush()

### 1. IMPORT TRACK FROM LAST.FM ###
cur = db_conn.cursor()
cur.execute("select playlist_id, lastfm_username, feed_type, last_updated from stalkify_playlists where (last_updated is null or last_updated+update_interval<now()) order by (last_updated is null) desc, last_updated+update_interval")
numtracks = 0
#print ("Start E")
#sys.stdout.flush()
for row in cur.fetchall():
    playlist_id = row[0]
    lastfm_username = row[1]
    feed_type = row[2]

    #print ("Start F, %s" % (playlist_id, ))
    #sys.stdout.flush()
    cur.execute("update stalkify_playlists set last_updated = now() where playlist_id = %s", (playlist_id, ))
    #print ("Start G")
    #sys.stdout.flush()

    try:
        last_user = pylast.User(lastfm_username, last_network);

        print ("Processing %s for %s" % (feed_type, lastfm_username))
        sys.stdout.flush()

        if feed_type == "recent":
            # Get most recent addition
            ### limit 1 is costly? ### cur.execute("select name, artist from stalkify_tracks where playlist_id = %s order by track_id desc limit 1", (playlist_id, ))
            cur.execute("select name, artist from stalkify_tracks where playlist_id = %s order by track_id desc", (playlist_id, ))
            if cur.rowcount>0:
                r = cur.fetchone()
                recent_name = r[0]
                recent_artist = r[1]
            else:
                recent_name = ""
                recent_artist = ""
            
            #print ("Most recent for %s is %s - %s" % (lastfm_username, recent_name, recent_artist))

            # Get data from last.fm
            tracks = last_user.get_recent_tracks(20)
            addtracks = []
            for played_track in tracks:
                t = played_track['track']
                name = t.get_title().encode('utf-8')
                artist = t.get_artist().get_name().encode('utf-8')
                
                # Already added?
                if artist == recent_artist and name == recent_name:
                    #print "Matched recent track, breaking"
                    break

                addtracks.append(played_track)

            addtracks.reverse()
            for played_track in addtracks:
                t = played_track['track']
                name = t.get_title().encode('utf-8')
                artist = t.get_artist().get_name().encode('utf-8')
                #print ("Recent track for %s: %s - %s, time = %s" % (lastfm_username, name, artist, played_track['timestamp']))
                
                # Add the row
                cur.execute("insert into stalkify_tracks (playlist_id, name, artist) values (%s, %s, %s)", (playlist_id, name, artist))
                numtracks = numtracks + 1
                db_conn.commit()


        else:
            # Clear previous lists
            print ("Queued clearing %s/%s" % (lastfm_username, feed_type))
            cur.execute("update stalkify_playlists set clear_p = true where playlist_id = %s", (playlist_id, ))
            cur.execute("delete from stalkify_tracks where playlist_id = %s", (playlist_id, ))

            if feed_type == "lovedtracks":
                print("Getting lovedtraks")
                sys.stdout.flush()
                tracks = last_user.get_loved_tracks(limit=500)
                print("Done retrieving lovedtraks")
                sys.stdout.flush()
            else:
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

                tracks = last_user.get_top_tracks(period)
 

            for track in tracks:
                if feed_type == "lovedtracks":
                    t = track['track']
                else:
                    t = track['item']

                name = t.get_title().encode('utf-8')
                artist = t.get_artist().get_name().encode('utf-8')

                # Add the row
                print("insert into stalkify_tracks (playlist_id, name, artist) values (%s, %s, %s)", (playlist_id, name, artist))
                sys.stdout.flush()
                cur.execute("insert into stalkify_tracks (playlist_id, name, artist) values (%s, %s, %s)", (playlist_id, name, artist))
                numtracks = numtracks + 1
                db_conn.commit()


    except:
        do = "nothing"


cur.close()
db_conn.commit()

print ("Retrieved %s tracks from last.fm" % (numtracks, ))
sys.stdout.flush()






