<?php
$GLOBALS['__test_failures'] = 0;

function assert_equal($actual, $expected, string $message): void {
    if ($actual === $expected) {
        echo "PASS: {$message}\n";
    } else {
        echo "FAIL: {$message}\n";
        echo "  expected: " . var_export($expected, true) . "\n";
        echo "  actual:   " . var_export($actual, true) . "\n";
        $GLOBALS['__test_failures']++;
    }
}

function test_summary_and_exit(): void {
    if ($GLOBALS['__test_failures'] > 0) {
        echo "\n{$GLOBALS['__test_failures']} failure(s)\n";
        exit(1);
    }
    echo "\nAll tests passed\n";
    exit(0);
}

// Mock WordPress functions for standalone CLI tests (not available in plain PHP)
if (!function_exists('esc_js')) {
    function esc_js(string $text): string {
        return $text;
    }
}
