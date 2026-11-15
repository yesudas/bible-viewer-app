<?php

/**
 * Simple JSON counters in counters/ (runtime data, gitignored, blocked from the web by counters/.htaccess).
 *   total counters   counters/{name}.txt  {"total": N}
 *   keyed counters   counters/{name}.txt  {"items": {"key": N}}
 * Every write happens under an exclusive lock.
 */
function viewerCounterFile(string $name): string
{
    return __DIR__ . '/../counters/' . $name . '.txt';
}

function viewerCounterOpen(string $name)
{
    $f = viewerCounterFile($name);
    if (!is_dir(dirname($f))) {
        @mkdir(dirname($f), 0775, true);
    }
    return @fopen($f, 'c+');
}

function viewerReadCounter(string $name): int
{
    $f = viewerCounterFile($name);
    $d = is_file($f) ? json_decode((string) file_get_contents($f), true) : null;
    return is_array($d) ? (int) ($d['total'] ?? 0) : (is_numeric($d) ? (int) $d : 0);
}

function viewerIncrementCounter(string $name, int $by = 1): int
{
    $fh = viewerCounterOpen($name);
    if (!$fh) {
        return viewerReadCounter($name);
    }
    flock($fh, LOCK_EX);
    $raw = (string) stream_get_contents($fh);
    $d = $raw !== '' ? json_decode($raw, true) : null;
    $total = (is_array($d) ? (int) ($d['total'] ?? 0) : (int) $d) + $by;
    ftruncate($fh, 0);
    rewind($fh);
    fwrite($fh, json_encode(['total' => $total]));
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
    return $total;
}

/**
 * Adds to one or more keys of a keyed counter in a single locked write.
 * $maxKeys > 0 keeps the map bounded: by default the newest N keys (by key order, for per-day maps);
 * with $keepTop the N highest counts instead (for maps keyed by free-form client text, e.g. words).
 * @param array<string,int> $keys key => amount
 */
function viewerIncrementMap(string $name, array $keys, int $maxKeys = 0, bool $keepTop = false): void
{
    if (!$keys) {
        return;
    }
    $fh = viewerCounterOpen($name);
    if (!$fh) {
        return;
    }
    flock($fh, LOCK_EX);
    $raw = (string) stream_get_contents($fh);
    $d = $raw !== '' ? json_decode($raw, true) : null;
    $items = is_array($d['items'] ?? null) ? $d['items'] : [];
    foreach ($keys as $k => $by) {
        $items[(string) $k] = (int) ($items[(string) $k] ?? 0) + $by;
    }
    if ($maxKeys > 0 && count($items) > $maxKeys) {
        if ($keepTop) {
            arsort($items);
            $items = array_slice($items, 0, $maxKeys, true);
        } else {
            ksort($items);
            $items = array_slice($items, -$maxKeys, null, true);
        }
    }
    ftruncate($fh, 0);
    rewind($fh);
    fwrite($fh, json_encode(['items' => $items], JSON_UNESCAPED_UNICODE));
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
}

/** @return array<string,int> highest first */
function viewerReadMap(string $name): array
{
    $f = viewerCounterFile($name);
    $d = is_file($f) ? json_decode((string) file_get_contents($f), true) : null;
    $items = is_array($d['items'] ?? null) ? array_map('intval', $d['items']) : [];
    arsort($items);
    return $items;
}

/** A per-day map ordered oldest → newest. @return array<string,int> */
function viewerReadDayMap(string $name): array
{
    $items = viewerReadMap($name);
    ksort($items);
    return $items;
}
