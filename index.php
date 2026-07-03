<?php
$today = date('Y-m-d');

$events = [
    // ── Spring Training ──────────────────────────────────────────────────────
    '2026-05-20' => ['label' => 'Move In Day',                       'detail' => 'Fly via Nashville (BNA) · transport provided to Owensboro', 'type' => 'milestone'],
    '2026-05-25' => ['label' => 'Catholic FB Practice',              'detail' => '4–6 PM · Memorial Day',                                    'type' => 'practice'],
    '2026-05-26' => ['label' => 'Catholic FB Practice',              'detail' => '4–6 PM',                                                    'type' => 'practice'],
    '2026-05-27' => ['label' => 'Catholic FB Practice',              'detail' => '4–6 PM · Flag Football Stadium (Evening)',                  'type' => 'practice'],
    '2026-06-01' => ['label' => 'Catholic FB Practice',              'detail' => '4–6 PM',                                                    'type' => 'practice'],
    '2026-06-03' => ['label' => 'Catholic Football Practice',        'detail' => '4–6 PM · Flag Football Stadium (Evening)',                  'type' => 'practice'],
    '2026-06-04' => ['label' => 'Chapel in use @ 6 PM',              'detail' => '',                                                          'type' => 'note'],
    '2026-06-05' => ['label' => 'Football Camp',                     'detail' => '5–8 PM',                                                    'type' => 'practice'],
    '2026-06-08' => ['label' => 'Catholic FB Practice',              'detail' => '4–6 PM',                                                    'type' => 'practice'],
    '2026-06-10' => ['label' => 'Catholic FB Practice',              'detail' => '4–6 PM · Flag Football Stadium (Evening)',                  'type' => 'practice'],
    '2026-06-14' => ['label' => 'Community Performance',             'detail' => '',                                                          'type' => 'show'],
    '2026-06-15' => ['label' => 'Catholic FB Practice',              'detail' => '4–6 PM',                                                    'type' => 'practice'],
    '2026-06-16' => ['label' => 'Cleaning & Moving Day',             'detail' => 'Spring training ends',                                      'type' => 'milestone'],
    // ── Early Tour ────────────────────────────────────────────────────────────
    '2026-06-17' => ['label' => 'Travel — Evansville',               'detail' => 'H: Evansville North HS · EVV',                             'type' => 'travel'],
    '2026-06-18' => ['label' => 'Evansville Community Perf.',        'detail' => 'H: Evansville North HS',                                   'type' => 'show'],
    '2026-06-19' => ['label' => 'NIU Spring Training',               'detail' => 'H: NIU · ORD',                                             'type' => 'rehearsal'],
    '2026-06-20' => ['label' => 'NIU',                               'detail' => 'H: NIU · ORD',                                             'type' => 'rehearsal'],
    '2026-06-21' => ['label' => 'NIU',                               'detail' => 'H: NIU · ORD',                                             'type' => 'rehearsal'],
    '2026-06-22' => ['label' => 'NIU Dress Rehearsal',               'detail' => 'H: NIU · ORD',                                             'type' => 'rehearsal'],
    '2026-06-23' => ['label' => 'Concert in the Park — Depart Tour', 'detail' => 'H: NIU · ORD',                                             'type' => 'milestone'],
    '2026-06-24' => ['label' => 'Travel — Lincoln, NE',              'detail' => 'H: Lincoln Sports Foundation',                             'type' => 'travel'],
    '2026-06-25' => ['label' => 'Rehearsal',                         'detail' => 'H: Eaton HS, CO · No Fly',                                 'type' => 'rehearsal'],
    '2026-06-26' => ['label' => 'Rehearsal',                         'detail' => 'H: Eaton HS, CO · DEN',                                    'type' => 'rehearsal'],
    '2026-06-27' => ['label' => 'Fort Collins Show / DCI Denver',    'detail' => 'H: Eaton HS · No Fly',                                     'type' => 'show'],
    '2026-06-28' => ['label' => 'Denver Free Day + Laundry',         'detail' => 'H: Eaton HS · DEN',                                        'type' => 'free'],
    '2026-06-29' => ['label' => 'CSU Rehearsal',                     'detail' => 'H: Eaton HS · DEN',                                        'type' => 'rehearsal'],
    '2026-06-30' => ['label' => 'Omaha Area Rehearsal',              'detail' => 'H: Bellevue East HS, NE · OMA',                            'type' => 'rehearsal'],
    // ── July ─────────────────────────────────────────────────────────────────
    '2026-07-01' => ['label' => 'Omaha Show',                        'detail' => 'H: Bellevue East HS · OMA',                                'type' => 'show'],
    '2026-07-02' => ['label' => 'Travel',                            'detail' => 'H: Guilford · ORD',                                        'type' => 'travel'],
    '2026-07-03' => ['label' => 'Rockford Show',                     'detail' => 'H: Guilford · ORD',                                        'type' => 'show'],
    '2026-07-04' => ['label' => 'Laundry Day',                       'detail' => 'H: Guilford · ORD',                                        'type' => 'free'],
    '2026-07-05' => ['label' => 'La Crosse, WI Show',                'detail' => 'H: St. Charles HS, MN · No Fly',                           'type' => 'show'],
    '2026-07-06' => ['label' => 'NIU',                               'detail' => 'H: NIU · ORD',                                             'type' => 'rehearsal'],
    '2026-07-07' => ['label' => 'NIU',                               'detail' => 'H: NIU · ORD',                                             'type' => 'rehearsal'],
    '2026-07-08' => ['label' => 'NIU',                               'detail' => 'H: NIU · ORD',                                             'type' => 'rehearsal'],
    '2026-07-09' => ['label' => 'NIU',                               'detail' => 'H: NIU · ORD',                                             'type' => 'rehearsal'],
    '2026-07-10' => ['label' => 'Lisle Show',                        'detail' => 'H: Rockford University · ORD',                             'type' => 'show'],
    '2026-07-11' => ['label' => 'Whitewater Show',                   'detail' => 'H: Rockford University · No Fly',                          'type' => 'show'],
    '2026-07-12' => ['label' => 'Des Moines Transition Day',         'detail' => 'Visual clinic @ Theodore Roosevelt HS · No Fly',           'type' => 'travel'],
    '2026-07-13' => ['label' => 'Olathe Show',                       'detail' => 'H: Fort Osage HS, Independence · MCI',                    'type' => 'show'],
    '2026-07-14' => ['label' => 'Broken Arrow Show',                 'detail' => 'H: Owasso HS · TUL',                                      'type' => 'show'],
    '2026-07-15' => ['label' => 'Travel',                            'detail' => 'H: Naamen Forest HS · No Fly',                             'type' => 'travel'],
    '2026-07-16' => ['label' => 'Denton Show',                       'detail' => 'H: Naamen Forest HS · DFW',                               'type' => 'show'],
    '2026-07-17' => ['label' => 'Travel',                            'detail' => 'H: Midway High School · No Fly',                           'type' => 'travel'],
    '2026-07-18' => ['label' => 'San Antonio Show',                  'detail' => 'H: Pleasanton High School · SAT',                         'type' => 'show'],
    '2026-07-19' => ['label' => 'San Antonio Free Day + Laundry',    'detail' => 'H: Pleasanton High School · SAT',                         'type' => 'free'],
    '2026-07-20' => ['label' => 'McKinney Show',                     'detail' => 'H: Pilot Point Middle School · No Fly',               'type' => 'show'],
    '2026-07-21' => ['label' => 'Travel',                            'detail' => 'H: Collierville HS · No Fly',                             'type' => 'travel'],
    '2026-07-22' => ['label' => 'Evansville Show',                   'detail' => 'H: Evansville North HS · EVV',                            'type' => 'show'],
    '2026-07-23' => ['label' => 'Travel',                            'detail' => 'H: NIU · ORD (no van)',                                   'type' => 'travel'],
    '2026-07-24' => ['label' => 'Madison Show',                      'detail' => 'H: NIU · No Fly',                                         'type' => 'show'],
    '2026-07-25' => ['label' => 'NIU Show',                          'detail' => 'H: NIU · ORD',                                            'type' => 'show'],
    '2026-07-26' => ['label' => 'NIU Rehearsal',                     'detail' => 'H: NIU · ORD',                                            'type' => 'rehearsal'],
    '2026-07-27' => ['label' => 'Mason, OH Show',                    'detail' => 'H: TBD · CVG',                                            'type' => 'show'],
    '2026-07-28' => ['label' => 'Travel — Pennsylvania',             'detail' => 'H: Salamanca HS, NY · No Fly',                            'type' => 'travel'],
    '2026-07-29' => ['label' => 'Boston Free Day + Laundry',         'detail' => 'BOS',                                                     'type' => 'free'],
    '2026-07-30' => ['label' => 'Lawrence, MA Show',                 'detail' => 'BOS',                                                     'type' => 'show'],
    '2026-07-31' => ['label' => 'Travel — Pennsylvania',             'detail' => 'H: Wilson High School, Reading · No Fly',                 'type' => 'travel'],
    // ── August ───────────────────────────────────────────────────────────────
    '2026-08-01' => ['label' => 'Allentown Show',                    'detail' => 'H: Wilson High School · ABE',                             'type' => 'show'],
    '2026-08-02' => ['label' => 'Travel — Indiana',                  'detail' => 'H: Indiana University of PA',                            'type' => 'travel'],
    '2026-08-03' => ['label' => 'Lexington, KY Show',                'detail' => 'H: Lexington Catholic HS',                               'type' => 'show'],
    '2026-08-04' => ['label' => 'Rehearsal',                         'detail' => 'Carmel Dad\'s Club, Carmel IN · IND',                     'type' => 'rehearsal'],
    '2026-08-05' => ['label' => 'Rehearsal',                         'detail' => 'Carmel Dad\'s Club, Carmel IN · IND',                     'type' => 'rehearsal'],
    '2026-08-06' => ['label' => 'DCI PRELIMS',                       'detail' => 'Indianapolis, IN',                                        'type' => 'dci'],
    '2026-08-07' => ['label' => 'DCI SEMIFINALS',                    'detail' => 'Indianapolis, IN',                                        'type' => 'dci'],
    '2026-08-08' => ['label' => 'DCI FINALS',                        'detail' => 'Indianapolis, IN',                                        'type' => 'dci'],
    '2026-08-09' => ['label' => 'Banquet',                           'detail' => 'End of season',                                           'type' => 'milestone'],
];

