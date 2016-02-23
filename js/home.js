/*global jQuery */
/*jslint devel:true, browser: true, indent: 2 */
(function ($) {
  'use strict';

  $.fn.hoverClass = function (cls) {
    return $(this).each(function (i, e) {
      $(e).hover(function () {
        $(this).addClass(cls);
      }, function () {
        $(this).removeClass(cls);
      });
    });
  };

  $(function () {
    //    $('nav.site .item a').hoverClass('animated swing');
    var $slides = $('.slides');
    $('nav.site .item.site a').click(function (event) {
      event.preventDefault();
      var $this = $(this), $key = $($this.attr('href')), $pre = $('.on'), $pkey = $($pre.attr('href'));
      if (!$pre.length) {
        $('.main').removeClass('start');
        setTimeout(function () {
          $this.addClass('on');
          $key.show(function () {
            $slides.slideDown();
          });
        }, 1000);
      } else {
        if (!$this.is($pre)) {
          $pre.removeClass('on');
          $this.addClass('on');
          $pkey.slideUp();
          $key.slideDown();
        } else {
          $this.removeClass('on');
          $slides.slideUp(function () {
            $key.hide();
            $('.main').addClass('start');
          });
        }
      }
    });

    /**
     * Update the archive part.
     */
    $('#blog .title').click(function (event) {
      event.preventDefault();
      var $this = $(this);
      $this.siblings('.content').slideToggle();
    });

    var drafts;

    /**
     * Update the list of drafts.
     */
    function updateDraftList() {
      var $drafts = $('#tell .drafts-list');
      if (drafts && drafts.forEach) {
        $drafts.empty();
        drafts.forEach(function (e) {
          $drafts.append('<li>《' + e.title + '》于' + e.time + '</li>');
        });
      }
    }

    /**
     * Set up local archives.
     */
    try {
      drafts = JSON.parse(localStorage.drafts);
      updateDraftList();
    } catch (e) {}

    $('#tell form').submit(function (event) {
      event.preventDefault();
      var article = {
        title: $('#tell-title').val(),
        content: $('#tell-content').val(),
        time: new Date().toLocaleString()
      }, $this = $(this);
      if (drafts) {
        drafts.push(article);
        while (drafts.length > 10) {
          drafts.shift(1);
        }
      } else {
        drafts = [article];
      }
      localStorage.drafts = JSON.stringify(drafts);
      updateDraftList();

      $.post($this.attr('action'), $this.serialize()).done(function (data) {
        if (data && data.status === 0) {
          alert('谢谢您的故事，我们会认真审核的！');
        } else {
          console.log(data);
          alert("服务器出了一些小错误呢 >_<");
        }
      }).fail(function () {
        alert("网络错误！");
      });
    });
  });
}(jQuery));
