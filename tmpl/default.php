<?php
// No direct access
defined('_JEXEC') or die;

// Include the template helper
require_once __DIR__ . '/helper.php';

// Get module parameters
$limit = $params->get('limit', 10);
$parentCategory = $params->get('parent_category', 0); // Obtener la categoría padre de los parámetros del módulo

$theme = $params->get('theme', 'default');
// Get search parameters
$category = JFactory::getApplication()->input->get('category', null, 'INT');
$tags = JFactory::getApplication()->input->get('tags', array(), 'ARRAY');
$startDate = JFactory::getApplication()->input->get('start_date');
$endDate = JFactory::getApplication()->input->get('end_date');

// Get search results
$results = ModAdvancedSearchHelper::getResults(new JObject(compact('category', 'tags', 'startDate', 'endDate', 'limit')), $parentCategory); // Pasar $parentCategory

// Get total results
$total = ModAdvancedSearchHelper::getTotalResults(new JObject(compact('category', 'tags', 'startDate', 'endDate')), $parentCategory); // Pasar $parentCategory

// Get pagination
$pagination = ModAdvancedSearchHelper::getPagination(new JObject(compact('limit')), $total);

// Get search history
$searchHistory = ModAdvancedSearchHelper::getSearchHistory();

$wrapperClass = 'mod-advancedsearch theme-' . htmlspecialchars($theme);

// Get search path
$searchPath = ModAdvancedSearchHelper::getSearchPath(new JObject(compact('category', 'tags', 'startDate', 'endDate')));

// Get subcategories - Usar la categoría padre configurada en los parámetros del módulo
$subcategories = ModAdvancedSearchHelper::getSubcategories($parentCategory);

// Get tags - Si hay una categoría seleccionada, obtener las etiquetas de esa categoría
if ($category) {
    $allTags = ModAdvancedSearchHelper::getTagsByCategory($category);
} else {
    // Si no hay categoría seleccionada, obtener las etiquetas de todas las subcategorías
    $allTags = array();
    foreach ($subcategories as $subcategory) {
        $categoryTags = ModAdvancedSearchHelper::getTagsByCategory($subcategory->id);
        foreach ($categoryTags as $tag) {
            // Evitar duplicados
            $found = false;
            foreach ($allTags as $existingTag) {
                if ($existingTag->id == $tag->id) {
                    $found = true;
                    break;                    
		}
            }
            if (!$found) {
                $allTags[] = $tag;
            }
        }
    }
    // Si no hay etiquetas en las subcategorías, obtener todas las etiquetas
    if (empty($allTags)) {
        $allTags = ModAdvancedSearchHelper::getTags();
    }
}

// Cargar los archivos CSS y JavaScript necesarios
$document = JFactory::getDocument();
$moduleRelativePath = 'modules/mod_advancedsearch';
$document->addStyleSheet(JUri::base() . $moduleRelativePath . '/assets/css/dropdown.css');
$document->addScript(JUri::base() . $moduleRelativePath . '/assets/js/dropdown.js');

// Incluir el código JavaScript directamente en la página para evitar problemas de carga
$dropdownJs = <<<EOD
function showDropdown(uniqueId) {
    // Cerrar todos los dropdowns abiertos primero
    closeAllDropdowns();

    // Mostrar el dropdown seleccionado
    document.getElementById('dropdown_' + uniqueId).style.display = 'block';

    // Enfocar el campo de búsqueda
    document.getElementById('filterInput_' + uniqueId).focus();

    // Agregar evento para cerrar el dropdown al hacer clic fuera
    document.addEventListener('click', function(event) {
        var dropdown = document.getElementById('dropdown_' + uniqueId);
        var searchBox = document.getElementById('searchBox_' + uniqueId);

        if (event.target !== dropdown && event.target !== searchBox && !dropdown.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });
}

// Función para cerrar todos los dropdowns
function closeAllDropdowns() {
    var dropdowns = document.getElementsByClassName('dropdown-content');
    for (var i = 0; i < dropdowns.length; i++) {
        dropdowns[i].style.display = 'none';
    }
}

// Función para filtrar opciones en el dropdown
function filterFunction(uniqueId) {
    var input, filter, div, items, i, txtValue;
    input = document.getElementById('filterInput_' + uniqueId);
    filter = input.value.toUpperCase();
    div = document.getElementById('dropdown_' + uniqueId);
    items = div.getElementsByClassName('dropdown-item');

    for (i = 0; i < items.length; i++) {
        txtValue = items[i].textContent || items[i].innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            items[i].style.display = '';
        } else {
            items[i].style.display = 'none';
        }
    }
}

// Función para seleccionar una opción en el dropdown de categorías
function selectOption(uniqueId, value, text) {
    // Actualizar el valor del campo de búsqueda
    document.getElementById('searchBox_' + uniqueId).value = text;

    // Marcar la opción seleccionada
    var radios = document.getElementsByName('category');
    for (var i = 0; i < radios.length; i++) {
        if (radios[i].value === value) {
            radios[i].checked = true;
        } else {
            radios[i].checked = false;
        }
    }

    // Cerrar el dropdown
    document.getElementById('dropdown_' + uniqueId).style.display = 'none';

    // Si es un cambio de categoría, cargar las etiquetas relacionadas
    if (uniqueId.startsWith('category_')) {
        loadTagsForCategory(value);
    }
}