$type_colors = [
    'milestone' => '#FFD97D',
    'show'      => '#7DD9A2',
    'dci'       => '#FFD700',
    'rehearsal' => '#5E9BD6',
    'practice'  => '#4FD1C0',
    'travel'    => '#7A7A85',
    'free'      => '#FFB59E',
    'note'      => '#7A7A85',
];
$type_icons = [
    'milestone' => '⭐',
    'show'      => '🎺',
    'dci'       => '🏆',
    'rehearsal' => '🥁',
    'practice'  => '📋',
    'travel'    => '✈',
    'free'      => '☀',
    'note'      => 'ℹ',
];

// Months to render
$months = [
    ['year' => 2026, 'month' => 5,  'name' => 'May 2026',    'phase' => 'Spring Training — Kentucky Wesleyan College, Owensboro KY'],
    ['year' => 2026, 'month' => 6,  'name' => 'June 2026',   'phase' => 'Spring Training → Summer Tour'],
    ['year' => 2026, 'month' => 7,  'name' => 'July 2026',   'phase' => 'Summer Tour'],
    ['year' => 2026, 'month' => 8,  'name' => 'August 2026', 'phase' => 'Summer Tour → DCI Championships · Indianapolis IN'],
];
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mateo · Phantom Regiment 2026</title>
<style>
:root {
  --bg:          #0A0A0B;
  --surface-1:   #161619;
  --surface-2:   #1E1E22;
  --surface-3:   #29292F;
  --primary:     #5E9BD6;
  --on-bg:       #F3F3F4;
  --on-var:      #9A9AA2;
  --outline:     #34343B;
  --outline-var: #1E1E22;
  --dci:         #FFD700;
  --show:        #7DD9A2;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
  background: var(--bg);
  color: var(--on-bg);
  font-size: 14px;
  line-height: 1.4;
  -webkit-font-smoothing: antialiased;
  padding-bottom: 60px;
}

