<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

function pdfsInDirectory(string $directory): array
{
    $files = [];

    foreach (new DirectoryIterator($directory) as $pdfFile) {
        if (strtolower($pdfFile->getExtension()) !== 'pdf') {
            continue;
        }

        // Recreating the SplFileInfo here works around some directory iterator weirdness.
        $files[] = new SplFileInfo($pdfFile->getRealPath());
    }

    return $files;
}

function getPagesInPdf(SplFileInfo $file): int
{
    $output = shell_exec('pdfinfo ' . escapeshellarg($file->getRealPath()) . ' 2> /dev/null'); // pdfinfo is noisy on stderr
    $matches = [];
    preg_match('~Pages:\s+(\d+)~', $output, $matches);
    if (!isset($matches[1])) {
        die('Error reading pages for ' . $file->getFilename());
    }

    return (int)$matches[1];
}

function prettyPrintPdfInfo(SplFileInfo $file): string
{
    $output = ucwords($file->getBasename('.' . $file->getExtension()));
    $output .= ' (' . getPagesInPdf($file) . ' slides)';
    return $output;
}

function zeroIndexedToOneIndexed(array $zIndexed): array
{
    $oneIndexed = [];

    foreach (array_values($zIndexed) as $index => $item) {
        $oneIndexed[$index + 1] = $item;
    }

    return $oneIndexed;
}
