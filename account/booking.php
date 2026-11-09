<?php
require __DIR__ . '/_layout.php';
$u = require_user();

$b = row('SELECT b.*, p.name AS provider_name, p.phone AS provider_phone, p.subcategory AS provider_sub, c.name AS cust_name
          FROM bookings b LEFT JOIN users p ON p.id = b.provider_id LEFT JOIN users c ON c.id = b.customer_id WHERE b.id = ?', [(int)get('id')]);

// Access rules: customer owns it; provider is assigned, or it is an open request.
$isCust = $b && $u['role'] === 'customer' && (int)$b['customer_id'] === (int)$u['id'];
$isProv = $b && $u['role'] === 'provider' && ((int)$b['provider_id'] === (int)$u['id'] || ($b['status'] === 'requested' && !$b['provider_id']));
if (!$isCust && !$isProv) { flash('error', 'Booking not found.'); redirect('account/index.php'); }

$offers = rows('SELECT * FROM booking_offers WHERE booking_id = ? ORDER BY id', [$b['id']]);
$fb = row('SELECT * FROM booking_feedback WHERE booking_id = ?', [$b['id']]);
$lastOffer = $offers ? end($offers) : null;
$chatOpen = in_array($b['status'], ['deal_locked', 'in_progress', 'completed'], true);
$isMine = $isProv && (int)$b['provider_id'] === (int)$u['id'];

/* Work out the "what happens next" banner for the viewer. */
$banner = ['muted-b', 'clock', 'Waiting', ''];
$s = $b['status'];
if ($isCust) {
    if ($s === 'requested')        $banner = ['muted-b', 'clock', 'Waiting for a provider', 'Your request is live. A verified provider will accept it and share a quotation.'];
    elseif ($s === 'provider_joined') $banner = ['muted-b', 'clock', 'Waiting on provider', $lastOffer && $lastOffer['by_role'] === 'customer' ? 'Your budget was sent. The provider will reply with a quotation.' : 'A provider joined. Share or update your budget so they can quote.'];
    elseif ($s === 'quoted')       $banner = ['', 'bell', 'Action needed: review the quotation', 'Accept the offer to lock the deal, or send a counter budget.'];
    elseif ($s === 'deal_locked')  $banner = ['muted-b', 'chat', 'Deal locked — chat is open', 'The provider can start the job. Message them any time below.'];
    elseif ($s === 'in_progress')  $banner = ['', 'bell', 'Action needed: mark completed', 'When the work is done to your satisfaction, mark the job completed.'];
    elseif ($s === 'completed')    $banner = $fb ? ['muted-b', 'check-circle', 'Job completed', 'Feedback submitted — thank you.'] : ['', 'star', 'Action needed: leave feedback', 'Rate your provider so others can trust verified professionals.'];
    elseif ($s === 'cancelled')    $banner = ['muted-b', 'x', 'Booking cancelled', 'This booking is closed and no further action is needed.'];
} else {
    if ($s === 'requested')        $banner = ['', 'inbox', 'Open request', 'Accept this booking to claim it, optionally with an initial quotation.'];
    elseif ($s === 'provider_joined') $banner = ['', 'tag', 'Action needed: send a quotation', 'Share your price so the customer can accept or counter.'];
    elseif ($s === 'quoted')       $banner = ['muted-b', 'clock', 'Waiting for customer', 'Your quotation has been shared. The customer can accept or counter.'];
    elseif ($s === 'deal_locked')  $banner = ['', 'bolt', 'Action needed: start the job', 'The deal is locked and chat is open. Start when you begin the work.'];
    elseif ($s === 'in_progress')  $banner = ['muted-b', 'clock', 'Job in progress', 'Only the customer can mark this completed when they are satisfied.'];
    elseif ($s === 'completed')    $banner = ['muted-b', 'check-circle', 'Job completed', 'This booking cycle is finished.'];
    elseif ($s === 'cancelled')    $banner = ['muted-b', 'x', 'Booking cancelled', 'This booking is closed.'];
}

portal_start('Booking ' . $b['ref'], $b['service'] . ' · ' . $b['city']);
?>
<div class="action-banner <?= e($banner[0]) ?>"><span class="ab-ic"><?= icon($banner[1]) ?></span><div><strong><?= e($banner[2]) ?></strong><span><?= e($banner[3]) ?></span></div>
  <div style="margin-left:auto;position:relative;z-index:1"><?= status_pill($s) ?></div>
</div>

<?php booking_steps($s); ?>

<div class="row-2">
  <div>
    <div class="card">
      <div class="card-head"><div><h2>Booking details</h2><p>Created <?= e(date('d M Y, g:i A', strtotime($b['created_at']))) ?></p></div>
        <a class="b b-ghost b-sm" href="<?= $u['role'] === 'provider' ? ($isMine ? 'jobs.php' : 'requests.php') : 'bookings.php' ?>"><?= icon('chev') ?>Back</a></div>
      <div class="card-body">
        <div class="dl">
          <div><small>Reference</small><strong class="mono"><?= e($b['ref']) ?></strong></div>
          <div><small>Category</small><strong><?= e($b['category']) ?></strong></div>
          <div><small>Service</small><strong><?= e($b['service']) ?></strong></div>
          <div><small>Urgency</small><strong><?= e(ucfirst($b['urgency'])) ?></strong></div>
          <div><small>Preferred date</small><strong><?= $b['preferred_date'] ? e(date('d M Y', strtotime($b['preferred_date']))) : 'Flexible' ?></strong></div>
          <div><small>Payment</small><strong><?= e($b['payment_method'] ?: '—') ?></strong></div>
          <div><small>Customer budget</small><strong><?= $b['budget'] ? e(money($b['budget'])) : '—' ?></strong></div>
          <div><small>Agreed deal</small><strong><?= $b['deal_amount'] && in_array($s, ['deal_locked', 'in_progress', 'completed'], true) ? e(money($b['deal_amount'])) : ($lastOffer && $lastOffer['by_role'] === 'provider' ? 'Offer: ' . e(money($lastOffer['amount'])) : '—') ?></strong></div>
          <div><small>City</small><strong><?= e($b['city']) ?></strong></div>
          <?php if ($isCust || $isMine): ?>
            <div><small>Address</small><strong><?= e($b['address']) ?></strong></div>
            <?php if ($b['map_link']): ?><div class="full"><small>Map</small><strong><a class="link-arrow" href="<?= e($b['map_link']) ?>" target="_blank" rel="noopener">Open location <?= icon('arrow-ur') ?></a></strong></div><?php endif; ?>
          <?php endif; ?>
          <div class="full"><small>Description</small><strong style="font-weight:400"><?= nl($b['description']) ?></strong></div>
          <?php if ($isCust): ?>
            <div><small>Provider</small><strong><?= e($b['provider_name'] ?: 'Not assigned yet') ?></strong></div>
            <div><small>Provider phone</small><strong><?= $chatOpen && $b['provider_phone'] ? e($b['provider_phone']) : '<span class="muted">Shared after deal</span>' ?></strong></div>
          <?php elseif ($isMine): ?>
            <div><small>Customer</small><strong><?= e($b['name']) ?></strong></div>
            <div><small>Customer phone</small><strong><?= $chatOpen ? e($b['phone']) : '<span class="muted">Shared after deal</span>' ?></strong></div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if ($chatOpen && ($isCust || $isMine)): ?>
    <div class="card">
      <div class="card-head"><div><h2>Booking chat</h2><p>Message the other party about this job</p></div></div>
      <div class="chat" id="chat" data-booking="<?= (int)$b['id'] ?>">
        <div class="chat-log" id="chatLog"></div>
        <form class="chat-form" id="chatForm"><input class="input" name="message" placeholder="Type a message…" maxlength="1000" autocomplete="off"><button class="b" type="submit"><?= icon('arrow') ?>Send</button></form>
      </div>
    </div>
    <?php elseif ($isCust || $isMine): ?>
    <div class="card"><div class="chat-locked"><?= icon('lock') ?><br>Chat unlocks after the customer accepts the provider's quotation.</div></div>
    <?php endif; ?>
  </div>

  <div>
    <!-- ACTIONS -->
    <div class="card">
      <div class="card-head"><div><h2>Actions</h2><p>What you can do now</p></div></div>
      <div class="card-body">
        <?php if ($isProv && $s === 'requested'): ?>
          <?php if ($u['status'] !== 'active'): ?>
            <div class="alert alert-info"><?= icon('clock') ?><span>Your profile must be verified before you can accept jobs.</span></div>
          <?php else: ?>
          <form data-action-form>
            <?= csrf_field() ?><input type="hidden" name="action" value="accept"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
            <div class="field"><label>Initial quotation (PKR) — optional</label><input class="input" type="number" min="0" name="amount" placeholder="<?= $b['budget'] ? e((int)$b['budget']) : 'e.g. 3500' ?>"></div>
            <div class="field"><label>Note to customer</label><textarea class="input" name="message" maxlength="500" placeholder="What's included, timing, materials…"></textarea></div>
            <button class="b b-gold" type="submit"><?= icon('check') ?>Accept booking</button>
          </form>
          <?php endif; ?>

        <?php elseif ($isMine && in_array($s, ['provider_joined', 'quoted'], true)): ?>
          <form data-action-form>
            <?= csrf_field() ?><input type="hidden" name="action" value="quote"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
            <div class="field"><label><?= $s === 'quoted' ? 'Update quotation' : 'Send quotation' ?> (PKR)</label><input class="input" type="number" min="1" name="amount" required></div>
            <div class="field"><label>Note</label><textarea class="input" name="message" maxlength="500"></textarea></div>
            <button class="b b-gold" type="submit"><?= icon('tag') ?><?= $s === 'quoted' ? 'Update quotation' : 'Send quotation' ?></button>
            <button class="b b-danger" type="button" data-action="release" data-id="<?= (int)$b['id'] ?>" data-confirm="Release this job back to open requests?">Release job</button>
          </form>

        <?php elseif ($isMine && $s === 'deal_locked'): ?>
          <button class="b b-gold" data-action="start" data-id="<?= (int)$b['id'] ?>" data-confirm="Start this job? The customer will be notified."><?= icon('bolt') ?>Start job</button>
          <button class="b b-danger" data-action="release" data-id="<?= (int)$b['id'] ?>" data-confirm="Release this job? It will return to open requests.">Release job</button>

        <?php elseif ($isCust && $s === 'quoted'): ?>
          <p style="margin:0 0 14px">Current offer: <b style="font-family:var(--f-alt);font-size:1.3rem"><?= e(money($lastOffer['amount'] ?? $b['deal_amount'])) ?></b></p>
          <button class="b b-gold" data-action="accept_deal" data-id="<?= (int)$b['id'] ?>" data-confirm="Accept this quotation and lock the deal?"><?= icon('check') ?>Accept deal</button>
          <hr style="border:0;border-top:1px solid var(--db-border);margin:18px 0">
          <form data-action-form>
            <?= csrf_field() ?><input type="hidden" name="action" value="counter"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
            <div class="field"><label>Counter budget (PKR)</label><input class="input" type="number" min="1" name="amount" required></div>
            <div class="field"><label>Optional message</label><input class="input" name="message" maxlength="500"></div>
            <button class="b b-ghost" type="submit">Send counter</button>
          </form>

        <?php elseif ($isCust && $s === 'provider_joined'): ?>
          <form data-action-form>
            <?= csrf_field() ?><input type="hidden" name="action" value="counter"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
            <div class="field"><label>Share / update your budget (PKR)</label><input class="input" type="number" min="1" name="amount" value="<?= $b['budget'] ? (int)$b['budget'] : '' ?>" required></div>
            <div class="field"><label>Optional message</label><input class="input" name="message" maxlength="500"></div>
            <button class="b" type="submit">Send budget</button>
          </form>

        <?php elseif ($isCust && $s === 'in_progress'): ?>
          <button class="b b-gold" data-action="complete" data-id="<?= (int)$b['id'] ?>" data-confirm="Confirm only when the work is finished to your satisfaction. This closes the booking."><?= icon('check-circle') ?>Mark job completed</button>

        <?php elseif ($isCust && $s === 'completed' && !$fb): ?>
          <form data-action-form>
            <?= csrf_field() ?><input type="hidden" name="action" value="feedback"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
            <div class="field"><span class="label">Overall rating</span>
              <div class="stars-input"><?php for ($i = 5; $i >= 1; $i--): ?><input type="radio" id="r<?= $i ?>" name="rating" value="<?= $i ?>"><label for="r<?= $i ?>"><?= icon('star') ?></label><?php endfor; ?></div>
            </div>
            <div class="form-grid">
              <?php foreach (['professionalism', 'communication', 'punctuality', 'cleanliness', 'behaviour'] as $k): ?>
                <div class="field"><label><?= e(ucfirst($k)) ?></label><select class="input" name="<?= $k ?>"><?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>"><?= str_repeat('★', $i) ?></option><?php endfor; ?></select></div>
              <?php endforeach; ?>
              <div class="field"><label>Recommend?</label><select class="input" name="recommend"><option value="1">Yes</option><option value="0">No</option></select></div>
            </div>
            <div class="field"><label>Optional comments</label><textarea class="input" name="comments" maxlength="1000"></textarea></div>
            <button class="b b-gold" type="submit"><?= icon('star') ?>Submit feedback</button>
          </form>

        <?php else: ?>
          <p class="muted" style="margin:0">No action needed right now.</p>
        <?php endif; ?>

        <?php if ($isCust && in_array($s, ['requested', 'provider_joined', 'quoted'], true)): ?>
          <div style="margin-top:16px"><button class="b b-danger b-sm" data-action="cancel" data-id="<?= (int)$b['id'] ?>" data-confirm="Cancel this booking?">Cancel booking</button></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-head"><div><h2>Budget &amp; quotation trail</h2></div></div>
      <div class="card-body">
        <?php if (!$offers): ?><p class="muted" style="margin:0">No offers yet.</p><?php else: ?>
        <ul class="trail">
          <?php foreach ($offers as $o): ?>
            <li><span class="who <?= e($o['by_role']) ?>"><?= $o['by_role'] === 'customer' ? 'CU' : 'PR' ?></span>
              <div><strong><?= e(money($o['amount'])) ?></strong> <span class="muted">· <?= $o['by_role'] === 'customer' ? 'Customer budget' : 'Provider quotation' ?> · <?= e(time_ago($o['created_at'])) ?></span><?php if ($o['message']): ?><br><?= e($o['message']) ?><?php endif; ?></div></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($fb): ?>
    <div class="card">
      <div class="card-head"><div><h2>Customer feedback</h2><p>Rating left after completion</p></div></div>
      <div class="card-body">
        <div style="font-size:1.6rem;color:var(--gold)"><?= str_repeat('★', (int)$fb['rating']) ?><span style="color:var(--db-border)"><?= str_repeat('★', 5 - (int)$fb['rating']) ?></span></div>
        <p class="muted">Recommend: <?= $fb['recommend'] ? 'Yes' : 'No' ?></p>
        <?php if ($fb['comments']): ?><p style="margin:0">“<?= e($fb['comments']) ?>”</p><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php portal_end(); ?>
