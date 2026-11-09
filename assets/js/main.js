/* =========================================================
   TASKORA — front-end interactions & animations (jQuery)
   ========================================================= */
(function ($) {
  'use strict';

  var $win = $(window), $body = $('body');
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var fine = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;

  function store(k, v) { try { if (v === undefined) return localStorage.getItem(k); localStorage.setItem(k, v); } catch (e) { return null; } }

  /* ---------- Toasts ---------- */
  window.toast = function (msg, type, title) {
    var ic = type === 'error'
      ? '<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>'
      : '<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m8 12.5 3 3 5-6"/></svg>';
    var $t = $('<div class="toast ' + (type || '') + '">' + ic + '<div><strong></strong><span></span></div></div>');
    $t.find('strong').text(title || (type === 'error' ? 'Something went wrong' : 'Success'));
    $t.find('span').text(msg);
    $('#toasts').append($t);
    setTimeout(function () { $t.addClass('out'); setTimeout(function () { $t.remove(); }, 450); }, 4800);
  };

  /* ---------- AJAX defaults ---------- */
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': (window.TASKORA || {}).csrf, 'X-Requested-With': 'XMLHttpRequest' } });
  function url(p) { return (window.TASKORA ? window.TASKORA.base : '/') + p; }

  /* ---------- Preloader ---------- */
  var seen = store('tsk_seen');
  $body.addClass('is-loading');
  function hideLoader() {
    $('#preloader').addClass('done');
    $body.removeClass('is-loading').addClass('loaded');
    store('tsk_seen', '1');
  }
  // Keyed to DOM-ready (not window load) so slow fonts/CDNs never hold the page hostage.
  if (seen || reduce) { $('#preloader').css('transition-duration', '.6s'); setTimeout(hideLoader, 150); }
  else { $(function () { setTimeout(hideLoader, 1300); }); }

  /* ---------- Promo strip ---------- */
  var $promo = $('#promo');
  if ($promo.length) {
    if (store('tsk_promo_closed')) $promo.addClass('hide');
    else $body.addClass('has-promo');
    var setPromoH = function () { document.documentElement.style.setProperty('--promo-h', ($promo.is(':visible') ? $promo.outerHeight() : 0) + 'px'); };
    setPromoH(); $win.on('resize', setPromoH);
    $promo.on('click', '.promo-close', function () { $promo.slideUp(300, function () { $promo.addClass('hide'); $body.removeClass('has-promo'); setPromoH(); $win.trigger('scroll'); }); store('tsk_promo_closed', '1'); });
  }

  /* ---------- Header: scrolled / hide on scroll down ---------- */
  var $header = $('#siteHeader'), lastY = 0;
  var $prog = $('#scrollProgress'), $toTop = $('#toTop'), toTopBar = document.getElementById('toTopBar');
  function promoHeight() { return ($promo.length && !$promo.hasClass('hide')) ? $promo.outerHeight() : 0; }
  function onScroll() {
    var y = window.pageYOffset, h = document.documentElement.scrollHeight - window.innerHeight;
    var p = h > 0 ? y / h : 0;
    // Header rides just below the promo strip, then pins to the top once it scrolls away.
    $header[0].style.top = ($body.hasClass('menu-open') ? 0 : Math.max(promoHeight() - y, 0)) + 'px';
    $header.toggleClass('scrolled', y > 30);
    if (!$body.hasClass('menu-open') && !$('#megaItem').hasClass('open')) $header.toggleClass('hidden-up', y > lastY && y > 500);
    lastY = y;
    $prog.css('transform', 'scaleX(' + p + ')');
    $toTop.toggleClass('show', y > 700);
    if (toTopBar) toTopBar.style.strokeDashoffset = 126 - 126 * p;
  }
  $win.on('scroll', onScroll); onScroll();
  $toTop.on('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });

  /* ---------- Mobile menu ---------- */
  var $menu = $('#mobileMenu'), $toggle = $('#navToggle');
  function setMenu(open) {
    if (open) {
      // Start the circular reveal exactly from the burger button.
      var r = $toggle[0].getBoundingClientRect();
      $menu[0].style.setProperty('--cx', (r.left + r.width / 2) + 'px');
      $menu[0].style.setProperty('--cy', (r.top + r.height / 2) + 'px');
      $menu[0].style.setProperty('--hdr-h', $header.outerHeight() + 'px');
    }
    $body.toggleClass('menu-open', open);
    $toggle.attr({ 'aria-expanded': open, 'aria-label': open ? 'Close menu' : 'Open menu' });
    $menu.attr('aria-hidden', !open);
    $header.removeClass('hidden-up');
    onScroll();
    if (open) setTimeout(function () { $menu.find('.mm-link').first().trigger('focus'); }, 450);
  }
  $toggle.on('click', function () { setMenu(!$body.hasClass('menu-open')); });
  $menu.on('click', 'a', function () { setMenu(false); });
  $menu.on('click', '.mm-toggle', function () {
    var ex = $(this).attr('aria-expanded') !== 'true';
    $(this).attr('aria-expanded', ex);
  });
  $(document).on('keydown', function (e) {
    if (e.key !== 'Escape') return;
    if ($body.hasClass('menu-open')) { setMenu(false); $toggle.trigger('focus'); }
    closeMega(true);
  });
  $win.on('resize', function () { if (window.innerWidth >= 1200 && $body.hasClass('menu-open')) setMenu(false); });

  /* ---------- Desktop nav: sliding glider ---------- */
  var $nav = $('#mainNav'), $glider = $nav.find('.nav-glider');
  function glideTo($link) {
    if (!$link || !$link.length) { $glider.css('opacity', 0); return; }
    var n = $nav[0].getBoundingClientRect(), l = $link[0].getBoundingClientRect();
    $glider.css({ opacity: 1, width: l.width + 'px', transform: 'translateX(' + (l.left - n.left) + 'px)' });
  }
  $nav.on('mouseenter focusin', '.nav-link', function () { glideTo($(this)); });
  $nav.on('mouseleave', function () { if (!$('#megaItem').hasClass('open')) glideTo($nav.find('.nav-link.active')); });

  /* ---------- Mega menu (hover intent + keyboard) ---------- */
  var $mega = $('#megaItem'), megaTimer;
  function openMega() {
    clearTimeout(megaTimer);
    // The panel hangs off the nav pill; shift it so it is centred on the viewport instead.
    var n = $nav[0].getBoundingClientRect();
    $mega[0].style.setProperty('--mega-shift', (window.innerWidth / 2 - (n.left + n.width / 2)) + 'px');
    $mega.addClass('open').children('.nav-link').attr('aria-expanded', 'true'); glideTo($mega.children('.nav-link')); }
  function closeMega(now) {
    clearTimeout(megaTimer);
    megaTimer = setTimeout(function () {
      $mega.removeClass('open').children('.nav-link').attr('aria-expanded', 'false');
      if (!$nav.is(':hover')) glideTo($nav.find('.nav-link.active'));
    }, now === true ? 0 : 160);
  }
  $mega.on('mouseenter', openMega).on('mouseleave', closeMega);
  $mega.on('focusin', openMega).on('focusout', function () { setTimeout(function () { if (!$mega[0].contains(document.activeElement)) closeMega(true); }, 0); });
  $mega.on('click', '.mega-link, .mega-feature a, .mega-head a', function () { closeMega(true); });

  /* ---------- Active nav link + scroll-spy ---------- */
  var path = location.pathname.split('/').pop() || 'index.php';
  $('.nav-link').each(function () {
    var href = this.getAttribute('href'), file = href.split('#')[0].split('/').pop();
    if (file === path && href.indexOf('#') === -1) $(this).addClass('active');
  });
  if (path === 'index.php' && 'IntersectionObserver' in window) {
    var spy = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        $('.nav-link').removeClass('active');
        var $a = $('.nav-link[data-spy="' + en.target.id + '"]').addClass('active');
        if (!$nav.is(':hover') && !$mega.hasClass('open')) glideTo($a);
      });
    }, { rootMargin: '-45% 0px -50% 0px' });
    $('.nav-link[data-spy]').each(function () { var t = document.getElementById($(this).data('spy')); if (t) spy.observe(t); });
    // Clear the highlight when back in the hero.
    var hero = document.getElementById('hero');
    if (hero) new IntersectionObserver(function (en) { if (en[0].isIntersecting) { $('.nav-link').removeClass('active'); if (!$nav.is(':hover')) glideTo(null); } }, { rootMargin: '-45% 0px -50% 0px' }).observe(hero);
  }
  $win.on('load resize', function () { glideTo($nav.find('.nav-link.active')); });
  /*__QA__*/ setTimeout(function () { if (location.hash === '#__mega') { $('#preloader').remove(); openMega(); } if (location.hash === '#__menu') { $('#preloader').remove(); setMenu(true); $('.mm-toggle').attr('aria-expanded', 'true'); } }, 300); /*__QA_END__*/

  /* ---------- Custom cursor ---------- */
  if (fine && !reduce) {
    $body.addClass('has-cursor');
    var dot = document.getElementById('cursorDot'), ring = document.getElementById('cursorRing');
    var mx = 0, my = 0, rx = 0, ry = 0;
    $(document).on('mousemove', function (e) {
      mx = e.clientX; my = e.clientY; dot.style.transform = 'translate(' + mx + 'px,' + my + 'px)';
      if (!$body.hasClass('cursor-on')) { rx = mx; ry = my; $body.addClass('cursor-on'); } // reveal only once the pointer is known
    });
    $(document).on('mouseleave', function () { $body.removeClass('cursor-on'); });
    (function loop() { rx += (mx - rx) * .16; ry += (my - ry) * .16; ring.style.transform = 'translate(' + rx + 'px,' + ry + 'px)'; requestAnimationFrame(loop); })();
    $(document).on('mouseenter', 'a, button, .svc-card, .chip, input[type=range], label', function () { ring.classList.add('hover'); })
               .on('mouseleave', 'a, button, .svc-card, .chip, input[type=range], label', function () { ring.classList.remove('hover'); });
  }

  /* ---------- Split words ---------- */
  function splitWords(el) {
    var wi = 0;
    (function walk(node) {
      Array.prototype.slice.call(node.childNodes).forEach(function (n) {
        if (n.nodeType === 3) {
          var parts = n.textContent.split(/(\s+)/), frag = document.createDocumentFragment();
          parts.forEach(function (p) {
            if (!p) return;
            if (/^\s+$/.test(p)) { frag.appendChild(document.createTextNode(' ')); return; }
            var w = document.createElement('span'); w.className = 'wd';
            var inner = document.createElement('span'); inner.textContent = p; inner.style.setProperty('--wi', wi++);
            w.appendChild(inner); frag.appendChild(w);
          });
          node.replaceChild(frag, n);
        } else if (n.nodeType === 1 && n.tagName !== 'BR') { walk(n); }
      });
    })(el);
  }
  $('.split-words').each(function () { splitWords(this); });

  /* ---------- Reveal on scroll ---------- */
  $('[data-delay]').each(function () { this.style.setProperty('--d', this.getAttribute('data-delay')); });
  $('[data-stagger]').each(function () {
    $(this).children().each(function (i) { if (!this.hasAttribute('data-reveal')) this.setAttribute('data-reveal', ''); this.style.setProperty('--d', i); });
  });
  var inViewTargets = '[data-reveal], .split-words, .stat, .flow-track, .price-card, .dist, .ladder, .trust-shield, [data-count], .donut-wrap';
  if ('IntersectionObserver' in window && !reduce) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('in'); $(en.target).trigger('inview'); io.unobserve(en.target); }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });
    $(inViewTargets).each(function () { io.observe(this); });
  } else {
    $(inViewTargets).addClass('in').trigger('inview');
  }

  /* ---------- Counters ---------- */
  $(document).on('inview', '[data-count]', function () {
    var el = this, raw = el.getAttribute('data-count');
    var m = raw.match(/^([^\d]*)([\d,.]+)(.*)$/);
    if (!m) { el.textContent = raw; return; }
    var target = parseFloat(m[2].replace(/,/g, '')), dec = (m[2].split('.')[1] || '').length, hasComma = m[2].indexOf(',') > -1;
    var start = null, dur = 2000;
    function fmt(v) { var s = v.toFixed(dec); if (hasComma) s = Number(s).toLocaleString('en-US', { minimumFractionDigits: dec, maximumFractionDigits: dec }); return m[1] + s + m[3]; }
    function step(ts) { if (!start) start = ts; var p = Math.min((ts - start) / dur, 1); var e = 1 - Math.pow(1 - p, 4); el.textContent = fmt(target * e); if (p < 1) requestAnimationFrame(step); }
    requestAnimationFrame(step);
  });

  /* ---------- Magnetic buttons ---------- */
  if (fine && !reduce) {
    $(document).on('mousemove', '.magnetic', function (e) {
      var r = this.getBoundingClientRect(), x = e.clientX - r.left - r.width / 2, y = e.clientY - r.top - r.height / 2;
      this.style.transform = 'translate(' + x * .25 + 'px,' + y * .35 + 'px)';
    }).on('mouseleave', '.magnetic', function () { this.style.transform = ''; });

    /* Spotlight on cards */
    $(document).on('mousemove', '.svc-card', function (e) {
      var r = this.getBoundingClientRect();
      this.style.setProperty('--mx', (e.clientX - r.left) + 'px');
      this.style.setProperty('--my', (e.clientY - r.top) + 'px');
    });

    /* Tilt */
    $(document).on('mousemove', '[data-tilt]', function (e) {
      var r = this.getBoundingClientRect(), x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
      this.style.transform = 'perspective(1000px) rotateY(' + x * 10 + 'deg) rotateX(' + (-y * 10) + 'deg)';
    }).on('mouseleave', '[data-tilt]', function () { this.style.transform = ''; });

    /* Parallax */
    var $par = $('[data-parallax]');
    if ($par.length) {
      $win.on('scroll', function () {
        var y = window.pageYOffset;
        $par.each(function () { this.style.transform = 'translate3d(0,' + (y * parseFloat(this.getAttribute('data-parallax'))) + 'px,0)'; });
      });
    }
  }

  /* ---------- Services live filter ---------- */
  $('#svcSearch').on('input', function () {
    var q = this.value.toLowerCase().trim(), shown = 0;
    $('.svc-card').each(function () {
      var hit = !q || $(this).text().toLowerCase().indexOf(q) > -1;
      $(this).toggleClass('is-hidden', !hit); if (hit) shown++;
    });
    $('.svc-empty').toggle(shown === 0);
  });

  /* ---------- Cost estimator (AJAX) ---------- */
  var $calc = $('#calcForm');
  if ($calc.length) {
    var $range = $calc.find('input[type=range]'), calcTimer;
    function paintRange() {
      var min = +$range.attr('min'), max = +$range.attr('max'), v = +$range.val();
      $range[0].style.setProperty('--p', ((v - min) / (max - min) * 100) + '%');
      $('#sizeVal').text(v + ' ' + $range.data('unit'));
    }
    function animateTo($el, from, to) {
      var start = null;
      function f(n) { return 'Rs ' + Math.round(n).toLocaleString('en-US'); }
      (function step(ts) {
        if (!start) start = ts; var p = Math.min((ts - start) / 700, 1), e = 1 - Math.pow(1 - p, 3);
        $el.find('.lo').text(f(from[0] + (to[0] - from[0]) * e)); $el.find('.hi').text(f(from[1] + (to[1] - from[1]) * e));
        if (p < 1) requestAnimationFrame(step);
      })(performance.now());
    }
    var last = [0, 0];
    function estimate() {
      $('#calcAmount').addClass('loading');
      $.post(url('ajax/estimate.php'), $calc.serialize()).done(function (r) {
        if (r && r.ok) {
          animateTo($('#calcAmount'), last, [r.low, r.high]); last = [r.low, r.high];
          $('#calcMeta').text(r.summary);
        }
      }).always(function () { $('#calcAmount').removeClass('loading'); });
    }
    $range.on('input', paintRange);
    $calc.on('input change', 'input, select', function () { clearTimeout(calcTimer); calcTimer = setTimeout(estimate, 220); });
    $calc.on('submit', function (e) { e.preventDefault(); estimate(); });
    paintRange(); estimate();
  }

  /* ---------- Testimonial slider ---------- */
  $('.t-slider').each(function () {
    var $s = $(this), $track = $s.find('.t-track'), n = $s.find('.t-slide').length, i = 0, timer;
    var $dots = $s.find('.t-dots');
    for (var k = 0; k < n; k++) $dots.append('<button aria-label="Slide ' + (k + 1) + '"></button>');
    function go(to) { i = (to + n) % n; $track.css('transform', 'translateX(' + (-100 * i) + '%)'); $dots.children().removeClass('active').eq(i).addClass('active'); }
    function auto() { clearInterval(timer); timer = setInterval(function () { go(i + 1); }, 6500); }
    $s.on('click', '.next', function () { go(i + 1); auto(); });
    $s.on('click', '.prev', function () { go(i - 1); auto(); });
    $dots.on('click', 'button', function () { go($(this).index()); auto(); });
    var sx = null;
    $s.on('touchstart', function (e) { sx = e.originalEvent.touches[0].clientX; });
    $s.on('touchend', function (e) { if (sx === null) return; var dx = e.originalEvent.changedTouches[0].clientX - sx; if (Math.abs(dx) > 40) go(i + (dx < 0 ? 1 : -1)); sx = null; auto(); });
    go(0); if (!reduce) auto();
  });

  /* ---------- Solutions tabs ---------- */
  $(document).on('click', '.sol-tab', function () {
    var t = $(this).data('tab');
    $('.sol-tab').removeClass('active').attr('aria-selected', 'false'); $(this).addClass('active').attr('aria-selected', 'true');
    $('.sol-panel').removeClass('active').filter('[data-panel="' + t + '"]').addClass('active');
  });

  /* ---------- Donut chart ---------- */
  $('.donut-wrap').on('inview', function () {
    var C = 2 * Math.PI * 80, acc = 0;
    $(this).find('circle.seg-c').each(function (idx) {
      var pct = +this.getAttribute('data-pct'), len = C * pct / 100, el = this, off = acc;
      el.style.strokeDasharray = '0 ' + C; el.style.strokeDashoffset = -off;
      setTimeout(function () { el.style.strokeDasharray = Math.max(len - 2, 0) + ' ' + (C - len + 2); }, 80 * idx);
      acc += len;
    });
  });
  $(document).on('mouseenter', '[data-seg]', function () {
    var k = $(this).data('seg');
    $('[data-seg="' + k + '"]').addClass('hl');
    var $c = $('circle.seg-c[data-seg="' + k + '"]');
    if ($c.length) { $('#donutPct').text($c.data('pct') + '%'); $('#donutLbl').text($c.data('label')); }
  }).on('mouseleave', '[data-seg]', function () {
    $('[data-seg]').removeClass('hl'); $('#donutPct').text($('#donutPct').data('def')); $('#donutLbl').text($('#donutLbl').data('def'));
  });

  /* ---------- Vision word highlight on scroll ---------- */
  var $vision = $('.vision-text');
  if ($vision.length) {
    var vt = $vision.text().trim().split(/\s+/);
    $vision.html(vt.map(function (w) { return '<span class="w">' + $('<i>').text(w).html() + '</span>'; }).join(' '));
    var $words = $vision.find('.w');
    var lightUp = function () {
      var r = $vision[0].getBoundingClientRect(), vh = window.innerHeight;
      var p = Math.min(Math.max((vh * .85 - r.top) / (r.height + vh * .35), 0), 1);
      var lit = Math.floor(p * $words.length);
      $words.each(function (i) { this.classList.toggle('lit', i < lit); });
    };
    if (reduce) $words.addClass('lit'); else { $win.on('scroll', lightUp); lightUp(); }
  }

  /* ---------- Accordion (catalogue) ---------- */
  $(document).on('click', '.acc-head', function () {
    var $a = $(this).closest('.acc'); $a.toggleClass('open');
    $(this).attr('aria-expanded', $a.hasClass('open'));
  });

  /* ---------- Generic AJAX forms ---------- */
  function clearErrors($f) { $f.find('.field').removeClass('has-error'); $f.find('.input').removeClass('invalid'); }
  function showErrors($f, errors) {
    $.each(errors || {}, function (name, msg) {
      var $in = $f.find('[name="' + name + '"]'), $field = $in.closest('.field');
      $in.addClass('invalid'); $field.addClass('has-error');
      var $err = $field.find('.field-error'); if (!$err.length) $err = $('<div class="field-error"></div>').appendTo($field);
      $err.text(msg);
    });
    var $first = $f.find('.has-error').first();
    if ($first.length) $('html,body').animate({ scrollTop: $first.offset().top - 140 }, 500);
  }
  $(document).on('submit', 'form[data-ajax]', function (e) {
    e.preventDefault();
    var $f = $(this), $btn = $f.find('[type=submit]'), label = $btn.html();
    clearErrors($f);
    $btn.prop('disabled', true).html('<span>' + ($btn.data('loading') || 'Sending…') + '</span>');
    $.ajax({ url: $f.attr('action'), method: 'POST', data: new FormData(this), processData: false, contentType: false, dataType: 'json' })
      .done(function (r) {
        if (r.ok) {
          if (r.redirect) { location.href = r.redirect; return; }
          if ($f.data('success') === 'replace' && r.html) {
            $f.closest('[data-form-wrap]').html(r.html);
            $('html,body').animate({ scrollTop: $('[data-form-wrap]').offset().top - 120 }, 500);
          } else { toast(r.message || 'Saved successfully.'); $f[0].reset(); }
        } else { showErrors($f, r.errors); toast(r.message || 'Please check the highlighted fields.', 'error'); }
      })
      .fail(function (x) {
        var r = x.responseJSON || {};
        if (r.errors) showErrors($f, r.errors);
        toast(r.message || 'Network error. Please try again.', 'error');
      })
      .always(function () { $btn.prop('disabled', false).html(label); });
  });
  $(document).on('input change', '.input.invalid', function () { $(this).removeClass('invalid').closest('.field').removeClass('has-error'); });

  /* ---------- Newsletter ---------- */
  $('#newsForm').on('submit', function (e) {
    e.preventDefault();
    var $f = $(this), email = $f.find('[name=email]').val();
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { toast('Please enter a valid email address.', 'error'); return; }
    $.post(url('ajax/subscribe.php'), $f.serialize()).done(function (r) {
      toast(r.message, r.ok ? '' : 'error', r.ok ? 'Subscribed' : null); if (r.ok) $f[0].reset();
    }).fail(function () { toast('Could not subscribe right now.', 'error'); });
  });

  /* ---------- Smooth anchor offset ---------- */
  $(document).on('click', 'a[href*="#"]', function (e) {
    var href = this.getAttribute('href'), hash = href.substring(href.indexOf('#'));
    var samePage = href.charAt(0) === '#' || this.pathname.replace(/^\//, '') === location.pathname.replace(/^\//, '');
    if (!samePage || hash.length < 2) return;
    var $t = $(hash); if (!$t.length) return;
    e.preventDefault();
    window.scrollTo({ top: $t.offset().top - 70, behavior: reduce ? 'auto' : 'smooth' });
    history.replaceState(null, '', hash);
  });

})(jQuery);
