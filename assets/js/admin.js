/* Taskora admin CMS helpers */
(function ($) {
  'use strict';

  // Re-index repeater input names so PHP receives rows in on-screen order.
  function reindex($table) {
    var key = $table.data('key');
    $table.find('tbody tr').each(function (i) {
      $(this).find('[name]').each(function () {
        this.name = this.name.replace(new RegExp('^rep\\[' + key + '\\]\\[\\d+\\]'), 'rep[' + key + '][' + i + ']');
      });
    });
  }

  $(document).on('click', '.rep-add', function () {
    var $t = $('#' + $(this).data('target')), $last = $t.find('tbody tr').last();
    var $row = $last.clone();
    $row.find('input:not([type=color]), textarea').val('');
    $t.find('tbody').append($row);
    reindex($t);
    $row.find('.input').first().trigger('focus');
  });

  $(document).on('click', '.rep-del', function () {
    var $t = $(this).closest('table');
    if ($t.find('tbody tr').length <= 1) { toast('At least one row is required. Clear its fields instead.', 'error'); return; }
    $(this).closest('tr').fadeOut(200, function () { $(this).remove(); reindex($t); });
  });

  $(document).on('click', '.rep-up', function () {
    var $tr = $(this).closest('tr'), $prev = $tr.prev();
    if ($prev.length) { $tr.insertBefore($prev); reindex($tr.closest('table')); }
  });

  // Colour picker ↔ text input sync
  $(document).on('input', '.color-pick', function () { $(this).next('.input').val(this.value); });
  $(document).on('input', '.color-pick + .input', function () { if (/^#[0-9a-f]{6}$/i.test(this.value)) $(this).prev('.color-pick').val(this.value); });

  // Confirm on submit buttons / links
  $(document).on('click', '[data-confirm-submit]', function (e) { if (!confirm($(this).data('confirm-submit'))) e.preventDefault(); });
  $(document).on('click', 'a[data-confirm]', function (e) { if (!confirm($(this).data('confirm'))) e.preventDefault(); });

  // Auto-slug
  $(document).on('input', '[data-slug-source]', function () {
    var $slug = $($(this).data('slug-source'));
    if ($slug.data('touched')) return;
    $slug.val(this.value.toLowerCase().replace(/&/g, 'and').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''));
  });
  $(document).on('input', '.slug-input', function () { $(this).data('touched', true); });

  // Image preview
  $(document).on('change', 'input[type=file][data-preview]', function () {
    var f = this.files && this.files[0], $img = $($(this).data('preview'));
    if (f && /^image\//.test(f.type)) { var r = new FileReader(); r.onload = function (e) { $img.attr('src', e.target.result).show(); }; r.readAsDataURL(f); }
  });
})(jQuery);
