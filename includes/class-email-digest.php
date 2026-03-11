<?php
/**
 * Weekly email digest — recap of new reviews.
 *
 * Sends a styled HTML email to the configured recipient(s) every week
 * with: new reviews count, average rating, top reviews, negative alerts.
 */

namespace SJ_Reviews\Includes;

defined('ABSPATH') || exit;

class EmailDigest {

    public const HOOK = 'sj_reviews_email_digest';

    public function init(): void {
        add_action(self::HOOK, [$this, 'send']);
    }

    /**
     * Schedule or unschedule the weekly digest cron.
     */
    public static function reschedule(bool $enabled): void {
        $ts = wp_next_scheduled(self::HOOK);
        if ($ts) {
            wp_unschedule_event($ts, self::HOOK);
        }
        if ($enabled) {
            // Schedule for next Monday 8:00 AM
            $next = strtotime('next monday 08:00:00');
            wp_schedule_event($next, 'sj_weekly', self::HOOK);
        }
    }

    /**
     * Send the weekly digest email.
     */
    public function send(): void {
        $settings = Settings::all();
        if (($settings['email_digest_enabled'] ?? '0') !== '1') {
            return;
        }

        $recipient = $settings['email_digest_email'] ?? '';
        if (empty($recipient)) {
            $recipient = get_option('admin_email');
        }

        $data = $this->gather_data();
        if ($data['new_count'] === 0 && $data['total'] === 0) {
            return; // Nothing to report
        }

        $subject = sprintf(
            '[%s] Recap avis — %d nouveaux avis cette semaine',
            get_bloginfo('name'),
            $data['new_count']
        );

        $html = $this->render_template($data);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        ];

