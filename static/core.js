var token = localStorage.getItem('token');
function linkify(inputText) {
    var replacedText, replacePattern1, replacePattern2, replacePattern3;
    // URLs starting with http://, https://, or ftp://
    replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
    replacedText = inputText.replace(replacePattern1, '<a href="$1" target="_blank">$1</a>');
    // URLs starting with "www." (without // before it, or it'd re-link the ones done above).
    replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
    replacedText = replacedText.replace(replacePattern2, '$1<a href="http://$2" target="_blank">$2</a>');
    //Change email addresses to mailto:: links.
    replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
    replacedText = replacedText.replace(replacePattern3, '<a href="mailto:$1">$1</a>');
    return replacedText;
}
function strip_tags(str) {
    str = str.toString();
    return str.replace(/<\/?[^>]+>/gi, '');
}
var cuties = ['ef61oIGVyckY8','3xz2BCohVTd7h2Kvfi','ONuQzM11fjvoY','vJRMuf14ygIec','RMycpvNIOk98I','ue4rk7zGOW2Qg','bXdjYegbUX3b2','ir2YUVoSgrgMU'];
(function($) {
    $.rand = function(arg) {
        if ($.isArray(arg)) {
            return arg[$.rand(arg.length)];
        } else if (typeof arg === "number") {
            return Math.floor(Math.random() * arg);
        } else {
            return 4;  // chosen by fair dice roll
        }
    };
})(jQuery);
var md = window.markdownit();
function replaceAtMentionsWithLinks(text) {
    return text.replace(/@([a-z\d_]+)/ig, '<a href="https://henlo.xyz/user/$1">@$1</a>'); 
}
function feed() {
	var feedMode = localStorage.getItem('feed-mode');
	if (feedMode === 'true') {
		var sort = 'everything';
	} else {
		var sort = 'following';
	}
	$.getJSON('https://henlo.xyz/api/feed.php', {"token": token, "sort": sort}, function(json) {
		$('#feed').empty();
		if (sort === 'everything') {
			$('#feed').append('<section id="feed"><h2>🌎 Everything<button type="button" id="feed-mode" ontouchstart="">😊 Following</button></h2></section>');
		} else {
			$('#feed').append('<section id="feed"><h2>😊 Following<button type="button" id="feed-mode" ontouchstart="">🌎 Everything</button></h2></section>');
		}
		if (json.error === "INVALID_TOKEN" || json.error === "NOT_REGISTERED") {
			localStorage.removeItem('token');
			// userProfile function already sends an alert.
			location.reload(true);
		} else {
			if (json.statuses === null) {
				$('#feed').append('<div class="card"><h3>😭</h3><p>There isn\'t any posts to display.</p></div>'); 
			} else {
				$.each(json.statuses, function(index, element) {
					var time = element.time;
					var id = element.id;
					var username = element.username;
					var pic = element.pic;
					var cutie = jQuery.rand(cuties);
					if (element.pic === false) {
						var pic = 'https://media.giphy.com/media/' + cutie + '/giphy.gif';
					} else {
						var pic = element.pic;
					}
					if (element.header === false) {
						var header = 'https://media0.giphy.com/media/Yqn9tE2E00k4U/giphy.gif';
					} else {
						var header = element.header;
					}
					var status = element.status;
					var domain = element.domain;
					var verified = element.verified;
					if (username === 'james') {
						var html = '<h3 class="username"><a href="https://henlo.xyz/user/' + username + '">' + username + ' 💟</a></h3><h3>💓 I 💓 L 💓 Y 💓 S 💓 M 💓</h3>';
					} else if (domain === false) {
						var html = '<h3 class="username"><a href="https://henlo.xyz/user/' + username + '">' + username + '</a></h3><h3 style="opacity: 0;">.</h3>';
					} else {
						if (verified === false) {
							var html = '<h3 class="username"><a href="https://henlo.xyz/user/' + username + '">' + username + '</a></h3><h3 style="opacity: 0;">.</h3></a>';
						} else {
							var html = '<h3 class="username"><a href="https://henlo.xyz/user/' + username + '">' + username + '</a></h3><h3 class="domain"><a href="http://' + domain + '" target="_blank">' + domain + ' ✅</a></h3>';
						}
					}
					var followed = element.followed;
					var followers = element.followers;
					var likes = element.likes;
					var liked = element.liked;
					var likers = element.likers;
					var output = md.render(strip_tags(status));
					$('#feed').append('<div class="status"><a href="https://henlo.xyz/user/' + username + '"><img class="profile-pic" src="https://henlo.xyz/api/image.php?url=' + pic + '" style="width: 80px; height:80px;"></a><div class="user-info">' + html + '</div><p>' + replaceAtMentionsWithLinks(output) + '</p><p><span class="timeago" title="' + time + '">' + time + '</span></p>');
					if (liked === false) {
						$('#feed .status:last-child').append('<form action="https://henlo.xyz/api/like.php" method="POST" name="like"><input type="hidden" name="action" value="add"><input type="hidden" name="token" value="' + token + '"><input type="hidden" name="id" value="' + id + '"><button type="submit" ontouchstart=""><h2>❤️</h2><p>' + likes + '</p></button></form>');
					} else {
						$('#feed .status:last-child').append('<form action="https://henlo.xyz/api/like.php" method="POST" name="like"><input type="hidden" name="action" value="remove"><input type="hidden" name="token" value="' + token + '"><input type="hidden" name="id" value="' + id + '"><button class="danger" type="submit" ontouchstart=""><h2>💔</h2><p>' + likes + '</p></button></form>');
					}
					if (likes > 0) {
						var a = [];
						$.each(likers, function(key, value) {
							a.push(key);
						});
						if (likes === 1) {
							var text = 'Like';
						} else {
							var text = 'Likes';
						}
						$('#feed .status:last-child').append('<p class="likes">Liked by ' + a.join(', ').replace(/,(?!.*,)/gmi, ' and') + '.</p>');
						// $('#feed .status:last-child').append('<p><a href="https://henlo.xyz/user/' + username + '">Liked by ' + likes + ' ' + text + '.</a></p>');
						}
						if (navigator.userAgent.match(/(iPod|iPhone|iPad|Mac)/i)) {
							// iOS
						} else {
							twemoji.size = '72x72';
  							twemoji.parse(document.body);
  						}
				});
			}
		}
	});
}
	
	function showProfile(username) {
	$.getJSON('https://henlo.xyz/api/profile.php', {"token": token, "username": username}, function(json) {
			$('#profile .x').empty();
			if (json.error === "NOT_REGISTERED") {
					$('#profile').append('<div class="card"><h3>😭</h3><p>This user doesn\'t exist.</p></div>'); 
			} else {
				if (json.error !== false) {
					$('#profile').append('<div class="card"><h3>😩</h3><p>Can\'t display profile.</p></div>'); 
				} else {
						var username = json.username;
						var pic = json.pic;
						var cutie = jQuery.rand(cuties);
						if (json.pic === false) {
							var pic = 'https://media.giphy.com/media/' + cutie + '/giphy.gif';
						} else {
							var pic = json.pic;
						}
						if (json.header === false) {
							var header = 'https://media0.giphy.com/media/Yqn9tE2E00k4U/giphy.gif';
						} else {
							var header = json.header;
						}
						var domain = json.domain;
						var verified = json.verified;
						if (domain === false) {
														var html = '<h3>' + username + '</h3><h3 style="opacity: 0;">.</h3>';
						} else {
							if (verified === false) {
								var html = '<h3>' + username + '</h3><h3 style="opacity: 0;">.</h3>';
							} else {
								var html = '<h3>' + username + '</h3><h3><a href="http://' + domain + '" target="_blank">' + domain + ' ✅</a></h3>';
							}
						}
						var followed = json.followed;
						var followers = json.followers;
						$('#profile').append('<div class="card showprofile"><div class="profile-header" style="background-image: url(https://henlo.xyz/api/image.php?url=' + header + ');"></div><div class="center"><img class="profile-pic" src="https://henlo.xyz/api/image.php?url=' + pic + '" style="width: 80px; height:80px;"><div class="user-info">' + html + '</div></div><table><tr><td>' + json.followers + '</td><td>' + json.following + '</td></tr><tr><td>Followers</td><td>Following</td></tr></table>');
						if (followed === null) {
							//
						} else if (followed === false) {
							$('#profile .card:last-child').append('<form action="https://henlo.xyz/api/follow.php" method="POST" name="follow"><input type="hidden" name="action" value="add"><input type="hidden" name="token" value="' + token + '"><input type="hidden" name="username" value="' + username + '"><div class="buttons"><button type="submit" ontouchstart=""><h2>😊</h2><p>Follow</p></button></div></form></div>');
						} else {
							$('#profile .card:last-child').append('<form action="https://henlo.xyz/api/follow.php" method="POST" name="follow"><input type="hidden" name="action" value="remove"><input type="hidden" name="token" value="' + token + '"><input type="hidden" name="username" value="' + username + '"><div class="buttons"><button class="danger" type="submit" ontouchstart=""><h2>😔</h2><p>Unfollow</p></button></div></form></div>');
						}
						if (navigator.userAgent.match(/(iPod|iPhone|iPad|Mac)/i)) {
							// iOS
						} else {
							twemoji.size = '72x72';
  							twemoji.parse(document.body);
  						}
			}
			}
		});
	}
