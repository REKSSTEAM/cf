<?php
/**
 * shows.st Multi-Source API — parallel requests
 * GET ?type=tv&id=94997&season=1&episode=1
 * GET ?type=movie&id=550
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store');

$type    = ($_GET['type'] ?? 'movie') === 'tv' ? 'tv' : 'movie';
$id      = (int)($_GET['id'] ?? 0);
$season  = (int)($_GET['season']  ?? 1);
$episode = (int)($_GET['episode'] ?? 1);

if (!$id) { echo json_encode(['ok'=>false,'error'=>'id مطلوب']); exit; }

$all_sources = [
    ['id'=>'moviebox',  'name'=>'MovieBox',  'params'=>['sources'=>'moviebox',  'hevc'=>'1']],
    ['id'=>'moviebox2', 'name'=>'MovieBox2', 'params'=>['sources'=>'moviebox2', 'hevc'=>'1']],
    ['id'=>'tcloud',    'name'=>'TCloud',    'params'=>['sources'=>'tcloud',    'sw'=>'1']],
    ['id'=>'ipcloud',   'name'=>'IPCloud',   'params'=>['sources'=>'ipcloud',   'sw'=>'1']],
    ['id'=>'cinefreak', 'name'=>'CineFreak', 'params'=>['sources'=>'cinefreak']],
];

$base_params = $type === 'tv'
    ? ['id'=>$id,'season'=>$season,'episode'=>$episode,'mode'=>'json']
    : ['id'=>$id,'mode'=>'json'];

$req_headers = [
    'User-Agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 Chrome/137.0.0.0 Mobile Safari/537.36',
    'Accept: application/json',
    'Origin: https://player.vidlove.cc',
    'Referer: https://player.vidlove.cc/',
];

$lang_map = [
    'arabic'=>'ar','english'=>'en','french'=>'fr','spanish'=>'es','german'=>'de',
    'turkish'=>'tr','persian'=>'fa','russian'=>'ru','italian'=>'it','portuguese'=>'pt',
    'dutch'=>'nl','polish'=>'pl','czech'=>'cs','romanian'=>'ro','greek'=>'el',
    'hebrew'=>'he','japanese'=>'ja','korean'=>'ko','chinese'=>'zh','indonesian'=>'id',
    'malay'=>'ms','hindi'=>'hi','urdu'=>'ur','thai'=>'th','norwegian'=>'no',
    'danish'=>'da','finnish'=>'fi','swedish'=>'sv','bulgarian'=>'bg','hungarian'=>'hu',
    'vietnamese'=>'vi','tamil'=>'ta','bengali'=>'bn','serbian'=>'sr','croatian'=>'hr',
    'filipino'=>'tl','tagalog'=>'tl','malayalam'=>'ml','telugu'=>'te',
    'hausa'=>'ha','swahili'=>'sw','panjabi'=>'pa','slovenian'=>'sl','slovak'=>'sk',
];

// ── Parallel fetch بـ curl_multi ──────────────────────────────────────────────
$mh      = curl_multi_init();
$handles = [];

foreach ($all_sources as $i => $src) {
    $params = array_merge($base_params, $src['params']);
    $url    = 'https://api.shows.st/' . $type . '?' . http_build_query($params);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => $req_headers,
        CURLOPT_ENCODING       => '',
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    curl_multi_add_handle($mh, $ch);
    $handles[$i] = ['ch' => $ch, 'src' => $src];
}

// تشغيل كل الطلبات بالتوازي
$running = null;
do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh);
} while ($running > 0);

// ── جمع النتائج ──────────────────────────────────────────────────────────────
$responses = [];
foreach ($handles as $i => $h) {
    $raw = curl_multi_getcontent($h['ch']);
    curl_multi_remove_handle($mh, $h['ch']);
    curl_close($h['ch']);
    $data = $raw ? json_decode($raw, true) : null;
    $responses[$i] = ['src' => $h['src'], 'data' => $data];
}
curl_multi_close($mh);

// ── Parse subtitles (من أول رد فيه subtitles) ────────────────────────────────
$subtitles = [];
foreach ($responses as $r) {
    if (!empty($r['data']['subtitles'])) {
        foreach ($r['data']['subtitles'] as $sub) {
            $label = $sub['label'] ?? '';
            $file  = $sub['file']  ?? '';
            if (!$file || $label === 'Vylian') continue;
            $lang  = 'unknown';
            $lower = strtolower(preg_replace('/[\s0-9\(\)\-]+$/', '', $label));
            foreach ($lang_map as $word => $code) {
                if (str_contains($lower, $word)) { $lang = $code; break; }
            }
            $subtitles[$file] = ['label'=>$label,'language'=>$lang,'url'=>$file,'format'=>$sub['type']??'vtt'];
        }
        break; // الترجمات نفسها لكل المصادر
    }
}
$subtitles = array_values($subtitles);
usort($subtitles, function($a,$b) {
    $o=['ar'=>0,'en'=>1]; $oa=$o[$a['language']]??99; $ob=$o[$b['language']]??99;
    return $oa!==$ob ? $oa-$ob : strcmp($a['label'],$b['label']);
});

// ── Parse sources ─────────────────────────────────────────────────────────────
$sources = [];
foreach ($responses as $r) {
    $src  = $r['src'];
    $data = $r['data'];
    if (!$data) continue;

    $source   = $data['source'] ?? [];
    $qualities = [];

    if (!empty($source['qualities'])) {
        foreach ($source['qualities'] as $q) {
            if (empty($q['url'])) continue;
            $qualities[] = [
                'label' => $q['quality'] ?? 'HD',
                'url'   => $q['url'],
                'type'  => $q['type']   ?? 'mp4',
                'codec' => $q['codec']  ?? '',
            ];
        }
    } elseif (!empty($source['url'])) {
        $qualities[] = [
            'label' => $source['label'] ?? 'Auto',
            'url'   => $source['url'],
            'type'  => 'hls',
        ];
    }

    $sources[] = [
        'id'        => $src['id'],
        'name'      => $src['name'],
        'ok'        => !empty($qualities),
        'type'      => !empty($qualities) ? ($qualities[0]['type'] ?? 'hls') : null,
        'qualities' => $qualities,
        'error'     => empty($qualities) ? 'No sources' : null,
    ];
}

echo json_encode([
    'ok'            => !empty(array_filter($sources, fn($s) => $s['ok'])),
    'provider'      => 'shows.st',
    'sources'       => $sources,
    'subtitles'     => $subtitles,
    'subtitle_count'=> count($subtitles),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