/* ── Hero photo ── */
.hero {
  width: 100%;
  max-height: 480px;
  overflow: hidden;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  background: #0a0a0b;
}
.hero img {
  width: 100%;
  max-width: 680px;
  height: auto;
  max-height: 480px;
  object-fit: cover;
  object-position: center top;
  display: block;
}
.hero-placeholder {
  width: 100%;
  max-width: 680px;
  height: 300px;
  background: var(--surface-2);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--on-var);
  font-size: 0.8rem;
  border-bottom: 1px solid var(--outline);
}

/* ── Header ── */
.header {
  background: var(--surface-1);
  border-bottom: 1px solid var(--outline);
  padding: 28px 20px 22px;
  text-align: center;
}
.header .eyebrow {
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: .14em;
  text-transform: uppercase;
  color: var(--dci);
  margin-bottom: 6px;
}
.header h1 {
  font-size: clamp(1.5rem, 5vw, 2.2rem);
  font-weight: 800;
  letter-spacing: -.025em;
}
.header .sub {
  margin-top: 8px;
  font-size: 0.8rem;
  color: var(--on-var);
}

/* ── Legend ── */
.legend {
  display: flex;
  flex-wrap: wrap;
  gap: 6px 14px;
  justify-content: center;
  padding: 16px 20px 0;
  max-width: 860px;
  margin: 0 auto;
}
.legend-item {
  display: flex; align-items: center; gap: 5px;
  font-size: 0.72rem; color: var(--on-var);
}
.legend-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }

