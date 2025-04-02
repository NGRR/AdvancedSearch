<?php
// No direct access
defined('_JEXEC') or die;

// Include the template helper
require_once __DIR__ . '/../helper.php';
?>

<?php if (!empty($results)): ?>
    <?php echo ModAdvancedSearchTemplateHelper::getResultsTable($results); ?>
<?php else: ?>
    <p><?php echo JText::_('MOD_ADVANCEDSEARCH_NO_RESULTS'); ?></p>
<?php endif; ?>