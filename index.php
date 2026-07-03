<?php
$today    = date('Y-m-d');
$today_ts = strtotime($today);

// Announcement + score
$_announcement = '';
$_ann_file = __DIR__ . '/data/announcement.txt';
if (file_exists($_ann_file)) $_announcement = trim(file_get_contents($_ann_file));
$_score = [];
$_score_file = __DIR__ . '/data/score.json';
if (file_exists($_score_file)) $_score = json_decode(file_get_contents($_score_file), true) ?: [];
$_scores_history = [];
$_hist_file = __DIR__ . '/data/scores_history.json';
if (file_exists($_hist_file)) $_scores_history = json_decode(file_get_contents($_hist_file), true) ?: [];

$_days_to_finals = max(0, (int)ceil((strtotime('2026-08-08') - $today_ts) / 86400));

// Messages DB
$_msg_db_path = __DIR__ . '/data/messages.db';
if (!is_dir(__DIR__ . '/data')) mkdir(__DIR__ . '/data', 0755, true);
$_msg_db = new PDO('sqlite:' . $_msg_db_path);
$_msg_db->exec("CREATE TABLE IF NOT EXISTS messages (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, message TEXT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
$_ticker_msgs = $_msg_db->query("SELECT name, message FROM messages ORDER BY created_at DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
$_msg_count = (int)$_msg_db->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$_photo_count = (int)$_msg_db->query("SELECT COUNT(*) FROM messages WHERE image_path IS NOT NULL")->fetchColumn();
$events = [
    '2026-05-20' => ['label' => 'Move In Day',                       'detail' => 'Fly via Nashville (BNA) · transport provided to Owensboro', 'type' => 'milestone', 'city' => 'Owensboro, KY'],
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
    '2026-06-17' => ['label' => 'Travel — Evansville',               'detail' => 'H: Evansville North HS · EVV',                             'type' => 'travel',    'city' => 'Evansville, IN'],
    '2026-06-18' => ['label' => 'Evansville Community Perf.',        'detail' => 'H: Evansville North HS',                                   'type' => 'show'],
    '2026-06-19' => ['label' => 'NIU Spring Training',               'detail' => 'H: NIU · ORD',                                             'type' => 'rehearsal', 'city' => 'DeKalb, IL'],
    '2026-06-20' => ['label' => 'NIU',                               'detail' => 'H: NIU · ORD',                                             'type' => 'rehearsal'],
    '2026-06-21' => ['label' => 'NIU',                               'detail' => 'H: NIU · ORD',                                             'type' => 'rehearsal'],
    '2026-06-22' => ['label' => 'NIU Dress Rehearsal',               'detail' => 'H: NIU · ORD',                                             'type' => 'rehearsal'],
    '2026-06-23' => ['label' => 'Concert in the Park — Depart Tour', 'detail' => 'H: NIU · ORD',                                             'type' => 'milestone'],
    '2026-06-24' => ['label' => 'Travel — Lincoln, NE',              'detail' => 'H: Lincoln Sports Foundation',                             'type' => 'travel',    'city' => 'Lincoln, NE'],
    '2026-06-25' => ['label' => 'Rehearsal',                         'detail' => 'H: Eaton HS, CO · No Fly',                                 'type' => 'rehearsal', 'city' => 'Eaton, CO'],
    '2026-06-26' => ['label' => 'Rehearsal',                         'detail' => 'H: Eaton HS, CO · DEN',                                    'type' => 'rehearsal'],
    '2026-06-27' => ['label' => 'Fort Collins Show / DCI Denver',    'detail' => 'H: Eaton HS · No Fly',                                     'type' => 'show'],
    '2026-06-28' => ['label' => 'Denver Free Day + Laundry',         'detail' => 'H: Eaton HS · DEN',                                        'type' => 'free'],
    '2026-06-29' => ['label' => 'CSU Rehearsal',                     'detail' => 'H: Eaton HS · DEN',                                        'type' => 'rehearsal'],
    '2026-06-30' => ['label' => 'Omaha Area Rehearsal',              'detail' => 'H: Bellevue East HS, NE · OMA',                            'type' => 'rehearsal', 'city' => 'Bellevue, NE'],
    '2026-07-01' => ['label' => 'Omaha Show',                        'detail' => 'H: Bellevue East HS · OMA',                                'type' => 'show'],
    '2026-07-02' => ['label' => 'Travel',                            'detail' => 'H: Guilford · ORD',                                        'type' => 'travel',    'city' => 'Rockford, IL'],
    '2026-07-03' => ['label' => 'Rockford Show',                     'detail' => 'H: Guilford · ORD',                                        'type' => 'show'],
    '2026-07-04' => ['label' => 'Laundry Day',                       'detail' => 'H: Guilford · ORD',                                        'type' => 'free'],
    '2026-07-05' => ['label' => 'La Crosse, WI Show',                'detail' => 'H: St. Charles HS, MN · No Fly',                           'type' => 'show',      'city' => 'St. Charles, MN'],
    '2026-07-06' => ['label' => 'NIU',                               'detail' => 'H: NIU · ORD',                                             'type' => 'rehearsal', 'city' => 'DeKalb, IL'],
    '2026-07-07' => ['label' => 'NIU',                               'detail' => 'H: NIU · ORD',                                             'type' => 'rehearsal'],
    '2026-07-08' => ['label' => 'NIU',                               'detail' => 'H: NIU · ORD',                                             'type' => 'rehearsal'],
    '2026-07-09' => ['label' => 'NIU',                               'detail' => 'H: NIU · ORD',                                             'type' => 'rehearsal'],
    '2026-07-10' => ['label' => 'Lisle Show',                        'detail' => 'H: Rockford University · ORD',                             'type' => 'show',      'city' => 'Rockford, IL'],
    '2026-07-11' => ['label' => 'Whitewater Show',                   'detail' => 'H: Rockford University · No Fly',                          'type' => 'show'],
    '2026-07-12' => ['label' => 'Des Moines Transition Day',         'detail' => 'Visual clinic @ Theodore Roosevelt HS · No Fly',           'type' => 'travel',    'city' => 'Des Moines, IA'],
    '2026-07-13' => ['label' => 'Olathe Show',                       'detail' => 'H: Fort Osage HS, Independence · MCI',                    'type' => 'show',      'city' => 'Independence, MO'],
    '2026-07-14' => ['label' => 'Broken Arrow Show',                 'detail' => 'H: Owasso HS · TUL',                                      'type' => 'show',      'city' => 'Owasso, OK'],
    '2026-07-15' => ['label' => 'Travel',                            'detail' => 'H: Naamen Forest HS · No Fly',                             'type' => 'travel',    'city' => 'Denton, TX'],
    '2026-07-16' => ['label' => 'Denton Show',                       'detail' => 'H: Naamen Forest HS · DFW',                               'type' => 'show'],
    '2026-07-17' => ['label' => 'Travel',                            'detail' => 'H: Midway High School · No Fly',                           'type' => 'travel',    'city' => 'Pleasanton, TX'],
    '2026-07-18' => ['label' => 'San Antonio Show',                  'detail' => 'H: Pleasanton High School · SAT',                         'type' => 'show'],
    '2026-07-19' => ['label' => 'San Antonio Free Day + Laundry',    'detail' => 'H: Pleasanton High School · SAT',                         'type' => 'free'],
    '2026-07-20' => ['label' => 'McKinney Show',                     'detail' => 'H: Pilot Point Middle School · No Fly',                   'type' => 'show',      'city' => 'Pilot Point, TX'],
    '2026-07-21' => ['label' => 'Travel',                            'detail' => 'H: Collierville HS · No Fly',                             'type' => 'travel',    'city' => 'Collierville, TN'],
    '2026-07-22' => ['label' => 'Evansville Show',                   'detail' => 'H: Evansville North HS · EVV',                            'type' => 'show',      'city' => 'Evansville, IN'],
    '2026-07-23' => ['label' => 'Travel',                            'detail' => 'H: NIU · ORD (no van)',                                   'type' => 'travel',    'city' => 'DeKalb, IL'],
    '2026-07-24' => ['label' => 'Madison Show',                      'detail' => 'H: NIU · No Fly',                                         'type' => 'show'],
    '2026-07-25' => ['label' => 'NIU Show',                          'detail' => 'H: NIU · ORD',                                            'type' => 'show'],
    '2026-07-26' => ['label' => 'NIU Rehearsal',                     'detail' => 'H: NIU · ORD',                                            'type' => 'rehearsal'],
    '2026-07-27' => ['label' => 'Mason, OH Show',                    'detail' => 'H: TBD · CVG',                                            'type' => 'show',      'city' => 'Mason, OH'],
    '2026-07-28' => ['label' => 'Travel — Pennsylvania',             'detail' => 'H: Salamanca HS, NY · No Fly',                            'type' => 'travel',    'city' => 'Salamanca, NY'],
    '2026-07-29' => ['label' => 'Boston Free Day + Laundry',         'detail' => 'BOS',                                                     'type' => 'free',      'city' => 'Lawrence, MA'],
    '2026-07-30' => ['label' => 'Lawrence, MA Show',                 'detail' => 'BOS',                                                     'type' => 'show'],
    '2026-07-31' => ['label' => 'Travel — Pennsylvania',             'detail' => 'H: Wilson High School, Reading · No Fly',                 'type' => 'travel',    'city' => 'Reading, PA'],
    '2026-08-01' => ['label' => 'Allentown Show',                    'detail' => 'H: Wilson High School · ABE',                             'type' => 'show'],
    '2026-08-02' => ['label' => 'Travel — Indiana',                  'detail' => 'H: Indiana University of PA',                            'type' => 'travel',    'city' => 'Indiana, PA'],
    '2026-08-03' => ['label' => 'Lexington, KY Show',                'detail' => 'H: Lexington Catholic HS',                               'type' => 'show',      'city' => 'Lexington, KY'],
    '2026-08-04' => ['label' => 'Rehearsal',                         'detail' => "Carmel Dad\'s Club, Carmel IN · IND",                     'type' => 'rehearsal', 'city' => 'Carmel, IN'],
    '2026-08-05' => ['label' => 'Rehearsal',                         'detail' => "Carmel Dad\'s Club, Carmel IN · IND",                     'type' => 'rehearsal'],
    '2026-08-06' => ['label' => 'DCI PRELIMS',                       'detail' => 'Indianapolis, IN',                                        'type' => 'dci',       'city' => 'Indianapolis, IN'],
    '2026-08-07' => ['label' => 'DCI SEMIFINALS',                    'detail' => 'Indianapolis, IN',                                        'type' => 'dci'],
    '2026-08-08' => ['label' => 'DCI FINALS',                        'detail' => 'Indianapolis, IN',                                        'type' => 'dci'],
    '2026-08-09' => ['label' => 'Banquet',                           'detail' => 'End of season',                                           'type' => 'milestone'],
];

// Judged (DCI-scored) shows from official DCI schedule PDF
$_judged_dates = [
    '2026-07-03','2026-07-05','2026-07-10','2026-07-11',
    '2026-07-13','2026-07-14','2026-07-16','2026-07-18','2026-07-20',
    '2026-07-22','2026-07-24','2026-07-25','2026-07-27','2026-07-30',
    '2026-08-01','2026-08-03',
];
$_dci_show_names = [
    '2026-07-03' => 'Show of Shows',
    '2026-07-05' => 'River City Rhapsody',
    '2026-07-10' => 'Cavalcade of Brass',
    '2026-07-11' => 'The Whitewater Classic',
    '2026-07-13' => 'Brass Impact',
    '2026-07-14' => 'DCI Broken Arrow',
    '2026-07-16' => 'DCI Denton',
    '2026-07-18' => 'DCI Southwestern Championship',
    '2026-07-20' => 'DCI McKinney',
    '2026-07-22' => 'Drums on the Ohio',
    '2026-07-24' => 'Drums on Parade',
    '2026-07-25' => 'Midwestern Championship',
    '2026-07-27' => 'Summer Music Games in Cincinnati',
    '2026-07-30' => 'DCI East Coast Showcase',
    '2026-08-01' => 'DCI Eastern Classic',
    '2026-08-03' => 'DCI Kentucky',
    '2026-08-06' => 'DCI World Championship Prelims',
    '2026-08-07' => 'DCI World Championship Semifinals',
    '2026-08-08' => 'DCI World Championship Finals',
];
// All judged dates (regular shows + DCI events)
$_all_judged = array_merge($_judged_dates, ['2026-08-06','2026-08-07','2026-08-08']);

// Show Night Mode: today is a scored competition
$_is_show_night = in_array($today, $_all_judged);
$_tonight_event = $_is_show_night && isset($events[$today]) ? $events[$today] : null;
$_tonight_dci_show = $_dci_show_names[$today] ?? null;

// Season progress (must be after $events)
$_total_shows = 0; $_past_shows = 0;
foreach ($events as $date_str => $ev) {
    if (in_array($ev['type'], ['show','dci'])) {
        $_total_shows++;
        if (strtotime($date_str) <= $today_ts) $_past_shows++;
    }
}

// Next upcoming show
$next_event = null;
foreach ($events as $date_str => $ev) {
    if (strtotime($date_str) >= $today_ts && in_array($ev['type'], ['show','dci'])) {
        $next_event = ['date' => $date_str] + $ev;
        break;
    }
}
if ($next_event) {
    $ne_ts       = strtotime($next_event['date']);
    $ne_dow      = date('l', $ne_ts);
    $ne_date_fmt = date('F j, Y', $ne_ts);
    $ne_detail   = $next_event['detail'];
    $ne_location = '';
    if (preg_match('/H:\s*([^·]+)/u', $ne_detail, $m)) {
        $ne_location = trim($m[1]);
    }
}

// Current location: most recent event on or before today that has a city
$current_city = null;
foreach ($events as $date_str => $ev) {
    if (strtotime($date_str) <= $today_ts && !empty($ev['city'])) {
        $current_city = $ev['city'];
    }
}

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
    'milestone' => '*',
    'show'      => 'Show',
    'dci'       => 'DCI',
    'rehearsal' => 'Rehearsal',
    'practice'  => 'Practice',
    'travel'    => 'Travel',
    'free'      => 'Free',
    'note'      => 'Note',
];
$months = [
    ['year' => 2026, 'month' => 5,  'name' => 'May 2026',    'phase' => 'Spring Training — Kentucky Wesleyan College, Owensboro KY'],
    ['year' => 2026, 'month' => 6,  'name' => 'June 2026',   'phase' => 'Spring Training → Summer Tour'],
    ['year' => 2026, 'month' => 7,  'name' => 'July 2026',   'phase' => 'Summer Tour'],
    ['year' => 2026, 'month' => 8,  'name' => 'August 2026', 'phase' => 'Summer Tour → DCI Championships · Indianapolis IN'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Keep up with Matéo — Phantom Regiment 2026</title>
  <meta name="description" content="Follow Matéo's 2026 DCI season with Phantom Regiment performing Bloodline. Dates, tickets, and ways to watch." />
  <meta property="og:title" content="Keep up with Matéo — Phantom Regiment 2026" />
  <meta property="og:description" content="Follow Matéo's 2026 DCI season with Phantom Regiment performing Bloodline. Dates, tickets, and ways to watch." />
  <meta property="og:image" content="https://phantom.agavelabs.dev/assets/mateo.jpg" />
  <meta property="og:url" content="https://phantom.agavelabs.dev" />
  <meta property="og:type" content="website" />
  <meta name="twitter:card" content="summary_large_image" />
  <link rel="icon" href="/assets/favicon.png" type="image/png" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
          --red: #B01A1C;
          --red-dark: #8A1214;
          --red-light: rgba(176,26,28,0.12);
          --page-bg: #111111;
          --surface: #1A1A1A;
          --surface-2: #222222;
          --text: #F2F0EA;
          --text-secondary: #A8A49C;
          --text-muted: #666360;
          --border: rgba(255,255,255,0.08);
          --border-strong: rgba(255,255,255,0.14);
          --radius: 14px;
          --green: #4CAF72;
          --green-bg: rgba(76,175,114,0.12);
        }

    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: var(--page-bg); color: var(--text); min-height: 100vh; padding-bottom: <?= !empty($_ticker_msgs) ? '4.5rem' : '3rem' ?>; font-size: 16px; }

    .hero { position: relative; width: 100%; height: clamp(416px, 65vw, 676px); overflow: hidden; background: #000; }
    .hero > img { width: 100%; height: 100%; object-fit: cover; object-position: center top; display: block; }
    .hero-overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.0) 10%, rgba(0,0,0,0.95) 100%); display: flex; flex-direction: column; justify-content: flex-end; padding: clamp(1.25rem, 4vw, 2.5rem); }
    .hero-bottom-row { position: relative; }
    .hero-left { flex: 1; min-width: 0; }
    .hero-eyebrow { font-size: 12px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #fff; opacity: 0.75; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 8px; cursor: pointer; transition: opacity 0.15s; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%; }
    .hero-eyebrow:hover { opacity: 1; }
    .pulse-dot { width: 8px; height: 8px; border-radius: 50%; background: #ff4444; flex-shrink: 0; box-shadow: 0 0 0 0 rgba(255,68,68,0.6); animation: pulse 2s infinite; }
    @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(255,68,68,0.6); } 70% { box-shadow: 0 0 0 7px rgba(255,68,68,0); } 100% { box-shadow: 0 0 0 0 rgba(255,68,68,0); } }
    .hero-title { font-family: 'Playfair Display', Georgia, serif; font-size: clamp(24px, 4vw, 42px); font-weight: 700; line-height: 1.1; color: #fff; margin-bottom: 0.75rem; text-shadow: 0 2px 12px rgba(0,0,0,0.6); }
    .hero-sub { font-size: 16px; color: rgba(255,255,255,0.75); line-height: 1.5; margin-bottom: 0.75rem; }
    .hero-location { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.7); white-space: nowrap; margin-top: 0.6rem; }
    @media (min-width: 480px) { .hero-location { position: absolute; bottom: 0; right: 0; margin-top: 0; } }
    .show-pill { display: inline-block; background: rgba(176,26,28,0.75); border: 1px solid rgba(255,255,255,0.2); color: #fff; font-size: 13px; font-weight: 600; padding: 5px 14px; border-radius: 20px; font-style: italic; backdrop-filter: blur(4px); }
    .msg-btn { display: inline-flex; align-items: center; gap: 6px; background: var(--red); border: none; color: #fff; font-size: 13px; font-weight: 600; padding: 9px 18px; border-radius: 20px; text-decoration: none; margin-top: 0.75rem; align-self: flex-start; transition: background 0.15s; }
    .msg-btn:hover { background: var(--red-dark); }

    .ticker-bar { position: fixed; bottom: 0; left: 0; right: 0; z-index: 200; background: rgba(20,20,20,0.95); border-top: 1px solid var(--border); backdrop-filter: blur(8px); height: 36px; display: flex; align-items: center; overflow: hidden; }
    .ticker-label { flex-shrink: 0; font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--red); padding: 0 12px 0 14px; border-right: 1px solid var(--border); height: 100%; display: flex; align-items: center; white-space: nowrap; }
    .ticker-track { flex: 1; overflow: hidden; position: relative; }
    .ticker-inner { display: flex; gap: 3rem; white-space: nowrap; animation: ticker-scroll 40s linear infinite; }
    .ticker-inner:hover { animation-play-state: paused; }
    .ticker-item { font-size: 13px; color: var(--text-secondary); flex-shrink: 0; }
    .ticker-item strong { color: var(--text); }
    @keyframes ticker-scroll { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }

    .tab-bar { position: sticky; top: 0; z-index: 100; background: var(--surface); border-bottom: 1px solid var(--border); display: flex; box-shadow: 0 2px 8px rgba(0,0,0,0.4); }
    .tab-btn { flex: 1; background: none; border: none; border-bottom: 3px solid transparent; color: var(--text-secondary); font-size: 14px; font-weight: 600; letter-spacing: 0.04em; padding: 16px 8px 13px; cursor: pointer; transition: color 0.15s, border-color 0.15s; text-align: center; }
    .tab-btn:hover { color: var(--text); }
    .tab-btn.active { color: var(--text); border-bottom-color: var(--red); }
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }

    .content { max-width: 1100px; margin: 0 auto; padding: 2rem 1.5rem 0; }
    .section-label { font-size: 12px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 1rem; }

    .cards { display: grid; grid-template-columns: 1fr; gap: 1rem; margin-bottom: 2rem; }
    @media (min-width: 780px) { .cards { grid-template-columns: repeat(3, 1fr); } }
    .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.3); display: flex; flex-direction: column; }
    .card.featured { border-color: rgba(176,26,28,0.5); box-shadow: 0 4px 16px rgba(176,26,28,0.15); }
    .card-header { padding: 1.4rem 1.5rem 0.875rem; display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
    .card-option-num { font-size: 11px; font-weight: 600; color: var(--text-muted); letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 4px; }
    .card-date { font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-top: 5px; }
    .card-horizontal { display: flex; flex-direction: row; gap: 0; align-items: stretch; }
    .card-horizontal-left { flex: 1; padding: 1.4rem 1.5rem; border-right: 1px solid var(--border); }
    .card-horizontal-right { flex: 0 0 auto; width: 286px; padding: 1.4rem 1.5rem; display: flex; flex-direction: column; justify-content: center; }
    @media (max-width: 600px) { .card-horizontal { flex-direction: column; } .card-horizontal-left { border-right: none; border-bottom: 1px solid var(--border); } .card-horizontal-right { width: auto; } }
    /* Status bar */
    .status-bar { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 1.5rem; margin-bottom: 1rem; }
    @media (max-width: 600px) { .status-bar { grid-template-columns: 1fr 1fr; } }
    .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 0.9rem 1rem; text-align: center; }
    .stat-val { font-size: 22px; font-weight: 800; color: var(--text); line-height: 1; }
    .stat-label { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted); margin-top: 4px; }
    .stat-sub { font-size: 12px; color: var(--text-secondary); margin-top: 3px; }
    /* Progress bar */
    .season-progress { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 0.9rem 1.1rem; margin-bottom: 1rem; }
    .progress-label { display: flex; justify-content: space-between; font-size: 12px; color: var(--text-muted); margin-bottom: 6px; }
    .progress-track { height: 6px; background: var(--surface-2); border-radius: 3px; overflow: hidden; }
    .progress-fill { height: 100%; background: var(--red); border-radius: 3px; transition: width 0.6s ease; }
    /* Announcement */
    .announcement-banner { background: rgba(176,26,28,0.1); border: 1px solid rgba(176,26,28,0.35); border-radius: var(--radius); padding: 0.9rem 1.1rem; margin-bottom: 1rem; font-size: 14px; color: var(--text); line-height: 1.6; }
    .announcement-banner strong { color: #E07070; font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; display: block; margin-bottom: 4px; }
    /* Score card */
    .score-card { background: var(--surface); border: 1px solid var(--border); border-left: 4px solid #FFD700; border-radius: var(--radius); padding: 0.9rem 1.1rem; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
    .score-left .score-num { font-size: 28px; font-weight: 800; color: #FFD700; line-height: 1; }
    .score-left .score-place { font-size: 13px; color: var(--text-secondary); margin-top: 2px; }
    .score-right { font-size: 12px; color: var(--text-muted); text-align: right; }
    /* Fanmail hook */
    .fanmail-hook { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 0.9rem 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; text-decoration: none; color: var(--text); transition: background 0.15s; }
    .fanmail-hook:hover { background: var(--surface-2); }
    .fanmail-hook-left { font-size: 14px; color: var(--text-secondary); }
    .fanmail-hook-left strong { color: var(--text); display: block; font-size: 15px; margin-bottom: 2px; }
    /* Countdown */
    .countdown-strip { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 0.85rem 1.1rem; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
    .countdown-label { font-size: 12px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); }
    .countdown-digits { display: flex; gap: 12px; align-items: baseline; }
    .countdown-unit { text-align: center; }
    .countdown-num { font-size: 22px; font-weight: 800; color: var(--text); line-height: 1; }
    .countdown-unit-label { font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; }

    /* Score chip (compact, Latest tab) */
    .score-chip { display: flex; align-items: center; gap: 10px; background: var(--surface); border: 1px solid var(--border); border-left: 3px solid #FFD700; border-radius: var(--radius); padding: 0.75rem 1rem; margin-bottom: 1rem; text-decoration: none; color: var(--text); transition: background 0.15s; flex-wrap: wrap; cursor: pointer; }
    .score-chip:hover { background: var(--surface-2); }
    .score-chip-num { font-size: 22px; font-weight: 800; color: #FFD700; line-height: 1; flex-shrink: 0; }
    .score-chip-meta { font-size: 13px; color: var(--text-secondary); flex: 1; }
    .score-chip-cta { font-size: 12px; color: var(--text-muted); white-space: nowrap; }
    /* Results tab */
    .results-table { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; font-size: 14px; }
    .results-table th { text-align: left; font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted); padding: 0 0.75rem 0.6rem; border-bottom: 1px solid var(--border); }
    .results-table td { padding: 0.7rem 0.75rem; border-bottom: 1px solid var(--border); color: var(--text-secondary); vertical-align: middle; }
    .results-table tr:last-child td { border-bottom: none; }
    .results-table .td-score { font-size: 17px; font-weight: 800; color: #FFD700; }
    .results-table .td-place { font-weight: 700; color: var(--text); }
    .results-table .td-trend { font-size: 18px; }
    .results-table .td-trend.up { color: #4CAF72; }
    .results-table .td-trend.down { color: #E07070; }
    .results-table .td-trend.same { color: var(--text-muted); }
    .results-empty { text-align: center; padding: 2.5rem 1rem; color: var(--text-muted); font-size: 14px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); margin-bottom: 1.5rem; }
    .results-empty strong { display: block; font-size: 16px; color: var(--text-secondary); margin-bottom: 0.4rem; }
    .score-chart { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1rem; margin-bottom: 1rem; overflow-x: auto; }
    .score-chart svg { display: block; width: 100%; }
    .ext-links { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 2rem; }
    @media (max-width: 500px) { .ext-links { grid-template-columns: 1fr; } }
    .ext-link { display: flex; align-items: center; justify-content: space-between; gap: 8px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 0.9rem 1rem; text-decoration: none; color: var(--text); font-size: 14px; font-weight: 600; transition: background 0.15s; }
    .ext-link:hover { background: var(--surface-2); }
    .ext-link span { color: var(--text-muted); font-size: 12px; }
    /* Hero score */
    .hero-score-card { background: linear-gradient(135deg, #1a1400 0%, #1A1A1A 100%); border: 1px solid rgba(255,215,0,0.25); border-radius: var(--radius); padding: 1.5rem; margin-bottom: 1rem; position: relative; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.5); }
    .hero-score-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background: linear-gradient(90deg, #FFD700 0%, rgba(255,215,0,0.3) 100%); }
    .hero-score-label { font-size: 11px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(255,215,0,0.6); margin-bottom: 0.5rem; }
    .hero-score-num { font-size: clamp(52px, 12vw, 80px); font-weight: 900; color: #FFD700; line-height: 1; letter-spacing: -0.02em; margin-bottom: 0.5rem; }
    .hero-score-chips { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-top: 0.75rem; }
    .score-badge { display: inline-flex; align-items: center; gap: 5px; font-size: 13px; font-weight: 700; padding: 5px 12px; border-radius: 20px; }
    .score-badge-place { background: rgba(255,215,0,0.12); border: 1px solid rgba(255,215,0,0.3); color: #FFD700; }
    .score-badge-show { background: rgba(255,255,255,0.06); border: 1px solid var(--border); color: var(--text-secondary); font-weight: 500; font-size: 12px; }
    .score-badge-best { background: rgba(76,175,114,0.12); border: 1px solid rgba(76,175,114,0.3); color: #4CAF72; }
    /* Caption bars */
    .caption-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; margin-bottom: 1rem; }
    .caption-card-header { padding: 1rem 1.25rem 0.75rem; border-bottom: 1px solid var(--border); display: flex; align-items: baseline; gap: 10px; }
    .caption-card-title { font-size: 13px; font-weight: 700; letter-spacing: 0.04em; color: var(--text); }
    .caption-card-sub { font-size: 11px; color: var(--text-muted); }
    .caption-row { display: flex; align-items: center; gap: 12px; padding: 0.75rem 1.25rem; border-bottom: 1px solid var(--border); }
    .caption-row:last-child { border-bottom: none; }
    .caption-row.percussion { background: rgba(255,215,0,0.04); }
    .caption-name { width: 90px; font-size: 12px; font-weight: 600; color: var(--text-secondary); flex-shrink: 0; }
    .caption-row.percussion .caption-name { color: #FFD700; }
    .caption-bar-wrap { flex: 1; height: 8px; background: var(--surface-2); border-radius: 4px; overflow: hidden; }
    .caption-bar { height: 100%; border-radius: 4px; background: var(--red); transition: width 0.6s ease; }
    .caption-row.percussion .caption-bar { background: #FFD700; }
    .caption-score { width: 44px; text-align: right; font-size: 14px; font-weight: 700; color: var(--text); flex-shrink: 0; }
    .caption-row.percussion .caption-score { color: #FFD700; }
    .caption-max { font-size: 11px; color: var(--text-muted); flex-shrink: 0; }
    /* Leaderboard */
    .leaderboard-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; margin-bottom: 1rem; }
    .leaderboard-card-header { padding: 1rem 1.25rem 0.75rem; border-bottom: 1px solid var(--border); font-size: 13px; font-weight: 700; color: var(--text); }
    .leaderboard-row { display: flex; align-items: center; gap: 10px; padding: 0.65rem 1.25rem; border-bottom: 1px solid var(--border); font-size: 13px; }
    .leaderboard-row:last-child { border-bottom: none; }
    .leaderboard-row.phantom { background: rgba(255,215,0,0.07); border-left: 3px solid #FFD700; }
    .leaderboard-rank { width: 24px; font-size: 12px; font-weight: 700; color: var(--text-muted); flex-shrink: 0; text-align: center; }
    .leaderboard-row.phantom .leaderboard-rank { color: #FFD700; }
    .leaderboard-name { flex: 1; color: var(--text-secondary); }
    .leaderboard-row.phantom .leaderboard-name { color: var(--text); font-weight: 700; }
    .leaderboard-score { font-size: 14px; font-weight: 800; color: var(--text); }
    .leaderboard-row.phantom .leaderboard-score { color: #FFD700; }

    .venue-banner { background: var(--surface); border: 1px solid var(--border); border-left: 4px solid var(--red); border-radius: var(--radius); padding: 1rem 1.25rem; margin-top: 1.5rem; margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
    .venue-banner-title { font-size: 20px; font-family: 'Playfair Display', Georgia, serif; font-weight: 700; color: var(--text); line-height: 1.2; }
    .venue-banner-sub { font-size: 14px; color: var(--text-secondary); margin-top: 3px; }
    .venue-banner-right { display: flex; align-items: center; gap: 6px; color: var(--text-muted); flex-shrink: 0; }
    .card-title { font-family: 'Playfair Display', Georgia, serif; font-size: 22px; font-weight: 700; color: var(--text); line-height: 1.2; }
    .badge { font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 20px; white-space: nowrap; flex-shrink: 0; margin-top: 4px; }
    .badge-us { background: var(--green-bg); color: var(--green); }
    .badge-free { background: var(--red-light); color: #E07070; }
    .card-body { padding: 0 1.5rem 1.25rem; font-size: 15px; color: var(--text-secondary); line-height: 1.75; flex: 1; }
    .card-body p + p { margin-top: 0.75rem; }
    .divider { height: 1px; background: var(--border); margin: 0 1.5rem; }
    .details { padding: 1rem 1.5rem 1.1rem; display: flex; flex-direction: column; gap: 9px; }
    .detail { display: flex; align-items: flex-start; gap: 10px; font-size: 14px; color: var(--text-muted); line-height: 1.5; }
    .detail svg { flex-shrink: 0; margin-top: 2px; }
    .ticket-btn { display: block; margin: 0 1.5rem 1.25rem; padding: 14px 20px; background: var(--red); color: white; text-align: center; border-radius: 10px; font-size: 15px; font-weight: 600; text-decoration: none; transition: background 0.15s; }
    .ticket-btn:hover { background: var(--red-dark); }

    .links-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.3); }
    .links-header { padding: 1.1rem 1.5rem 0.6rem; font-size: 11px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--text-muted); }
    .link-row { display: flex; align-items: center; gap: 14px; padding: 1rem 1.5rem; border-top: 1px solid var(--border); text-decoration: none; color: var(--text); transition: background 0.12s; }
    .link-row:hover { background: var(--surface-2); }
    .link-icon { width: 38px; height: 38px; border-radius: 10px; background: var(--red-light); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .link-icon svg { color: #E07070; }
    .link-label { flex: 1; font-size: 15px; font-weight: 500; }
    .link-sub { font-size: 13px; color: var(--text-muted); margin-top: 2px; }
    .link-arrow { color: var(--text-muted); }

    .footer-card { background: var(--surface); border: 1px solid var(--border); border-left: 3px solid var(--red); border-radius: var(--radius); padding: 1.25rem 1.5rem; font-size: 15px; color: var(--text-secondary); line-height: 1.75; box-shadow: 0 2px 8px rgba(0,0,0,0.3); margin-bottom: 2rem; }
    .footer-card strong { color: var(--text); font-weight: 600; }
    .social-section { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem; align-items: start; }
    @media (max-width: 600px) { .social-section { grid-template-columns: 1fr; } }
    .reel-wrap { border-radius: var(--radius); overflow: hidden; }
    .ig-follow-btn { display: flex; align-items: center; gap: 12px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1rem 1.5rem; color: var(--text); text-decoration: none; font-size: 15px; font-weight: 500; transition: border-color 0.15s, background 0.15s; }
    .ig-follow-btn:hover { border-color: var(--border-strong); background: var(--surface-2); }
    .ig-follow-btn .link-arrow { margin-left: auto; color: var(--text-muted); }

    .gallery-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; margin-bottom: 2rem; }
    .gallery-item { border-radius: 8px; overflow: hidden; aspect-ratio: 1 / 1; background: var(--surface-2); cursor: pointer; position: relative; }
    .gallery-item img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.3s ease; }
    .gallery-item:hover img { transform: scale(1.05); }
    @media (max-width: 480px) { .gallery-grid { grid-template-columns: repeat(2, 1fr); gap: 4px; } }
    .lightbox { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.92); z-index: 1000; align-items: center; justify-content: center; padding: 1rem; }
    .lightbox.open { display: flex; }
    .lightbox img { max-width: 100%; max-height: 90vh; border-radius: 8px; object-fit: contain; }
    .lightbox-close { position: fixed; top: 1rem; right: 1.25rem; background: none; border: none; color: white; font-size: 2rem; cursor: pointer; line-height: 1; opacity: 0.8; }
    .lightbox-close:hover { opacity: 1; }
    .video-wrap { position: relative; border-radius: var(--radius); overflow: hidden; aspect-ratio: 16 / 9; background: #000; }
    .video-wrap iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }

    .calendar-section { max-width: 1100px; margin: 0 auto; padding: 2rem 1.5rem 3rem; }
    .cal-nav { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
    .cal-nav-btn { background: var(--surface); border: 1px solid var(--border); color: var(--text); width: 44px; height: 44px; border-radius: 50%; font-size: 1.25rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.15s, box-shadow 0.15s; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.3); }
    .cal-nav-btn:hover { background: var(--surface-2); box-shadow: 0 4px 12px rgba(0,0,0,0.4); }
    .cal-nav-btn:disabled { opacity: 0.25; cursor: default; box-shadow: none; }
    .cal-nav-center { text-align: center; flex: 1; padding: 0 1rem; }
    .cal-month-name { font-size: 1.5rem; font-weight: 700; color: var(--text); }
    .cal-month-phase { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
    .cal-legend { display: flex; flex-wrap: wrap; gap: 6px 16px; margin-bottom: 1rem; padding: 0 2px; }
    .cal-legend-item { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-secondary); white-space: nowrap; }
    .cal-legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .bloodline-banner { width: 100%; max-height: 175px; object-fit: contain; object-position: center; display: block; margin-bottom: 1.5rem; }
    .cal-wrap { background: #161616; border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
    .cal-dow-row { display: grid; grid-template-columns: repeat(7, 1fr); border-bottom: 1px solid var(--border); }
    .cal-dow { text-align: center; padding: 10px 4px; font-size: 0.72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--text-muted); }
    .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); }
    .cal-cell { min-height: 100px; padding: 8px; border-right: 1px solid rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.05); }
    .cal-cell:nth-child(7n) { border-right: none; }
    .cal-cell.empty { background: transparent; }
    .cal-cell.today { background: rgba(176,26,28,0.14); border: 1.5px solid rgba(176,26,28,0.5); border-radius: 6px; }
    .cal-cell.today .day-num { color: #B01A1C; font-weight: 800; }
    .today-num-wrap { display: flex; align-items: center; gap: 4px; }
    .today-dot { width: 6px; height: 6px; border-radius: 50%; background: #B01A1C; flex-shrink: 0; box-shadow: 0 0 0 0 rgba(176,26,28,0.5); animation: pulse 2s infinite; }
    .day-num { font-size: 0.82rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 5px; display: block; }
    .event-pill { display: block; border-radius: 5px; padding: 4px 7px; margin-bottom: 4px; font-size: 0.72rem; font-weight: 600; line-height: 1.3; color: #000; cursor: default; overflow: hidden; position: relative; }
    .event-pill.dci { font-size: 0.78rem; font-weight: 800; letter-spacing: .02em; }
    .event-pill:hover::after { content: attr(data-detail); position: absolute; bottom: calc(100% + 4px); left: 0; min-width: 150px; max-width: 220px; background: #2a2a2a; color: #F2F0EA; border: 1px solid rgba(255,255,255,0.12); border-radius: 6px; padding: 6px 9px; font-size: 0.7rem; font-weight: 400; white-space: normal; z-index: 10; pointer-events: none; box-shadow: 0 4px 12px rgba(0,0,0,.6); }
    .dci-info-box { margin-top: 1.5rem; background: rgba(176,26,28,0.06); border: 1px solid rgba(176,26,28,0.2); border-radius: 10px; padding: 14px 18px; font-size: 0.82rem; color: var(--text-secondary); }
    .dci-info-box strong { color: #FFD700; display: block; margin-bottom: 6px; font-size: 0.88rem; }
    .month-view { display: none; }
    .month-view.active { display: block; }

    @media (max-width: 600px) {
      .cal-cell { min-height: 64px; padding: 4px; }
      .event-pill { font-size: 0.6rem; padding: 2px 4px; }
      .event-pill:hover::after { display: none; }
      .cal-month-name { font-size: 1rem; }
      .tab-btn { font-size: 13px; padding: 14px 4px 11px; }
    }

    /* Show Night Mode */
    .show-night-banner { background: linear-gradient(135deg, rgba(176,26,28,0.22) 0%, rgba(176,26,28,0.07) 100%); border: 1px solid rgba(176,26,28,0.5); border-left: 4px solid var(--red); border-radius: var(--radius); padding: 1.1rem 1.25rem; margin-bottom: 1rem; position: relative; overflow: hidden; }
    .show-night-banner::before { content:''; position:absolute; top:0; left:0; right:0; height:2px; background:linear-gradient(90deg, var(--red), transparent); }
    .snb-eyebrow { font-size: 11px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--red); margin-bottom: 4px; display: flex; align-items: center; gap: 6px; }
    .snb-title { font-size: 20px; font-weight: 800; color: var(--text); line-height: 1.2; }
    .snb-show-name { font-size: 13px; color: var(--text-secondary); margin-top: 3px; }
    .snb-status { font-size: 11px; color: var(--text-muted); margin-top: 8px; display: flex; align-items: center; gap: 6px; }
    /* Score update toast */
    .score-toast { position: fixed; bottom: 5.5rem; left: 50%; transform: translateX(-50%) translateY(80px); background: #1A1A1A; border: 1px solid rgba(255,215,0,0.5); border-radius: 12px; padding: 11px 20px; font-size: 14px; font-weight: 700; color: #FFD700; z-index: 500; transition: transform 0.35s ease, opacity 0.35s ease; opacity: 0; pointer-events: none; white-space: nowrap; box-shadow: 0 8px 24px rgba(0,0,0,0.7); }
    .score-toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
    /* Tour map */
    .tour-map-wrap { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; margin-top: 1.5rem; margin-bottom: 1.5rem; }
    .tour-map-header { padding: 1rem 1.25rem 0.75rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .tour-map-title { font-size: 13px; font-weight: 700; color: var(--text); }
    .tour-map-sub { font-size: 11px; color: var(--text-muted); }
    .tour-map-svg-wrap { overflow-x: auto; padding: 0.75rem; }
    /* Season projection */
    .projection-card { background: var(--surface); border: 1px solid var(--border); border-left: 3px solid #4CAF72; border-radius: var(--radius); padding: 1rem 1.25rem; margin-bottom: 1rem; }
    .projection-eyebrow { font-size: 11px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: #4CAF72; margin-bottom: 0.5rem; }
    .projection-score { font-size: 32px; font-weight: 900; color: var(--text); line-height: 1; }
    .projection-sub { font-size: 13px; color: var(--text-secondary); margin-top: 4px; line-height: 1.5; }
    .projection-detail { font-size: 11px; color: var(--text-muted); margin-top: 6px; }
    /* Caption trend */
    .caption-trend-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; margin-bottom: 1rem; }
    .caption-trend-header { padding: 0.9rem 1.25rem 0.75rem; border-bottom: 1px solid var(--border); }
    .caption-trend-title { font-size: 13px; font-weight: 700; color: var(--text); }
    .caption-trend-sub { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
    .caption-trend-body { overflow-x: auto; padding: 0.75rem 0.5rem 0; }
    .caption-trend-legend { display: flex; gap: 14px; flex-wrap: wrap; padding: 0.5rem 1rem 0.75rem; }
    .clt-item { display: flex; align-items: center; gap: 5px; font-size: 11px; color: var(--text-muted); }
    .clt-line { width: 18px; height: 2px; border-radius: 1px; }
  </style>
</head>
<body>

  <div class="hero">
    <img src="/assets/mateo.jpg" alt="Matéo — Phantom Regiment 2026">
    <div class="hero-overlay">
      <div class="hero-bottom-row">
        <div class="hero-left">
          <?php if ($next_event): ?>
          <a class="hero-eyebrow" href="javascript:switchTab('more',document.querySelectorAll('.tab-btn')[3])" style="text-decoration:none;">
            <span class="pulse-dot"></span>Next show &nbsp;&middot;&nbsp; <?= date('M j', $ne_ts) ?><?= $ne_location ? ' &middot; ' . htmlspecialchars($ne_location) : '' ?> &nbsp;&middot;&nbsp; <?= htmlspecialchars($next_event['label']) ?> ›
          </a>
          <?php else: ?>
          <div class="hero-eyebrow"><span style="opacity:0.6;">&#10003;</span>&nbsp; Season complete &nbsp;&middot;&nbsp; DCI Championships, Indianapolis</div>
          <?php endif; ?>
          <div class="hero-title">Keep up with Matéo</div>
          <div class="hero-sub">Phantom Regiment &middot; Drum Corps International</div>
          <a class="msg-btn" href="/fanmail.php">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Post Phanmail to Matéo
          </a>
        </div>
        <?php if ($current_city): ?>
        <div class="hero-location">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          Currently in <?= htmlspecialchars($current_city) ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <nav class="tab-bar">
    <button class="tab-btn active" onclick="switchTab('latest', this)">Latest</button>
    <button class="tab-btn" onclick="switchTab('media', this)">Media</button>
    <button class="tab-btn" onclick="switchTab('results', this)">Results</button>
    <button class="tab-btn" onclick="switchTab('more', this)">Dates</button>
  </nav>

  <div class="tab-panel active" id="tab-latest">
    <div class="content">
      <picture><source srcset="/assets/bloodline.webp" type="image/webp"><img src="/assets/bloodline.png" alt="Bloodline — Phantom Regiment 2026" class="bloodline-banner"></picture>

      <?php if ($_announcement): ?>
      <div class="announcement-banner"><strong>Update</strong><?= nl2br(htmlspecialchars($_announcement)) ?></div>
      <?php endif; ?>

      <?php if ($_is_show_night && $_tonight_event): ?>
      <div class="show-night-banner">
        <div class="snb-eyebrow"><span class="pulse-dot"></span>Competition Night</div>
        <div class="snb-title"><?= htmlspecialchars($_tonight_event['label']) ?></div>
        <?php if ($_tonight_dci_show): ?>
        <div class="snb-show-name"><?= htmlspecialchars($_tonight_dci_show) ?></div>
        <?php endif; ?>
        <div class="snb-status">
          <span>Checking for score updates automatically</span>
          <span id="snb-check-time"></span>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($_score['score'])): ?>
      <a class="score-chip" href="javascript:switchTab('results',document.querySelectorAll('.tab-btn')[2])">
        <span class="score-chip-num" id="live-score-num"><?= htmlspecialchars($_score['score']) ?></span>
        <span class="score-chip-meta"><?= htmlspecialchars($_score['placement']) ?> place &nbsp;&middot;&nbsp; <?= htmlspecialchars($_score['show']) ?></span>
        <span class="score-chip-cta">Season results →</span>
      </a>
      <?php endif; ?>

      <?php if ($next_event):
        $days_to_next = (int)floor(($ne_ts - $today_ts) / 86400);
        if ($days_to_next <= 0)      $next_label = 'Tonight!';
        elseif ($days_to_next === 1) $next_label = 'Tomorrow';
        else                         $next_label = 'In ' . $days_to_next . ' days';
      ?>
      <div class="countdown-strip">
        <div class="countdown-label">Next show</div>
        <div style="display:flex;align-items:baseline;gap:10px;flex-wrap:wrap;">
          <span style="font-size:22px;font-weight:800;color:var(--text);line-height:1;"><?= $next_label ?></span>
          <span style="font-size:13px;color:var(--text-secondary);"><?= htmlspecialchars($next_event['label']) ?> &nbsp;&middot;&nbsp; <?= date('M j', $ne_ts) ?></span>
        </div>
      </div>
      <?php endif; ?>

      <div class="venue-banner">
        <div class="venue-banner-left">
          <div class="venue-banner-title">San Antonio, TX</div>
          <div class="venue-banner-sub">Alamodome &nbsp;·&nbsp; July 17–18, 2026</div>
        </div>
        <div class="venue-banner-right">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <a href="https://maps.google.com/?q=Alamodome,+San+Antonio,+TX" target="_blank" rel="noopener" style="color:inherit;text-decoration:none;font-size:13px;font-weight:600;">Get directions</a>
        </div>
      </div>
      <div class="section-label">Ways to watch in person</div>
      <p style="font-size:13px;color:var(--text-muted);margin:-0.5rem 0 1rem;">These stack — many fans do the rehearsal Friday, then the lots and show on Saturday.</p>
      <div class="cards">

      <!-- Option 1 -->
      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-option-num">Friday · Free</div>
            <div class="card-title">Free rehearsal</div>
            <div class="card-date">Friday, July 17</div>
          </div>
          <span class="badge badge-us">We'll be here</span>
        </div>
        <div class="card-body">
          <p>Phantom Regiment rehearses at a high school about 1 hr 40 min south of the Alamodome. Free, up-close, and you'll likely see them run the full show.</p>
          <p><strong>We'll be there</strong> — come say hi before heading into San Antonio for the show.</p>
          <p style="font-size:13px;color:var(--text-muted);margin-top:0.5rem;"><strong style="color:var(--text-secondary);">What to bring:</strong> Sunscreen, water, folding chair or blanket, hat, comfortable shoes, cash for gas. It's South Texas in July — expect 95–100°F.</p>
        </div>
        <div class="divider"></div>
        <div class="details">
          <div class="detail">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Free admission
          </div>
          <div class="detail">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            No uniforms — outdoor practice setting
          </div>
          <div class="detail">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <a href="https://maps.app.goo.gl/T7ppQ4y9r49DTmVV6" target="_blank" rel="noopener" style="color:inherit;">Pleasanton High School, 900 W Adams St, Pleasanton, TX</a>
          </div>
        </div>
        <a href="https://maps.app.goo.gl/T7ppQ4y9r49DTmVV6" target="_blank" rel="noopener" style="display:block;margin:0 1.5rem 1.25rem;border-radius:10px;overflow:hidden;border:1px solid var(--border);">
          <iframe src="https://maps.google.com/maps?q=Pleasanton+High+School,+900+W+Adams+St,+Pleasanton,+TX+78064&output=embed&z=15" width="100%" height="130" style="border:0;display:block;pointer-events:none;" loading="lazy"></iframe>
        </a>
      </div>

      <!-- Option 2 -->
      <div class="card featured">
        <div class="card-header">
          <div>
            <div class="card-option-num">Saturday · Free</div>
            <div class="card-title">The Lots</div>
            <div class="card-date">Saturday, July 18 · ~6 PM</div>
          </div>
          <span class="badge badge-us">We'll be here</span>
        </div>
        <div class="card-body">
          <p>Starting around 6 PM, corps warm up in the parking lots around the Alamodome in partial uniform. You'll see the horn lines and snare lines running drills separately — not the full show, but a cool behind-the-scenes look at how it all comes together.</p>
          <p>You're free to roam and listen to different sections up close. Multiple corps will be doing the same thing. Phantom wears bright red — hard to miss. Once we find them, we'll text everyone a pin so you can find us!</p>
          <p>If you're joining us, let us know — once we get a head count we can figure out if dinner together works out after!</p>
          <p style="font-size:13px;color:var(--text-muted);margin-top:0.5rem;"><strong style="color:var(--text-secondary);">Weather:</strong> Expect 95°F+ on the pavement. Bring a full water bottle, wear light clothing, and bring a portable chair.</p>
        </div>
        <div class="divider"></div>
        <div class="details">
          <div class="detail">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            Parking ~$35
          </div>
          <div class="detail">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            Hot on the pavement — bring water
          </div>
          <div class="detail">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Starts ~6 PM, about an hour
          </div>
          <div class="detail">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            Bring a chair if you want to sit
          </div>
        </div>
      </div>

      <!-- Option 3 -->
      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-option-num">Saturday · Ticketed</div>
            <div class="card-title">Official performance</div>
            <div class="card-date">Saturday, July 18 · ~9 PM</div>
          </div>
        </div>
        <div class="card-body">
          <p>Phantom performs <em>Bloodline</em> inside the Alamodome around 9 PM. The full event runs from 1:30 PM. Matéo will be near <strong>Section 116</strong> on the field sideline. Exact times post day-of.</p>
        </div>
        <div class="divider"></div>
        <div class="details">
          <div class="detail">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/></svg>
            Tickets from ~$129
          </div>
          <div class="detail">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            Watch near Section 116
          </div>
          <div class="detail">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Matéo performs ~9 PM
          </div>
        </div>
        <a class="ticket-btn" href="https://www.ticketmaster.com/event/3A00636CF94C7C32" target="_blank" rel="noopener">
          Buy tickets on Ticketmaster →
        </a>
        <a href="https://calendar.google.com/calendar/render?action=TEMPLATE&text=Phantom+Regiment+%E2%80%94+Bloodline+%7C+DCI+San+Antonio&dates=20260718T210000/20260718T230000&location=Alamodome,+San+Antonio,+TX&details=Matéo+performs+with+Phantom+Regiment.+Section+116." target="_blank" rel="noopener" style="display:block;text-align:center;font-size:12px;color:var(--text-muted);padding:0.5rem 1.5rem 1rem;text-decoration:none;">
          + Add to Google Calendar
        </a>
      </div>

    </div>

      <!-- Watch online — horizontal card -->
      <div class="card card-horizontal" style="margin-bottom:1.5rem;">
        <div class="card-horizontal-left">
          <div class="card-option-num">Also available</div>
          <div class="card-title" style="font-size:18px;">Watch online</div>
          <div class="card-date">Saturday, July 18 · ~9 PM CT</div>
          <p style="font-size:14px;color:var(--text-secondary);margin-top:0.5rem;line-height:1.6;">FloMarching streams DCI events live — watch Phantom perform <em>Bloodline</em> from anywhere.</p>
        </div>
        <div class="card-horizontal-right">
          <div class="details" style="padding:0;margin-bottom:1rem;">
            <div class="detail">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="15" rx="2"/><polyline points="17 2 12 7 7 2"/></svg>
              Live stream on FloMarching
            </div>
            <div class="detail">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              Watch from anywhere
            </div>
          </div>
          <a class="ticket-btn" href="https://www.flomarching.com/signup" target="_blank" rel="noopener" style="margin:0;">
            Sign up for FloMarching →
          </a>
        </div>
      </div>

      <a class="fanmail-hook" href="/fanmail.php">
        <div class="fanmail-hook-left">
          <strong><?= $_msg_count ?> Phanmail<?= $_msg_count !== 1 ? 's' : '' ?> for Matéo<?= $_photo_count ? " · {$_photo_count} photos" : '' ?></strong>
          See what fans have been sending him →
        </div>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;color:var(--text-muted);"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
      </a>

      <div style="display:flex;gap:10px;margin-bottom:2rem;">
        <a href="/messages.php" style="flex:1;display:flex;align-items:center;justify-content:center;gap:7px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:0.9rem 1rem;font-size:14px;font-weight:600;color:var(--text);text-decoration:none;">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          All messages
        </a>
        <a href="/fanmail.php" style="flex:1;display:flex;align-items:center;justify-content:center;gap:7px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:0.9rem 1rem;font-size:14px;font-weight:600;color:var(--text);text-decoration:none;">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          Phanmail
        </a>
      </div>
      <div class="section-label">About Phantom Regiment</div>
      <div class="links-card">
      <div class="links-header">Links</div>

      <a class="link-row" href="https://regiment.org/tickets/" target="_blank" rel="noopener">
        <div class="link-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
        </div>
        <div>
          <div class="link-label">About the show — Bloodline</div>
          <div class="link-sub">regiment.org</div>
        </div>
        <svg class="link-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      </a>

      <a class="link-row" href="https://thephanshop.com/" target="_blank" rel="noopener">
        <div class="link-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        </div>
        <div>
          <div class="link-label">Phantom Regiment merch</div>
          <div class="link-sub">thephanshop.com</div>
        </div>
        <svg class="link-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      </a>

      <a class="link-row" href="https://www.instagram.com/regimentsnares/?hl=en" target="_blank" rel="noopener">
        <div class="link-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
        </div>
        <div>
          <div class="link-label">Follow the snare line</div>
          <div class="link-sub">@regimentsnares</div>
        </div>
        <svg class="link-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      </a>

      <a class="link-row" href="https://www.instagram.com/thephantomregiment/?hl=en" target="_blank" rel="noopener">
        <div class="link-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
        </div>
        <div>
          <div class="link-label">Follow Phantom Regiment</div>
          <div class="link-sub">@thephantomregiment</div>
        </div>
        <svg class="link-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      </a>
    </div>
    </div>
  </div>

  <div class="tab-panel" id="tab-media">
    <div class="content">
      <div class="section-label" style="margin-top:1.5rem;">Photos</div>
      <?php
      $asset_dir = __DIR__ . '/assets';
      $gallery_files = glob($asset_dir . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE);
      $skip = ['bloodline.png','bloodline.webp','mateo.jpg','favicon.png'];
      // Deduplicate: group by stem, prefer jpg/png as base, webp as source
      $stems = [];
      foreach ($gallery_files as $path) {
          $fname = basename($path);
          if (in_array($fname, $skip)) continue;
          $ext  = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
          $stem = pathinfo($fname, PATHINFO_FILENAME);
          if (!isset($stems[$stem])) $stems[$stem] = ['base' => null, 'webp' => null, 'path' => null];
          if ($ext === 'webp') { $stems[$stem]['webp'] = '/assets/' . $fname; }
          else { $stems[$stem]['base'] = '/assets/' . $fname; $stems[$stem]['path'] = $path; }
      }
      // Build flat render list (no orientation classification needed)
      $render = [];
      foreach ($stems as $stem => $f) {
          $render[] = ['src' => $f['base'] ?? $f['webp'], 'webp' => $f['webp']];
      }
      ?>
      <div class="gallery-grid" id="gallery">
        <?php
        function gallery_picture(array $item, string $alt = 'Phantom Regiment'): string {
            $src = htmlspecialchars($item['src']);
            $webp = $item['webp'] ? htmlspecialchars($item['webp']) : null;
            $inner = $webp ? "<source srcset=\"$webp\" type=\"image/webp\">" : '';
            $inner .= "<img src=\"$src\" alt=\"$alt\" loading=\"lazy\" />";
            return "<picture>$inner</picture>";
        }
        ?>
        <?php foreach ($render as $item): ?>
          <div class="gallery-item" onclick="openLightbox(this)">
            <?= gallery_picture($item) ?>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="lightbox" id="lightbox" onclick="closeLightbox()">
        <button class="lightbox-close" onclick="closeLightbox()">&#215;</button>
        <img id="lightbox-img" src="" alt="" />
      </div>
      <div class="section-label" style="margin-top:1.75rem;">Videos &amp; Social</div>
      <div class="social-section">
        <div class="video-wrap">
          <iframe src="https://www.youtube.com/embed/lrOzW2I4r3U" title="Phantom Regiment 2026" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <div class="video-wrap">
          <iframe src="https://www.youtube.com/embed/sIMSeloDV3k" title="Phantom Regiment 2026" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <div class="video-wrap">
          <iframe src="https://www.youtube.com/embed/rwodYLaTWc4" title="Phantom Regiment 2026" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <div class="reel-wrap">
        <blockquote
          class="instagram-media"
          data-instgrm-permalink="https://www.instagram.com/reel/DaIBv_ABv5a/"
          data-instgrm-version="14"
          style="background:#FFF;border:0;border-radius:3px;box-shadow:0 0 1px 0 rgba(0,0,0,.5),0 1px 10px 0 rgba(0,0,0,.15);margin:0;max-width:100%;min-width:326px;padding:0;width:calc(100% - 2px);">
          <div style="padding:16px;">
            <a href="https://www.instagram.com/reel/DaIBv_ABv5a/" target="_blank" rel="noopener"
               style="color:#c9c8cd;font-family:Arial,sans-serif;font-size:14px;line-height:17px;text-decoration:none;">
              View this reel on Instagram
            </a>
          </div>
        </blockquote>
      </div>
        <a class="ig-follow-btn" href="https://www.instagram.com/thephantomregiment/" target="_blank" rel="noopener">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
        Follow @thephantomregiment on Instagram
        <svg class="link-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      </a>
      </div>
    </div>
  </div>

  <div class="tab-panel" id="tab-results">
    <div class="content">
    <?php
      // Pull latest history entry for caption/leaderboard
      $_latest_hist  = !empty($_scores_history) ? end($_scores_history) : null;
      $_has_captions = !empty($_latest_hist['captions']);
      $_has_leader   = !empty($_latest_hist['leaderboard']);
      // Season best
      $_best_score   = null; $_best_show = null;
      if (!empty($_scores_history)) {
          $best = null;
          foreach ($_scores_history as $h) {
              if ($best === null || (float)$h['score'] > (float)$best['score']) $best = $h;
          }
          $_best_score = $best['score'] ?? null;
          $_best_show  = $best['show']  ?? null;
      }
    ?>

    <?php if (empty($_scores_history)): ?>
      <!-- Empty state -->
      <div style="margin-top:1.5rem;">
        <div class="results-empty">
          <strong>Season just getting started</strong>
          Scores will appear here after each competition night.
        </div>
      </div>
    <?php else: ?>

      <!-- Hero score card -->
      <?php if (!empty($_score['score'])): ?>
      <div class="hero-score-card" style="margin-top:1.5rem;">
        <div class="hero-score-label">Latest score</div>
        <div class="hero-score-num"><?= htmlspecialchars($_score['score']) ?></div>
        <div class="hero-score-chips">
          <span class="score-badge score-badge-place"><?= htmlspecialchars($_score['placement'] ?? '') ?> place</span>
          <span class="score-badge score-badge-show"><?= htmlspecialchars($_score['show'] ?? '') ?></span>
          <?php if ($_best_score && $_score['score'] === $_best_score): ?>
          <span class="score-badge score-badge-best">Season best</span>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Season at a glance -->
      <div class="status-bar" style="margin-top:0.75rem;">
        <div class="stat-card">
          <div class="stat-val"><?= $_past_shows ?> <span style="font-size:14px;font-weight:400;color:var(--text-muted);">/ <?= $_total_shows ?></span></div>
          <div class="stat-label">Shows</div>
          <div class="stat-sub">this season</div>
        </div>
        <div class="stat-card">
          <div class="stat-val"><?= $_days_to_finals ?></div>
          <div class="stat-label">Days to Finals</div>
          <div class="stat-sub">Aug 8 · Indianapolis</div>
        </div>
        <?php if ($_best_score): ?>
        <div class="stat-card">
          <div class="stat-val" style="font-size:18px;color:#FFD700;"><?= htmlspecialchars($_best_score) ?></div>
          <div class="stat-label">Season Best</div>
          <div class="stat-sub" style="font-size:11px;"><?= htmlspecialchars(explode(',', $_best_show ?? '')[0] ?? '') ?></div>
        </div>
        <?php else: ?>
        <div class="stat-card" style="cursor:pointer;" onclick="location.href='/fanmail.php'">
          <div class="stat-val"><?= $_msg_count ?></div>
          <div class="stat-label">Fan Messages</div>
          <div class="stat-sub"><?= $_photo_count ?> with photos</div>
        </div>
        <?php endif; ?>
      </div>

      <?php $_pct = $_total_shows > 0 ? round(($_past_shows / $_total_shows) * 100) : 0; ?>
      <div class="season-progress">
        <div class="progress-label">
          <span>Season progress</span>
          <span><?= $_pct ?>% &nbsp;&middot;&nbsp; <?= $_total_shows - $_past_shows ?> shows remaining</span>
        </div>
        <div class="progress-track"><div class="progress-fill" style="width:<?= $_pct ?>%;"></div></div>
      </div>

      <?php if ($_has_captions): ?>
      <!-- Caption breakdown -->
      <?php
        $caps = $_latest_hist['captions'];
        $cap_rows = [
          ['key' => 'ge1',        'label' => 'GE Music',    'class' => ''],
          ['key' => 'ge2',        'label' => 'GE Visual',   'class' => ''],
          ['key' => 'visual',     'label' => 'Visual Perf', 'class' => ''],
          ['key' => 'music',      'label' => 'Music',       'class' => ''],
          ['key' => 'percussion', 'label' => 'Percussion',  'class' => 'percussion'],
        ];
        $cap_max = 20.0; // DCI: each caption out of 20 pts
      ?>
      <div class="caption-card">
        <div class="caption-card-header">
          <span class="caption-card-title">Caption Breakdown</span>
          <span class="caption-card-sub"><?= htmlspecialchars($_latest_hist['show'] ?? '') ?></span>
        </div>
        <?php foreach ($cap_rows as $cr):
          $val = isset($caps[$cr['key']]) ? (float)$caps[$cr['key']] : null;
          $pct = $val !== null ? min(100, ($val / $cap_max) * 100) : 0;
        ?>
        <div class="caption-row <?= $cr['class'] ?>">
          <div class="caption-name"><?= $cr['label'] ?></div>
          <div class="caption-bar-wrap">
            <div class="caption-bar" style="width:<?= number_format($pct, 1) ?>%"></div>
          </div>
          <div class="caption-score"><?= $val !== null ? number_format($val, 2) : '—' ?></div>
          <div class="caption-max">/ <?= $cap_max ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if ($_has_leader): ?>
      <!-- Show leaderboard -->
      <?php
        $leader = $_latest_hist['leaderboard'];
        $show_label = $_latest_hist['show'] ?? '';
      ?>
      <div class="leaderboard-card">
        <div class="leaderboard-card-header">Show Leaderboard &mdash; <?= htmlspecialchars($show_label) ?></div>
        <?php foreach ($leader as $corps):
          $is_phantom = stripos($corps['name'] ?? '', 'Phantom') !== false;
        ?>
        <div class="leaderboard-row <?= $is_phantom ? 'phantom' : '' ?>">
          <div class="leaderboard-rank"><?= (int)($corps['rank'] ?? 0) ?></div>
          <div class="leaderboard-name"><?= htmlspecialchars($corps['name'] ?? '') ?></div>
          <div class="leaderboard-score"><?= isset($corps['total']) ? number_format((float)$corps['total'], 3) : '—' ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if (count($_scores_history) >= 2): ?>
      <!-- Score trend chart -->
      <?php
        $scores_vals = array_map(fn($h) => (float)$h['score'], $_scores_history);
        $min_s = min($scores_vals); $max_s = max($scores_vals);
        $range = max($max_s - $min_s, 2.0);
        $pad = $range * 0.2;
        $y_min = $min_s - $pad; $y_max = $max_s + $pad;
        $W = 560; $H = 140; $ML = 48; $MR = 16; $MT = 12; $MB = 28;
        $cw = $W - $ML - $MR; $ch = $H - $MT - $MB;
        $n = count($_scores_history);
        function sx($i, $n, $cw, $ML) { return $ML + ($n > 1 ? ($i / ($n - 1)) * $cw : $cw / 2); }
        function sy($v, $y_min, $y_max, $ch, $MT) { return $MT + $ch - (($v - $y_min) / ($y_max - $y_min)) * $ch; }
        $pts = [];
        for ($i = 0; $i < $n; $i++) $pts[] = sx($i,$n,$cw,$ML) . ',' . sy($scores_vals[$i],$y_min,$y_max,$ch,$MT);
      ?>
      <div class="section-label" style="margin-top:1.25rem;">Score Trend</div>
      <div class="score-chart">
        <svg viewBox="0 0 <?= $W ?> <?= $H ?>" xmlns="http://www.w3.org/2000/svg">
          <?php for ($g = 0; $g <= 4; $g++): $gy = $MT + ($g / 4) * $ch; $gv = $y_max - ($g / 4) * ($y_max - $y_min); ?>
          <line x1="<?= $ML ?>" y1="<?= $gy ?>" x2="<?= $W - $MR ?>" y2="<?= $gy ?>" stroke="rgba(255,255,255,0.06)" stroke-width="1"/>
          <text x="<?= $ML - 4 ?>" y="<?= $gy + 4 ?>" text-anchor="end" font-size="9" fill="rgba(255,255,255,0.3)"><?= number_format($gv, 1) ?></text>
          <?php endfor; ?>
          <polyline points="<?= implode(' ', $pts) ?>" fill="none" stroke="#FFD700" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
          <polygon points="<?= implode(' ', $pts) ?> <?= $W - $MR ?>,<?= $MT + $ch ?> <?= $ML ?>,<?= $MT + $ch ?>" fill="rgba(255,215,0,0.07)"/>
          <?php for ($i = 0; $i < $n; $i++):
            $px = sx($i,$n,$cw,$ML); $py = sy($scores_vals[$i],$y_min,$y_max,$ch,$MT);
            $lbl = isset($_scores_history[$i]['show']) ? explode(',', $_scores_history[$i]['show'])[0] : '';
            $lbl = mb_strimwidth($lbl, 0, 10, '');
          ?>
          <circle cx="<?= $px ?>" cy="<?= $py ?>" r="4" fill="#FFD700" stroke="#111" stroke-width="1.5"/>
          <text x="<?= $px ?>" y="<?= $py - 8 ?>" text-anchor="middle" font-size="9.5" font-weight="700" fill="#FFD700"><?= number_format($scores_vals[$i], 3) ?></text>
          <?php if ($n <= 10): ?>
          <text x="<?= $px ?>" y="<?= $H - 4 ?>" text-anchor="middle" font-size="8.5" fill="rgba(255,255,255,0.35)"><?= htmlspecialchars($lbl) ?></text>
          <?php endif; ?>
          <?php endfor; ?>
        </svg>
      </div>
      <?php endif; ?>

      <?php
      // Season Projection — linear regression on scores to project Finals score
      if (count($_scores_history) >= 3):
        $n = count($_scores_history);
        $totalShows = 17; // Total judged shows in 2026 DCI season for Phantom
        $sum_x=0; $sum_y=0; $sum_xy=0; $sum_x2=0;
        foreach ($_scores_history as $i => $h) {
          $x=$i+1; $y=(float)$h['score'];
          $sum_x+=$x; $sum_y+=$y; $sum_xy+=$x*$y; $sum_x2+=$x*$x;
        }
        $denom = $n*$sum_x2 - $sum_x*$sum_x;
        $slope = $denom!=0 ? ($n*$sum_xy - $sum_x*$sum_y)/$denom : 0;
        $intercept = ($sum_y - $slope*$sum_x)/$n;
        $proj = $slope*$totalShows + $intercept;
        $proj_low  = max(0, $proj-1.8);
        $proj_high = min(100, $proj+1.8);
      ?>
      <div class="projection-card" style="margin-top:1.25rem;">
        <div class="projection-eyebrow">Season Projection</div>
        <div class="projection-score"><?= number_format($proj, 2) ?></div>
        <div class="projection-sub">Projected Finals score &nbsp;&middot;&nbsp; range <?= number_format($proj_low, 1) ?>–<?= number_format($proj_high, 1) ?></div>
        <div class="projection-detail">Based on <?= $n ?> shows &nbsp;&middot;&nbsp; +<?= number_format($slope, 3) ?> pts/show trend &nbsp;&middot;&nbsp; extrapolated to show <?= $totalShows ?></div>
      </div>
      <?php endif; ?>

      <?php
      // Caption Trend — multi-line chart across all shows with caption data
      $_shows_with_caps = array_values(array_filter($_scores_history, fn($h) => !empty($h['captions'])));
      if (count($_shows_with_caps) >= 2):
        $cap_defs = [
          'ge1'        => ['label'=>'GE Music',   'color'=>'#7B9FD4'],
          'ge2'        => ['label'=>'GE Visual',  'color'=>'#9B7DD4'],
          'visual'     => ['label'=>'Visual',     'color'=>'#7DD4C5'],
          'music'      => ['label'=>'Music',      'color'=>'#D4A07B'],
          'percussion' => ['label'=>'Percussion', 'color'=>'#FFD700'],
        ];
        $CW=570; $CH=145; $CML=44; $CMR=12; $CMT=12; $CMB=28;
        $ccw=$CW-$CML-$CMR; $cch=$CH-$CMT-$CMB;
        $cn=count($_shows_with_caps);
        $all_cv=[];
        foreach($_shows_with_caps as $h) foreach($cap_defs as $k=>$_) if(isset($h['captions'][$k])) $all_cv[]=(float)$h['captions'][$k];
        $cmin=$all_cv?min($all_cv):0; $cmax=$all_cv?max($all_cv):20;
        $crange=max($cmax-$cmin,1.0); $cpad=$crange*0.15;
        $cy_min=$cmin-$cpad; $cy_max=$cmax+$cpad;
        function capX($i,$cn,$ccw,$CML){return $CML+($cn>1?($i/($cn-1))*$ccw:$ccw/2);}
        function capY($v,$cy_min,$cy_max,$cch,$CMT){return $CMT+$cch-(($v-$cy_min)/($cy_max-$cy_min))*$cch;}
      ?>
      <div class="section-label" style="margin-top:1.25rem;">Caption Trend</div>
      <div class="caption-trend-card">
        <div class="caption-trend-header">
          <div class="caption-trend-title">Caption Scores Across Shows</div>
          <div class="caption-trend-sub">Percussion highlighted &nbsp;&middot;&nbsp; each out of 20 pts</div>
        </div>
        <div class="caption-trend-body">
          <svg viewBox="0 0 <?=$CW?> <?=$CH?>" xmlns="http://www.w3.org/2000/svg" style="min-width:320px;display:block;width:100%;">
            <?php for($g=0;$g<=4;$g++): $gy=$CMT+($g/4)*$cch; $gv=$cy_max-($g/4)*($cy_max-$cy_min); ?>
            <line x1="<?=$CML?>" y1="<?=$gy?>" x2="<?=$CW-$CMR?>" y2="<?=$gy?>" stroke="rgba(255,255,255,0.06)" stroke-width="1"/>
            <text x="<?=$CML-4?>" y="<?=$gy+4?>" text-anchor="end" font-size="9" fill="rgba(255,255,255,0.3)"><?=number_format($gv,1)?></text>
            <?php endfor; ?>
            <?php foreach($cap_defs as $k=>$cd):
              $isperc=($k==='percussion');
              $cpts=[];
              for($ci=0;$ci<$cn;$ci++){
                $cv=isset($_shows_with_caps[$ci]['captions'][$k])?(float)$_shows_with_caps[$ci]['captions'][$k]:null;
                if($cv===null) continue;
                $cpx=capX($ci,$cn,$ccw,$CML); $cpy=capY($cv,$cy_min,$cy_max,$cch,$CMT);
                $cpts[]="$cpx,$cpy";
              }
              if(count($cpts)<2) continue;
            ?>
            <polyline points="<?=implode(' ',$cpts)?>" fill="none" stroke="<?=$cd['color']?>" stroke-width="<?=$isperc?2.5:1.5?>" stroke-linejoin="round" stroke-linecap="round" opacity="<?=$isperc?1:0.65?>"/>
            <?php foreach($cpts as $cpstr): list($cpx,$cpy)=explode(',',$cpstr); ?>
            <circle cx="<?=$cpx?>" cy="<?=$cpy?>" r="<?=$isperc?3.5:2.5?>" fill="<?=$cd['color']?>" opacity="<?=$isperc?1:0.65?>"/>
            <?php endforeach; ?>
            <?php endforeach; ?>
            <?php if($cn<=9): for($ci=0;$ci<$cn;$ci++):
              $cpx=capX($ci,$cn,$ccw,$CML);
              $lbl=isset($_shows_with_caps[$ci]['show'])?explode(',',$_shows_with_caps[$ci]['show'])[0]:'';
              $lbl=mb_strimwidth($lbl,0,10,'');
            ?>
            <text x="<?=$cpx?>" y="<?=$CH-4?>" text-anchor="middle" font-size="8.5" fill="rgba(255,255,255,0.28)"><?=htmlspecialchars($lbl)?></text>
            <?php endfor; endif; ?>
          </svg>
        </div>
        <div class="caption-trend-legend">
          <?php foreach($cap_defs as $k=>$cd): ?>
          <div class="clt-item"><div class="clt-line" style="background:<?=$cd['color']?>"></div><?=$cd['label']?></div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Score history table -->
      <div class="section-label" style="margin-top:1.25rem;">Score History</div>
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:1.5rem;">
        <table class="results-table">
          <thead><tr>
            <th>Show</th><th>Score</th><th>Place</th><th></th>
          </tr></thead>
          <tbody>
          <?php foreach (array_reverse($_scores_history) as $i => $h):
            $prev = $i < count($_scores_history) - 1 ? $_scores_history[count($_scores_history) - 2 - $i] : null;
            $trend = ''; $tclass = 'same';
            if ($prev) {
              $diff = (float)$h['score'] - (float)$prev['score'];
              if ($diff > 0.001) { $trend = '↑'; $tclass = 'up'; }
              elseif ($diff < -0.001) { $trend = '↓'; $tclass = 'down'; }
              else { $trend = '—'; $tclass = 'same'; }
            }
          ?>
          <tr>
            <td><?= htmlspecialchars($h['show']) ?></td>
            <td class="td-score"><?= htmlspecialchars($h['score']) ?></td>
            <td class="td-place"><?= htmlspecialchars($h['placement']) ?></td>
            <td class="td-trend <?= $tclass ?>"><?= $trend ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <?php endif; ?>

      <!-- External links -->
      <div class="section-label">Full Standings</div>
      <div class="ext-links">
        <a class="ext-link" href="https://drumcorps.app/corps/phantom-regiment" target="_blank" rel="noopener">
          drumcorps.app <span>Phantom Regiment →</span>
        </a>
        <a class="ext-link" href="https://drumcorps.app/rankings" target="_blank" rel="noopener">
          DCI Rankings <span>drumcorps.app →</span>
        </a>
      </div>
    </div>
  </div>

  <div class="tab-panel" id="tab-more">
    <div class="content">
      <picture><source srcset="/assets/bloodline.webp" type="image/webp"><img src="/assets/bloodline.png" alt="Bloodline — Phantom Regiment 2026" class="bloodline-banner"></picture>
      <div class="footer-card">
      We're also flying to <strong>Indianapolis August 6–9</strong> to watch Matéo compete at the DCI World Championships at Lucas Oil Stadium. Can't wait — hope to see some of you in San Antonio!
    </div>
    <div class="footer-card" style="margin-top:0.75rem;border-left-color:#FFD700;">
      <strong style="color:#FFD700;">About DCI Championships</strong> — DCI (Drum Corps International) is the top competitive level of marching music. The season ends with three rounds in Indianapolis: <strong>Prelims</strong> (Aug 6, all corps compete), <strong>Semifinals</strong> (Aug 7, top scores advance), and <strong>Finals</strong> (Aug 8, the championship). Over 20,000 fans attend each night at Lucas Oil Stadium.
    </div>
    </div>
    <div class="calendar-section">
      <?php
      $monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
      // Build phase lookup from $months array
      $phaseMap = [];
      foreach ($months as $mo) $phaseMap[sprintf('%04d-%02d', $mo['year'], $mo['month'])] = $mo['phase'];

      $calMonths = [];
      foreach ($events as $date_str => $ev) {
        $ts = strtotime($date_str);
        $k  = date('Y-m', $ts);
        if (!isset($calMonths[$k])) {
          $calMonths[$k] = [
            'label'  => $monthNames[(int)date('m',$ts)-1].' '.date('Y',$ts),
            'year'   => (int)date('Y',$ts),
            'month'  => (int)date('m',$ts),
            'phase'  => $phaseMap[$k] ?? '',
            'events' => [],
          ];
        }
        $calMonths[$k]['events'][] = ['day'=>(int)date('j',$ts), 'date'=>$date_str] + $ev;
      }
      ksort($calMonths);
      $mKeys    = array_keys($calMonths);
      $todayY   = (int)date('Y');
      $todayM   = (int)date('m');
      $todayD   = (int)date('j');
      $curIdx   = 0;
      foreach ($mKeys as $i => $k) {
        if ($calMonths[$k]['year'] == $todayY && $calMonths[$k]['month'] == $todayM) $curIdx = $i;
      }
      $eventsByMon = [];
      foreach ($calMonths as $k => $m) {
        $byDay = [];
        foreach ($m['events'] as $ev) $byDay[$ev['day']][] = $ev;
        $eventsByMon[$k] = ['meta'=>$m, 'byDay'=>$byDay];
      }
      ?>

      <div class="cal-nav">
        <button class="cal-nav-btn" id="cal-prev" onclick="calNav(-1)">&#8592;</button>
        <div class="cal-nav-center">
          <div class="cal-month-name" id="cal-month-label"></div>
          <div class="cal-month-phase" id="cal-month-phase"></div>
        </div>
        <button class="cal-nav-btn" id="cal-next" onclick="calNav(1)">&#8594;</button>
      </div>

      <div class="cal-legend">
        <?php foreach ($type_colors as $t => $c): ?>
        <div class="cal-legend-item"><span class="cal-legend-dot" style="background:<?= $c ?>"></span><?= htmlspecialchars($t) ?></div>
        <?php endforeach; ?>
      </div>

      <?php foreach ($eventsByMon as $k => $data): ?>
      <?php $m = $data['meta']; $byDay = $data['byDay']; ?>
      <div class="month-view" id="mv-<?= $k ?>">
        <div class="cal-wrap">
          <div class="cal-dow-row">
            <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d): ?>
            <div class="cal-dow"><?= $d ?></div>
            <?php endforeach; ?>
          </div>
          <div class="cal-grid">
            <?php
            $firstDow = (int)date('w', mktime(0,0,0,$m['month'],1,$m['year']));
            $daysInMon = (int)date('t', mktime(0,0,0,$m['month'],1,$m['year']));
            for ($e = 0; $e < $firstDow; $e++): ?>
            <div class="cal-cell empty"></div>
            <?php endfor; ?>
            <?php for ($d = 1; $d <= $daysInMon; $d++): ?>
            <?php $isToday = ($m['year']==$todayY && $m['month']==$todayM && $d==$todayD); ?>
            <div class="cal-cell<?= $isToday ? ' today' : '' ?>">
              <?php if ($isToday): ?>
              <div class="today-num-wrap"><span class="day-num"><?= $d ?></span><span class="today-dot"></span></div>
              <?php else: ?>
              <span class="day-num"><?= $d ?></span>
              <?php endif; ?>
              <?php if (isset($byDay[$d])): ?>
              <?php foreach ($byDay[$d] as $ev): ?>
              <?php
                $c = $type_colors[$ev['type']] ?? '#888';
                $isDCI = ($ev['type']==='dci');
                $isJudged = in_array($ev['date'] ?? '', $_all_judged) || $isDCI;
                $dciName  = $_dci_show_names[$ev['date'] ?? ''] ?? null;
                $tipDetail = ($dciName ? '★ '.$dciName.' · ' : '') . ($ev['detail'] ?? '');
              ?>
              <span class="event-pill<?= $isDCI ? ' dci' : '' ?>"
                style="background:<?= $c ?>"
                data-detail="<?= htmlspecialchars($tipDetail) ?>">
                <?= $isJudged ? '★ ' : '' ?><?= htmlspecialchars($ev['label']) ?>
              </span>
              <?php endforeach; ?>
              <?php endif; ?>
            </div>
            <?php endfor; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

      <div class="dci-info-box">
        <strong>DCI World Championships — Indianapolis, IN</strong>
        August 7–9, 2026: Semifinals & Finals at Lucas Oil Stadium. The pinnacle of drum corps — over 20,000 fans. <a href="https://dci.org" target="_blank" style="color:#FFD700;">dci.org</a>
      </div>

      <?php
      // Tour map city data: [city label, dci show name, date, svg_x, svg_y]
      $_map_cities = [
        ['Rockford IL',       'Show of Shows',               '2026-07-03', 427, 134],
        ['La Crosse WI',      'River City Rhapsody',         '2026-07-05', 388, 100],
        ['Lisle IL',          'Cavalcade of Brass',          '2026-07-10', 452, 148],
        ['Whitewater WI',     'The Whitewater Classic',      '2026-07-11', 432, 117],
        ['Olathe KS',         'Brass Impact',                '2026-07-13', 355, 196],
        ['Broken Arrow OK',   'DCI Broken Arrow',            '2026-07-14', 343, 248],
        ['Denton TX',         'DCI Denton',                  '2026-07-16', 323, 302],
        ['San Antonio TX',    'DCI Southwestern Champ.',     '2026-07-18', 308, 368],
        ['McKinney TX',       'DCI McKinney',                '2026-07-20', 340, 310],
        ['Evansville IN',     'Drums on the Ohio',           '2026-07-22', 447, 214],
        ['Madison WI',        'Drums on Parade',             '2026-07-24', 420, 119],
        ['DeKalb IL',         'Midwestern Championship',     '2026-07-25', 436, 142],
        ['Mason OH',          'Summer Music Games',          '2026-07-27', 484, 188],
        ['Lawrence MA',       'DCI East Coast Showcase',     '2026-07-30', 638, 128],
        ['Allentown PA',      'DCI Eastern Classic',         '2026-08-01', 587, 166],
        ['Lexington KY',      'DCI Kentucky',                '2026-08-03', 479, 213],
        ['Indianapolis IN',   'DCI World Championships',     '2026-08-06', 461, 178],
      ];
      ?>
      <div class="tour-map-wrap">
        <div class="tour-map-header">
          <div>
            <div class="tour-map-title">2026 Tour Map</div>
            <div class="tour-map-sub"><?= count($_map_cities) ?> DCI judged competitions &nbsp;&middot;&nbsp; hover for show name</div>
          </div>
        </div>
        <div class="tour-map-svg-wrap">
          <svg viewBox="0 0 700 420" xmlns="http://www.w3.org/2000/svg" style="min-width:340px;width:100%;max-width:700px;display:block;">
            <rect width="700" height="420" fill="#0E0E0E" rx="4"/>
            <?php for($gx=100;$gx<700;$gx+=100): ?>
            <line x1="<?=$gx?>" y1="0" x2="<?=$gx?>" y2="420" stroke="rgba(255,255,255,0.03)" stroke-width="1"/>
            <?php endfor; ?>
            <?php for($gy=84;$gy<420;$gy+=84): ?>
            <line x1="0" y1="<?=$gy?>" x2="700" y2="<?=$gy?>" stroke="rgba(255,255,255,0.03)" stroke-width="1"/>
            <?php endfor; ?>
            <!-- Faint region labels for orientation -->
            <text x="55" y="200" font-size="10" fill="rgba(255,255,255,0.07)" font-style="italic">Rocky Mts.</text>
            <text x="360" y="88" font-size="10" fill="rgba(255,255,255,0.07)" font-style="italic">Great Lakes</text>
            <text x="298" y="378" font-size="10" fill="rgba(255,255,255,0.07)" font-style="italic">Gulf Coast</text>
            <text x="598" y="108" font-size="10" fill="rgba(255,255,255,0.07)" font-style="italic">New England</text>
            <text x="558" y="250" font-size="10" fill="rgba(255,255,255,0.07)" font-style="italic">Mid-Atlantic</text>
            <?php foreach($_map_cities as $mc):
              list($city,$dciShow,$date,$mx,$my) = $mc;
              $mts = strtotime($date);
              $isTonight = ($date === $today);
              $isPast    = ($mts < $today_ts);
              $isDCIChamp = ($date >= '2026-08-06');
              if ($isTonight)       { $fill='#B01A1C'; $fc='#E07070'; $r=7; }
              elseif ($isPast)      { $fill='rgba(255,255,255,0.25)'; $fc='rgba(255,255,255,0.3)'; $r=4; }
              elseif ($isDCIChamp)  { $fill='#FFD700'; $fc='#FFD700'; $r=8; }
              else                  { $fill='#7DD9A2'; $fc='rgba(255,255,255,0.72)'; $r=5; }
              // Label side: left edge if near right border
              $lx=$mx+9; $ly=$my+4; $anchor='start';
              if($mx>580){ $lx=$mx-9; $anchor='end'; }
            ?>
            <g>
              <title><?= htmlspecialchars($dciShow.' · '.date('M j',strtotime($date))) ?></title>
              <?php if($isTonight): ?>
              <circle cx="<?=$mx?>" cy="<?=$my?>" r="14" fill="rgba(176,26,28,0)" stroke="rgba(176,26,28,0.5)" stroke-width="1.5">
                <animate attributeName="r" values="8;15;8" dur="2s" repeatCount="indefinite"/>
                <animate attributeName="opacity" values="0.6;0;0.6" dur="2s" repeatCount="indefinite"/>
              </circle>
              <?php endif; ?>
              <circle cx="<?=$mx?>" cy="<?=$my?>" r="<?=$r?>" fill="<?=$fill?>" <?=$isPast?'opacity="0.5"':''?>/>
              <text x="<?=$lx?>" y="<?=$ly?>" text-anchor="<?=$anchor?>" font-size="8.5" fill="<?=$fc?>" font-weight="<?=$isTonight||$isDCIChamp?'700':'400'?>"><?= htmlspecialchars($city) ?></text>
            </g>
            <?php endforeach; ?>
            <!-- Legend -->
            <circle cx="20" cy="408" r="5" fill="#7DD9A2"/>
            <text x="29" y="412" font-size="9" fill="rgba(255,255,255,0.45)">Upcoming</text>
            <circle cx="102" cy="408" r="4" fill="rgba(255,255,255,0.25)" opacity="0.5"/>
            <text x="110" y="412" font-size="9" fill="rgba(255,255,255,0.3)">Past</text>
            <circle cx="152" cy="408" r="5" fill="#B01A1C"/>
            <text x="161" y="412" font-size="9" fill="rgba(255,255,255,0.45)">Tonight</text>
            <circle cx="218" cy="408" r="6" fill="#FFD700"/>
            <text x="228" y="412" font-size="9" fill="rgba(255,255,255,0.45)">DCI Championships</text>
          </svg>
        </div>
      </div>
    </div>

    <script>
      var mKeys   = <?php echo json_encode(array_keys($eventsByMon)); ?>;
      var mLabels = <?php echo json_encode(array_values(array_map(function($d){return $d['meta']['label'];}, $eventsByMon))); ?>;
      var mPhases = <?php echo json_encode(array_values(array_map(function($d){return $d['meta']['phase'] ?? '';}, $eventsByMon))); ?>;
      var curIdx  = <?php echo $curIdx; ?>;
      function calShow(idx) {
        document.querySelectorAll('.month-view').forEach(function(el){el.classList.remove('active');});
        document.getElementById('mv-' + mKeys[idx]).classList.add('active');
        document.getElementById('cal-month-label').textContent = mLabels[idx];
        document.getElementById('cal-month-phase').textContent = mPhases[idx];
        document.getElementById('cal-prev').disabled = (idx === 0);
        document.getElementById('cal-next').disabled = (idx === mKeys.length - 1);
        curIdx = idx;
      }
      function calNav(dir) { calShow(curIdx + dir); }
      calShow(curIdx);
    </script>
  </div>

<?php if (!empty($_ticker_msgs)): ?>
<?php
  // Double the items so the scroll loops seamlessly
  $ticker_items = array_merge($_ticker_msgs, $_ticker_msgs);
?>
<div class="ticker-bar">
  <div class="ticker-label">Messages</div>
  <div class="ticker-track">
    <div class="ticker-inner">
      <?php foreach ($ticker_items as $tm): ?>
      <span class="ticker-item"><strong><?= htmlspecialchars($tm['name']) ?>:</strong> <?= htmlspecialchars(mb_strimwidth($tm['message'], 0, 80, '…')) ?></span>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="score-toast" id="score-toast"></div>
<script async src="https://www.instagram.com/embed.js"></script>
<script>
  function openLightbox(el) {
    var img = el.querySelector('img');
    document.getElementById('lightbox-img').src = img.src;
    document.getElementById('lightbox-img').alt = img.alt;
    document.getElementById('lightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeLightbox() {
    document.getElementById('lightbox').classList.remove('open');
    document.body.style.overflow = '';
  }
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLightbox();
  });
  function switchTab(name, btn) {
    document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.remove('active'); });
    document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
    if (name === 'more' && typeof calShow === 'function') calShow(curIdx);
  }

  // Score toast notification
  function showScoreToast(score, placement) {
    var t = document.getElementById('score-toast');
    if (!t) return;
    t.textContent = 'Score updated: ' + score + (placement ? ' · ' + placement + ' place' : '');
    t.classList.add('show');
    setTimeout(function(){ t.classList.remove('show'); }, 5500);
  }

<?php if ($_is_show_night): ?>
  // Show Night Mode — poll /score_check.php every 2 minutes for live score updates
  (function() {
    var lastScore = '<?= addslashes($_score['score'] ?? '') ?>';
    var lastPlace = '<?= addslashes($_score['placement'] ?? '') ?>';
    function doCheck() {
      var ts = (new Date()).getTime();
      fetch('/score_check.php?_=' + ts)
        .then(function(r){ return r.json(); })
        .then(function(d) {
          var el = document.getElementById('snb-check-time');
          if (el) {
            var t = new Date();
            var hm = t.getHours().toString().padStart(2,'0') + ':' + t.getMinutes().toString().padStart(2,'0');
            el.textContent = '· Last checked ' + hm;
          }
          if (d.score && d.score !== lastScore) {
            var chip = document.getElementById('live-score-num');
            if (chip) chip.textContent = d.score;
            showScoreToast(d.score, d.placement || '');
            lastScore = d.score;
            lastPlace = d.placement || lastPlace;
          }
        })
        .catch(function(){});
      setTimeout(doCheck, 120000); // re-check every 2 minutes
    }
    setTimeout(doCheck, 30000); // first check after 30 seconds
  })();
<?php endif; ?>

</script>

</body>
</html>