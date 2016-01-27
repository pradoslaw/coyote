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

/* ========================================================================
 * Bootstrap: modal.js v3.3.5
 * http://getbootstrap.com/javascript/#modals
 * ========================================================================
 * Copyright 2011-2015 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */


+function ($) {
  'use strict';

  // MODAL CLASS DEFINITION
  // ======================

  var Modal = function (element, options) {
    this.options             = options
    this.$body               = $(document.body)
    this.$element            = $(element)
    this.$dialog             = this.$element.find('.modal-dialog')
    this.$backdrop           = null
    this.isShown             = null
    this.originalBodyPad     = null
    this.scrollbarWidth      = 0
    this.ignoreBackdropClick = false

    if (this.options.remote) {
      this.$element
        .find('.modal-content')
        .load(this.options.remote, $.proxy(function () {
          this.$element.trigger('loaded.bs.modal')
        }, this))
    }
  }

  Modal.VERSION  = '3.3.5'

  Modal.TRANSITION_DURATION = 300
  Modal.BACKDROP_TRANSITION_DURATION = 150

  Modal.DEFAULTS = {
    backdrop: true,
    keyboard: true,
    show: true
  }

  Modal.prototype.toggle = function (_relatedTarget) {
    return this.isShown ? this.hide() : this.show(_relatedTarget)
  }

  Modal.prototype.show = function (_relatedTarget) {
    var that = this
    var e    = $.Event('show.bs.modal', { relatedTarget: _relatedTarget })

    this.$element.trigger(e)

    if (this.isShown || e.isDefaultPrevented()) return

    this.isShown = true

    this.checkScrollbar()
    this.setScrollbar()
    this.$body.addClass('modal-open')

    this.escape()
    this.resize()

    this.$element.on('click.dismiss.bs.modal', '[data-dismiss="modal"]', $.proxy(this.hide, this))

    this.$dialog.on('mousedown.dismiss.bs.modal', function () {
      that.$element.one('mouseup.dismiss.bs.modal', function (e) {
        if ($(e.target).is(that.$element)) that.ignoreBackdropClick = true
      })
    })

    this.backdrop(function () {
      var transition = $.support.transition && that.$element.hasClass('fade')

      if (!that.$element.parent().length) {
        that.$element.appendTo(that.$body) // don't move modals dom position
      }

      that.$element
        .show()
        .scrollTop(0)

      that.adjustDialog()

      if (transition) {
        that.$element[0].offsetWidth // force reflow
      }

      that.$element.addClass('in')

      that.enforceFocus()

      var e = $.Event('shown.bs.modal', { relatedTarget: _relatedTarget })

      transition ?
        that.$dialog // wait for modal to slide in
          .one('bsTransitionEnd', function () {
            that.$element.trigger('focus').trigger(e)
          })
          .emulateTransitionEnd(Modal.TRANSITION_DURATION) :
        that.$element.trigger('focus').trigger(e)
    })
  }

  Modal.prototype.hide = function (e) {
    if (e) e.preventDefault()

    e = $.Event('hide.bs.modal')

    this.$element.trigger(e)

    if (!this.isShown || e.isDefaultPrevented()) return

    this.isShown = false

    this.escape()
    this.resize()

    $(document).off('focusin.bs.modal')

    this.$element
      .removeClass('in')
      .off('click.dismiss.bs.modal')
      .off('mouseup.dismiss.bs.modal')

    this.$dialog.off('mousedown.dismiss.bs.modal')

    $.support.transition && this.$element.hasClass('fade') ?
      this.$element
        .one('bsTransitionEnd', $.proxy(this.hideModal, this))
        .emulateTransitionEnd(Modal.TRANSITION_DURATION) :
      this.hideModal()
  }

  Modal.prototype.enforceFocus = function () {
    $(document)
      .off('focusin.bs.modal') // guard against infinite focus loop
      .on('focusin.bs.modal', $.proxy(function (e) {
        if (this.$element[0] !== e.target && !this.$element.has(e.target).length) {
          this.$element.trigger('focus')
        }
      }, this))
  }

  Modal.prototype.escape = function () {
    if (this.isShown && this.options.keyboard) {
      this.$element.on('keydown.dismiss.bs.modal', $.proxy(function (e) {
        e.which == 27 && this.hide()
      }, this))
    } else if (!this.isShown) {
      this.$element.off('keydown.dismiss.bs.modal')
    }
  }

  Modal.prototype.resize = function () {
    if (this.isShown) {
      $(window).on('resize.bs.modal', $.proxy(this.handleUpdate, this))
    } else {
      $(window).off('resize.bs.modal')
    }
  }

  Modal.prototype.hideModal = function () {
    var that = this
    this.$element.hide()
    this.backdrop(function () {
      that.$body.removeClass('modal-open')
      that.resetAdjustments()
      that.resetScrollbar()
      that.$element.trigger('hidden.bs.modal')
    })
  }

  Modal.prototype.removeBackdrop = function () {
    this.$backdrop && this.$backdrop.remove()
    this.$backdrop = null
  }

  Modal.prototype.backdrop = function (callback) {
    var that = this
    var animate = this.$element.hasClass('fade') ? 'fade' : ''

    if (this.isShown && this.options.backdrop) {
      var doAnimate = $.support.transition && animate

      this.$backdrop = $(document.createElement('div'))
        .addClass('modal-backdrop ' + animate)
        .appendTo(this.$body)

      this.$element.on('click.dismiss.bs.modal', $.proxy(function (e) {
        if (this.ignoreBackdropClick) {
          this.ignoreBackdropClick = false
          return
        }
        if (e.target !== e.currentTarget) return
        this.options.backdrop == 'static'
          ? this.$element[0].focus()
          : this.hide()
      }, this))

      if (doAnimate) this.$backdrop[0].offsetWidth // force reflow

      this.$backdrop.addClass('in')

      if (!callback) return

      doAnimate ?
        this.$backdrop
          .one('bsTransitionEnd', callback)
          .emulateTransitionEnd(Modal.BACKDROP_TRANSITION_DURATION) :
        callback()

    } else if (!this.isShown && this.$backdrop) {
      this.$backdrop.removeClass('in')

      var callbackRemove = function () {
        that.removeBackdrop()
        callback && callback()
      }
      $.support.transition && this.$element.hasClass('fade') ?
        this.$backdrop
          .one('bsTransitionEnd', callbackRemove)
          .emulateTransitionEnd(Modal.BACKDROP_TRANSITION_DURATION) :
        callbackRemove()

    } else if (callback) {
      callback()
    }
  }

  // these following methods are used to handle overflowing modals

  Modal.prototype.handleUpdate = function () {
    this.adjustDialog()
  }

  Modal.prototype.adjustDialog = function () {
    var modalIsOverflowing = this.$element[0].scrollHeight > document.documentElement.clientHeight

    this.$element.css({
      paddingLeft:  !this.bodyIsOverflowing && modalIsOverflowing ? this.scrollbarWidth : '',
      paddingRight: this.bodyIsOverflowing && !modalIsOverflowing ? this.scrollbarWidth : ''
    })
  }

  Modal.prototype.resetAdjustments = function () {
    this.$element.css({
      paddingLeft: '',
      paddingRight: ''
    })
  }

  Modal.prototype.checkScrollbar = function () {
    var fullWindowWidth = window.innerWidth
    if (!fullWindowWidth) { // workaround for missing window.innerWidth in IE8
      var documentElementRect = document.documentElement.getBoundingClientRect()
      fullWindowWidth = documentElementRect.right - Math.abs(documentElementRect.left)
    }
    this.bodyIsOverflowing = document.body.clientWidth < fullWindowWidth
    this.scrollbarWidth = this.measureScrollbar()
  }

  Modal.prototype.setScrollbar = function () {
    var bodyPad = parseInt((this.$body.css('padding-right') || 0), 10)
    this.originalBodyPad = document.body.style.paddingRight || ''
    if (this.bodyIsOverflowing) this.$body.css('padding-right', bodyPad + this.scrollbarWidth)
  }

  Modal.prototype.resetScrollbar = function () {
    this.$body.css('padding-right', this.originalBodyPad)
  }

  Modal.prototype.measureScrollbar = function () { // thx walsh
    var scrollDiv = document.createElement('div')
    scrollDiv.className = 'modal-scrollbar-measure'
    this.$body.append(scrollDiv)
    var scrollbarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth
    this.$body[0].removeChild(scrollDiv)
    return scrollbarWidth
  }


  // MODAL PLUGIN DEFINITION
  // =======================

  function Plugin(option, _relatedTarget) {
    return this.each(function () {
      var $this   = $(this)
      var data    = $this.data('bs.modal')
      var options = $.extend({}, Modal.DEFAULTS, $this.data(), typeof option == 'object' && option)

      if (!data) $this.data('bs.modal', (data = new Modal(this, options)))
      if (typeof option == 'string') data[option](_relatedTarget)
      else if (options.show) data.show(_relatedTarget)
    })
  }

  var old = $.fn.modal

  $.fn.modal             = Plugin
  $.fn.modal.Constructor = Modal


  // MODAL NO CONFLICT
  // =================

  $.fn.modal.noConflict = function () {
    $.fn.modal = old
    return this
  }


  // MODAL DATA-API
  // ==============

  $(document).on('click.bs.modal.data-api', '[data-toggle="modal"]', function (e) {
    var $this   = $(this)
    var href    = $this.attr('href')
    var $target = $($this.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, ''))) // strip for ie7
    var option  = $target.data('bs.modal') ? 'toggle' : $.extend({ remote: !/#/.test(href) && href }, $target.data(), $this.data())

    if ($this.is('a')) e.preventDefault()

    $target.one('show.bs.modal', function (showEvent) {
      if (showEvent.isDefaultPrevented()) return // only register focus restorer if modal will actually get shown
      $target.one('hidden.bs.modal', function () {
        $this.is(':visible') && $this.trigger('focus')
      })
    })
    Plugin.call($target, option, this)
  })

}(jQuery);

