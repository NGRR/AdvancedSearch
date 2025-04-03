<?php
// No direct access
defined('_JEXEC') or die;

// Include helper file
require_once __DIR__ . '/helper.php';

// Get module parameters
$parentCategory = $params->get('parent_category');
$limit = $params->get('limit', 10);
$startDate = $params->get('start_date');
$endDate = $params->get('end_date');

// Get subcategories
$subcategories = ModAdvancedSearchHelper::getSubcategories($parentCategory);

// Get selected category from input
$selectedCategory = JFactory::getApplication()->input->get('category', null, 'INT');

// If no category is selected, default to "All subcategories" of the parent category
if (!$selectedCategory) {
    $selectedCategory = $parentCategory;
}

// Prepare search parameters
$searchParams = new JObject();
$searchParams->set('category', $selectedCategory);
$searchParams->set('tags', JFactory::getApplication()->input->get('tags', array(), 'ARRAY'));
$searchParams->set('start_date', $startDate);
$searchParams->set('end_date', $endDate);
$searchParams->set('limit', $limit);

// Get search results
$results = ModAdvancedSearchHelper::getResults($searchParams);

// Get total results
$total = ModAdvancedSearchHelper::getTotalResults($searchParams);

// Get pagination
$pagination = ModAdvancedSearchHelper::getPagination($searchParams, $total);

// Get search history
$searchHistory = ModAdvancedSearchHelper::getSearchHistory();

// Add current search to history
ModAdvancedSearchHelper::addSearchToHistory($searchParams);

// Get search path (breadcrumb)
$searchPath = ModAdvancedSearchHelper::getSearchPath($searchParams);

// Include the template file
require JModuleHelper::getLayoutPath('mod_advancedsearch', $params->get('layout', 'default'));