

function attendEvent(eventid, url) {
    FB.login(function(response) {
              if (response.session) {
                var access_token = response.session.access_token;
                  if (response.perms) {
                        FB.api('/'+eventid+'/attending', 'post', {access_token: access_token},function(response) {
                            if (response) {
                                    var attending = parseInt(jQuery('#fbeventattending' + eventid).html());
                                    jQuery('#fbeventattending' + eventid).html(attending+1);
                             }
                         });
                  } else {
                      // user is logged in, but did not grant any permissions
                  }
               } else {
                    // user is not logged in
               }
            }, {perms:'rsvp_event'});
}
function maybeEvent(eventid, url) {
    FB.login(function(response) {
              if (response.session) {
                var access_token = response.session.access_token;
                  if (response.perms) {
                        FB.api('/'+eventid+'/maybe', 'post', {access_token: access_token},function(response) {
                            if (response) {
                                    var maybe = parseInt(jQuery('#fbeventmaybe' + eventid).html());
                                    jQuery('#fbeventmaybe' + eventid).html(maybe+1);
                             }

                              });
                  } else {
                      // user is logged in, but did not grant any permissions
                  }
               } else {
                    // user is not logged in
               }
            }, {perms:'rsvp_event'});

}
function declineEvent(eventid, url) {
    FB.login(function(response) {
              if (response.session) {
                var access_token = response.session.access_token;
                  if (response.perms) {
                        FB.api('/'+eventid+'/declined', 'post', {access_token: access_token},function(response) {

                            if (response) {
                                    var declined = parseInt(jQuery('#fbeventdeclined' + eventid).html());
                                    jQuery('#fbeventdeclined' + eventid).html(declined+1);
                             }
                              });
                  } else {
                      // user is logged in, but did not grant any permissions
                  }
               } else {
                    // user is not logged in
               }
            }, {perms:'rsvp_event'});

}
