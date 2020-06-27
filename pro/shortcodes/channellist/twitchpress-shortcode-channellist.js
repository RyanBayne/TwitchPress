/* 
    To do:
    --------------------------------------
    - Add button to expand stream preview on mobile
*/

$(document).ready(function() {
  // Filters event handler
  var filters = $('.filters').children();
  filters.on('click', filterChannels);

  // Users List
  var channels = ["ESL_SC2", "OgamingSC2", "cretetion", "freecodecamp", "storbeck", "habathcx", "RobotCaleb", "noobs2ninjas", "brunofin", "comster404"];

  // Generic ajax call to Twitch.tv API
  var getData = function(type, user, options) {
    return $.ajax({
      dataType: 'json',
      url: "https://wind-bow.gomix.me/twitch-api/" + type + "/" + user + "?callback=?"
    });
  };

  // Initialize app
  for (var i = 0; i < channels.length; i++) {
    // Set ajax calls
    var channel = channels[i],
      getChannelData = getData("channels", channel, true),
      getStreamData = getData("streams", channel, false);

    // Use a closure to pass channel name to .then()
    (function(channel) {
      // Send simoultaneous ajax calls
      $.when(
        getChannelData,
        getStreamData
      ).then(function(channelData, streamData) {
        var name = channelData[0].display_name || channel,
          logo = channelData[0].logo || "http://krzme.com/satyatest/wp-content/uploads/sites/6/2014/06/placeholder1.jpg",
          url = channelData[0].url || "https://www.twitch.tv/",
          liveStatus,
          status = channelData[0].status || "No status available for this channel",
          livePreview = streamData[0].stream ? streamData[0].stream.preview.large : null, // this is a link
          game = streamData[0].stream ? streamData[0].stream.game : null,
          followers = channelData[0].followers ? channelData[0].followers.toLocaleString() : "N/A",
          views = channelData[0].views ? channelData[0].views.toLocaleString() : "N/A",
          viewers = streamData[0].stream ? streamData[0].stream.viewers.toLocaleString() : "N/A";

        // Set live status
        if (channelData[0].error) {
          // If channel doesn't exist
          liveStatus = "closed";
          status = "This account has been closed or never existed";
        } else {
          liveStatus = streamData[0].stream ? "online" : "offline";
        }

        // Inject data in HTML
        showChannelData(name, logo, url, liveStatus, status, followers, views, game, livePreview, viewers);
      });
    })(channel);
  }

  // Create a new div to hold user data, then append it to #channels
  function showChannelData(name, logo, url, liveStatus, status, followers, views, game, livePreview, viewers) {
    var isOnline = liveStatus === 'online',
      isClosed = liveStatus === 'closed',
      category = "#" + liveStatus,
      channelCard = '<article class="channel" id="' + channel + '">\
                                <a class="channel-link" href="' + url + '" target="_blank">\
                                    <header class="channel-primary row">\
                                        <div class="channel-logo col-s"><img src="' + logo + '"></div>\
                                        <div class="col-lg">\
                                            <div class="row">\
                                                <h3 class="channel-name">' + name + '</h3>\
                                                <div class="channel-curr-status">' + liveStatus + '</div>\
                                            </div>\
                                            <div class="channel-status row">' + status + '</div>\
                                        </div>\
                                    </header>';

    if (isOnline) {
      channelCard += '<div class="stream-preview row"><img src="' + livePreview + '"></div>';
    }
    if (!isClosed) {
      channelCard += '<div class="channel-details row">\
                                    <ul class="channel-stats">\
                                        <li><i class="fa fa-heart"></i>' + followers + '</li>\
                                        <li><i class="fa fa-eye"></i>' + views + '</li>\
                                    </ul>';
    }
    if (isOnline) {
      channelCard += '<div class="stream-details">\
                                    <span class="stream-game">Playing ' + game + '</span><span class="stream-stats"><i class="fa fa-group"></i>' + viewers + '</span>\
                                </div>';
    }
    if (!isClosed) {
      channelCard += '<div class="more-btn"><i class="fa fa-chevron-down"></i></div>';
    }

    channelCard += '</div></a></article>';

    // Create and append element to corresponding container
    jQuery(channelCard).appendTo(category);
  }

  // Filtering
  function filterChannels() {
    if (this.innerHTML === 'all') {
      $('#online, #offline, #closed').show();
    } else if (this.innerHTML === 'online') {
      $('#online').show();
      $('#offline, #closed').hide();
    } else {
      $('#offline, #closed').show();
      $('#online').hide();
    }
  }

});