/* ── Month section ── */
.months { max-width: 860px; margin: 0 auto; padding: 0 12px; }
.month-section { margin-top: 28px; }
.month-header {
  padding: 14px 16px 10px;
  background: var(--surface-2);
  border: 1px solid var(--outline);
  border-radius: 12px 12px 0 0;
}
.month-header h2 { font-size: 1rem; font-weight: 700; }
.month-header .phase-note { font-size: 0.73rem; color: var(--on-var); margin-top: 3px; }

/* ── Calendar grid ── */
.cal-wrap {
  background: var(--surface-1);
  border: 1px solid var(--outline);
  border-top: none;
  border-radius: 0 0 12px 12px;
  overflow: hidden;
}
.cal-dow-row {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  border-bottom: 1px solid var(--outline);
}
.cal-dow {
  text-align: center;
  padding: 8px 4px;
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: .06em;
  text-transform: uppercase;
  color: var(--on-var);
}
.cal-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
}
.cal-cell {
  min-height: 80px;
  padding: 6px 7px;
  border-right: 1px solid var(--outline-var);
  border-bottom: 1px solid var(--outline-var);
  vertical-align: top;
  position: relative;
}
.cal-cell:nth-child(7n) { border-right: none; }
.cal-cell.empty { background: transparent; }
.cal-cell.today { background: rgba(94,155,214,.07); }
.cal-cell.today .day-num { color: var(--primary); font-weight: 800; }

.day-num {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--on-var);
  margin-bottom: 4px;
  display: block;
}

.event-pill {
  display: block;
  border-radius: 5px;
  padding: 3px 6px;
  margin-bottom: 3px;
  font-size: 0.67rem;
  font-weight: 600;
  line-height: 1.3;
  color: #000;
  cursor: default;
  overflow: hidden;
}
.event-pill.dci {
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: .02em;
}

/* Tooltip on hover */
.event-pill { position: relative; }
.event-pill:hover::after {
  content: attr(data-detail);
  position: absolute;
  bottom: calc(100% + 4px);
  left: 0;
  min-width: 160px;
  max-width: 240px;
  background: #29292F;
  color: #F3F3F4;
  border: 1px solid #34343B;
  border-radius: 6px;
  padding: 6px 9px;
  font-size: 0.72rem;
  font-weight: 400;
  white-space: normal;
  z-index: 10;
  pointer-events: none;
  box-shadow: 0 4px 12px rgba(0,0,0,.5);
}

/* Spring training note bar */
.st-bar {
  font-size: 0.62rem;
  color: #4FD1C0;
  margin-bottom: 3px;
  font-weight: 600;
  letter-spacing: .03em;
}

/* DCI info box */
.dci-info {
  margin: 0 12px 12px;
  background: rgba(255,215,0,.06);
  border: 1px solid rgba(255,215,0,.2);
  border-radius: 10px;
  padding: 14px 18px;
  font-size: 0.8rem;
}
.dci-info strong { color: var(--dci); display: block; margin-bottom: 6px; font-size: 0.85rem; }
.dci-info p { color: var(--on-var); margin-top: 4px; }

