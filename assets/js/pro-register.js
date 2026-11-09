/* Taskora — professional registration wizard (account/register.php?role=provider).
   Mirrors pro_fee_row(), pro_docs() and pro_fees() in includes/pro_registration.php for display;
   the server recalculates everything on submit. */
(function () {
  'use strict';
  var D = JSON.parse(document.getElementById('pregData').textContent);
  var form = document.getElementById('pregForm');
  if (!form) return;
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
  var pkr = function (n) { return 'PKR ' + Math.round(n || 0).toLocaleString('en-PK'); };
  var mb = function (b) { return b < 1048576 ? Math.max(1, Math.round(b / 1024)) + ' KB' : (b / 1048576).toFixed(1).replace(/\.0$/, '') + ' MB'; };
  var SV = D.services.map(function (s) { return { id: s[0], cat: s[1], sub: s[2], name: s[3], tier: s[4], phase: s[5], who: s[6] }; });
  var BIZ = ['T4', 'T5', 'P', 'V'], TORD = { T1: 1, T2: 2, T3: 3 };
  var step = 0, docFiles = {}, busy = false;

  var type = function () { return ($('input[name=ptype]:checked') || {}).value || 'Individual'; };
  var cat = function () { return $('#category').value; };
  var checkedIds = function () { return $$('input[name="services[]"]:checked').map(function (c) { return c.value; }); };

  function feeRow() {
    var ci = D.cats[cat()]; if (!ci) return null;
    var base = ci.types[type()]; if (!base) return null;
    if (type() !== 'Individual' || !TORD[base.tier]) return base;
    var ids = checkedIds(), top = base.tier;
    SV.forEach(function (s) { if (ids.indexOf(s.id) > -1 && TORD[s.tier] && TORD[s.tier] > TORD[top]) top = s.tier; });
    if (top === base.tier) return base;
    var T = D.tiers[top];
    return Object.assign({}, base, { tier: top, app: T.app, reg: T.reg, annual: T.annual, monthly: T.monthly, escalated: true, who: T.desc });
  }
  var isBiz = function () { var f = feeRow(); return !!f && BIZ.indexOf(f.tier) > -1; };
  var whoOk = function (s) { return !s.who || s.who === 'B' || (s.who === 'I' && type() === 'Individual') || (s.who === 'F' && type() !== 'Individual'); };

  /* ---------- Step 1: categories and services ---------- */
  function fillCats() {
    var sel = $('#category'), prev = sel.value;
    var opts = Object.keys(D.cats).filter(function (k) { return D.cats[k].types[type()]; });
    sel.innerHTML = '<option value="">Select category</option>' + opts.map(function (k) { return '<option>' + esc(k) + '</option>'; }).join('');
    if (opts.indexOf(prev) > -1) sel.value = prev;
    if (opts.length === 1) sel.value = opts[0];
    renderServices();
  }
  function renderServices() {
    var box = $('#svcBox'), ci = D.cats[cat()];
    if (!ci) { box.innerHTML = '<div class="preg-empty">Choose a category to see its services.</div>'; $('#svcCount').textContent = ''; $('#catHint').textContent = ''; return; }
    var groups = {}, order = [];
    SV.forEach(function (s) { if (s.cat === cat() && whoOk(s)) { if (!groups[s.sub]) { groups[s.sub] = []; order.push(s.sub); } groups[s.sub].push(s); } });
    box.innerHTML = order.map(function (g) {
      return '<div class="preg-svcgroup"><h4>' + esc(g) + '</h4><div class="preg-svclist">' + groups[g].map(function (s) {
        return '<label class="preg-chk"><input type="checkbox" name="services[]" value="' + esc(s.id) + '"> <span>' + esc(s.name) +
          (s.phase !== 'Launch' ? ' <span class="preg-tag soon">' + esc(s.phase) + '</span>' : '') +
          (s.tier === 'T3' && type() === 'Individual' ? ' <span class="preg-tag flag">Licence needed</span>' : '') + '</span></label>';
      }).join('') + '</div></div>';
    }).join('') +
      '<label class="preg-chk preg-all"><input type="checkbox" id="svcAll"> Select all in this category</label><p class="preg-hint" id="tierNote"></p>' +
      (ci.req.length ? '<details class="preg-req"><summary>Requirements for this category</summary><ul class="preg-plain">' + ci.req.map(function (x) { return '<li>' + esc(x) + '</li>'; }).join('') + '</ul></details>' : '');
    $('#svcAll').addEventListener('change', function (e) { $$('input[name="services[]"]', box).forEach(function (c) { c.checked = e.target.checked; }); svcChanged(); });
    svcChanged();
  }
  function svcChanged() {
    var f = feeRow();
    $('#svcCount').textContent = '(' + checkedIds().length + ' selected)';
    $('#catHint').textContent = f ? (f.who ? f.who + '. ' : '') + (f.lic ? 'Also needed: ' + f.lic : '') : '';
    var n = $('#tierNote'); if (n) n.textContent = f && f.escalated ? 'You picked services that need a professional licence, so the licensed-professional fee tier (' + f.tier + ') applies.' : '';
    if (checkedIds().length) clearErr('services');
  }
  $('#ptype').addEventListener('change', function () { fillCats(); });
  $('#category').addEventListener('change', function () { clearErr('category'); renderServices(); });
  $('#svcBox').addEventListener('change', function (e) { if (e.target.name === 'services[]') svcChanged(); });
  $('#city').addEventListener('change', function () {
    var v = this.value; clearErr('city');
    $('#cityHint').textContent = v && D.launch.indexOf(v) < 0 ? 'Taskora opens in ' + D.launch.join(' and ') + ' first. You will join the waitlist for ' + v + '.' : '';
  });

  /* ---------- Step 3: documents ---------- */
  function docsFor(tier, c, t) {
    var firm = BIZ.indexOf(tier) > -1, inhome = D.inhome.indexOf(c) > -1, d = [];
    var add = function (id, label, req, x) { d.push(Object.assign({ id: id, label: label, req: req }, x || {})); };
    var has = function (list) { return list.indexOf(tier) > -1; };
    add('cnic', firm ? 'CNIC of authorised signatory (front and back)' : 'CNIC front and back', true, { multi: true });
    if (!firm) add('selfie', 'Live selfie (photo of your face)', true, { capture: true });
    if (tier === 'T1' || (tier === 'T2' && inhome)) add('pcc', 'Police character certificate (issued in the last 180 days)', true);
    else if (!has(['P', 'V']) && inhome) add('pcc', 'Police certificate for staff sent to homes', false);
    add('address', firm ? 'Office utility bill or rent agreement' : 'Utility bill for your address', true);
    if (has(['T2', 'T3', 'T4', 'T5'])) add('qual', firm ? 'Key staff qualifications' : 'Qualification or skill certificate', true, { multi: true });
    else if (tier === 'T1') add('qual', 'Skill certificate, if you have one (NAVTTC, TEVTA)', false);
    if (has(['T3', 'T5', 'P'])) add('lic', 'Professional licence or regulator registration', true);
    if (has(['T1', 'T2', 'T3', 'T4'])) add('portfolio', 'Photos of past work or portfolio', false, { multi: true });
    if (has(['T3', 'T4', 'T5', 'P', 'V'])) add('secp', tier === 'T3' ? 'NTN certificate' : 'SECP certificate and NTN', true);
    if (has(['T4', 'T5'])) add('ins', 'Business insurance policy', true);
    if (t === 'Vendor - Distributor') add('auth', 'Principal or brand authorisation letter', true);
    return d;
  }
  var currentDocs = [];
  function renderDocs() {
    var f = feeRow(); if (!f) return;
    $('#tierLine').textContent = 'Tier ' + f.tier + ': ' + D.tiers[f.tier].desc + '.';
    currentDocs = docsFor(f.tier, cat(), type());
    // Forget files for documents this tier no longer asks for.
    Object.keys(docFiles).forEach(function (k) { if (!currentDocs.some(function (d) { return d.id === k; })) delete docFiles[k]; });
    $('#docList').innerHTML = currentDocs.map(function (d) {
      return '<li class="field" data-err="doc_' + d.id + '"><div><span class="label">' + esc(d.label) + (d.req ? ' <span class="req">*</span>' : ' <span class="preg-opt">(optional)</span>') + '</span>' +
        '<ul class="preg-files" id="files-' + d.id + '"></ul></div>' +
        '<label class="preg-upload"><input type="file" data-doc="' + d.id + '" accept="image/jpeg,image/png,image/webp,application/pdf"' + (d.multi ? ' multiple' : '') + (d.capture ? ' capture="user"' : '') + '>' +
        '<span>' + (d.multi ? 'Add files' : 'Choose file') + '</span></label></li>';
    }).join('');
    currentDocs.forEach(function (d) { listFiles(d.id); });
    $('#checkList').innerHTML = D.checks.map(function (c) {
      var v = c.m[f.tier]; if (v !== 'M' && v !== 'C') return '';
      return '<span class="preg-tag' + (v === 'C' ? ' flag' : '') + '" title="' + esc(c.what) + '">' + esc(c.n) + (v === 'C' ? ' (if applicable)' : '') + '</span>';
    }).join('');
  }
  function listFiles(id) {
    var ul = $('#files-' + id); if (!ul) return;
    ul.innerHTML = (docFiles[id] || []).map(function (f, i) {
      return '<li>' + esc(f.name) + ' <small>' + mb(f.size) + '</small><button type="button" class="preg-x" data-doc="' + id + '" data-i="' + i + '" aria-label="Remove ' + esc(f.name) + '">×</button></li>';
    }).join('');
  }
  // Large phone photos are resized (max 1800 px, JPEG) so uploads stay under the server limit.
  function shrink(file) {
    return new Promise(function (res) {
      if (!/^image\/(jpeg|png|webp)$/.test(file.type) || file.size < 400 * 1024 || !window.HTMLCanvasElement) return res(file);
      var url = URL.createObjectURL(file), img = new Image();
      img.onload = function () {
        var k = Math.min(1, 1800 / Math.max(img.width, img.height)), c = document.createElement('canvas');
        c.width = Math.round(img.width * k); c.height = Math.round(img.height * k);
        var x = c.getContext('2d'); x.fillStyle = '#fff'; x.fillRect(0, 0, c.width, c.height); x.drawImage(img, 0, 0, c.width, c.height);
        URL.revokeObjectURL(url);
        c.toBlob(function (b) {
          if (!b || b.size >= file.size) return res(file);
          try { res(new File([b], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' })); } catch (e) { b.name = file.name.replace(/\.\w+$/, '') + '.jpg'; res(b); }
        }, 'image/jpeg', 0.82);
      };
      img.onerror = function () { URL.revokeObjectURL(url); res(file); };
      img.src = url;
    });
  }
  $('#docList').addEventListener('change', function (e) {
    var inp = e.target; if (!inp.dataset || !inp.dataset.doc) return;
    var id = inp.dataset.doc, picked = Array.prototype.slice.call(inp.files), multi = inp.multiple;
    inp.value = '';
    var bad = picked.filter(function (f) { return !/\.(jpe?g|png|webp|pdf)$/i.test(f.name); });
    if (bad.length) return setErr('doc_' + id, 'Upload a JPG, PNG, WEBP image or a PDF.');
    var label = inp.nextElementSibling; label.textContent = 'Preparing…';
    Promise.all(picked.map(shrink)).then(function (files) {
      label.textContent = multi ? 'Add files' : 'Choose file';
      var big = files.filter(function (f) { return f.size > D.limits.file; });
      if (big.length) return setErr('doc_' + id, '"' + big[0].name + '" is larger than ' + mb(D.limits.file) + '. Upload a smaller file.');
      docFiles[id] = multi ? (docFiles[id] || []).concat(files).slice(0, 10) : files.slice(0, 1);
      clearErr('doc_' + id); listFiles(id);
    });
  });
  $('#docList').addEventListener('click', function (e) {
    var b = e.target.closest('.preg-x'); if (!b) return;
    docFiles[b.dataset.doc].splice(+b.dataset.i, 1); listFiles(b.dataset.doc);
  });

  /* ---------- Step 4: fees ---------- */
  function renderFees() {
    var f = feeRow(); if (!f) return;
    var partner = f.tier === 'P';
    $('#feeOptions').hidden = partner;
    if (partner) {
      $('#receipt').innerHTML = '<p style="margin:0">No registration fees for licensed partner institutions. Commercial terms are agreed in a partnership agreement.</p>';
      $('#feeNote').textContent = 'Our partnerships team contacts you within 5 working days.';
      return;
    }
    var T = D.tiers[f.tier], founding = $('#founding').checked, monthly = $('input[name=billing]:checked').value === 'monthly';
    var app = founding ? f.app * (1 - T.wApp) : f.app, reg = founding ? f.reg * (1 - T.wReg) : f.reg, free = founding ? T.free : 0;
    var mem = monthly ? f.monthly * (12 - free) : f.annual * (12 - free) / 12;
    var label = monthly ? 'Membership, monthly (' + pkr(f.monthly) + ' × ' + (12 - free) + ' months)' : 'Membership, yearly' + (free ? ' (' + free + ' months free)' : '');
    var row = function (l, v, orig) { return '<tr><td>' + l + '</td><td>' + (orig !== undefined && Math.round(orig) !== Math.round(v) ? '<s>' + pkr(orig) + '</s> ' : '') + pkr(v) + '</td></tr>'; };
    $('#receipt').innerHTML = '<table>' + row('Application fee (non-refundable)', app, f.app) + row('Registration fee (one-time: training, profile, digital card)', reg, f.reg) +
      row(label, mem) + '<tr class="total"><td>First-year total</td><td>' + pkr(app + reg + mem) + '</td></tr></table>';
    $('#feeNote').textContent = 'Nothing is charged now. After your CNIC and mobile are confirmed you pay the application fee first; the rest is due at approval.' +
      (founding ? ' The founding-partner offer applies to the first 10,000 approved pros.' : '');
  }
  $('#founding').addEventListener('change', renderFees);
  $('#billing').addEventListener('change', renderFees);

  /* ---------- Errors ---------- */
  function fieldFor(key) {
    var el = form.querySelector('[data-err="' + key + '"]');
    if (el) return { wrap: el, input: null };
    var inp = form.querySelector('[name="' + key + '"]');
    return inp ? { wrap: inp.closest('.field'), input: inp } : null;
  }
  function setErr(key, msg) {
    var f = fieldFor(key); if (!f || !f.wrap) return;
    f.wrap.classList.add('has-error');
    if (f.input) { f.input.classList.add('invalid'); f.input.setAttribute('aria-invalid', 'true'); }
    var m = f.wrap.querySelector(':scope > .field-error');
    if (!m) { m = document.createElement('div'); m.className = 'field-error'; m.setAttribute('role', 'alert'); f.wrap.appendChild(m); }
    m.textContent = msg;
  }
  function clearErr(key) {
    var f = fieldFor(key); if (!f || !f.wrap) return;
    f.wrap.classList.remove('has-error');
    if (f.input) { f.input.classList.remove('invalid'); f.input.removeAttribute('aria-invalid'); }
    var m = f.wrap.querySelector(':scope > .field-error'); if (m) m.remove();
  }
  function clearStep(s) { $$('.has-error', $('.preg-step[data-step="' + s + '"]')).forEach(function (w) { w.classList.remove('has-error'); var m = w.querySelector(':scope > .field-error'); if (m) m.remove(); }); $$('.invalid', $('.preg-step[data-step="' + s + '"]')).forEach(function (i) { i.classList.remove('invalid'); i.removeAttribute('aria-invalid'); }); }
  form.addEventListener('input', function (e) { if (e.target.name && e.target.classList.contains('invalid')) clearErr(e.target.name); });
  function alertBox(msg) { var a = $('#pregAlert'); a.hidden = !msg; a.textContent = msg || ''; }
  function focusFirstError() {
    var w = $('.preg-step:not([hidden]) .has-error'); if (!w) return;
    w.scrollIntoView({ behavior: 'smooth', block: 'center' });
    var i = w.querySelector('input:not([type=hidden]),select,textarea'); if (i) setTimeout(function () { i.focus({ preventScroll: true }); }, 350);
  }

  /* ---------- Validation (mirrors the server) ---------- */
  var reCnic = /^\d{5}-\d{7}-\d$/, reMob = /^03\d{2}-?\d{7}$/;
  var v = function (n) { var el = form.querySelector('[name="' + n + '"]'); return el ? el.value.trim() : ''; };
  function validate(s) {
    clearStep(s); var ok = true;
    var need = function (n, msg, bad) { if (bad === undefined ? !v(n) : bad) { setErr(n, msg); ok = false; } };
    if (s === 0) {
      need('category', 'Choose a category.');
      need('city', 'Choose a city.');
      if (cat() && !checkedIds().length) { setErr('services', 'Select at least one service.'); ok = false; }
    }
    if (s === 1) {
      if (isBiz()) {
        need('b_name', 'Enter the registered business name.'); need('b_secp', 'Enter the SECP or partnership registration number.');
        need('b_ntn', 'Enter the NTN.'); need('b_signatory', 'Enter the authorised signatory.');
        need('b_scnic', 'Use the format 12345-1234567-1.', !reCnic.test(v('b_scnic')));
      } else {
        need('name', 'Enter your full name as on your CNIC.', v('name').length < 3);
        need('father', "Enter your father's or husband's name.");
        need('cnic', 'Use the format 12345-1234567-1.', !reCnic.test(v('cnic')));
        need('dob', 'Enter your date of birth.');
        if (v('dob') && v('dob') > $('#dob').max) { setErr('dob', 'You must be at least 18 years old.'); ok = false; }
        need('experience', 'Enter years of experience (0–60).', !/^\d+$/.test(v('experience')) || +v('experience') > 60);
      }
      need('mobile', 'Use a Pakistani mobile number, e.g. 0300-1234567.', !reMob.test(v('mobile')));
      need('email', 'Enter a valid email. You use it to sign in.', !/^\S+@\S+\.\S+$/.test(v('email')));
      need('address', 'Enter your address.'); need('ref1', 'Add a reference.'); need('ref2', 'Add a second reference.');
      if (v('password').length < 8) { setErr('password', 'Use at least 8 characters.'); ok = false; }
      else need('password2', 'Passwords do not match.', v('password') !== v('password2'));
    }
    if (s === 2) {
      currentDocs.forEach(function (d) { if (d.req && !(docFiles[d.id] || []).length) { setErr('doc_' + d.id, 'Upload this document.'); ok = false; } });
      var total = 0; Object.keys(docFiles).forEach(function (k) { docFiles[k].forEach(function (f) { total += f.size; }); });
      if (total > D.limits.post - 512 * 1024) { alertBox('Your files add up to ' + mb(total) + '. The limit is ' + mb(D.limits.post) + '. Remove some portfolio photos or upload smaller files.'); ok = false; }
    }
    if (s === 3) {
      if (feeRow().tier !== 'P') {
        var p = v('payout').replace(/\s+/g, '').toUpperCase();
        need('payout', 'Enter a 24-character IBAN (PK…) or a mobile wallet number (03XX-XXXXXXX).', !/^PK\d{2}[A-Z]{4}[A-Z0-9]{16}$/.test(p) && !reMob.test(p));
      }
      if ($$('input[name="decl[]"]:checked').length < 4) { setErr('decl', 'Tick all four declarations to submit.'); ok = false; }
    }
    if (!ok) focusFirstError();
    return ok;
  }

  /* ---------- Navigation ---------- */
  function show(scroll) {
    $$('.preg-step').forEach(function (el) { el.hidden = +el.dataset.step !== step; });
    $$('#pregSteps li').forEach(function (li, i) { li.className = i < step ? 'done' : i === step ? 'on' : ''; if (i === step) li.setAttribute('aria-current', 'step'); else li.removeAttribute('aria-current'); });
    var names = $$('#pregSteps li').map(function (li) { return li.textContent; });
    $('#pregStepLabel').innerHTML = '<b>Step ' + (step + 1) + ' of ' + names.length + '</b> · ' + esc(names[step]);
    $('#pregBack').hidden = step === 0;
    $('#pregNext span').textContent = step === 3 ? 'Submit application' : 'Continue';
    var biz = isBiz(); $('#indivFields').hidden = biz; $('#bizFields').hidden = !biz;
    if (step === 2) renderDocs();
    if (step === 3) renderFees();
    if (scroll) $('#pregWrap').scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
  $('#pregBack').addEventListener('click', function () { alertBox(''); step--; show(true); });
  $('#pregNext').addEventListener('click', function () {
    alertBox('');
    if (!validate(step)) return;
    if (step < 3) { step++; show(true); } else submit();
  });
  $$('input[data-cnic]').forEach(function (el) {
    el.addEventListener('input', function () {
      var d = el.value.replace(/\D/g, '').slice(0, 13);
      el.value = d.length > 12 ? d.slice(0, 5) + '-' + d.slice(5, 12) + '-' + d.slice(12) : d.length > 5 ? d.slice(0, 5) + '-' + d.slice(5) : d;
    });
  });

  /* ---------- Submit ---------- */
  function submit() {
    if (busy) return; busy = true;
    var btn = $('#pregNext'); btn.disabled = true; $('#pregNext span').textContent = 'Submitting…';
    var fd = new FormData(form);
    Object.keys(docFiles).forEach(function (k) { docFiles[k].forEach(function (f) { fd.append('doc_' + k + '[]', f, f.name); }); });
    fetch(form.action, { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.ok) return done(res);
        if (typeof res.step === 'number' && res.step !== step) { step = res.step; show(false); }
        Object.keys(res.errors || {}).forEach(function (k) { if (k !== '_') setErr(k, res.errors[k]); });
        alertBox(res.message || 'Please fix the highlighted fields.');
        focusFirstError();
        if (!$('.preg-step:not([hidden]) .has-error')) $('#pregAlert').scrollIntoView({ behavior: 'smooth', block: 'center' });
      })
      .catch(function () { alertBox('We could not reach the server. Check your connection and try again.'); })
      .then(function () { busy = false; btn.disabled = false; if (!$('#pregDone').innerHTML) $('#pregNext span').textContent = 'Submit application'; });
  }
  function done(res) {
    var city = v('city'), wait = D.launch.indexOf(city) < 0;
    $('#pregWrap').hidden = true;
    var d = $('#pregDone'); d.hidden = false;
    d.innerHTML = '<span class="preg-done-ic" aria-hidden="true">✓</span><h2>Application submitted</h2><p>Your application ID</p><p class="preg-appid">' + esc(res.ref) + '</p>' +
      '<p>Next: we check your CNIC and mobile number, usually within 24 hours, then send a payment link for the application fee by SMS and WhatsApp.' +
      (wait ? ' You are on the waitlist for ' + esc(city) + '.' : '') + '</p><p class="preg-hint">You are signed in. You can track your verification from your dashboard.</p>' +
      '<a class="btn btn-gold" href="' + esc(res.redirect) + '"><span>Go to my dashboard</span></a>';
    d.focus(); d.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  fillCats();
  show(false);
})();