function userProfile() {
	$.getJSON("https://henlo.xyz/api/userinfo.php", {"token": token}, function (data) {
		if (data.error === "INVALID_TOKEN" || data.error === "NOT_REGISTERED") {
			localStorage.removeItem('token');
			alert('Your session has expired. You will be redirected to the login page.');
			location.reload(true);
		} else {
			var username = data.username;
			var cutie = jQuery.rand(cuties);
			if (data.pic === false) {
				var pic = 'https://media.giphy.com/media/' + cutie + '/giphy.gif';
			} else {
				var pic = data.pic;
			}
			if (data.header === false) {
				var header = 'https://media0.giphy.com/media/Yqn9tE2E00k4U/giphy.gif';
			} else {
				var header = data.header;
			}
			var domain = data.domain;
			var verified = data.verified;
			if (domain === false) {
				var html = '<h3>' + username + '</h3><h3 style="opacity: 0;">.</h3></a>';
			} else {
				if (verified === false) {
					var html = '<h3>' + username + '</h3><h3 style="opacity: 0;">.</h3>';
				} else {
					var html = '<h3>' + username + '</h3><h3><a href="http://' + domain + '" target="_blank">' + domain + ' ✅</a></h3>';
				}
			}
			if (data.status === null) {
				var status = 'You haven\'t posted yet.';
			} else {
				var status = data.status;
			}
			var nightMode = localStorage.getItem('night-mode');
			if (nightMode === 'true') {
				var text = '<h2>🌝</h2><p>Light</p>';
			} else {
				var text = '<h2>🌚</h2><p>Dark</p>';
			}
			var thehours = new Date().getHours();
			var themessage;
			var morning = 'Good morning';
			var afternoon = 'Good afternoon';
			var evening = 'Good evening';
			if (thehours >= 0 && thehours < 12) {
				themessage = morning; 
			} else if (thehours >= 12 && thehours < 17) {
				themessage = afternoon;
			} else if (thehours >= 17 && thehours < 24) {
				themessage = evening;
			}
			$('main').prepend('<h2>' + themessage + ', ' + username + '</h2>');
			$('#user .grid').prepend('<div class="card" id="profile"><div class="profile-header" style="background-image: url(https://henlo.xyz/api/image.php?url=' + header + ');"></div><div class="center"><img class="profile-pic" src="https://henlo.xyz/api/image.php?url=' + pic + '" style="width: 80px; height: 80px;"><div class="user-info">' + html + '</div></div><table><tr><td>' + data.followers + '</td><td>' + data.following + '</td></tr><tr><td>Followers</td><td>Following</td></tr></table>');
			$('#user .grid .card:first-child').append('<div class="buttons"><button type="button" id="night-mode" ontouchstart="">' + text + '</button><button type="button" id="api-token" ontouchstart=""><h2>🔑</h2><p>API Token</p></button><button type="button" class="danger" id="logout" ontouchstart=""><h2>💣</h2><p>Logout</p></button></div></div></div>');
		}
		if (navigator.userAgent.match(/(iPod|iPhone|iPad|Mac)/i)) {
			// iOS
		} else {
			twemoji.size = '72x72';
  			twemoji.parse(document.body);
  		}
	});
}
function displayPage(name) {
	$.getJSON("https://henlo.xyz/api/page.php", {"name": name}, function (data) {
		var error = data.error;
		if (error !== false) {
			var html = '<h2>Error</h2>';
		} else {
			var html = data.html;
		}
		$('main').html(html);
	});
}
var q = decodeURIComponent(window.location.pathname);
if (q == '/page/tos' || q == '/page/privacy' || q == '/page/commands' || q == '/page/verification') {
	displayPage(q.replace('/page/',''));
} else if (q.match("/user/")) {
	$('main').append('<section id="profile"><h2>Profile</h2><div class="x"><div class="card"><h3><i class="bx bx-loader-circle bx-spin"></i></h3><p>Searching for user...</p></div></div></section>');
	var user = q.replace('/user/','');
	if (user === 'everyone') {
		$('#profile').empty();
		$('#profile').append('<h2>Why am I seeing this instead of a profile?</h2><p>The @everyone account doesn\'t actually exist. This fake account is mentioned by admins to put their status at the top of everybody\'s feeds.</p><h2 class="emoji-fren">😀</h2>');
					if (navigator.userAgent.match(/(iPod|iPhone|iPad|Mac)/i)) {
							// iOS
						} else {
							twemoji.size = '72x72';
  							twemoji.parse(document.body);
  						}
	} else if (user === 'alien_emoji') {
		window.location.replace('https://henlo.xyz/user/👽');
	} else if (user === 'pizza_emoji') {
		window.location.replace('https://henlo.xyz/user/🍕');
	} else if (user === 'unicorn_emoji') {
		window.location.replace('https://henlo.xyz/user/🦄');
	} else {
		showProfile(user);
	}
} else if (token === null) {
	$('main').append('<section id="welcome"><h2 class="emoji-fren">💖</h2><h2>Welcome to Henlo</h2><p>Your developer-friendly, uncensored, ad-free Twitter alternative.</p></section><section><h2>Developer-Friendly</h2><p>This website is powered by the same API that developers have access to.</p><p><i>API Documentation Coming Soon</i></p></section><section><h2>Uncensored</h2><p>Henlo encourages open discussion. As long as you aren\'t sharing anything illegal you won\'t get in trouble.</p></section><section><h2>Ad-Free</h2><p>Henlo doesn\'t make money by placing ads on our site. They just get in the way 😊</p></section><section><h2>A Chronological Feed</h2><p>Henlo\'s feed is programmed to show statuses in order.</p></section><div class="grid"><div class="form-card"><h2>Login</h2><form action="https://henlo.xyz/api/auth.php" method="POST" name="login"><input type="hidden" name="action" value="login"><input type="text" name="username" placeholder="Username"><input type="password" name="password" placeholder="Password"><p><button type="submit" ontouchstart=""><h2>🦄</h2><p>Login</p></button></p></form></div><div class="form-card"><h2>Register</h2><form action="https://henlo.xyz/api/auth.php" method="POST" name="register"><input type="hidden" name="action" value="register"><input type="text" name="username" placeholder="Username"><input type="password" name="password" placeholder="Password"><p><button type="submit" ontouchstart=""><h2>👽</h2><p>Register</p></button></p><p>By registering an account you agree to our <a href="https://henlo.xyz/page/tos" target="_blank">Terms of Service</a> and <a href="https://henlo.xyz/page/privacy" target="_blank">Privacy Policy</a>.</p></form></div></div>');
				if (navigator.userAgent.match(/(iPod|iPhone|iPad|Mac)/i)) {
							// iOS
						} else {
							twemoji.size = '72x72';
  							twemoji.parse(document.body);
  						}
} else {
	$('main').append('<section id="user"><div class="grid"><div class="card" id="composestatus"><form action="https://henlo.xyz/api/status.php" method="POST" name="status"><input type="hidden" name="token" value="' + token + '"><textarea name="status" placeholder="Enter a status, @mention a friend, or enter a command..." id="compose_status"></textarea><div class="left"><button type="submit" ontouchstart=""><h2>🚀</h2><p>Send</p></button> <button type="button" id="commands" ontouchstart=""><h2>💫</h2><p>Commands</p></button></div><div class="right"><div id="characters"></div></div></form></div></div></section>');
	userProfile();
	var feedMode = localStorage.getItem('feed-mode');
	if (feedMode === 'true') {
		$('main').append('<section id="feed"><h2>🌎 Everything <button type="button" id="feed-mode" ontouchstart="">😊 Following</button></h2><div class="card"><h3><i class="bx bx-loader-circle bx-spin"></i></h3><p>Searching for posts...</p></div></section>');
	} else {
		$('main').append('<section id="feed"><h2>😊 Following<button type="button" id="feed-mode" ontouchstart="">🌎 Everything</button></h2><div class="card"><h3><i class="bx bx-loader-circle bx-spin"></i></h3><p>Searching for posts...</p></div></section>');
	}
	feed();
}
$(function () {
	$(document).on('submit','form[name=login], form[name=register]', function() {
		$.post($(this).attr('action'), $(this).serialize(), function (json) {
			if (json.error !== false) {
				alert(json.error);
			} else {
				localStorage.setItem('token', json.token);
				location.reload(true);
			}
		}, 'json');
		return false;
	});
});
var nightMode = localStorage.getItem('night-mode');
$(document).on('click', '#night-mode', function() {
	if (nightMode === null || nightMode === 'null') {
		localStorage.setItem('night-mode', 'true');
	} else {
		localStorage.removeItem('night-mode');
	}
	location.reload(true);
});
var feedMode = localStorage.getItem('feed-mode');
$(document).on('click', '#feed-mode', function() {
	if (feedMode === null || feedMode === 'null') {
		localStorage.setItem('feed-mode', 'true');
	} else {
		localStorage.removeItem('feed-mode');
	}
	location.reload(true);
});
if (nightMode === 'true') {
	$('body').addClass('night');
} else {
	$('body').removeClass('night');
}
$(document).on('click', '#whispers-btn', function() {
	window.location.replace('https://henlo.xyz/page/whispers');
});
$(document).on('click', '#api-token', function() {
	alert('API Token: ' + token);
});
$(document).on('click', '#logout', function() {
	localStorage.removeItem('token');
	location.reload(true);
});
$(document).on('click', '#commands', function() {
	window.location.replace('https://henlo.xyz/page/commands');
});
$(function () {
	$('form[name=status]').submit(function () {
		$.post($(this).attr('action'), $(this).serialize(), function (json) {
			if (json.error === "INVALID_TOKEN" || json.error === "NOT_REGISTERED") {
				localStorage.removeItem('token');
				alert('Your session has expired. You will be redirected to the login page.');
				location.reload(true);
			} else if (json.error != false) {
				alert(json.error);
			} else {
				location.reload(true);
			}
		}, 'json');
		return false;
	});
});
$(function () {
	$(document).on('submit','form[name=like], form[name=follow]', function(event) {
		$.post($(this).attr('action'), $(this).serialize(), function (json) {
			if (json.error === "INVALID_TOKEN" || json.error === "NOT_REGISTERED") {
				localStorage.removeItem('token');
				alert('Your session has expired. You will be redirected to the login page.');
				location.reload(true);
			} else if (json.error != false) {
				alert(json.error);
			} else {
		 		// alert('Success');
		 		$(event.target).html('<div class="center"><button type="button"><h2>✨</h2><p>Success</p></button></center>');
			}
		}, 'json');
		return false;
	});
});
$('#compose_status').keyup(function () {
	var len = $(this).val().length;	
	if (len >= 1000) {
		$('#characters').html('<p>🛑</p>');
	} else if (len >= 900) {
		$('#characters').html('<p style="color: red;">' + len + '</p>');
    } else if (len >= 500) {
		$('#characters').html('<p style="color: orange;">' + len + '</p>');
  	} else {
		$('#characters').html('<p>' + len + '</p>');
	}
});
/*
 * jQuery autoResize (textarea auto-resizer)
 * @copyright James Padolsey http://james.padolsey.com
 * @version 1.04
 */