/* ========================================================================
 * Bootstrap: tab.js v3.3.5
 * http://getbootstrap.com/javascript/#tabs
 * ========================================================================
 * Copyright 2011-2015 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */


+function ($) {
  'use strict';

  // TAB CLASS DEFINITION
  // ====================

  var Tab = function (element) {
    // jscs:disable requireDollarBeforejQueryAssignment
    this.element = $(element)
    // jscs:enable requireDollarBeforejQueryAssignment
  }

  Tab.VERSION = '3.3.5'

  Tab.TRANSITION_DURATION = 150

  Tab.prototype.show = function () {
    var $this    = this.element
    var $ul      = $this.closest('ul:not(.dropdown-menu)')
    var selector = $this.data('target')

    if (!selector) {
      selector = $this.attr('href')
      selector = selector && selector.replace(/.*(?=#[^\s]*$)/, '') // strip for ie7
    }

    if ($this.parent('li').hasClass('active')) return

    var $previous = $ul.find('.active:last a')
    var hideEvent = $.Event('hide.bs.tab', {
      relatedTarget: $this[0]
    })
    var showEvent = $.Event('show.bs.tab', {
      relatedTarget: $previous[0]
    })

    $previous.trigger(hideEvent)
    $this.trigger(showEvent)

    if (showEvent.isDefaultPrevented() || hideEvent.isDefaultPrevented()) return

    var $target = $(selector)

    this.activate($this.closest('li'), $ul)
    this.activate($target, $target.parent(), function () {
      $previous.trigger({
        type: 'hidden.bs.tab',
        relatedTarget: $this[0]
      })
      $this.trigger({
        type: 'shown.bs.tab',
        relatedTarget: $previous[0]
      })
    })
  }

  Tab.prototype.activate = function (element, container, callback) {
    var $active    = container.find('> .active')
    var transition = callback
      && $.support.transition
      && ($active.length && $active.hasClass('fade') || !!container.find('> .fade').length)

    function next() {
      $active
        .removeClass('active')
        .find('> .dropdown-menu > .active')
          .removeClass('active')
        .end()
        .find('[data-toggle="tab"]')
          .attr('aria-expanded', false)

      element
        .addClass('active')
        .find('[data-toggle="tab"]')
          .attr('aria-expanded', true)

      if (transition) {
        element[0].offsetWidth // reflow for transition
        element.addClass('in')
      } else {
        element.removeClass('fade')
      }

      if (element.parent('.dropdown-menu').length) {
        element
          .closest('li.dropdown')
            .addClass('active')
          .end()
          .find('[data-toggle="tab"]')
            .attr('aria-expanded', true)
      }

      callback && callback()
    }

    $active.length && transition ?
      $active
        .one('bsTransitionEnd', next)
        .emulateTransitionEnd(Tab.TRANSITION_DURATION) :
      next()

    $active.removeClass('in')
  }


  // TAB PLUGIN DEFINITION
  // =====================

  function Plugin(option) {
    return this.each(function () {
      var $this = $(this)
      var data  = $this.data('bs.tab')

      if (!data) $this.data('bs.tab', (data = new Tab(this)))
      if (typeof option == 'string') data[option]()
    })
  }

  var old = $.fn.tab

  $.fn.tab             = Plugin
  $.fn.tab.Constructor = Tab


  // TAB NO CONFLICT
  // ===============

  $.fn.tab.noConflict = function () {
    $.fn.tab = old
    return this
  }


  // TAB DATA-API
  // ============

  var clickHandler = function (e) {
    e.preventDefault()
    Plugin.call($(this), 'show')
  }

  $(document)
    .on('click.bs.tab.data-api', '[data-toggle="tab"]', clickHandler)
    .on('click.bs.tab.data-api', '[data-toggle="pill"]', clickHandler)

}(jQuery);

/* ========================================================================
 * Bootstrap: popover.js v3.3.5
 * http://getbootstrap.com/javascript/#popovers
 * ========================================================================
 * Copyright 2011-2015 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */


+function ($) {
  'use strict';

  // POPOVER PUBLIC CLASS DEFINITION
  // ===============================

  var Popover = function (element, options) {
    this.init('popover', element, options)
  }

  if (!$.fn.tooltip) throw new Error('Popover requires tooltip.js')

  Popover.VERSION  = '3.3.5'

  Popover.DEFAULTS = $.extend({}, $.fn.tooltip.Constructor.DEFAULTS, {
    placement: 'right',
    trigger: 'click',
    content: '',
    template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
  })


  // NOTE: POPOVER EXTENDS tooltip.js
  // ================================

  Popover.prototype = $.extend({}, $.fn.tooltip.Constructor.prototype)

  Popover.prototype.constructor = Popover

  Popover.prototype.getDefaults = function () {
    return Popover.DEFAULTS
  }

  Popover.prototype.setContent = function () {
    var $tip    = this.tip()
    var title   = this.getTitle()
    var content = this.getContent()

    $tip.find('.popover-title')[this.options.html ? 'html' : 'text'](title)
    $tip.find('.popover-content').children().detach().end()[ // we use append for html objects to maintain js events
      this.options.html ? (typeof content == 'string' ? 'html' : 'append') : 'text'
    ](content)

    $tip.removeClass('fade top bottom left right in')

    // IE8 doesn't accept hiding via the `:empty` pseudo selector, we have to do
    // this manually by checking the contents.
    if (!$tip.find('.popover-title').html()) $tip.find('.popover-title').hide()
  }

  Popover.prototype.hasContent = function () {
    return this.getTitle() || this.getContent()
  }

  Popover.prototype.getContent = function () {
    var $e = this.$element
    var o  = this.options

    return $e.attr('data-content')
      || (typeof o.content == 'function' ?
            o.content.call($e[0]) :
            o.content)
  }

  Popover.prototype.arrow = function () {
    return (this.$arrow = this.$arrow || this.tip().find('.arrow'))
  }


  // POPOVER PLUGIN DEFINITION
  // =========================

  function Plugin(option) {
    return this.each(function () {
      var $this   = $(this)
      var data    = $this.data('bs.popover')
      var options = typeof option == 'object' && option

      if (!data && /destroy|hide/.test(option)) return
      if (!data) $this.data('bs.popover', (data = new Popover(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  var old = $.fn.popover

  $.fn.popover             = Plugin
  $.fn.popover.Constructor = Popover


  // POPOVER NO CONFLICT
  // ===================

  $.fn.popover.noConflict = function () {
    $.fn.popover = old
    return this
  }

}(jQuery);

var SCREEN_MD = 1024;

$(function () {
    'use strict';

    function toggleSidebar(flag) {
        $('#btn-toggle-sidebar').toggleClass('sidebar-hidden', !flag);
        $('#sidebar').toggle(flag);
        $('#index').toggleClass('sidebar', flag).children('.btn-watch-xs, .btn-atom-xs, .btn-mark-read-xs').toggleClass('show', !flag);
    }

    if ($('#sidebar').is(':hidden')) {
        $('#index').children('.btn-watch-xs, .btn-atom-xs, .btn-mark-read-xs').addClass('show');
    }

    $(document).click(function (e) {
        var container = $('#sidebar, #btn-toggle-sidebar');

        if ($('#sidebar').css('position') === 'absolute' && !container.is(e.target) && container.has(e.target).length == 0) {
            $('#sidebar').hide();
        }
    });

    if ($(window).width() <= SCREEN_MD) {
        $('#btn-toggle-sidebar').addClass('sidebar-hidden');
    }

    $('#btn-toggle-sidebar').click(function () {
        if ($(window).width() <= SCREEN_MD) {
            $('#sidebar').toggle();

            var handler = function () {
                if ($(window).width() > SCREEN_MD) {
                    if ($('#index').hasClass('sidebar')) {
                        toggleSidebar(true);
                        $(window).unbind('resize', handler);
                    }
                }
            };

            $(window).unbind('resize', handler).bind('resize', handler);
        }
        else {
            var flag = $('#index').hasClass('sidebar');
            toggleSidebar(!flag);

            $.ajax({
                type: 'POST',
                url: baseUrl + '/User/Settings/Ajax',
                data: {'forum_sidebar': !flag},
                dataType: 'html',
                crossDomain: true,
                xhrFields: {
                    withCredentials: true
                }
            });
        }
    });

    /**
     * Show post preview
     */
    $('a[href="#preview"]').click(function(e) {
        $('#preview').find('.post-content').html('<i class="fa fa-spinner fa-spin fa-2x"></i>');

        $.post($(this).data('url'), {'text': $('#submit-form').find('textarea[name="text"]').val()}, function(html) {
            $('#preview').find('.post-content').html(html);
        });
    });

    /**
     * Collapse forum category
     */
    $('.toggle[data-toggle="collapse"]').click(function() {
        $.post($(this).data('ajax'), {flag: +$(this).hasClass('in')});
        $(this).toggleClass('in');
    });

    /**
     * Change limit of posts/topics shown on one page
     */
    $('select[name="perPage"]').change(function() {
        window.location.href = $(this).data('url') + '?perPage=' + $(this).val();
    });

    /**
     * Show "flag to report" page
     */
    $('.btn-report').click(function() {
        var metadata = {'post_id': $(this).data('post-id')};

        $.get(baseUrl + '/Flag', {url: $(this).data('url'), metadata: JSON.stringify(metadata)}, function(html) {
            $(html).appendTo('body');

            $('#flag').find('.modal').modal('show');
        });
    });

    function toPost(url) {
        var form = $('<form>', {'method': 'POST', 'action': url});
        form.append('<input type="hidden" name="_token" value="' + $('meta[name="csrf-token"]').attr('content') + '">');

        return form;
    }

    function error(text) {
        $('#alert').modal('show');
        $('#alert').find('.modal-body').text(text);
    }

    /**
     * Restore deleted post
     */
    $('.btn-res').click(function() {
        toPost($(this).attr('href')).submit();

        return false;
    });

    /**
     * Subscribe/unsubscribe topic (sidebar option)
     */
    $('.btn-watch a').click(function() {
        var $this = $(this);

        $.post($this.attr('href'), function(count) {
            $this.parent().toggleClass('on');

            $this.find('span').text($this.parent().hasClass('on') ? 'Zakończ obserwację' : 'Obserwuj');
            $this.find('small').text('(' + count + ' ' + declination(count, ['obserwujący', 'obserwujących', 'obserwujących']) + ')');
        })
        .error(function(event) {
            if (typeof event.responseJSON.error !== 'undefined') {
                error(event.responseJSON.error);
            }
        });

        return false;
    });

    $('#btn-lock a').click(function() {
        var $this = $(this);

        $.post($this.attr('href'), function() {
            $this.parent().toggleClass('on');
            $this.text($this.parent().hasClass('on') ? 'Odblokuj wątek' : 'Zablokuj wątek');
        })
        .error(function(event) {
            if (typeof event.responseJSON.error !== 'undefined') {
                error(event.responseJSON.error);
            }
        });

        return false;
    });

    /**
     * Move to another category
     */
    $('#btn-move ul a').click(function() {
        $('#modal-move').modal('show').find(':hidden[name="path"]').val($(this).data('path'));

        return false;
    });

    /**
     * Edit topic subject
     */
    $('#btn-edit-subject a').click(function() {
        $('#modal-subject').modal('show');

        return false;
    });

    /**
     * Mark category/categories as read
     */
    $('.btn-mark-read a').click(function() {
        $('.btn-view').removeClass('unread');
        $('.ico').each(function() {
            if ($(this).hasClass('new')) {
                $(this).removeClass('new').addClass('normal');
            }
        });

        $('.sub-unread').removeClass('sub-unread');
        $.post($(this).attr('href'));

        return false;
    });

    /**
     * Mark category/topic as read by clicking on it
     */
    $('.new').click(function() {
        $(this).addClass('normal').removeClass('new');
        $(this).parent().next().find('.btn-view').removeClass('unread');

        $.post($(this).attr('href'));

        return false;
    });

    /**
     * Subscribe/unsubscribe topic (from topics list)
     */
    $('.btn-watch-sm').click(function() {
        $(this).toggleClass('on');
        $.post($(this).attr('href'));

        return false;
    });

    /**
     * Subscribe/unsubscribe post
     */
    $('.btn-sub').click(function() {
        $(this).toggleClass('active');
        $.post($(this).attr('href'));

        return false;
    });

    /**
     * Delete post/topic
     */
    $('.post').on('click', '.btn-del', function() {
        $('#modal-post-delete').parent().attr('action', $(this).attr('href'));
        $('#modal-post-delete').modal('show');

        return false;
    });

    /**
     * Share post link
     */
    $('.btn-share').one('click', function() {
        var url = $(this).attr('href');
        var $input = $('<input type="text" class="form-control input-sm" style="width: 300px" value="' + url + '" />');

        $input.click(function() {
            this.select();
        });

        $(this).popover({
            'html': true,
            'content': $input,
            'title': '',
            'container': 'body'
        }).tooltip('destroy');
    })
    .click(function() {
        $(this).popover('show');

        return false;
    });

    /**
     * Add to multi quote list
     */
    $('.btn-multi-quote').click(function() {
        var cookies = document.cookie.split(';');
        var cookie = [];
        var postId = parseInt($(this).data('post-id'));
        var topicId = parseInt($(this).data('topic-id'));

        var map = function(element) {
            return parseInt(element);
        };

        for (var item in cookies) {
            var name = '', value = '';
            var parts = cookies[item].split('=', 2);

            name = parts[0];
            value = parts[1];

            if ($.trim(name) === 'mqid' + topicId) {
                cookie = value.split(',').map(map);
            }
        }

        var indexOf = $.inArray(postId, cookie);
        if (indexOf === -1) {
            cookie.push($(this).data('post-id'));
        } else {
            cookie.splice(indexOf, 1);
        }

        $(this).toggleClass('active');
        document.cookie = 'mqid' + topicId + '=' + cookie.join(',') + ';path=/';
    });

    $('body').on('click', function (e) {
        $('.btn-share').each(function () {
            //the 'is' for buttons that trigger popups
            //the 'has' for icons within a button that triggers a popup
            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                $(this).popover('hide');
            }
        });
    });

    $('.vote-up').click(function() {
        var $this = $(this);

        $.post($this.attr('href'), function(json) {
            $this.toggleClass('on');
            $this.prev().text(json.count);
        })
            .error(function(event) {
                if (typeof event.responseJSON.error !== 'undefined') {
                    error(event.responseJSON.error);
                }
            });

        return false;
    });

    $('.vote-accept[href]').click(function() {
        var $this = $(this);

        $.post($this.attr('href'), function() {
            $this.toggleClass('on');
            $('.vote-accept').not($this).removeClass('on');
        })
            .error(function(event) {
                if (typeof event.responseJSON.error !== 'undefined') {
                    error(event.responseJSON.error);
                }
            });

        return false;
    });

    $('#btn-fast-reply').click(function() {
        $('#box-fast-form').find('textarea').focus();
    });

    /**
     * Change forum category
     */
    $('#sel-forum-list').change(function() {
        window.location.href = forumUrl + '/' + $(this).val();
    });

    /**
     * Refresh forum category
     */
    $('#btn-goto').click(function() {
        $('#sel-forum-list').trigger('change');
    });

    var comments = {};

    $('.comments').on('submit', 'form', function() {
        var $form = $(this);
        $('button', $form).attr('disabled', 'disabled').text('Wysyłanie...');
        $('textarea', $form).attr('readonly', 'readonly');

        $.post($form.attr('action'), $form.serialize(), function(html) {
            if ($form.hasClass('collapse')) {
                $('textarea', $form).val('');
                $form.collapse('hide');

                $(html).insertBefore($form);
                $('.btn-sub[data-post-id="' + $(':hidden[name="post_id"]', $form).val() + '"]').addClass('active');
            } else {
                $form.parent().replaceWith(html);
            }
        })
        .always(function() {
            $('button', $form).removeAttr('disabled').text('Zapisz komentarz');
            $('textarea', $form).removeAttr('readonly');
        })
        .error(function(event, jqxhr) {
            if (typeof event.responseJSON.text !== 'undefined') {
                error(event.responseJSON.text);
            } else if (typeof event.responseJSON.error !== 'undefined') {
                error(event.responseJSON.error);
            }
        });

        return false;
    })
    .on('click', '.btn-comment-del', function() {
        var $this = $(this);

        $('#modal-comment-delete').modal('show').one('click', '.danger', function() {
            $(this).attr('disabled', 'disabled').text('Usuwanie...');

            $.post($this.attr('href'), function() {
                $('#modal-comment-delete').modal('hide');

                $this.parent().fadeOut(function() {
                    $(this).remove();
                });
            })
            .error(function(event) {
                if (typeof event.responseJSON.error !== 'undefined') {
                    error(event.responseJSON.error);
                }
            });
        });

        return false;
    })
    .on('click', '.btn-show-all', function() {
        $(this).nextAll('div:hidden').fadeIn(1000);
        $(this).remove();
    })
    .on('keyup', 'textarea', function() {
        if (parseInt($(this).val().length) > 580) {
            $(this).val($(this).val().substr(0, 580));
        }

        $('strong', $(this).parents('form')).text(580 - parseInt($(this).val().length));
    })
    .on('click', '.btn-comment-edit', function() {
        var $comment = $(this).parent();

        $.get($(this).attr('href'), function(html) {
            comments[$comment.data('comment-id')] = $comment.html();
            $comment.html(html).find('textarea').prompt(promptUrl).fastSubmit().autogrow().focus();
        });

        return false;
    })
    .on('click', '.btn-reset', function() {
        var $comment = $(this).parent().parent();

        $comment.html(comments[$comment.data('comment-id')]);
        return false;
    })
    .find('textarea').one('focus', function() {
        $(this).prompt(promptUrl).fastSubmit().autogrow().focus();
    });


    /**
     * Show/hide new comment's form
     */
    $('.comments form').on('shown.bs.collapse', function() {
        $(this).find('textarea').focus();
        $('.btn-comment[href="#' + $(this).attr('id') + '"]').addClass('active');
    })
    .on('hidden.bs.collapse', function() {
        $('.btn-comment[href="#' + $(this).attr('id') + '"]').removeClass('active');
    });

    /**
     * Quick edit of post
     */
    var posts = {};

    $('.btn-fast-edit').click(function() {
        var $this = $(this);
        var $post = $('.post-content[data-post-id="' + $this.data('post-id') + '"]');

        if (!$this.hasClass('active')) {
            $.get($this.attr('href'), function(html) {
                posts[$this.data('post-id')] = $post.html();
                $post.html(html).find('textarea').prompt(promptUrl).fastSubmit().autogrow().focus();

                $this.addClass('active');
            });
        } else {
            $post.html(posts[$this.data('post-id')]);
            $this.removeClass('active');
        }

        return false;
    });

    $('.post-content').on('submit', 'form', function() {
        var $form = $(this);
        var $post = $(this).parent();

        $(':submit', $form).attr('disabled', 'disabled').text('Zapisywanie...');
        $('textarea', $form).attr('readonly', 'readonly');

        $.post($form.attr('action'), $form.serialize(), function(html) {
            $post.html(html);
            $('.btn-fast-edit[data-post-id="' + $post.data('post-id') + '"]').removeClass('active');
        })
        .error(function(event) {
            $(':submit', $form).removeAttr('disabled').text('Zapisz');
            $('textarea', $form).removeAttr('readonly');

            if (typeof event.responseJSON.error !== 'undefined') {
                error(event.responseJSON.error);
            } else if (typeof event.responseJSON.text !== 'undefined') {
                error(event.responseJSON.text);
            }
        });

        return false;
    })
    .on('click', '.btn-reset', function() {console.log(1);
        var $post = $(this).parent().parent();

        $post.html(posts[$post.data('post-id')]);
        $('.btn-fast-edit[data-post-id="' + $post.data('post-id') + '"]').removeClass('active');

        return false;
    });

    /**
     * Upload attachment
     */
    $('#btn-upload').click(function() {
        $('.input-file').click();
    });

    $('.input-file').change(function() {
        var $form = $('#submit-form');
        var formData = new FormData($form[0]);

        $.ajax({
            url: uploadUrl,
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#btn-upload').attr('disabled', 'disabled').text('Wysyłanie...');
            },
            success: function (html) {
                $('#attachments .text-center').remove();
                $('#attachments tbody').append(html);
            },
            error: function (err) {
                $('#alert').modal('show');

                if (typeof err.responseJSON !== 'undefined') {
                    $('.modal-body').text(err.responseJSON.attachment[0]);
                }
            },
            complete: function() {
                $('#btn-upload').removeAttr('disabled').text('Dodaj załącznik');
            }
        }, 'json');

        return false;
    });

    $('#attachments').on('click', '.btn-del', function() {
        $(this).parents('tr').remove();
    })
    .on('click', '.btn-append', function() {
        var $form = $(this).parents('form');

        var parent = $(this).parents('tr');
        var file = $(':hidden', parent).val();
        var suffix = file.split('.').pop().toLowerCase();
        var markdown = '';

        if (suffix === 'png' || suffix === 'jpg' || suffix === 'jpeg' || suffix === 'gif') {
            markdown = '![' + $(this).text() + '](' + $(this).data('url') + ')';
        }

        $('textarea', $form).insertAtCaret("\n", "\n", markdown);
        $('.nav-tabs a:first').tab('show');
    });

    $('#submit-form textarea').pasteImage(pasteUrl, function(textarea, html) {
        $('#attachments .text-center').remove();
        $('#attachments tbody').append(html);

        var link = $('a', html);
        textarea.insertAtCaret("\n", "\n", '![' + link.text() + '](' + link.data('url') + ')');
    })
        .wikiEditor()
        .prompt(promptUrl)
        .fastSubmit()
        .autogrow();

    /////////////////////////////////////////////////////////////////////////////////

    if ('onhashchange' in window) {
        var onHashChange = function () {
            var hash = window.location.hash;
            var object = null;
            var color = null;

            if (hash.substring(1, 3) === 'id') {
                object = $(hash).parents('.post-body');
                color = '#fff';
            } else {
                object = $(hash);

                if (object.is(':hidden')) {
                    $('div:hidden', object.parent()).show();
                    $('.btn-show-all', object.parent()).remove();
                }

                color = '#fafafa';
            }

            object.addClass('highlight').css('background-color', '#FFDCA5');
            $('#container-fluid').one('mousemove', function () {
                object.animate({backgroundColor: color}, 1500, function() {
                    $(this).removeClass('highlight');
                });
            });
        };

        window.onhashchange = onHashChange;
        onHashChange();
    }
});

//# sourceMappingURL=forum.js.map