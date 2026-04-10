<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/abdussalamseoex/Multi-Site-Publishing/commits/main");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, "PHP Updater");
$result = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "Status: $httpcode\n";
