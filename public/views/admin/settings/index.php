<?php
/** Requires $pending and $applied in scope. Super-admin-only. */
require __DIR__ . '/../layout-header.php';

$pendingCount = count($pending);
?>

<p class="text-sm text-neutral-500 max-w-2xl mb-6">
  After you upload a new version of the site, any new PHP migrations wait here.
  Click once to apply them — you do not need SSH or raw SQL.
</p>

<div class="bg-white border border-neutral-200 rounded-xl shadow-sm overflow-hidden mb-6">
  <div class="px-5 py-4 border-b border-neutral-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h2 class="font-serif-heading text-lg font-bold text-[#0f2852]">Database migrations</h2>
      <p class="text-xs text-neutral-500 mt-0.5">
        <?php if ($pendingCount): ?>
          <?= (int) $pendingCount ?> new update<?= $pendingCount === 1 ? '' : 's' ?> waiting to run.
        <?php else: ?>
          Everything is up to date.
        <?php endif; ?>
      </p>
    </div>
    <?php if ($pendingCount): ?>
      <form method="post" action="<?= url('/admin/settings/migrate') ?>">
        <?= csrfField() ?>
        <input type="hidden" name="redirect_to" value="/admin/settings">
        <button type="submit" class="bg-amber-400 hover:bg-amber-300 text-[#132c5c] text-xs font-bold px-5 py-2.5 rounded-lg uppercase tracking-widest transition-colors cursor-pointer whitespace-nowrap">
          Run migrations
        </button>
      </form>
    <?php else: ?>
      <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">Up to date</span>
    <?php endif; ?>
  </div>

  <?php if ($pendingCount): ?>
    <div class="px-5 py-4 bg-amber-50 border-b border-amber-100">
      <p class="text-[10px] font-bold uppercase tracking-wider text-amber-800 mb-2">Pending</p>
      <ul class="space-y-1.5">
        <?php foreach ($pending as $item): ?>
          <li class="text-sm font-mono text-amber-950"><?= e($item['name']) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
      <thead class="bg-neutral-50 text-neutral-500 uppercase tracking-wider text-[10px]">
        <tr>
          <th class="px-5 py-3">Applied migration</th>
          <th class="px-5 py-3">Ran at</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-neutral-100">
        <?php if (!$applied): ?>
          <tr><td colspan="2" class="px-5 py-8 text-center text-neutral-400">No migrations have been recorded yet.</td></tr>
        <?php endif; ?>
        <?php foreach (array_reverse($applied) as $row): ?>
          <tr class="hover:bg-neutral-50">
            <td class="px-5 py-3 font-mono text-neutral-800"><?= e($row['migration']) ?></td>
            <td class="px-5 py-3 text-neutral-500 whitespace-nowrap"><?= e($row['applied_at'] ? date('M j, Y g:ia', strtotime($row['applied_at'])) : '—') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../layout-footer.php'; ?>