        // Support multiple recipients (comma-separated)
        $recipients = array_map('trim', explode(',', $recipient));
        foreach ($recipients as $to) {
            if (is_email($to)) {
                wp_mail($to, $subject, $html, $headers);
            }
        }
    }

    /**
     * Gather weekly review data.
     */
    private function gather_data(): array {
        global $wpdb;

        $week_ago = gmdate('Y-m-d H:i:s', strtotime('-7 days'));

        // Total reviews
        $total = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts}
             WHERE post_type = 'sj_avis' AND post_status = 'publish'"
        );

        // New reviews this week
        $new_count = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts}
             WHERE post_type = 'sj_avis' AND post_status = 'publish'
             AND post_date >= %s",
            $week_ago
        ));

        // Average rating (all time)
        $avg = (float) $wpdb->get_var(
            "SELECT AVG(CAST(pm.meta_value AS DECIMAL(3,1)))
             FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.meta_key = 'avis_rating'
             AND p.post_type = 'sj_avis' AND p.post_status = 'publish'"
        );

        // Average this week
        $avg_week = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(CAST(pm.meta_value AS DECIMAL(3,1)))
             FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.meta_key = 'avis_rating'
             AND p.post_type = 'sj_avis' AND p.post_status = 'publish'
             AND p.post_date >= %s",
            $week_ago
        ));

        // Rating distribution this week
        $dist_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT CAST(pm.meta_value AS UNSIGNED) as rating, COUNT(*) as cnt
             FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.meta_key = 'avis_rating'
             AND p.post_type = 'sj_avis' AND p.post_status = 'publish'
             AND p.post_date >= %s
             GROUP BY rating ORDER BY rating DESC",
            $week_ago
        ));
        $distribution = [];
        foreach ($dist_rows as $row) {
            $distribution[(int) $row->rating] = (int) $row->cnt;
        }

        // Top 3 reviews (5★ this week)
        $top_reviews = array_map('sj_normalize_review', get_posts([
            'post_type'      => 'sj_avis',
            'post_status'    => 'publish',
            'posts_per_page' => 3,
            'date_query'     => [['after' => '7 days ago']],
            'meta_key'       => 'avis_rating',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
        ]));

        // Negative reviews (1-2★ this week) — alerts
        $negative = array_map('sj_normalize_review', get_posts([
            'post_type'      => 'sj_avis',
            'post_status'    => 'publish',
            'posts_per_page' => 5,
            'date_query'     => [['after' => '7 days ago']],
            'meta_query'     => [
                [
                    'key'     => 'avis_rating',
                    'value'   => 2,
                    'compare' => '<=',
                    'type'    => 'NUMERIC',
                ],
            ],
        ]));

        return compact('total', 'new_count', 'avg', 'avg_week', 'distribution', 'top_reviews', 'negative');
    }

    /**
     * Render the HTML email template.
     */
    private function render_template(array $data): string {
        $site_name = get_bloginfo('name');
        $admin_url = admin_url('admin.php?page=sj-reviews#/dashboard');
        $stars_fn  = function (float $rating): string {
            $html = '';
            for ($i = 1; $i <= 5; $i++) {
                $html .= $i <= round($rating) ? '★' : '☆';
            }
            return $html;
        };

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f5;padding:32px 16px">
<tr><td align="center">
<table width="580" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden">

<!-- Header -->
<tr><td style="background:#111111;padding:24px 32px">
  <h1 style="margin:0;color:#ffffff;font-size:18px;font-weight:600"><?php echo esc_html($site_name); ?> — Recap Avis</h1>
  <p style="margin:6px 0 0;color:#a1a1aa;font-size:13px">Semaine du <?php echo esc_html(date_i18n('j M', strtotime('-7 days'))); ?> au <?php echo esc_html(date_i18n('j M Y')); ?></p>
</td></tr>

<!-- Stats row -->
<tr><td style="padding:24px 32px 16px">
  <table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td width="33%" style="text-align:center;padding:12px">
      <div style="font-size:28px;font-weight:700;color:#111"><?php echo esc_html($data['new_count']); ?></div>
      <div style="font-size:12px;color:#71717a;margin-top:4px">Nouveaux avis</div>
    </td>
    <td width="33%" style="text-align:center;padding:12px;border-left:1px solid #f4f4f5;border-right:1px solid #f4f4f5">
      <div style="font-size:28px;font-weight:700;color:#111"><?php echo $data['avg_week'] > 0 ? esc_html(number_format($data['avg_week'], 1)) : '—'; ?></div>
      <div style="font-size:12px;color:#71717a;margin-top:4px">Moyenne semaine</div>
    </td>
    <td width="33%" style="text-align:center;padding:12px">
      <div style="font-size:28px;font-weight:700;color:#111"><?php echo esc_html(number_format($data['avg'], 1)); ?></div>
      <div style="font-size:12px;color:#71717a;margin-top:4px">Moyenne globale</div>
    </td>
  </tr>
  </table>
</td></tr>

<?php if (!empty($data['distribution'])): ?>
<!-- Distribution -->
<tr><td style="padding:0 32px 16px">
  <p style="font-size:13px;font-weight:600;color:#3f3f46;margin:0 0 8px">Répartition cette semaine</p>
  <table width="100%" cellpadding="0" cellspacing="0">
  <?php for ($s = 5; $s >= 1; $s--):
      $cnt = $data['distribution'][$s] ?? 0;
      $pct = $data['new_count'] > 0 ? round(($cnt / $data['new_count']) * 100) : 0;
  ?>
  <tr>
    <td width="40" style="font-size:12px;color:#71717a;padding:3px 0"><?php echo str_repeat('★', $s); ?></td>
    <td style="padding:3px 8px">
      <div style="background:#f4f4f5;border-radius:4px;height:14px;overflow:hidden">
        <div style="background:<?php echo $s >= 4 ? '#22c55e' : ($s === 3 ? '#eab308' : '#ef4444'); ?>;height:100%;width:<?php echo $pct; ?>%;border-radius:4px"></div>
      </div>
    </td>
    <td width="30" style="font-size:12px;color:#71717a;text-align:right;padding:3px 0"><?php echo esc_html($cnt); ?></td>
  </tr>
  <?php endfor; ?>
  </table>
</td></tr>
<?php endif; ?>

<?php if (!empty($data['top_reviews'])): ?>
<!-- Top reviews -->
<tr><td style="padding:0 32px 16px">
  <p style="font-size:13px;font-weight:600;color:#3f3f46;margin:0 0 8px">Meilleurs avis</p>
  <?php foreach ($data['top_reviews'] as $r): ?>
  <div style="border:1px solid #f4f4f5;border-radius:6px;padding:12px;margin-bottom:8px">
    <div style="font-size:13px;color:#f59e0b;margin-bottom:4px"><?php echo $stars_fn((float) $r['rating']); ?></div>
    <?php if (!empty($r['text'])): ?>
    <p style="margin:0 0 6px;font-size:13px;color:#3f3f46;line-height:1.5"><?php echo esc_html(mb_substr($r['text'], 0, 150)); ?><?php echo mb_strlen($r['text']) > 150 ? '…' : ''; ?></p>
    <?php endif; ?>
    <p style="margin:0;font-size:11px;color:#a1a1aa">— <?php echo esc_html($r['author']); ?> · <?php echo esc_html(ucfirst($r['source'])); ?></p>
  </div>
  <?php endforeach; ?>
</td></tr>
<?php endif; ?>

<?php if (!empty($data['negative'])): ?>
<!-- Negative alerts -->
<tr><td style="padding:0 32px 16px">
  <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:12px">
    <p style="font-size:13px;font-weight:600;color:#dc2626;margin:0 0 8px">⚠ Avis négatifs à traiter (<?php echo count($data['negative']); ?>)</p>
    <?php foreach ($data['negative'] as $r): ?>
    <div style="border-top:1px solid #fecaca;padding-top:8px;margin-top:8px">
      <div style="font-size:12px;color:#dc2626;margin-bottom:2px"><?php echo $stars_fn((float) $r['rating']); ?> — <?php echo esc_html($r['author']); ?></div>
      <?php if (!empty($r['text'])): ?>
      <p style="margin:0;font-size:12px;color:#7f1d1d;line-height:1.4"><?php echo esc_html(mb_substr($r['text'], 0, 120)); ?>…</p>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</td></tr>
<?php endif; ?>

<!-- CTA -->
<tr><td style="padding:16px 32px 24px;text-align:center">
  <a href="<?php echo esc_url($admin_url); ?>"
     style="display:inline-block;background:#111;color:#fff;text-decoration:none;padding:10px 24px;border-radius:6px;font-size:13px;font-weight:500">
    Voir le dashboard
  </a>
</td></tr>

<!-- Footer -->
<tr><td style="background:#fafafa;padding:16px 32px;border-top:1px solid #f4f4f5">
  <p style="margin:0;font-size:11px;color:#a1a1aa;text-align:center">
    <?php echo esc_html($site_name); ?> · SJ Reviews · Total : <?php echo esc_html($data['total']); ?> avis ·
    <a href="<?php echo esc_url(admin_url('admin.php?page=sj-reviews#/settings/api')); ?>" style="color:#a1a1aa">Désactiver le digest</a>
  </p>
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}
