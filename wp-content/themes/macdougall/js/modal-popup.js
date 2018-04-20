jQuery(document).ready(function($) {
  // Close modal 
  $('.close-modal').click(function() {
    $('.modal').toggleClass('show');
  });

  // Detect windows width function
  var $window = $(window);

  function checkWidth() {
    var windowsize = $window.width();
    if (windowsize > 767) {
      // if the window is greater than 767px wide then do below. we don't want the modal to show on mobile devices and instead the link will be followed.

      $(".modal-link").click(function(e) {
        var modalContent = $("#modal-content");
        var post_link = $('.show-in-modal').html(); // get content to show in modal
        //var post_link = $(this).attr("href"); // this can be used in WordPress and it will pull the content of the page in the href
        
        e.preventDefault(); // prevent link from being followed
        
        $('.modal').addClass('show', 1000, "easeOutSine"); // show class to display the previously hidden modal
        modalContent.html("loading..."); // display loading animation or in this case static content
        modalContent.html(post_link); // for dynamic content, change this to use the load() function instead of html() -- like this: modalContent.load(post_link + ' #modal-ready')
        $("html, body").animate({ // if you're below the fold this will animate and scroll to the modal
          scrollTop: 0
        }, "slow");
        return false;
      });
    }
  };

  checkWidth(); // excute function to check width on load
  $(window).resize(checkWidth); // execute function to check width on resize
});