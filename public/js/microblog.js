/*
 Color animation 1.6.0
 http://www.bitstorm.org/jquery/color-animation/
 Copyright 2011, 2013 Edwin Martin
 Released under the MIT and GPL licenses.
*/
'use strict';(function(d){function h(a,b,e){var c="rgb"+(d.support.rgba?"a":"")+"("+parseInt(a[0]+e*(b[0]-a[0]),10)+","+parseInt(a[1]+e*(b[1]-a[1]),10)+","+parseInt(a[2]+e*(b[2]-a[2]),10);d.support.rgba&&(c+=","+(a&&b?parseFloat(a[3]+e*(b[3]-a[3])):1));return c+")"}function f(a){var b;return(b=/#([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/.exec(a))?[parseInt(b[1],16),parseInt(b[2],16),parseInt(b[3],16),1]:(b=/#([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])/.exec(a))?[17*parseInt(b[1],16),17*parseInt(b[2],
16),17*parseInt(b[3],16),1]:(b=/rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(a))?[parseInt(b[1]),parseInt(b[2]),parseInt(b[3]),1]:(b=/rgba\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9\.]*)\s*\)/.exec(a))?[parseInt(b[1],10),parseInt(b[2],10),parseInt(b[3],10),parseFloat(b[4])]:l[a]}d.extend(!0,d,{support:{rgba:function(){var a=d("script:first"),b=a.css("color"),e=!1;if(/^rgba/.test(b))e=!0;else try{e=b!=a.css("color","rgba(0, 0, 0, 0.5)").css("color"),
a.css("color",b)}catch(c){}return e}()}});var k="color backgroundColor borderBottomColor borderLeftColor borderRightColor borderTopColor outlineColor".split(" ");d.each(k,function(a,b){d.Tween.propHooks[b]={get:function(a){return d(a.elem).css(b)},set:function(a){var c=a.elem.style,g=f(d(a.elem).css(b)),m=f(a.end);a.run=function(a){c[b]=h(g,m,a)}}}});d.Tween.propHooks.borderColor={set:function(a){var b=a.elem.style,e=[],c=k.slice(2,6);d.each(c,function(b,c){e[c]=f(d(a.elem).css(c))});var g=f(a.end);
a.run=function(a){d.each(c,function(d,c){b[c]=h(e[c],g,a)})}}};var l={aqua:[0,255,255,1],azure:[240,255,255,1],beige:[245,245,220,1],black:[0,0,0,1],blue:[0,0,255,1],brown:[165,42,42,1],cyan:[0,255,255,1],darkblue:[0,0,139,1],darkcyan:[0,139,139,1],darkgrey:[169,169,169,1],darkgreen:[0,100,0,1],darkkhaki:[189,183,107,1],darkmagenta:[139,0,139,1],darkolivegreen:[85,107,47,1],darkorange:[255,140,0,1],darkorchid:[153,50,204,1],darkred:[139,0,0,1],darksalmon:[233,150,122,1],darkviolet:[148,0,211,1],fuchsia:[255,
0,255,1],gold:[255,215,0,1],green:[0,128,0,1],indigo:[75,0,130,1],khaki:[240,230,140,1],lightblue:[173,216,230,1],lightcyan:[224,255,255,1],lightgreen:[144,238,144,1],lightgrey:[211,211,211,1],lightpink:[255,182,193,1],lightyellow:[255,255,224,1],lime:[0,255,0,1],magenta:[255,0,255,1],maroon:[128,0,0,1],navy:[0,0,128,1],olive:[128,128,0,1],orange:[255,165,0,1],pink:[255,192,203,1],purple:[128,0,128,1],violet:[128,0,128,1],red:[255,0,0,1],silver:[192,192,192,1],white:[255,255,255,1],yellow:[255,255,
0,1],transparent:[255,255,255,0]}})(jQuery);

/*!
 * Lightbox for Bootstrap 3 by @ashleydw
 * https://github.com/ashleydw/lightbox
 *
 * License: https://github.com/ashleydw/lightbox/blob/master/LICENSE
 */
