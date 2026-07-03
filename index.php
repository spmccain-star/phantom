<?php
$today = date('Y-m-d');
$events = [
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
    '2026-07-20' => ['label' => 'McKinney Show',                     'detail' => 'H: Pilot Point Middle School · No Fly',                   'type' => 'show'],
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
    '2026-08-01' => ['label' => 'Allentown Show',                    'detail' => 'H: Wilson High School · ABE',                             'type' => 'show'],
    '2026-08-02' => ['label' => 'Travel — Indiana',                  'detail' => 'H: Indiana University of PA',                            'type' => 'travel'],
    '2026-08-03' => ['label' => 'Lexington, KY Show',                'detail' => 'H: Lexington Catholic HS',                               'type' => 'show'],
    '2026-08-04' => ['label' => 'Rehearsal',                         'detail' => "Carmel Dad\'s Club, Carmel IN · IND",                     'type' => 'rehearsal'],
    '2026-08-05' => ['label' => 'Rehearsal',                         'detail' => "Carmel Dad\'s Club, Carmel IN · IND",                     'type' => 'rehearsal'],
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
  <title>Come watch Mateo perform — DCI San Antonio</title>
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

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: var(--page-bg);
      color: var(--text);
      min-height: 100vh;
      padding-bottom: 3rem;
      font-size: 16px;
    }

    .hero {
      position: relative;
      width: 100%;
      height: clamp(320px, 50vw, 520px);
      overflow: hidden;
      background: #000;
    }
    .hero > img {
      width: 100%; height: 100%;
      object-fit: cover; object-position: center top;
      display: block;
    }
    .hero-overlay {
      position: absolute; inset: 0;
      background: linear-gradient(to bottom, rgba(0,0,0,0.05) 30%, rgba(0,0,0,0.82) 100%);
      display: flex; flex-direction: column; justify-content: flex-end;
      padding: clamp(1.25rem, 4vw, 2.5rem);
    }
    .hero-eyebrow { font-size: 12px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: #E07070; margin-bottom: 0.5rem; }
    .hero-title { font-family: 'Playfair Display', Georgia, serif; font-size: clamp(28px, 5vw, 48px); font-weight: 700; line-height: 1.1; color: #fff; margin-bottom: 0.75rem; text-shadow: 0 2px 12px rgba(0,0,0,0.6); }
    .hero-sub { font-size: 16px; color: rgba(255,255,255,0.75); line-height: 1.5; margin-bottom: 0.75rem; }
    .show-pill { display: inline-block; background: rgba(176,26,28,0.75); border: 1px solid rgba(255,255,255,0.2); color: #fff; font-size: 13px; font-weight: 600; padding: 5px 14px; border-radius: 20px; font-style: italic; backdrop-filter: blur(4px); }

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
    .social-section { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2rem; }
    .reel-wrap { border-radius: var(--radius); overflow: hidden; }
    .ig-follow-btn { display: flex; align-items: center; gap: 12px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1rem 1.5rem; color: var(--text); text-decoration: none; font-size: 15px; font-weight: 500; transition: border-color 0.15s, background 0.15s; }
    .ig-follow-btn:hover { border-color: var(--border-strong); background: var(--surface-2); }
    .ig-follow-btn .link-arrow { margin-left: auto; color: var(--text-muted); }

    .gallery-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 2rem; }
    .gallery-grid .wide { grid-column: span 2; }
    .gallery-item { border-radius: 12px; overflow: hidden; aspect-ratio: 4 / 3; background: var(--surface-2); cursor: pointer; position: relative; }
    .gallery-item.tall { aspect-ratio: 3 / 4; }
    .gallery-item.wide { aspect-ratio: 16 / 7; }
    .gallery-item img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.3s ease; }
    .gallery-item:hover img { transform: scale(1.03); }
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
    .cal-month-name { font-size: 1.25rem; font-weight: 700; color: var(--text); }
    .cal-phase { font-size: 0.82rem; color: var(--text-secondary); margin-top: 4px; }
    .cal-legend { display: flex; flex-wrap: wrap; gap: 6px 14px; margin-bottom: 1rem; }
    .cal-legend-item { display: flex; align-items: center; gap: 5px; font-size: 0.72rem; color: var(--text-secondary); }
    .cal-legend-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
    .cal-wrap { background: #161616; border: 1px solid var(--border); border-radius: 0 0 10px 10px; overflow: hidden; }
    .cal-dow-row { display: grid; grid-template-columns: repeat(7, 1fr); border-bottom: 1px solid var(--border); }
    .cal-dow { text-align: center; padding: 10px 4px; font-size: 0.72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--text-muted); }
    .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); }
    .cal-cell { min-height: 100px; padding: 8px; border-right: 1px solid rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.05); }
    .cal-cell:nth-child(7n) { border-right: none; }
    .cal-cell.empty { background: transparent; }
    .cal-cell.today { background: rgba(176,26,28,0.07); }
    .cal-cell.today .day-num { color: #B01A1C; font-weight: 800; }
    .day-num { font-size: 0.82rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 5px; display: block; }
    .event-pill { display: block; border-radius: 5px; padding: 4px 7px; margin-bottom: 4px; font-size: 0.72rem; font-weight: 600; line-height: 1.3; color: #000; cursor: default; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; position: relative; }
    .event-pill.dci { font-size: 0.78rem; font-weight: 800; letter-spacing: .02em; }
    .event-pill:hover::after { content: attr(data-detail); position: absolute; bottom: calc(100% + 4px); left: 0; min-width: 150px; max-width: 220px; background: #2a2a2a; color: #F2F0EA; border: 1px solid rgba(255,255,255,0.12); border-radius: 6px; padding: 6px 9px; font-size: 0.7rem; font-weight: 400; white-space: normal; z-index: 10; pointer-events: none; box-shadow: 0 4px 12px rgba(0,0,0,.6); }
    .dci-info-box { margin-top: 1.5rem; background: rgba(176,26,28,0.06); border: 1px solid rgba(176,26,28,0.2); border-radius: 10px; padding: 14px 18px; font-size: 0.82rem; color: var(--text-secondary); }
    .dci-info-box strong { color: #FFD700; display: block; margin-bottom: 6px; font-size: 0.88rem; }
    .month-view { display: none; }
    .month-view.active { display: block; }

    .cal-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .cal-dow-row, .cal-grid { min-width: 280px; }
    .cal-phase { white-space: normal; line-height: 1.4; }

    @media (max-width: 600px) {
      .cal-cell { min-height: 58px; padding: 3px; }
      .day-num { font-size: 0.72rem; margin-bottom: 3px; }
      .event-pill { font-size: 0.58rem; padding: 2px 3px; }
      .event-pill:hover::after { display: none; }
      .cal-month-name { font-size: 1rem; }
      .cal-phase { font-size: 0.72rem; }
      .tab-btn { font-size: 13px; padding: 14px 4px 11px; }
      .dci-info-box { font-size: 0.78rem; }
      .dci-info-box p { margin-top: 4px; }
    }
  </style>
</head>
<body>

  <div class="hero">
    <img src="/assets/mateo.jpg" alt="Mateo — Phantom Regiment 2026">
    <div class="hero-overlay">
      <div class="hero-eyebrow">Saturday, July 18, 2026 &nbsp;&middot;&nbsp; San Antonio, TX</div>
      <div class="hero-title">Come watch Mateo perform!</div>
      <div class="hero-sub">Phantom Regiment &middot; <em>Bloodline</em> &middot; DCI Southwestern Championship</div>
      <div><span class="show-pill">Bloodline</span></div>
    </div>
  </div>

  <nav class="tab-bar">
    <button class="tab-btn active" onclick="switchTab('watch', this)">Latest</button>
    <button class="tab-btn" onclick="switchTab('more', this)">Media</button>
    <button class="tab-btn" onclick="switchTab('schedule', this)">Dates</button>
  </nav>

  <div class="tab-panel active" id="tab-watch">
    <div class="content">
      <div class="section-label" style="margin-top:1.5rem;">3 ways to watch</div>
      <div class="cards">

      <!-- Option 1 -->
      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-option-num">Option 1</div>
            <div class="card-title">Free rehearsal</div>
          </div>
          <span class="badge badge-free">Free</span>
        </div>
        <div class="card-body">
          <p>Phantom Regiment rehearses at a high school about 1 hr 40 min south of the Alamodome. Free, up-close, and you'll likely see them run the full show.</p>
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
            Address TBD — checking with Mateo
          </div>
        </div>
      </div>

      <!-- Option 2 -->
      <div class="card featured">
        <div class="card-header">
          <div>
            <div class="card-option-num">Option 2</div>
            <div class="card-title">The Lots</div>
          </div>
          <span class="badge badge-us">We'll be here</span>
        </div>
        <div class="card-body">
          <p>Starting around 6 PM, corps warm up in the parking lots around the Alamodome in partial uniform. You'll see the horn lines and snare lines running drills separately — not the full show, but a cool behind-the-scenes look at how it all comes together.</p>
          <p>You're free to roam and listen to different sections up close. Multiple corps will be doing the same thing. Phantom wears bright red — hard to miss. Once we find them, we'll text everyone a pin so you can find us!</p>
          <p>If you're joining us, let us know — once we get a head count we can figure out if dinner together works out after!</p>
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
            <div class="card-option-num">Option 3</div>
            <div class="card-title">Official performance</div>
          </div>
        </div>
        <div class="card-body">
          <p>Phantom performs <em>Bloodline</em> inside the Alamodome around 9 PM. The full event runs from 1:30 PM. Mateo will be near <strong>Section 116</strong> on the field sideline. Exact times post day-of.</p>
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
            Mateo performs ~9 PM
          </div>
        </div>
        <a class="ticket-btn" href="https://www.ticketmaster.com/event/3A00636CF94C7C32" target="_blank" rel="noopener">
          Buy tickets on Ticketmaster →
        </a>
      </div>

    </div>
      <div class="section-label">More about Phantom Regiment</div>
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

  <div class="tab-panel" id="tab-schedule">
    <!-- Full Season Calendar -->
  <div class="calendar-section">

    <div class="cal-nav">
      <button class="cal-nav-btn" id="cal-prev" onclick="calNav(-1)" aria-label="Previous month">&#8592;</button>
      <div class="cal-nav-center">
        <div class="cal-month-name" id="cal-month-name"></div>
        <div class="cal-phase" id="cal-phase"></div>
      </div>
      <button class="cal-nav-btn" id="cal-next" onclick="calNav(1)" aria-label="Next month">&#8594;</button>
    </div>

    <div class="cal-legend">
      <?php foreach ([
        'show'      => ['#7DD9A2','Show'],
        'dci'       => ['#FFD700','DCI Championships'],
        'rehearsal' => ['#5E9BD6','Rehearsal / NIU'],
        'practice'  => ['#4FD1C0','Practice'],
        'travel'    => ['#7A7A85','Travel'],
        'free'      => ['#FFB59E','Free Day'],
        'milestone' => ['#FFD97D','Milestone'],
      ] as $t => [$c, $l]): ?>
      <div class="cal-legend-item">
        <div class="cal-legend-dot" style="background:<?= $c ?>"></div>
        <?= $l ?>
      </div>
      <?php endforeach; ?>
    </div>

    <?php foreach ($months as $idx => $m):
      $year = $m['year']; $mon = $m['month'];
      $first_dow = (int)date('w', mktime(0,0,0,$mon,1,$year));
      $days_in_month = (int)date('t', mktime(0,0,0,$mon,1,$year));
    ?>
    <div class="month-view" id="month-<?= $idx ?>"
         data-name="<?= htmlspecialchars($m['name']) ?>"
         data-phase="<?= htmlspecialchars($m['phase']) ?>">
      <div class="cal-wrap">
        <div class="cal-dow-row">
          <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d): ?>
            <div class="cal-dow"><?= $d ?></div>
          <?php endforeach; ?>
        </div>
        <div class="cal-grid">
          <?php
          for ($i = 0; $i < $first_dow; $i++) echo '<div class="cal-cell empty"></div>';
          for ($day = 1; $day <= $days_in_month; $day++):
            $date_str = sprintf('%04d-%02d-%02d', $year, $mon, $day);
            $ev = $events[$date_str] ?? null;
            $is_today = ($date_str === $today);
            $cls = 'cal-cell' . ($is_today ? ' today' : '');
          ?>
          <div class="<?= $cls ?>">
            <span class="day-num"><?= $day ?></span>
            <?php if ($ev):
              $color  = $type_colors[$ev['type']];
              $pcls   = 'event-pill' . ($ev['type'] === 'dci' ? ' dci' : '');
            ?>
            <span class="<?= $pcls ?>" style="background:<?= $color ?>" data-detail="<?= htmlspecialchars($ev['detail']) ?>"><?= htmlspecialchars($ev['label']) ?></span>
            <?php endif; ?>
          </div>
          <?php endfor;
          $total = $first_dow + $days_in_month;
          $trailing = (7 - ($total % 7)) % 7;
          for ($i = 0; $i < $trailing; $i++) echo '<div class="cal-cell empty"></div>';
          ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <div class="dci-info-box" id="dci-info-box" style="display:none;">
      <strong>DCI Championships — Indianapolis, IN (Aug 6–9)</strong>
      <p>Housing: Lawrence Armory · 9920 E 59th St, Indianapolis IN 46216</p>
      <p>Rehearsal (Tue–Fri): Carmel Dad's Club · 5459 E Main St, Carmel IN 46033</p>
    </div>

  </div>

  <script>
    var CAL_COUNT = <?= count($months) ?>;
    var CAL_LAST  = CAL_COUNT - 1;

    // Default to the current month if it falls in range, otherwise first month
    var todayStr = '<?= $today ?>';
    var todayMonth = parseInt(todayStr.slice(5,7), 10);
    var monthMap = <?= json_encode(array_map(fn($m) => $m['month'], $months)) ?>;
    var initIdx = monthMap.indexOf(todayMonth);
    if (initIdx === -1) initIdx = 0;

    var curIdx = initIdx;

    function calShow(idx) {
      document.querySelectorAll('.month-view').forEach(function(el) { el.classList.remove('active'); });
      var el = document.getElementById('month-' + idx);
      el.classList.add('active');
      document.getElementById('cal-month-name').textContent = el.dataset.name;
      document.getElementById('cal-phase').textContent       = el.dataset.phase;
      document.getElementById('cal-prev').disabled = (idx === 0);
      document.getElementById('cal-next').disabled = (idx === CAL_LAST);
      document.getElementById('dci-info-box').style.display = (idx === CAL_LAST) ? 'block' : 'none';
    }

    function calNav(dir) {
      var next = curIdx + dir;
      if (next < 0 || next > CAL_LAST) return;
      curIdx = next;
      calShow(curIdx);
    }

    calShow(curIdx);
  </script>
  </div>


  <!-- Tab: More -->
  <div class="tab-panel" id="tab-more">
    <div class="content">
      <div class="footer-card">
      We're also flying to <strong>Indianapolis on August 8</strong> to watch Mateo's final performance at Lucas Oil Stadium. Can't wait — hope to see some of you in San Antonio! 🎶
    </div>
      <div class="section-label" style="margin-top:0.5rem;">Photos</div>
      <div class="gallery-grid" id="gallery">
      <div class="gallery-item wide" onclick="openLightbox(this)">
        <img src="/assets/mateo.jpg" alt="Phantom Regiment snare line rehearsal" loading="lazy" />
      </div>
      <div class="gallery-item" onclick="openLightbox(this)">
        <img src="/assets/mateo.jpg" alt="Phantom Regiment — Bloodline" loading="lazy" />
      </div>
      <div class="gallery-item" onclick="openLightbox(this)">
        <img src="/assets/mateo.jpg" alt="Phantom Regiment percussion" loading="lazy" />
      </div>
    </div>

    <div class="section-label" style="margin-top:1.75rem;">Follow along</div>

    <div class="social-section">
      <div class="video-wrap">
        <iframe
          src="https://www.youtube.com/embed/lrOzW2I4r3U"
          title="Phantom Regiment 2026"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen>
        </iframe>
      </div>

      <div class="reel-wrap">
        <blockquote
          class="instagram-media"
          data-instgrm-permalink="https://www.instagram.com/reel/DaIBv_ABv5a/"
          data-instgrm-version="14"
          style="background:#FFF;border:0;border-radius:3px;box-shadow:0 0 1px 0 rgba(0,0,0,.5),0 1px 10px 0 rgba(0,0,0,.15);margin:0;max-width:100%;min-width:0;padding:0;width:calc(100% - 2px);">
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

  <!-- Lightbox overlay -->
  <div class="lightbox" id="lightbox" onclick="closeLightbox()">
    <button class="lightbox-close" onclick="closeLightbox()">×</button>
    <img id="lightbox-img" src="" alt="" />
  </div>

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
    if (name === 'schedule' && typeof calShow === 'function') calShow(curIdx);
  }
</script>

</body>
</html>
