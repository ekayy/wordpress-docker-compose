function debounced(delay, fn) {
  let timerId;
  return function(...args) {
    if (timerId) {
      clearTimeout(timerId);
    }
    timerId = setTimeout(() => {
      fn(...args);
      timerId = null;
    }, delay);
  };
}

($ => {
  const $window = $(window);
  const $page = $('#page');

  // Back to top link at end of single article
  const scrollToTop = () => {
    const scroll = $('.nav-back a');

    scroll.on('click', e => {
      e.preventDefault();
      $('html, body').animate({ scrollTop: 0 }, 'slow');
    });
  };

  // Share dropdown
  const shareLinks = {
    shareURL: window.location.href,

    openFacebook() {
      let title = $('title').text();
      let fbURL = `http://www.facebook.com/sharer.php?u=${encodeURIComponent(
        this.shareURL
      )}`;
      window.open(
        fbURL,
        title,
        'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600'
      );
    },

    openGoogle() {
      window.open(
        `https://plus.google.com/share?url=${encodeURIComponent(
          this.shareURL
        )}`,
        '',
        'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600'
      );
    },

    openTwitter() {
      let title = $('title').text();
      let twitterURL = `https://twitter.com/intent/tweet?text=${encodeURIComponent(
        title
      )} ${encodeURIComponent(this.shareURL)}`;
      window.open(twitterURL, '', 'width=600, height=355');
    },

    openTumblr() {
      let title = $('title').text();
      window.open(
        `http://www.tumblr.com/share?v=3&u=${encodeURIComponent(
          this.shareURL
        )}&t=${encodeURIComponent(title)}`,
        '',
        'left=20,top=20,width=600,height=700,toolbar=0,resizable=1'
      );
    }
  };

  const initShare = () => {
    const shareAnchor = $(
      '.subheader--share .sub-menu .menu-item:not(:last-child) a'
    );
    shareAnchor.on('click', function(e) {
      e.preventDefault();
      switch ($(this).data('share')) {
        case 'facebook':
          shareLinks.openFacebook();
          break;
        case 'twitter':
          shareLinks.openTwitter();
        case 'google':
          shareLinks.openGoogle();
        case 'tumblr':
          shareLinks.openTumblr();
        // case 'pinterest':
        //   shareLinks.openPinterest();
        default:
          break;
      }
    });
  };

  const openLinksInNewTab = () => {
    $('a')
      .filter('[href^="http"], [href^="//"]')
      .not('[href*="' + window.location.host + '"]')
      .attr('rel', 'noopener noreferrer')
      .attr('target', '_blank');
  };

  // on document ready
  $(() => {
    // initialize functions

    $('body, html').animate({ scrollTop: 0 }, 0);

    if ($('body').hasClass('touch')) {
      //
    } else {
      //
    }
  });
})(jQuery);
