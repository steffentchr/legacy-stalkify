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
    last_updated                timestamptz
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
