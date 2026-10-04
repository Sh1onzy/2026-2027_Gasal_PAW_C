<?php

$matkul = array("PTI", "ALPRO", "DPW", "STRUKDAT", "JARKOM", "PAW", "PSBF", "RPL");

$PTI = "PTI";
$ALPRO = "ALPRO";
$DPW = "DPW";
$STRUKDAT = "STRUKDAT";
$JARKOM = "JARKOM";
$PAW = "PAW";

foreach ($matkul as $alias) {
    switch ($alias) {
        case $PTI:
            echo "Saya suka " . $PTI . PHP_EOL;
            break;
        case $ALPRO:
            echo "Saya suka " . $ALPRO . PHP_EOL;
            break;
        case $DPW:
            echo "Saya suka " . $DPW . PHP_EOL;
            break;
        case $STRUKDAT:
            echo "Saya suka " . $STRUKDAT . PHP_EOL;
            break;
        case $JARKOM:
            echo "Saya suka " . $JARKOM . PHP_EOL;
            break;
        case $PAW:
            echo "Saya suka " . $PAW . PHP_EOL;
            break;
        default:
            echo "Saya tidak mengambil matkul " . $alias . PHP_EOL;
            break;
    }
}