(function(){"use strict";var a,b;a=jQuery,b=function(b,c){var d,e,f,g=this;return this.options=a.extend({title:null,footer:null,remote:null},a.fn.ekkoLightbox.defaults,c||{}),this.$element=a(b),d="",this.modal_id=this.options.modal_id?this.options.modal_id:"ekkoLightbox-"+Math.floor(1e3*Math.random()+1),f='<div class="modal-header"'+(this.options.title||this.options.always_show_close?"":' style="display:none"')+'><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title">'+(this.options.title||"&nbsp;")+"</h4></div>",e='<div class="modal-footer"'+(this.options.footer?"":' style="display:none"')+">"+this.options.footer+"</div>",a(document.body).append('<div id="'+this.modal_id+'" class="ekko-lightbox modal fade" tabindex="-1"><div class="modal-dialog"><div class="modal-content">'+f+'<div class="modal-body"><div class="ekko-lightbox-container"><div></div></div></div>'+e+"</div></div></div>"),this.modal=a("#"+this.modal_id),this.modal_dialog=this.modal.find(".modal-dialog").first(),this.modal_content=this.modal.find(".modal-content").first(),this.modal_body=this.modal.find(".modal-body").first(),this.lightbox_container=this.modal_body.find(".ekko-lightbox-container").first(),this.lightbox_body=this.lightbox_container.find("> div:first-child").first(),this.showLoading(),this.modal_arrows=null,this.border={top:parseFloat(this.modal_dialog.css("border-top-width"))+parseFloat(this.modal_content.css("border-top-width"))+parseFloat(this.modal_body.css("border-top-width")),right:parseFloat(this.modal_dialog.css("border-right-width"))+parseFloat(this.modal_content.css("border-right-width"))+parseFloat(this.modal_body.css("border-right-width")),bottom:parseFloat(this.modal_dialog.css("border-bottom-width"))+parseFloat(this.modal_content.css("border-bottom-width"))+parseFloat(this.modal_body.css("border-bottom-width")),left:parseFloat(this.modal_dialog.css("border-left-width"))+parseFloat(this.modal_content.css("border-left-width"))+parseFloat(this.modal_body.css("border-left-width"))},this.padding={top:parseFloat(this.modal_dialog.css("padding-top"))+parseFloat(this.modal_content.css("padding-top"))+parseFloat(this.modal_body.css("padding-top")),right:parseFloat(this.modal_dialog.css("padding-right"))+parseFloat(this.modal_content.css("padding-right"))+parseFloat(this.modal_body.css("padding-right")),bottom:parseFloat(this.modal_dialog.css("padding-bottom"))+parseFloat(this.modal_content.css("padding-bottom"))+parseFloat(this.modal_body.css("padding-bottom")),left:parseFloat(this.modal_dialog.css("padding-left"))+parseFloat(this.modal_content.css("padding-left"))+parseFloat(this.modal_body.css("padding-left"))},this.modal.on("show.bs.modal",this.options.onShow.bind(this)).on("shown.bs.modal",function(){return g.modal_shown(),g.options.onShown.call(g)}).on("hide.bs.modal",this.options.onHide.bind(this)).on("hidden.bs.modal",function(){return g.gallery&&a(document).off("keydown.ekkoLightbox"),g.modal.remove(),g.options.onHidden.call(g)}).modal("show",c),this.modal},b.prototype={modal_shown:function(){var b,c=this;return this.options.remote?(this.gallery=this.$element.data("gallery"),this.gallery&&("document.body"===this.options.gallery_parent_selector||""===this.options.gallery_parent_selector?this.gallery_items=a(document.body).find('*[data-toggle="lightbox"][data-gallery="'+this.gallery+'"]'):this.gallery_items=this.$element.parents(this.options.gallery_parent_selector).first().find('*[data-toggle="lightbox"][data-gallery="'+this.gallery+'"]'),this.gallery_index=this.gallery_items.index(this.$element),a(document).on("keydown.ekkoLightbox",this.navigate.bind(this)),this.options.directional_arrows&&this.gallery_items.length>1&&(this.lightbox_container.append('<div class="ekko-lightbox-nav-overlay"><a href="#" class="'+this.strip_stops(this.options.left_arrow_class)+'"></a><a href="#" class="'+this.strip_stops(this.options.right_arrow_class)+'"></a></div>'),this.modal_arrows=this.lightbox_container.find("div.ekko-lightbox-nav-overlay").first(),this.lightbox_container.find("a"+this.strip_spaces(this.options.left_arrow_class)).on("click",function(a){return a.preventDefault(),c.navigate_left()}),this.lightbox_container.find("a"+this.strip_spaces(this.options.right_arrow_class)).on("click",function(a){return a.preventDefault(),c.navigate_right()}))),this.options.type?"image"===this.options.type?this.preloadImage(this.options.remote,!0):"youtube"===this.options.type&&(b=this.getYoutubeId(this.options.remote))?this.showYoutubeVideo(b):"vimeo"===this.options.type?this.showVimeoVideo(this.options.remote):"instagram"===this.options.type?this.showInstagramVideo(this.options.remote):"url"===this.options.type?this.loadRemoteContent(this.options.remote):"video"===this.options.type?this.showVideoIframe(this.options.remote):this.error('Could not detect remote target type. Force the type using data-type="image|youtube|vimeo|instagram|url|video"'):this.detectRemoteType(this.options.remote)):this.error("No remote target given")},strip_stops:function(a){return a.replace(/\./g,"")},strip_spaces:function(a){return a.replace(/\s/g,"")},isImage:function(a){return a.match(/(^data:image\/.*,)|(\.(jp(e|g|eg)|gif|png|bmp|webp|svg)((\?|#).*)?$)/i)},isSwf:function(a){return a.match(/\.(swf)((\?|#).*)?$/i)},getYoutubeId:function(a){var b;return b=a.match(/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/),b&&11===b[2].length?b[2]:!1},getVimeoId:function(a){return a.indexOf("vimeo")>0?a:!1},getInstagramId:function(a){return a.indexOf("instagram")>0?a:!1},navigate:function(a){if(a=a||window.event,39===a.keyCode||37===a.keyCode){if(39===a.keyCode)return this.navigate_right();if(37===a.keyCode)return this.navigate_left()}},navigateTo:function(b){var c,d;return 0>b||b>this.gallery_items.length-1?this:(this.showLoading(),this.gallery_index=b,this.$element=a(this.gallery_items.get(this.gallery_index)),this.updateTitleAndFooter(),d=this.$element.attr("data-remote")||this.$element.attr("href"),this.detectRemoteType(d,this.$element.attr("data-type")||!1),this.gallery_index+1<this.gallery_items.length&&(c=a(this.gallery_items.get(this.gallery_index+1),!1),d=c.attr("data-remote")||c.attr("href"),"image"===c.attr("data-type")||this.isImage(d))?this.preloadImage(d,!1):void 0)},navigate_left:function(){return 1!==this.gallery_items.length?(0===this.gallery_index?this.gallery_index=this.gallery_items.length-1:this.gallery_index--,this.options.onNavigate.call(this,"left",this.gallery_index),this.navigateTo(this.gallery_index)):void 0},navigate_right:function(){return 1!==this.gallery_items.length?(this.gallery_index===this.gallery_items.length-1?this.gallery_index=0:this.gallery_index++,this.options.onNavigate.call(this,"right",this.gallery_index),this.navigateTo(this.gallery_index)):void 0},detectRemoteType:function(a,b){var c;return b=b||!1,"image"===b||this.isImage(a)?(this.options.type="image",this.preloadImage(a,!0)):"youtube"===b||(c=this.getYoutubeId(a))?(this.options.type="youtube",this.showYoutubeVideo(c)):"vimeo"===b||(c=this.getVimeoId(a))?(this.options.type="vimeo",this.showVimeoVideo(c)):"instagram"===b||(c=this.getInstagramId(a))?(this.options.type="instagram",this.showInstagramVideo(c)):"video"===b?(this.options.type="video",this.showVideoIframe(c)):(this.options.type="url",this.loadRemoteContent(a))},updateTitleAndFooter:function(){var a,b,c,d;return c=this.modal_content.find(".modal-header"),b=this.modal_content.find(".modal-footer"),d=this.$element.data("title")||"",a=this.$element.data("footer")||"",d||this.options.always_show_close?c.css("display","").find(".modal-title").html(d||"&nbsp;"):c.css("display","none"),a?b.css("display","").html(a):b.css("display","none"),this},showLoading:function(){return this.lightbox_body.html('<div class="modal-loading">'+this.options.loadingMessage+"</div>"),this},showYoutubeVideo:function(a){var b,c;return c=this.checkDimensions(this.$element.data("width")||560),b=c/(560/315),this.showVideoIframe("//www.youtube.com/embed/"+a+"?badge=0&autoplay=1&html5=1",c,b)},showVimeoVideo:function(a){var b,c;return c=this.checkDimensions(this.$element.data("width")||560),b=c/(500/281),this.showVideoIframe(a+"?autoplay=1",c,b)},showInstagramVideo:function(a){var b,c;return c=this.checkDimensions(this.$element.data("width")||612),this.resize(c),b=c+80,this.lightbox_body.html('<iframe width="'+c+'" height="'+b+'" src="'+this.addTrailingSlash(a)+'embed/" frameborder="0" allowfullscreen></iframe>'),this.options.onContentLoaded.call(this),this.modal_arrows?this.modal_arrows.css("display","none"):void 0},showVideoIframe:function(a,b,c){return c=c||b,this.resize(b),this.lightbox_body.html('<div class="embed-responsive embed-responsive-16by9"><iframe width="'+b+'" height="'+c+'" src="'+a+'" frameborder="0" allowfullscreen class="embed-responsive-item"></iframe></div>'),this.options.onContentLoaded.call(this),this.modal_arrows&&this.modal_arrows.css("display","none"),this},loadRemoteContent:function(b){var c,d,e=this;return d=this.$element.data("width")||560,this.resize(d),c=this.$element.data("disableExternalCheck")||!1,c||this.isExternal(b)?(this.lightbox_body.html('<iframe width="'+d+'" height="'+d+'" src="'+b+'" frameborder="0" allowfullscreen></iframe>'),this.options.onContentLoaded.call(this)):this.lightbox_body.load(b,a.proxy(function(){return e.$element.trigger("loaded.bs.modal")})),this.modal_arrows&&this.modal_arrows.css("display","none"),this},isExternal:function(a){var b;return b=a.match(/^([^:\/?#]+:)?(?:\/\/([^\/?#]*))?([^?#]+)?(\?[^#]*)?(#.*)?/),"string"==typeof b[1]&&b[1].length>0&&b[1].toLowerCase()!==location.protocol?!0:"string"==typeof b[2]&&b[2].length>0&&b[2].replace(new RegExp(":("+{"http:":80,"https:":443}[location.protocol]+")?$"),"")!==location.host?!0:!1},error:function(a){return this.lightbox_body.html(a),this},preloadImage:function(b,c){var d,e=this;return d=new Image,(null==c||c===!0)&&(d.onload=function(){var b;return b=a("<img />"),b.attr("src",d.src),b.addClass("img-responsive"),e.lightbox_body.html(b),e.modal_arrows&&e.modal_arrows.css("display","block"),b.load(function(){return e.resize(d.width),e.options.onContentLoaded.call(e)})},d.onerror=function(){return e.error("Failed to load image: "+b)}),d.src=b,d},resize:function(b){var c;return c=b+this.border.left+this.padding.left+this.padding.right+this.border.right,this.modal_dialog.css("width","auto").css("max-width",c),this.lightbox_container.find("a").css("line-height",function(){return a(this).parent().height()+"px"}),this},checkDimensions:function(a){var b,c;return c=a+this.border.left+this.padding.left+this.padding.right+this.border.right,b=document.body.clientWidth,c>b&&(a=this.modal_body.width()),a},close:function(){return this.modal.modal("hide")},addTrailingSlash:function(a){return"/"!==a.substr(-1)&&(a+="/"),a}},a.fn.ekkoLightbox=function(c){return this.each(function(){var d;return d=a(this),c=a.extend({remote:d.attr("data-remote")||d.attr("href"),gallery_parent_selector:d.attr("data-parent"),type:d.attr("data-type")},c,d.data()),new b(this,c),this})},a.fn.ekkoLightbox.defaults={gallery_parent_selector:"document.body",left_arrow_class:".glyphicon .glyphicon-chevron-left",right_arrow_class:".glyphicon .glyphicon-chevron-right",directional_arrows:!0,type:null,always_show_close:!0,loadingMessage:"Loading...",onShow:function(){},onShown:function(){},onHide:function(){},onHidden:function(){},onNavigate:function(){},onContentLoaded:function(){}}}).call(this);
$(function () {
    'use strict';

    $(document).ajaxError(function(event, jqxhr) {
        $('#alert').modal('show');
        var error;

        if (typeof jqxhr.responseJSON.error !== 'undefined') {
            error = jqxhr.responseJSON.error;
        } else {
            error = jqxhr.responseJSON.text;
        }

        $('.modal-body').text(error);
    });

    // zawartosc tresci wpisow
    // dane do tego obiektu zapisywane sa w momencie klikniecia przycisku "Edytuj"
    var entries = {};
    // zawartosc komentarzy
    // dane do tego boiektu zapisywane sa w momencie edycji komentarza. jezeli user zrezygnuje z edycji
    // to przywracamy HTML-a z tego obiektu
    var comments = {};
    var timeoutId;

    var Thumbs =
    {
        click: function () {
            var count = parseInt($(this).data('count'));
            var $this = $(this);

            $this.addClass('loader').text('Proszę czekać...');

            $.post($this.attr('href'), function (data) {
                count = parseInt(data.count);
                $this.data('count', count);

                if (!$this.hasClass('thumbs-on')) {
                    $this.next('.btn-subscribe').click(); // po doceneniu wpisu automatycznie go obserwujemy
                }

                $this.toggleClass('thumbs-on');
            })
                .complete(function () {
                    $this.removeClass('loader');
                    $this.text(count + ' ' + declination(count, ['głos', 'głosy', 'głosów']));

                    // jezeli wpis jest w sekcji "popularne wpisy" to tam tez nalezy oznaczyc, ze
                    // wpis jest "lubiany"
                    $('a[href="' + $this.attr('href') + '"]').not($this).toggleClass('thumbs-on', $this.hasClass('thumbs-on')).text($this.text());
                });

            return false;
        },
        enter: function () {
            var count = parseInt($(this).data('count'));

            if (count > 0) {
                var $this = $(this);

                if (typeof $this.attr('title') === 'undefined') {
                    timeoutId = setTimeout(function() {
                        $.get($this.attr('href'), function(html) {
                            $this.attr('title', html);

                            if (html.length) {
                                var count = html.split("\n").length;

                                $this.attr('title', html.replace(/\n/g, '<br />'))
                                    .data('count', count)
                                    .text(count + ' ' + declination(count, ['głos', 'głosy', 'głosów']))
                                    .tooltip({html: true, trigger: 'hover'})
                                    .tooltip('show');
                            }
                        });

                    }, 500);
                }
            }

            $(this).off('mouseenter');
        },
        leave: function () {
            clearTimeout(timeoutId);
        }
    };

    $('#microblog')
        .on('click', '.btn-reply', function () {
            $(this).parent().next('.microblog-comments').find('input').focus();
        })
        .on('click', '.btn-subscribe', function () {
            var $this = $(this);

            $.post($this.attr('href'), function () {
                $this.toggleClass('subscribe-on');
            });

            return false;
        })
        .on('click', '.btn-thumbs, .btn-sm-thumbs', Thumbs.click)
        .on('mouseenter', '.btn-thumbs, .btn-sm-thumbs', Thumbs.enter)
        .on('mouseleave', '.btn-thumbs, .btn-sm-thumbs', Thumbs.leave)
        .on('click', '.btn-edit', function (e) {
            var $this = $(this);
            var entryText = $('#entry-' + $this.data('id')).find('.microblog-text');

            if (typeof entries[$this.data('id')] === 'undefined') {
                $.get($this.attr('href'), function (html) {
                    entries[$this.data('id')] = entryText.html();
                    entryText.html(html);

                    var $form = initForm($('.microblog-submit', entryText));

                    $form.unbind('submit').submit(function() {
                        var data = $form.serialize();
                        $(':input', $form).attr('disabled', 'disabled');

                        $.post($form.attr('action'), data, function(html) {
                            entryText.html(html);
                            delete entries[$this.data('id')];
                        })
                            .always(function() {
                                $(':input', $form).removeAttr('disabled');
                            });

                        return false;
                    });
                });
            } else {
                entryText.html(entries[$this.data('id')]);
                delete entries[$this.data('id')];
            }

            e.preventDefault();
        })
        .on('click', '.btn-remove', function () {
            var $this = $(this);

            $('#confirm').modal('show').one('click', '.danger', function() {
                $.post($this.attr('href'), function() {
                    $('#entry-' + $this.data('id')).fadeOut(500);
                });

                $('#confirm').modal('hide');
            });

            return false;
        })
        .on('focus', '.comment-submit input', function() {
            if (typeof $(this).data('prompt') === 'undefined') {
                $(this).prompt(promptUrl).data('prompt', 'yes');
            }
        })
        .on('submit', '.comment-submit', function() {
            var $form = $(this);
            var $input = $('input[type="text"]', $form);
            var data = $form.serialize();

            $input.attr('disabled', 'disabled');

            $.post($form.attr('action'), data, function(json) {
                $(json.html).hide().insertBefore($form).fadeIn(800);
                $input.val('');

                if (json.subscribe) {
                    $('#entry-' + $('input[name="parent_id"]', $form).val()).find('.btn-subscribe').addClass('subscribe-on');
                }
            })
            .always(function() {
                $input.removeAttr('disabled');
            });

            return false;
        })
        .on('click', '.btn-sm-edit', function(e) {
            var $this = $(this);
            var commentText = $('#comment-' + $this.data('id')).find('.inline-edit');

            var cancel = function() {
                commentText.html(comments[$this.data('id')]);
                delete comments[$this.data('id')];
            };

            if (typeof comments[$this.data('id')] === 'undefined') {
                $.get($this.attr('href'), function(text) {
                    comments[$this.data('id')] = commentText.html();
                    commentText.html('');

                    var $form = $('<form>');
                    var $input = $('<input>', {'value': text, 'class': 'form-control', 'name': 'text', 'autocomplete': 'off'})
                        .keydown(function(e) {
                            if (e.keyCode === 27) {
                                cancel();
                            }
                        })
                        .appendTo($form);

                    $form.submit(function() {
                        var data = $form.serialize();
                        $input.attr('disabled', 'disabled');

                        $.post($this.attr('href'), data, function(json) {
                            $('#comment-' + $this.data('id')).replaceWith(json.html);
                            delete comments[$this.data('id')];
                        })
                            .always(function() {
                                $input.removeAttr('disabled');
                            });

                        return false;
                    });

                    $form.appendTo(commentText);
                    $input.focus().prompt(promptUrl);
                });
            } else {
                cancel();
            }

            e.preventDefault();
        })
        .on('click', '.btn-sm-remove', function() {
            var $this = $(this);

            $('#confirm').modal('show').one('click', '.danger', function() {
                $.post($this.attr('href'), function() {
                    $('#comment-' + $this.data('id')).fadeOut(500);
                });

                $('#confirm').modal('hide');
            });

            return false;
        })
        .on('click', '.show-all a', function() {
            var $this = $(this);
            $this.text('Proszę czekać...');

            $.get($this.attr('href'), function(html) {
                $this.parent().replaceWith(html);
            });

            return false;
        })
        .on('click', 'a[data-toggle="lightbox"]', function(e) {
            e.preventDefault();
            $(this).ekkoLightbox();
        });

    function initForm($form) {

        var removeThumbnail = function () {
            $(this).parent().parent().remove();
        };

        function add(data) {
            var thumbnail = $('.thumbnail:last', $form);

            $('.spinner', thumbnail).remove();
            $('img', thumbnail).attr('src', data.url);

            $('<div>', {'class': 'btn-flush'}).html('<i class="fa fa-remove fa-2x"></i>').click(removeThumbnail).appendTo(thumbnail);
            $('<input type="hidden" name="thumbnail[]" value="' + data.name + '">').appendTo(thumbnail);
        }

        if (jQuery.fn.pasteImage) {
            $('textarea', $form).pasteImage(pasteUrl, function (textarea, result) {
                    $.get(uploadUrl, function (tmpl) {
                        $('.thumbnails', $form).append(tmpl);
                        add(result);
                    });
                })
                .prompt(promptUrl)
                .fastSubmit()
                .autogrow()
                .focus();
        }

        $form.on('click', '.btn-flush', removeThumbnail)
            .submit(function () {
                var data = $form.serialize();
                $(':input', $form).attr('disabled', 'disabled');

                $.post($form.attr('action'), data, function(html) {
                    $(html).hide().insertAfter('nav.text-center').fadeIn(900);
                    $('textarea', $form).val('').trigger('keydown');
                    $('.thumbnails', $form).html('');
                })
                    .always(function() {
                        $(':input', $form).removeAttr('disabled');
                    });

                return false;
            })
            .on('click', '.btn-cancel', function () {
                var id = parseInt($(this).data('id'));
                $('#entry-' + id).find('.microblog-text').html(entries[id]);

                delete entries[id];
                return false;
            })
            .delegate('#btn-upload', 'click', function () {
                $('.input-file', $form).click();
            })
            .delegate('.input-file', 'change', function () {
                var file = this.files[0];

                if (file.type !== 'image/png' && file.type !== 'image/jpg' && file.type !== 'image/gif' && file.type !== 'image/jpeg') {
                    $('#alert').modal('show');
                    $('.modal-body').text('Format pliku jest nieprawidłowy. Załącznik musi być zdjęciem JPG, PNG lub GIF');
                }
                else {
                    var formData = new FormData($form[0]);

                    $.get(uploadUrl, function(tmpl) {
                        $('.thumbnails', $form).append(tmpl);

                        $.ajax({
                            url: uploadUrl,
                            type: 'POST',
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false,
                            success: function (data) {
                                add(data);
                            },
                            error: function (err) {
                                $('#alert').modal('show');

                                if (typeof err.responseJSON !== 'undefined') {
                                    $('.modal-body').text(err.responseJSON.photo[0]);
                                }

                                $('.thumbnail:last', $form).remove();
                            }
                        }, 'json');
                    });
                }
            });

        return $form;
    }

    initForm($('.microblog-submit'));

    if ('onhashchange' in window) {
        var onHashChange = function () {
            var hash = window.location.hash;

            if (hash.substring(1, 6) === 'entry' || hash.substring(1, 8) === 'comment') {
                var object = $(hash);
                var panel = object.find('.panel');

                if (panel.length) {
                    object = panel;
                }

                object.css('background-color', '#FFDCA5');
                $('#container-fluid').one('mousemove', function () {
                    object.animate({backgroundColor: '#FFF'}, 1500);
                });
            }
        };

        window.onhashchange = onHashChange;
        onHashChange();
    }
});
(function ($) {
    'use strict';

    $.fn.wikiEditor = function () {
        return this.each(function () {
            var textarea = $(this);
            var toolbar = $('#wiki-toolbar');

            $('.btn-group button', toolbar).click(function() {
                textarea.insertAtCaret($(this).data('open').replace(/<br>/g, "\n"), $(this).data('close').replace(/<br>/g, "\n"), ' ');
            });

            //$(textarea).bind($.browser.opera ? 'keypress' : 'keydown', function(e)
            $(textarea).bind('keydown', function (e) {
                if ((e.which === 9 || e.keyCode === 9) && e.shiftKey) {
                    textarea.insertAtCaret("\t", '', "");

                    return false;
                }
            });
        });
    };

    $.fn.extend({
        insertAtCaret: function (openWith, closeWith, value) {
            var element = this[0];

            if (document.selection) {
                element.focus();
                var sel = document.selection.createRange();
                sel.text = openWith + (sel.text.length > 0 ? sel.text : value) + closeWith;

                element.focus();
            }
            else if (element.selectionStart || element.selectionStart == '0') {
                var startPos = element.selectionStart;
                var endPos = element.selectionEnd;
                var scrollTop = element.scrollTop;

                if (startPos !== endPos) {
                    value = openWith + element.value.substring(startPos, endPos) + closeWith;
                }
                else {
                    value = openWith + value + closeWith;
                }

                element.value = element.value.substring(0, startPos) + value + element.value.substring(endPos, element.value.length);

                element.focus();
                element.selectionStart = startPos + value.length;
                element.selectionEnd = startPos + value.length;
                element.scrollTop = scrollTop;
            }
            else {
                element.value += (openWith + value + closeWith);
                element.focus();
            }
        }
    });
})(jQuery);
//# sourceMappingURL=microblog.js.map