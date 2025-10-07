<?php
/**
 * Admin Logs Template
 */

$logs = get_option('tbp_logs', array());
$logs = array_reverse($logs); // Show newest first

// Pagination
$per_page = 20;
$total_logs = count($logs);
$total_pages = ceil($total_logs / $per_page);
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;
$paginated_logs = array_slice($logs, $offset, $per_page);

// Filter logs by action
$filter_action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
if ($filter_action) {
    $paginated_logs = array_filter($paginated_logs, function($log) use ($filter_action) {
        return $log['action'] === $filter_action;
    });
}

// Get unique actions for filter
$unique_actions = array_unique(array_column($logs, 'action'));
?>

<div class="wrap tbp-logs">
    <h1>📋 Telegram Blog Publisher Logs</h1>
    
    <!-- Filters -->
    <div class="tbp-filters">
        <form method="get" class="tbp-filter-form">
            <input type="hidden" name="page" value="telegram-blog-publisher-logs">
            
            <label for="action-filter">Filter by Action:</label>
            <select id="action-filter" name="action">
                <option value="">All Actions</option>
                <?php foreach ($unique_actions as $action): ?>
                    <option value="<?php echo esc_attr($action); ?>" <?php selected($filter_action, $action); ?>>
                        <?php echo esc_html(ucfirst(str_replace('_', ' ', $action))); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="button">Filter</button>
            <a href="<?php echo admin_url('admin.php?page=telegram-blog-publisher-logs'); ?>" class="button">Clear</a>
        </form>
    </div>
    
    <!-- Logs Table -->
    <div class="tbp-logs-container">
        <?php if (empty($paginated_logs)): ?>
            <div class="tbp-no-logs">
                <p>No logs found.</p>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped tbp-logs-table">
                <thead>
                    <tr>
                        <th class="tbp-col-timestamp">Timestamp</th>
                        <th class="tbp-col-action">Action</th>
                        <th class="tbp-col-details">Details</th>
                        <th class="tbp-col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paginated_logs as $index => $log): ?>
                        <tr>
                            <td class="tbp-col-timestamp">
                                <?php echo esc_html(date('M j, Y g:i A', strtotime($log['timestamp']))); ?>
                            </td>
                            <td class="tbp-col-action">
                                <span class="tbp-action-badge tbp-action-<?php echo esc_attr($log['action']); ?>">
                                    <?php echo esc_html(ucfirst(str_replace('_', ' ', $log['action']))); ?>
                                </span>
                            </td>
                            <td class="tbp-col-details">
                                <?php if ($log['action'] === 'webhook_received'): ?>
                                    <strong>Topic:</strong> <?php echo esc_html($log['data']['topic'] ?? 'N/A'); ?><br>
                                    <strong>Details:</strong> <?php echo esc_html(wp_trim_words($log['data']['details'] ?? 'N/A', 10)); ?>
                                <?php elseif ($log['action'] === 'post_created'): ?>
                                    <strong>Post ID:</strong> <?php echo esc_html($log['post_id'] ?? 'N/A'); ?><br>
                                    <strong>Topic:</strong> <?php echo esc_html($log['data']['topic'] ?? 'N/A'); ?>
                                <?php else: ?>
                                    <?php echo esc_html(wp_trim_words(json_encode($log['data']), 15)); ?>
                                <?php endif; ?>
                            </td>
                            <td class="tbp-col-actions">
                                <button type="button" class="button button-small tbp-view-details" data-log-index="<?php echo $offset + $index; ?>">
                                    View Details
                                </button>
                                <?php if (isset($log['post_id'])): ?>
                                    <a href="<?php echo get_edit_post_link($log['post_id']); ?>" class="button button-small">
                                        Edit Post
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="tbp-pagination">
            <?php
            $pagination_args = array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => '&laquo; Previous',
                'next_text' => 'Next &raquo;',
                'total' => $total_pages,
                'current' => $current_page,
            );
            
            if ($filter_action) {
                $pagination_args['add_args'] = array('action' => $filter_action);
            }
            
            echo paginate_links($pagination_args);
            ?>
        </div>
    <?php endif; ?>
    
    <!-- Log Details Modal -->
    <div id="tbp-log-modal" class="tbp-modal" style="display: none;">
        <div class="tbp-modal-content">
            <div class="tbp-modal-header">
                <h3>Log Details</h3>
                <button type="button" class="tbp-modal-close">&times;</button>
            </div>
            <div class="tbp-modal-body">
                <div id="log-details-content"></div>
            </div>
        </div>
    </div>
    
    <!-- Actions -->
    <div class="tbp-logs-actions">
        <button type="button" class="button" id="clear-logs">Clear All Logs</button>
        <button type="button" class="button" id="export-logs">Export Logs</button>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const logs = <?php echo json_encode($logs); ?>;
    
    // View log details
    $('.tbp-view-details').on('click', function() {
        const logIndex = $(this).data('log-index');
        const log = logs[logIndex];
        
        if (log) {
            let content = '<div class="tbp-log-detail">';
            content += '<h4>Log Information</h4>';
            content += '<p><strong>Timestamp:</strong> ' + log.timestamp + '</p>';
            content += '<p><strong>Action:</strong> ' + log.action + '</p>';
            
            if (log.post_id) {
                content += '<p><strong>Post ID:</strong> ' + log.post_id + '</p>';
            }
            
            content += '<h4>Data</h4>';
            content += '<pre>' + JSON.stringify(log.data, null, 2) + '</pre>';
            content += '</div>';
            
            $('#log-details-content').html(content);
            $('#tbp-log-modal').show();
        }
    });
    
    // Close modal
    $('.tbp-modal-close, #tbp-log-modal').on('click', function(e) {
        if (e.target === this) {
            $('#tbp-log-modal').hide();
        }
    });
    
    // Clear logs
    $('#clear-logs').on('click', function() {
        if (confirm('Are you sure you want to clear all logs? This action cannot be undone.')) {
            $.ajax({
                url: tbp_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'tbp_clear_logs',
                    nonce: tbp_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Failed to clear logs: ' + response.data);
                    }
                },
                error: function() {
                    alert('Failed to clear logs: Network error');
                }
            });
        }
    });
    
    // Export logs
    $('#export-logs').on('click', function() {
        const logsData = JSON.stringify(logs, null, 2);
        const blob = new Blob([logsData], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'telegram-blog-publisher-logs-' + new Date().toISOString().split('T')[0] + '.json';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });
});
</script>
