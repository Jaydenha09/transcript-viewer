<?php

function sanitizeUrl($url) {
    // Remove potentially harmful characters
    $url = filter_var($url, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);

    // Encode URL components
    $url = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&#39;', '&lt;', '&gt;'), $url);

    return $url;
}

function validateUrl($url) {
    $isValid = filter_var($url, FILTER_VALIDATE_URL);
    if ($isValid !== false) {
        $urlComponents = parse_url($url);
        if (!isset($urlComponents['scheme']) || !isset($urlComponents['host'])) {
            return false;
        }
        // Validate the host against the Discord CDN server
        if ($urlComponents['host'] === 'cdn.discordapp.com') {
            return true;
        }
    }
    return false;
}

function redirectHttps() {
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        // Check for trusted proxies or load balancers
        $trustedProxies = ['127.0.0.1']; // Add the IP addresses of your trusted proxies
        $httpsForwarded = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https';
        $httpForwarded = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'http';
        if ($httpsForwarded || ($httpForwarded && in_array($_SERVER['REMOTE_ADDR'], $trustedProxies))) {
            // Redirect to HTTPS
            $httpsUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('Location: ' . $httpsUrl);
            exit;
        }
    }
}

function fetchHtmlContent($url) {
    // Fetch the HTML content from the external URL
    $html = file_get_contents($url);
    return $html;
}

if (isset($_GET['url'])) {
    $url = $_GET['url'];

    // Sanitize and validate the URL parameter
    $url = sanitizeUrl($url);
    if (validateUrl($url)) {
        // Enforce HTTPS
        redirectHttps();

        // Fetch the HTML content from the external URL
        $html = fetchHtmlContent($url);
        if ($html !== false) {
            // Output the HTML content
            echo $html;
        } else {
            echo "Invalid URL or unsupported content type.";
        }
    } else {
        echo "Invalid URL.";
    }
} else {
    echo "URL parameter not provided.";
}
?>
