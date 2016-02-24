$('.logo-btn').popover();
$.getJSON('/weather', function (data) {
  var $weather = $('#weather');
  $('.icon', $weather).attr('src', 'http://openweathermap.org/img/w/' + data.weather[0].icon + '.png')
  $('.temp', $weather).html('' + data.main.temp + '&deg;C' + ' ' + data.weather[0].description);
});
var $doc = $(document), $win = $(window);
var $head = $('[data-headroom]');
var pinnedClass = 'pinned';
$doc.on('mousewheel', function (event) {
  if ($doc.height() > $win.height()) {
    var evt = event.originalEvent;
    if (!$head.hasClass(pinnedClass)) {
      if (evt.deltaY > 0) {
        $head.addClass(pinnedClass);
        $head.data('pinned-ts', +new Date());
        $doc.scrollTop(0);
        event.preventDefault();
      }
    } else {
      if (evt.deltaY < 0) {
        if ($doc.scrollTop() + evt.deltaY <= 0) {
          $head.removeClass(pinnedClass);
          $doc.scrollTop(0);
          event.preventDefault();
        }
      } else {
        var lastAttampt = parseInt($head.data('pinned-ts')) || 0;
        if (new Date() - lastAttampt < 300) {
          event.preventDefault();
        }
      }
    }
  }
});
