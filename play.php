#!/usr/bin/env php
<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

use League\CLImate\CLImate;

$cli = new CLImate();

$unplayedPdfFiles = pdfsInDirectory(__DIR__ . '/slides/unplayed');
shuffle($unplayedPdfFiles);

/** @var SplFileInfo[] $playerOptions */
$playerOptions = [];

while (true) {
    $cli->clear();

    if (count($unplayedPdfFiles) === 0) {
        $cli->output->write('Everything was already played! Congrats!');
        exit;
    }

    do {
        $playerOptions[] = array_pop($unplayedPdfFiles);
    } while (count($playerOptions) < 3 && count($unplayedPdfFiles) !== 0);
    $playerOptions = zeroIndexedToOneIndexed($playerOptions);

    $cli->output->write("Possible slide decks:\n");
    foreach ($playerOptions as $index => $playerOption) {
        $cli->output->write($index . '. ' . prettyPrintPdfInfo($playerOption));
    }

    $input = $cli->input("\nWhich deck will you rock?");
    $input->accept(array_keys($playerOptions), true);
    $answer = $input->prompt();

    $fileToPlay = $playerOptions[$answer];
    unset($playerOptions[$answer]);
    $unplayedPdfFiles = array_filter($unplayedPdfFiles, function ($i) use ($fileToPlay) {
        return $i !== $fileToPlay;
    });

    $cli->output->write("\nOkay, here we go. Starting in...");
    $cli->output->write('3...');
    sleep(1);
    $cli->output->write('2...');
    sleep(1);
    $cli->output->write('1...');
    sleep(1);
    $cli->output->write('GO!');
    sleep(1);

    shell_exec('evince -s -i 1 ' . escapeshellarg($fileToPlay->getRealPath()));

    rename($fileToPlay->getRealPath(), __DIR__.'/slides/played/'. $fileToPlay->getFilename());

    $cli->clear();
    $cli->output->write('YOU ROCKED!');

    sleep(2);
};

