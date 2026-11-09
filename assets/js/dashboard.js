/* Taskora dashboard shell (portal + admin) */
(function ($) {
  'use strict';
  var $html = $('html'), $body = $('body');

  // Sidebar (mobile)
  $('#dbBurger').on('click', function () { $body.toggleClass('db-open'); });
  $('#dbOverlay').on('click', function () { $body.removeClass('db-open'); });

  // Theme
  $('#themeBtn').on('click', function () {
    var dark = $html.attr('data-theme') !== 'dark';
    $html.attr('data-theme', dark ? 'dark' : 'light');
    try { localStorage.setItem('tsk_theme', dark ? 'dark' : 'light'); } catch (e) {}
  });

  // Notifications
  $('#notifBtn').on('click', function (e) { e.stopPropagation(); $('#notifMenu').toggleClass('open'); });
  $(document).on('click', function (e) { if (!$(e.target).closest('.notif-wrap').length) $('#notifMenu').removeClass('open'); });
  $('#markAll').on('click', function () {
    $.post('ajax.php', { action: 'notif_read_all', _csrf: TASKORA.csrf }).done(function () {
      $('.notif-item').removeClass('unread'); $('.dotn').remove(); $('#markAll').remove();
    });
  });

  // Action buttons: <button data-action="accept_deal" data-id="5" data-confirm="...">
  $(document).on('click', '[data-action]', function (e) {
    e.preventDefault();
    var $b = $(this), msg = $b.data('confirm');
    if (msg && !window.confirm(msg)) return;
    var data = $.extend({ _csrf: TASKORA.csrf }, $b.data());
    delete data.confirm;
    $b.prop('disabled', true);
    $.post($b.data('endpoint') || 'ajax.php', data).done(function (r) {
      if (r.ok) { toast(r.message || 'Done'); setTimeout(function () { if (r.redirect) location.href = r.redirect; else location.reload(); }, 600); }
      else { toast(r.message || 'Action failed', 'error'); $b.prop('disabled', false); }
    }).fail(function (x) { toast((x.responseJSON || {}).message || 'Action failed', 'error'); $b.prop('disabled', false); });
  });

  // Inline AJAX action forms (quotes, counters, feedback)
  $(document).on('submit', 'form[data-action-form]', function (e) {
    e.preventDefault();
    var $f = $(this), $btn = $f.find('[type=submit]');
    $btn.prop('disabled', true);
    $.post($f.attr('action') || 'ajax.php', $f.serialize()).done(function (r) {
      if (r.ok) { toast(r.message || 'Saved'); setTimeout(function () { if (r.redirect) location.href = r.redirect; else location.reload(); }, 600); }
      else { toast(r.message || 'Please check the form', 'error'); $btn.prop('disabled', false); }
    }).fail(function (x) { toast((x.responseJSON || {}).message || 'Request failed', 'error'); $btn.prop('disabled', false); });
  });

  // Chat
  var $chat = $('#chat');
  if ($chat.length) {
    var bid = $chat.data('booking'), lastId = 0, $log = $('#chatLog');
    function render(msgs) {
      msgs.forEach(function (m) {
        lastId = Math.max(lastId, m.id);
        var $m = $('<div class="msg"></div>').toggleClass('me', !!m.mine);
        $m.append(document.createTextNode(m.message)).append($('<small></small>').text(m.who + ' · ' + m.time));
        $log.append($m);
      });
      if (msgs.length) $log.scrollTop($log[0].scrollHeight);
      if (!$log.children('.msg').length && !$log.children('.empty-chat').length) $log.append('<div class="empty-chat muted" style="text-align:center;margin:auto">No messages yet. Say hello 👋</div>');
      if (msgs.length) $log.children('.empty-chat').remove();
    }
    function poll() {
      $.getJSON('ajax.php', { action: 'chat_poll', booking_id: bid, after: lastId }).done(function (r) { if (r.ok) render(r.messages); });
    }
    poll(); setInterval(poll, 4000);
    $('#chatForm').on('submit', function (e) {
      e.preventDefault();
      var $in = $(this).find('[name=message]'), txt = $.trim($in.val());
      if (!txt) return;
      $in.val('');
      $.post('ajax.php', { action: 'chat_send', booking_id: bid, message: txt, _csrf: TASKORA.csrf }).done(function (r) {
        if (r.ok) poll(); else toast(r.message, 'error');
      });
    });
  }

  // Password show/hide
  $(document).on('click', '.pw-toggle', function () {
    var $i = $(this).siblings('input'); $i.attr('type', $i.attr('type') === 'password' ? 'text' : 'password');
  });

  // Live table search
  $(document).on('input', '[data-table-search]', function () {
    var q = this.value.toLowerCase(), t = $(this).data('table-search');
    $(t).find('tbody tr').each(function () { $(this).toggle($(this).text().toLowerCase().indexOf(q) > -1); });
  });
})(jQuery);