// Función para actualizar la selección de etiquetas
function updateTagsSelection(uniqueId) {
    var checkboxes = document.querySelectorAll('#dropdown_' + uniqueId + ' input[type="checkbox"]:checked');
    var selectedTags = [];

    for (var i = 0; i < checkboxes.length; i++) {
        var label = document.querySelector('label[for="' + checkboxes[i].id + '"]');
        selectedTags.push(label.textContent);
    }

    // Actualizar el texto del campo de búsqueda
    var searchBox = document.getElementById('searchBox_' + uniqueId);
    if (selectedTags.length > 0) {
        searchBox.value = selectedTags.join(', ');
    } else {
        searchBox.value = '';
        searchBox.placeholder = 'Seleccionar etiquetas';
    }
}

// Función para cargar etiquetas por categoría mediante AJAX
function loadTagsForCategory(categoryId) {
    // Esta función se implementaría con AJAX en un entorno real
    // Por ahora, simplemente recargamos la página con el parámetro de categoría
    if (categoryId) {
        var currentUrl = window.location.href;
        var newUrl;

        if (currentUrl.indexOf('?') > -1) {
            if (currentUrl.indexOf('category=') > -1) {
                newUrl = currentUrl.replace(/category=\d+/, 'category=' + categoryId);
            } else {
                newUrl = currentUrl + '&category=' + categoryId;
            }
        } else {
            newUrl = currentUrl + '?category=' + categoryId;
        }

        window.location.href = newUrl;
    }
}

// Inicializar los dropdowns cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar la selección de etiquetas
    var tagsDropdowns = document.querySelectorAll('[id^="searchBox_tags_"]');
    for (var i = 0; i < tagsDropdowns.length; i++) {
        var uniqueId = tagsDropdowns[i].id.replace('searchBox_', '');
        updateTagsSelection(uniqueId);
    }
});
EOD;

// Estilos CSS para los dropdowns
$dropdownCss = <<<EOD
/* Estilos para los dropdowns personalizados */
.custom-dropdown {
    position: relative;
    width: 100%;
    margin-bottom: 15px;
}

.dropdown-container {
    position: relative;
}

.search-box {
    width: 100%;
    cursor: pointer;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: #f6f6f6;
    width: 100%;
    border: 1px solid #ddd;
    z-index: 1000;
    max-height: 300px;
    overflow-y: auto;
}

.dropdown-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}

.dropdown-item:hover {
    background-color: #e9e9e9;
}

.filter-input {
    width: 100%;
    padding: 8px;
    border: none;
    border-bottom: 1px solid #ddd;
}

/* Estilos para los checkboxes y radio buttons */
.dropdown-item input[type="checkbox"],
.dropdown-item input[type="radio"] {
    margin-right: 8px;
}

/* Estilos para las etiquetas seleccionadas */
.selected-tags {
    margin-top: 5px;
    display: flex;
    flex-wrap: wrap;
}

.tag-badge {
    background-color: #e1e1e1;
    padding: 2px 8px;
    margin: 2px;
    border-radius: 3px;
    display: inline-flex;
    align-items: center;
}

.tag-badge .remove-tag {
    margin-left: 5px;
    cursor: pointer;
    font-weight: bold;
}
EOD;

// Agregar los estilos y scripts directamente en la página
$document->addStyleDeclaration($dropdownCss);
$document->addScriptDeclaration($dropdownJs);
?>

<form action="<?php echo JRoute::_('index.php?option=com_content&view=articles'); ?>" method="get" class="<?php echo $wrapperClass; ?>">
    <div class="uk-margin">
        <label class="uk-form-label" for="category"><?php echo JText::_('MOD_ADVANCEDSEARCH_CATEGORY'); ?></label>
        <div class="uk-form-controls">
            <?php echo ModAdvancedSearchTemplateHelper::getSubcategoriesDropdown($subcategories, $category); ?>
        </div>
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="tags"><?php echo JText::_('MOD_ADVANCEDSEARCH_TAGS'); ?></label>
        <div class="uk-form-controls">
            <?php echo ModAdvancedSearchTemplateHelper::getTagsDropdown($allTags, $tags); ?>
        </div>
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="start_date"><?php echo JText::_('MOD_ADVANCEDSEARCH_START_DATE'); ?></label>
        <div class="uk-form-controls">
            <input type="date" name="start_date" id="start_date" class="uk-input" value="<?php echo $startDate; ?>">
        </div>
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="end_date"><?php echo JText::_('MOD_ADVANCEDSEARCH_END_DATE'); ?></label>
        <div class="uk-form-controls">
            <input type="date" name="end_date" id="end_date" class="uk-input" value="<?php echo $endDate; ?>">
        </div>
    </div>

    <div class="uk-margin">
        <button type="submit" class="uk-button uk-button-primary"><?php echo JText::_('MOD_ADVANCEDSEARCH_SEARCH'); ?></button>
    </div>
</form>

<?php if ($total > 0) : ?>
    <div class="<?php echo $wrapperClass; ?>-results">
        <p><?php echo JText::sprintf('MOD_ADVANCEDSEARCH_RESULTS_FOUND', $total, $searchPath); ?></p>
        <?php echo ModAdvancedSearchTemplateHelper::getResultsTable($results); ?>
        <?php echo ModAdvancedSearchTemplateHelper::getPaginationHtml($pagination); ?>
    </div>
<?php elseif (JFactory::getApplication()->input->get('start_date') || JFactory::getApplication()->input->get('category') || JFactory::getApplication()->input->get('tags')): ?>
    <div class="<?php echo $wrapperClass; ?>-no-results">
        <p><?php echo JText::_('MOD_ADVANCEDSEARCH_NO_RESULTS_FOUND'); ?></p>
    </div>
<?php endif; ?>

<?php if (!empty($searchHistory)) : ?>
    <div class="<?php echo $wrapperClass; ?>-history">
        <?php echo ModAdvancedSearchTemplateHelper::getSearchHistoryHtml($searchHistory); ?>
    </div>
<?php endif; ?>