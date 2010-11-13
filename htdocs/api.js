var stalkify = {
  proxyJSON: function(url, callback){$.getJSON('/simple-proxy?url='+encodeURIComponent(url), callback);},
  spotify: {
    search:function(type, objname, search, callback){stalkify.proxyJSON('http://ws.spotify.com/search/1/'+type+'.json?q='+encodeURIComponent(search), function(data) {callback(data.contents[objname]);});},
    searchArtist:function(artist, callback){stalkify.spotify.search('artist', 'artists', artist, callback);},
    searchTrack:function(track, callback){stalkify.spotify.search('track', 'tracks', track, callback);},
    searchAlbum:function(album, callback){stalkify.spotify.search('album', 'albums', album, callback);}
  },
  lastfm: {
    get:function(request, callback){request['api_key']='0f9d58ba56bfa4bd4b24ba62b9568615'; request['format']='json'; stalkify.proxyJSON('http://ws.audioscrobbler.com/2.0/?'+$.param(request), function(data) {callback(data.contents);});},
    getNeighbours:function(username, callback){stalkify.lastfm.get({method:'user.getneighbours', user:username}, function(o){callback(o.neighbours.user);});},
    getTopArtists:function(username, callback){stalkify.lastfm.get({method:'user.gettopartists', user:username}, function(o){callback(o.topartists.artist);});},
    getWeeklyArtistChart:function(username, callback){stalkify.lastfm.get({method:'user.getweeklyartistchart', user:username}, function(o){callback(o.weeklyartistchart.artist);});},
    squareImage:function(url){return(url.replace(/\/([0-9]{2,3})\//img, "/$1s/"));}
  }
}