@media (max-width: 540px) {
  .cal-cell { min-height: 62px; padding: 4px 4px; }
  .event-pill { font-size: 0.6rem; padding: 2px 4px; }
  .event-pill:hover::after { display: none; }
}
</style>
</head>
<body>

<?php $hero = __DIR__ . '/assets/mateo.jpg'; ?>
<div class="hero">
  <?php if (file_exists($hero)): ?>
    <img src="/assets/mateo.jpg" alt="Mateo — Phantom Regiment 2026">
  <?php else: ?>
    <div class="hero-placeholder">Photo coming soon — place mateo.jpg in phantom/assets/</div>
  <?php endif; ?>
</div>

<header class="header">
  <div class="eyebrow">Phantom Regiment · 2026 Season</div>
  <h1>Mateo's Schedule</h1>
  <div class="sub">Move In: May 20 &nbsp;·&nbsp; Tour Departs: Jun 23 &nbsp;·&nbsp; DCI Finals: Aug 8</div>
</header>

<div class="legend">
  <?php foreach ([
    'show'      => ['#7DD9A2','Show'],
    'dci'       => ['#FFD700','DCI Championships'],
    'rehearsal' => ['#5E9BD6','Rehearsal / NIU'],
    'practice'  => ['#4FD1C0','Practice'],
    'travel'    => ['#7A7A85','Travel'],
    'free'      => ['#FFB59E','Free Day'],
    'milestone' => ['#FFD97D','Milestone'],
    'note'      => ['#7A7A85','Note'],
  ] as $t => [$c, $l]): ?>
  <div class="legend-item">
    <div class="legend-dot" style="background:<?= $c ?>"></div>
    <?= $l ?>
  </div>
  <?php endforeach; ?>
</div>

<div class="months">
<?php foreach ($months as $m):
  $year = $m['year']; $mon = $m['month'];
  $first_dow = (int)date('w', mktime(0,0,0,$mon,1,$year)); // 0=Sun
  $days_in_month = (int)date('t', mktime(0,0,0,$mon,1,$year));
?>
<section class="month-section">
  <div class="month-header">
    <h2><?= $m['name'] ?></h2>
    <div class="phase-note"><?= htmlspecialchars($m['phase']) ?></div>
  </div>
  <div class="cal-wrap">
    <div class="cal-dow-row">
      <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d): ?>
        <div class="cal-dow"><?= $d ?></div>
      <?php endforeach; ?>
    </div>
    <div class="cal-grid">
      <?php
      // leading empty cells
      for ($i = 0; $i < $first_dow; $i++) echo '<div class="cal-cell empty"></div>';

      for ($day = 1; $day <= $days_in_month; $day++):
        $date_str = sprintf('%04d-%02d-%02d', $year, $mon, $day);
        $ev = $events[$date_str] ?? null;
        $is_today = ($date_str === $today);
        $classes = 'cal-cell' . ($is_today ? ' today' : '') . (!$ev ? ' no-event' : '');
      ?>
      <div class="<?= $classes ?>">
        <span class="day-num"><?= $day ?></span>
        <?php if ($ev):
          $type   = $ev['type'];
          $color  = $type_colors[$type];
          $icon   = $type_icons[$type];
          $detail = $ev['detail'];
          $pill_classes = 'event-pill' . ($type === 'dci' ? ' dci' : '');
        ?>
        <span class="<?= $pill_classes ?>"
              style="background:<?= $color ?>"
              data-detail="<?= htmlspecialchars($detail) ?>"><?= $icon ?> <?= htmlspecialchars($ev['label']) ?></span>
        <?php endif; ?>
      </div>
      <?php endfor; ?>

      <?php
      // trailing empty cells to complete last row
      $total = $first_dow + $days_in_month;
      $trailing = (7 - ($total % 7)) % 7;
      for ($i = 0; $i < $trailing; $i++) echo '<div class="cal-cell empty"></div>';
      ?>
    </div>
  </div>
</section>
<?php endforeach; ?>

<div class="dci-info" style="margin-top:16px;">
  <strong>🏆 DCI Championships — Indianapolis, IN (Aug 6–9)</strong>
  <p>Housing: Lawrence Armory · 9920 E 59th St, Indianapolis IN 46216</p>
  <p>Rehearsal (Tue–Fri): Carmel Dad's Club · 5459 E Main St, Carmel IN 46033</p>
</div>
</div>

</body>
</html>
