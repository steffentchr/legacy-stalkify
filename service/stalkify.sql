create sequence seq_stalkify;
create table stalkify_playlists (
    playlist_id                 integer
                                not null
                                primary key
                                default nextval('seq_stalkify'),
    lastfm_username             text
                                not null,
    playlist_name               text
                                not null,
    feed_type                   text
                                not null,
    spotify_uri                 text,
    creation_date               timestamptz
                                not null
                                default now(),
    update_interval             interval
                                not null
                                default '1 day'::interval,
    num_opens            	integer
                                not null
                                default 0,
    last_updated                timestamptz,
    clear_p                     boolean
                                default false
);

insert into stalkify_playlists (lastfm_username, playlist_name, feed_type, update_interval) values ('steffentchr', 'Stalkify: steffentchr''s recent', 'recent', '1 minute'::interval);
insert into stalkify_playlists (lastfm_username, playlist_name, feed_type) values ('steffentchr', 'Stalkify: steffentchr''s all-time top tracks', 'toptracks-overall');
insert into stalkify_playlists (lastfm_username, playlist_name, feed_type) values ('steffentchr', 'Stalkify: steffentchr''s top tracks this week', 'toptracks-7day');
insert into stalkify_playlists (lastfm_username, playlist_name, feed_type) values ('steffentchr', 'Stalkify: steffentchr''s 3-months top tracks', 'toptracks-3month');
insert into stalkify_playlists (lastfm_username, playlist_name, feed_type) values ('steffentchr', 'Stalkify: steffentchr''s 6-months top tracks', 'toptracks-6month');
insert into stalkify_playlists (lastfm_username, playlist_name, feed_type) values ('steffentchr', 'Stalkify: steffentchr''s top tracks this year', 'toptracks-12month');

insert into stalkify_playlists (lastfm_username, playlist_name, feed_type, update_interval) values ('pollethewonder', 'Stalkify: pollethewonder''s recent', 'recent', '1 minute'::interval);
insert into stalkify_playlists (lastfm_username, playlist_name, feed_type) values ('pollethewonder', 'Stalkify: pollethewonder''s all-time top tracks', 'toptracks-overall');
insert into stalkify_playlists (lastfm_username, playlist_name, feed_type) values ('pollethewonder', 'Stalkify: pollethewonder''s top tracks this week', 'toptracks-7day');
insert into stalkify_playlists (lastfm_username, playlist_name, feed_type) values ('pollethewonder', 'Stalkify: pollethewonder''s 3-months top tracks', 'toptracks-3month');
insert into stalkify_playlists (lastfm_username, playlist_name, feed_type) values ('pollethewonder', 'Stalkify: pollethewonder''s 6-months top tracks', 'toptracks-6month');
insert into stalkify_playlists (lastfm_username, playlist_name, feed_type) values ('pollethewonder', 'Stalkify: pollethewonder''s top tracks this year', 'toptracks-12month');

create table stalkify_tracks (
    track_id                    integer
                                not null
                                primary key
                                default nextval('seq_stalkify'),
    playlist_id                 integer
                                not null
                                references stalkify_playlists,
    name                        text
                                not null,
    artist                      text
                                not null,
    spotify_uri                 text,
    creation_date               timestamptz
                                not null
                                default now(),
    processed_p                 boolean
                                not null
                                default false,
    processed_date              timestamptz
);

create index idx_track_playlist_id on stalkify_tracks(playlist_id);
create index idx_playlist_spotify_uri on stalkify_playlists(spotify_uri);
create index idx_track_name on stalkify_tracks(name);
create index idx_track_artist on stalkify_tracks(artist);
create index idx_track_name_artist on stalkify_tracks(name, artist);
create index idx_track_processed_p on stalkify_tracks(processed_p);

