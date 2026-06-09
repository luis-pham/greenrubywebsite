/*
Shameless port of a shameless port
@defunkt => @janl => @aq
 
See http://github.com/defunkt/mustache for more info.
http://mustache.github.io/mustache.5.html
*/
 
;(function($) {

  $.mustache = function (template, view, partials) {
    return Mustache.render(template, view, partials);
  };

  $.fn.mustache = function (view, partials) {
    return $(this).map(function (i, elm) {
      var template = $.trim($(elm).html());
      var output = $.mustache(template, view, partials);
      return $(output).get();
    });
  };

})(jQuery);