(function($){
    
    $.fn.autoResize = function(options) {
        
        // Just some abstracted details,
        // to make plugin users happy:
        var settings = $.extend({
            onResize : function(){},
            animate : true,
            animateDuration : 150,
            animateCallback : function(){},
            extraSpace : 20,
            limit: 1000
        }, options);
        
        // Only textarea's auto-resize:
        this.filter('textarea').each(function(){
            
                // Get rid of scrollbars and disable WebKit resizing:
            var textarea = $(this).css({resize:'none','overflow-y':'hidden'}),
            
                // Cache original height, for use later:
                origHeight = textarea.height(),
                
                // Need clone of textarea, hidden off screen:
                clone = (function(){
                    
                    // Properties which may effect space taken up by chracters:
                    var props = ['height','width','lineHeight','textDecoration','letterSpacing'],
                        propOb = {};
                        
                    // Create object of styles to apply:
                    $.each(props, function(i, prop){
                        propOb[prop] = textarea.css(prop);
                    });
                    
                    // Clone the actual textarea removing unique properties
                    // and insert before original textarea:
                    return textarea.clone().removeAttr('id').removeAttr('name').css({
                        position: 'absolute',
                        top: 0,
                        left: -9999
                    }).css(propOb).attr('tabIndex','-1').insertBefore(textarea);
					
                })(),
                lastScrollTop = null,
                updateSize = function() {
					
                    // Prepare the clone:
                    clone.height(0).val($(this).val()).scrollTop(10000);
					
                    // Find the height of text:
                    var scrollTop = Math.max(clone.scrollTop(), origHeight) + settings.extraSpace,
                        toChange = $(this).add(clone);
						
                    // Don't do anything if scrollTip hasen't changed:
                    if (lastScrollTop === scrollTop) { return; }
                    lastScrollTop = scrollTop;
					
                    // Check for limit:
                    if ( scrollTop >= settings.limit ) {
                        $(this).css('overflow-y','');
                        return;
                    }
                    // Fire off callback:
                    settings.onResize.call(this);
					
                    // Either animate or directly apply height:
                    settings.animate && textarea.css('display') === 'block' ?
                        toChange.stop().animate({height:scrollTop}, settings.animateDuration, settings.animateCallback)
                        : toChange.height(scrollTop);
                };
            
            // Bind namespaced handlers to appropriate events:
            textarea
                .unbind('.dynSiz')
                .bind('keyup.dynSiz', updateSize)
                .bind('keydown.dynSiz', updateSize)
                .bind('change.dynSiz', updateSize);
            
        });
        
        // Chain:
        return this;
        
    };
    
    
    
})(jQuery);

