(function(a){a(document).ready(function(){a("#post-body-content > .grid a").on("click.achievements",function(b){b.preventDefault();a("#post-body-content > .current").removeClass("current");a("#post-body-content > .detail").addClass("current")});a("#dpa-toolbar-slider").on("change.achievements",function(b){var c=7.72*this.value*10;a(".grid .plugin").each(function(b,d){a(d).css("width",c+"px")})});a("#dpa-toolbar-wrapper a").on("click.achievements",function(b){b.preventDefault();var c=a(this),d=c.prop("class");if(c.hasClass("current"))return;"grid"===d?a(".dpa-toolbar-slider").addClass("current"):a(".dpa-toolbar-slider").removeClass("current");c.parent().parent().find("a").removeClass("current");c.addClass("current");a("#post-body-content > div").removeClass("current");a("#post-body-content > div."+d).addClass("current")})})})(jQuery);