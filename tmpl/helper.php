<?php
// No direct access
defined('_JEXEC') or die;

class ModAdvancedSearchTemplateHelper
{
    /**
     * Genera el HTML para la lista desplegable de subcategorías con búsqueda.
     *
     * @param   array   $subcategories  Lista de subcategorías.
     * @param   int     $selected       ID de la subcategoría seleccionada.
     * @return  string  HTML de la lista desplegable con búsqueda.
     */
    public static function getSubcategoriesDropdown($subcategories, $selected = null)
    {
        $uniqueId = 'category_' . rand(1000, 9999);
        
        $html = '<div class="custom-dropdown">';
        $html .= '<div class="dropdown-container">';
        $html .= '<input type="text" class="uk-input search-box" id="searchBox_' . $uniqueId . '" placeholder="' . JText::_('MOD_ADVANCEDSEARCH_SEARCH_CATEGORY') . '" onclick="showDropdown(\'' . $uniqueId . '\')">';
        $html .= '<div class="dropdown-content" id="dropdown_' . $uniqueId . '">';
        $html .= '<input type="text" class="uk-input filter-input" id="filterInput_' . $uniqueId . '" onkeyup="filterFunction(\'' . $uniqueId . '\')" placeholder="' . JText::_('MOD_ADVANCEDSEARCH_SEARCH') . '">';
        
        // Opciones para cada subcategoría
        foreach ($subcategories as $subcategory) {
            $isSelected = in_array($subcategory->id, $selected);
            $html .= '<div class="dropdown-item"  onclick="event.stopPropagation();">';
            $html .= '<input type="checkbox" name="category[]" id="category_' . $subcategory->id . '" value="' . $subcategory->id . '" ' . ($isSelected ? 'checked' : '') . '>';
            $html .= '<label for="category_' . $subcategory->id . '">' . $subcategory->title . '</label>';
            $html .= '</div>';
        }
        
        $html .= '</div></div></div>';
                return $html;
    }

    /**
     * Genera el HTML para la lista desplegable de etiquetas con búsqueda.
     *
     * @param   array   $tags       Lista de etiquetas.
     * @param   array   $selected   Lista de IDs de etiquetas seleccionadas.
     * @return  string  HTML de la lista desplegable de etiquetas con búsqueda.
     */
    public static function getTagsDropdown($tags, $selected = array())
    {
        $uniqueId = 'tags_' . rand(1000, 9999);
        
        $html = '<div class="custom-dropdown">';
        $html .= '<div class="dropdown-container">';
        $html .= '<input type="text" class="uk-input search-box" id="searchBox_' . $uniqueId . '" placeholder="' . JText::_('MOD_ADVANCEDSEARCH_SELECT_TAGS') . '" onclick="showDropdown(\'' . $uniqueId . '\')" readonly>';
        $html .= '<div class="dropdown-content" id="dropdown_' . $uniqueId . '">';
        $html .= '<input type="text" class="uk-input filter-input" id="filterInput_' . $uniqueId . '" onkeyup="filterFunction(\'' . $uniqueId . '\')" placeholder="' . JText::_('MOD_ADVANCEDSEARCH_SEARCH') . '">';
        
        // Filtrar etiquetas para excluir ROOT
        $filteredTags = array();
        foreach ($tags as $tag) {
            if ($tag->title !== 'ROOT') {
                $filteredTags[] = $tag;
            }
        }
        
        // Opciones para cada etiqueta (excluyendo ROOT)
        foreach ($filteredTags as $tag) {
            $isSelected = in_array($tag->id, $selected);
            $html .= '<div class="dropdown-item">';
            $html .= '<input type="checkbox" name="tags[]" id="tag_option_' . $tag->id . '" value="' . $tag->id . '" ' . ($isSelected ? 'checked' : '') . ' onchange="updateTagsSelection(\'' . $uniqueId . '\')">';
            $html .= '<label for="tag_option_' . $tag->id . '">' . $tag->title . '</label>';
            $html .= '</div>';
        }
        
        $html .= '</div>'; // Fin dropdown-content
        $html .= '</div>'; // Fin dropdown-container
        $html .= '</div>'; // Fin custom-dropdown
        
        return $html;
    }

    /**
     * Genera el HTML para la tabla de resultados.
     *
     * @param   array   $results    Lista de resultados.
     * @return  string  HTML de la tabla de resultados.
     */
    public static function getResultsTable($results)
    {
        $html = '<table class="uk-table uk-table-striped">';
        $html .= '<thead><tr><th>' . JText::_('MOD_ADVANCEDSEARCH_TITLE') . '</th><th>' . JText::_('MOD_ADVANCEDSEARCH_CATEGORY') . '</th><th>' . JText::_('MOD_ADVANCEDSEARCH_DATE') . '</th><th>' . JText::_('MOD_ADVANCEDSEARCH_LINK') . '</th><th>' . JText::_('MOD_ADVANCEDSEARCH_TAGS') . '</th></tr></thead>';
        $html .= '<tbody>';
        foreach ($results as $result) {
            $html .= '<tr>';
            $html .= '<td>' . $result->title . '</td>';
            $html .= '<td>' . $result->category . '</td>';
            $html .= '<td>' . JHtml::_('date', $result->publish_up) . '</td>';
            $html .= '<td><a href="' . JRoute::_(ContentHelperRoute::getArticleRoute($result->id, $result->catid)) . '">' . JText::_('MOD_ADVANCEDSEARCH_READ_MORE') . '</a></td>';
            $html .= '<td>' . (isset($result->article_tags) ? $result->article_tags : '') . '</td>'; // Mostramos las etiquetas del artículo
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * Genera el HTML para el paginador.
     *
     * @param   JPagination $pagination Objeto de paginación.
     * @return  string  HTML del paginador.
     */
    public static function getPaginationHtml($pagination)
    {
        return $pagination->getListFooter();
    }

    /**
     * Genera el HTML para el historial de búsqueda.
     *
     * @param   array   $searchHistory  Lista de búsquedas en el historial.
     * @return  string  HTML del historial de búsqueda.
     */
    public static function getSearchHistoryHtml($searchHistory)
    {
        $html = '<div class="search-history">';
        $html .= '<h3>' . JText::_('MOD_ADVANCEDSEARCH_SEARCH_HISTORY') . '</h3>';
        $html .= '<ul>';
        foreach ($searchHistory as $search) {
            $html .= '<li>';
            $html .= '<a href="' . JRoute::_('index.php?option=com_content&view=articles&category=' . $search['category'] . '&tags=' . implode(',', $search['tags']) . '&start_date=' . $search['start_date'] . '&end_date=' . $search['end_date']) . '">';
            $html .= JText::sprintf('MOD_ADVANCEDSEARCH_RESULTS_FOUND', ModAdvancedSearchHelper::getTotalResults(new JObject($search)), ModAdvancedSearchHelper::getSearchPath(new JObject($search)));
            $html .= '</a>';
            $html .= '</li>';
        }
        $html .= '</ul></div>';
        return $html;
    }
}
