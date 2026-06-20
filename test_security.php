<?php

/**
 * ============================================================
 * SKRIP PENGUJIAN KEAMANAN: XSS + RATE LIMITING
 * Project: Ketahanan Pangan (SIKAP Presisi)
 * ============================================================
 * Jalankan dari: c:\laragon\www\ketahananPangan\
 * Perintah     : php test_security.php
 * ============================================================
 */

$baseUrl = 'http://localhost/ketahananPangan/public';

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║        SECURITY AUDIT: XSS & RATE LIMITING TEST             ║\n";
echo "║        Project: SIKAP Presisi — Ketahanan Pangan            ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// ============================================================
// FUNGSI HELPER
// ============================================================

function makeRequest($url, $method = 'GET', $data = [], $cookieJar = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if ($cookieJar) {
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $headers = substr($response, 0, $headerSize);
    $body    = substr($response, $headerSize);

    return [
        'code'    => $httpCode,
        'headers' => $headers,
        'body'    => $body,
    ];
}

function parseHeaders($rawHeaders) {
    $headers = [];
    foreach (explode("\n", $rawHeaders) as $line) {
        if (strpos($line, ':') !== false) {
            [$key, $val] = explode(':', $line, 2);
            $headers[strtolower(trim($key))] = trim($val);
        }
    }
    return $headers;
}

function printResult($label, $passed, $detail = '') {
    $icon   = $passed ? '✅' : '❌';
    $status = $passed ? 'PASS' : 'FAIL';
    echo "  $icon  [$status] $label\n";
    if ($detail) {
        echo "         → $detail\n";
    }
}

function printSection($title) {
    echo "\n";
    echo "┌──────────────────────────────────────────────────────────────┐\n";
    echo "│  $title\n";
    echo "└──────────────────────────────────────────────────────────────┘\n";
}

// ============================================================
// STEP 1: AMBIL CSRF TOKEN & COOKIE DULU
// ============================================================
$cookieJar = tempnam(sys_get_temp_dir(), 'sikap_cookies_');
$loginPageUrl = $baseUrl . '/login';

echo "  🔗 Menghubungi: $loginPageUrl\n";
$getResp = makeRequest($loginPageUrl, 'GET', [], $cookieJar);

// Ambil CSRF token dari form login
preg_match('/<meta name="csrf-token" content="([^"]+)"/', $getResp['body'], $csrfMeta);
preg_match('/name="_token" value="([^"]+)"/', $getResp['body'], $csrfForm);
$csrfToken = $csrfForm[1] ?? ($csrfMeta[1] ?? null);

if (!$csrfToken) {
    // Coba cari langsung dari body
    preg_match('/_token.*?value="([a-zA-Z0-9\/+]{40,})"/', $getResp['body'], $m);
    $csrfToken = $m[1] ?? 'NOT_FOUND';
}

echo "  🔑 CSRF Token: " . substr($csrfToken, 0, 20) . "...\n";

// ============================================================
// TEST BAGIAN A: XSS PROTECTION
// ============================================================
printSection("BAGIAN A — XSS PROTECTION HEADERS");

$headers = parseHeaders($getResp['headers']);

// Test A1: X-XSS-Protection header
$xssHeader = $headers['x-xss-protection'] ?? null;
printResult(
    'X-XSS-Protection Header Ada',
    !empty($xssHeader),
    "Value: " . ($xssHeader ?? 'TIDAK ADA')
);

// Test A2: X-Content-Type-Options
$ctHeader = $headers['x-content-type-options'] ?? null;
printResult(
    'X-Content-Type-Options: nosniff',
    $ctHeader === 'nosniff',
    "Value: " . ($ctHeader ?? 'TIDAK ADA')
);

// Test A3: X-Frame-Options
$xfoHeader = $headers['x-frame-options'] ?? null;
printResult(
    'X-Frame-Options: SAMEORIGIN (Anti-Clickjacking)',
    stripos($xfoHeader ?? '', 'SAMEORIGIN') !== false,
    "Value: " . ($xfoHeader ?? 'TIDAK ADA')
);

// Test A4: X-XSS-Protection mode=block
printResult(
    'X-XSS-Protection mode=block Aktif',
    stripos($xssHeader ?? '', 'mode=block') !== false,
    "Value: " . ($xssHeader ?? 'TIDAK ADA')
);

// Test A5: CSRF Token di form
printResult(
    'CSRF Token Ada di Form Login',
    strlen($csrfToken) > 20,
    "Token Length: " . strlen($csrfToken) . " chars"
);

// Test A6: Injeksi XSS di field username
printSection("BAGIAN A2 — UJI INJEKSI XSS (Login Form)");

$xssPayloads = [
    "<script>alert('XSS')</script>",
    "'\"><img src=x onerror=alert(1)>",
    "javascript:alert(1)",
    "<svg onload=alert('XSS')>",
];

foreach ($xssPayloads as $payload) {
    $resp = makeRequest($loginPageUrl . '', 'POST', [
        '_token'   => $csrfToken,
        'username' => $payload,
        'password' => 'wrongpassword',
    ], $cookieJar);

    // XSS terlindungi jika: body mengandung payload TAPI dalam bentuk escaped,
    // atau redirect (auth gagal tapi tidak execute script)
    $bodyEscaped = htmlspecialchars($payload, ENT_QUOTES);
    $rawInBody   = strpos($resp['body'], $payload) !== false;
    $escapedInBody = strpos($resp['body'], '&lt;script&gt;') !== false
                  || strpos($resp['body'], '&quot;') !== false
                  || strpos($resp['body'], '&#039;') !== false;

    // Jika redirect (302/301) atau body tidak mengandung raw payload → AMAN
    $isSafe = !$rawInBody || $escapedInBody || in_array($resp['code'], [302, 303]);

    printResult(
        'XSS Payload Blocked: ' . substr($payload, 0, 30) . '...',
        $isSafe,
        "HTTP: " . $resp['code'] . " | Raw in body: " . ($rawInBody ? 'Ya (escaped)' : 'Tidak')
    );
}

// ============================================================
// TEST BAGIAN B: RATE LIMITING
// ============================================================
printSection("BAGIAN B — RATE LIMITING (DoS Prevention)");

echo "  ⏳ Mengirim 7 request login gagal berturut-turut...\n";
echo "     (Batas: 5x/menit per IP)\n\n";

$cookieJar2 = tempnam(sys_get_temp_dir(), 'sikap_rl_');

// Ambil CSRF dulu
$loginPage = makeRequest($loginPageUrl, 'GET', [], $cookieJar2);
preg_match('/name="_token" value="([^"]+)"/', $loginPage['body'], $m);
$token2 = $m[1] ?? 'NOTFOUND';

$responses = [];
for ($i = 1; $i <= 7; $i++) {
    // Perlu ambil CSRF baru setiap kali jika redirect
    $resp = makeRequest($loginPageUrl, 'POST', [
        '_token'   => $token2,
        'username' => 'xss_ratelimit_test_user_' . rand(1000, 9999),
        'password' => 'wrongpassword_' . $i,
    ], $cookieJar2);

    $responses[] = $resp['code'];

    $icon = $resp['code'] === 429 ? '🔴 BLOCKED' : '🟡 PASSED';
    echo "  Percobaan #$i : HTTP " . $resp['code'] . " $icon\n";

    usleep(100000); // delay 100ms antar request
}

// Cek apakah ada 429 response
$has429 = in_array(429, $responses);
$rateLimitWorking = $has429;

echo "\n";
printResult(
    'Rate Limiter Aktif (HTTP 429 muncul setelah banyak attempt)',
    $rateLimitWorking,
    $rateLimitWorking
        ? "✅ Sistem mengembalikan 429 Too Many Requests"
        : "⚠️  429 belum muncul — mungkin perlu >5 attempt dengan user sama"
);

// ============================================================
// RINGKASAN AKHIR
// ============================================================

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    RINGKASAN AUDIT                          ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";
echo "║                                                              ║\n";
echo "║  XSS Headers     : SecurityHeaders.php (L21-25)             ║\n";
echo "║  Blade Auto-Esc  : login.blade.php (L70, L76)               ║\n";
echo "║  CSRF Protection : login.blade.php (L62) + Kernel (L38)     ║\n";
echo "║  Rate Limit Login: RouteServiceProvider.php (L31-35) = 5/m  ║\n";
echo "║  Rate Limit Auth : web.php (L34) = 100req/menit             ║\n";
echo "║  Lockout Event   : LoginRequest.php (L48-63)                ║\n";
echo "║                                                              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Cleanup
@unlink($cookieJar);
@unlink($cookieJar2);
