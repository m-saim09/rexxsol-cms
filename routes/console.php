<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Support\MediaReferenceBackfill;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('media:register-existing {--apply : Register verified, missing CMS image references}', function (MediaReferenceBackfill $backfill): int {
    $apply = (bool) $this->option('apply');
    $report = $backfill->run($apply);

    $this->table(['Check', 'Count'], [
        ['Distinct CMS image paths', $report['references']],
        ['Already registered', $report['registered']],
        [$apply ? 'New records created' : 'Would register', $apply ? $report['created'] : count($report['would_register'])],
        [$apply ? 'Checksums populated' : 'Would populate checksums', $apply ? $report['checksums_populated'] : count($report['would_populate_checksums'])],
        ['Shared file content', count($report['checksum_shared'])],
        ['Checksum ambiguities', count($report['checksum_ambiguous'])],
        ['Missing registered files', count($report['media_files_missing'])],
        ['Missing files', count($report['missing_files'])],
        ['Ambiguous', count($report['ambiguous'])],
        ['Unsupported files', count($report['unsupported'])],
        ['Broken media-ID references', count($report['dangling_media_ids'])],
    ]);

    foreach (['would_register', 'would_populate_checksums', 'checksum_shared', 'checksum_ambiguous', 'media_files_missing', 'missing_files', 'ambiguous', 'unsupported', 'dangling_media_ids'] as $section) {
        foreach ($report[$section] as $item) {
            $this->line(strtoupper(str_replace('_', ' ', $section)).': '.$item);
        }
    }

    if (! $apply) {
        $this->comment('Dry run only. Re-run with --apply to register eligible public images.');
    }

    return $report['checksum_ambiguous'] || $report['media_files_missing'] || $report['missing_files'] || $report['ambiguous'] || $report['unsupported'] || $report['dangling_media_ids']
        ? self::FAILURE
        : self::SUCCESS;
})->purpose('Compare CMS image references and safely register untracked public images');