$('textarea').autoResize();

(function timeAgo(selector) {

    var templates = {
        prefix: "",
        suffix: "",
        seconds: "just now",
        minute: "about a minute",
        minutes: "%d minutes",
        hour: "about an hour",
        hours: "about %d hours",
        day: "a day",
        days: "%d days",
        month: "about a month",
        months: "%d months",
        year: "about a year",
        years: "%d years"
    };
    var template = function(t, n) {
        return templates[t] && templates[t].replace(/%d/i, Math.abs(Math.round(n)));
    };

    var timer = function(time) {
        if (!time)
            return;
        time = time.replace(/\.\d+/, ""); // remove milliseconds
        time = time.replace(/-/, "/").replace(/-/, "/");
        time = time.replace(/T/, " ").replace(/Z/, " UTC");
        time = time.replace(/([\+\-]\d\d)\:?(\d\d)/, " $1$2"); // -04:00 -> -0400
        time = new Date(time * 1000 || time);

        var now = new Date();
        var seconds = ((now.getTime() - time) * .001) >> 0;
        var minutes = seconds / 60;
        var hours = minutes / 60;
        var days = hours / 24;
        var years = days / 365;

        return templates.prefix + (
                seconds < 45 && template('seconds', seconds) ||
                seconds < 90 && template('minute', 1) ||
                minutes < 45 && template('minutes', minutes) ||
                minutes < 90 && template('hour', 1) ||
                hours < 24 && template('hours', hours) ||
                hours < 42 && template('day', 1) ||
                days < 30 && template('days', days) ||
                days < 45 && template('month', 1) ||
                days < 365 && template('months', days / 30) ||
                years < 1.5 && template('year', 1) ||
                template('years', years)
                ) + templates.suffix;
    };

    var elements = document.getElementsByClassName('timeago');
    for (var i in elements) {
        var $this = elements[i];
        if (typeof $this === 'object') {
            $this.innerHTML = timer($this.getAttribute('title') || $this.getAttribute('datetime'));
        }
    }
    setTimeout(timeAgo, 1000);

})();
