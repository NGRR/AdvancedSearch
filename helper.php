<?php
// No direct access
defined('_JEXEC') or die;

// Importar la clase TagsHelper
use Joomla\CMS\Helper\TagsHelper;

class ModAdvancedSearchHelper
{
    // Obtiene las subcategorías basadas en la categoría padre
    public static function getSubcategories($parentCategory)
    {
        if ($parentCategory == 0){
            return array();
        }
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id, title')
            ->from('#__categories')
            ->where('parent_id = ' . $db->quote($parentCategory))
            ->where('extension = "com_content"')
            ->order('title ASC');

        $db->setQuery($query);
        $results = $db->loadObjectList();

        // Asegurarse de que se devuelva un array incluso si no hay resultados
        return $results ? $results : array();
    }
    
    // Obtiene todas las etiquetas
    public static function getTags()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id, title')
            ->from('#__tags')
            ->where('published = 1')
            ->where('title != ' . $db->quote('ROOT'))
            ->order('title ASC');
        $db->setQuery($query);
        return $db->loadObjectList();
    }
    
    // Obtiene las etiquetas asociadas a una categoría específica
    public static function getTagsByCategory($categoryId)
    {
        if (!$categoryId) {
            return self::getTags();
        }
        
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        // Seleccionar etiquetas distintas asociadas a artículos en la categoría
        $query->select('DISTINCT t.id, t.title')
            ->from('#__tags AS t')
            ->join('INNER', '#__contentitem_tag_map AS m ON m.tag_id = t.id')
            ->join('INNER', '#__content AS c ON c.id = m.content_item_id')
            ->where('c.catid = ' . $db->quote($categoryId))
            ->where('t.published = 1')
            ->where('t.title != ' . $db->quote('ROOT'))
            ->order('t.title ASC');
            
        $db->setQuery($query);
        $results = $db->loadObjectList();
        
        // Si no hay resultados, devolver un array vacío
        return $results ? $results : array();
    }

    // Obtiene los resultados de la búsqueda basados en los parámetros
    public static function getResults($params)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('a.id, a.title, c.title AS category, a.publish_up, a.catid, (SELECT GROUP_CONCAT(t.title) FROM #__tags AS t INNER JOIN #__contentitem_tag_map AS m ON t.id = m.tag_id WHERE m.content_item_id = a.id) AS article_tags') // Añadimos la subconsulta para las etiquetas
            ->from('#__content AS a')
            ->join('INNER', '#__categories AS c ON c.id = a.catid')
            ->where('a.state = 1');

        // Filtra por categoría
        $category = $params->get('category');
        if ($category) {
            $query->where('a.catid = ' . $db->quote($category));
        }


    // Filtra por etiquetas
    $tags = $params->get('tags');
    if ($tags) {
        $query->join('INNER', '#__contentitem_tag_map AS m ON m.content_item_id = a.id')
            ->where('m.tag_id IN (' . implode(',', $tags) . ')');

        // Obtener los nombres de las etiquetas seleccionadas
        $tagNames = array();
        foreach ($tags as $tagId) {
            // Usar TagsHelper en lugar de Tag::getInstance
            $tagsHelper = new TagsHelper();
            $tagItems = $tagsHelper->getItemTags('com_tags.tag', $tagId);
            if (!empty($tagItems)) {
                $tagNames[] = $tagItems[0]->title;
            }
        }
    }
        // Filtra por rango de fechas
        $startDate = $params->get('start_date');
        $endDate = $params->get('end_date');
        if ($startDate && $endDate) {
            $query->where('a.publish_up >= ' . $db->quote($startDate) . ' AND a.publish_up <= ' . $db->quote($endDate));
        }

        // Añade paginación
        $limit = $params->get('limit', 10);
        $limitStart = JFactory::getApplication()->input->getInt('limitstart', 0);
        $query->setLimit($limit, $limitStart);

        $db->setQuery($query);

        // Añadir los nombres de las etiquetas a los resultados
        $results = $db->loadObjectList();
        if (isset($tagNames)) {
            foreach ($results as $result) {
                $result->tags = implode(', ', $tagNames);
            }
        }

        return $results;
    }

    // Obtiene el total de resultados de la búsqueda basados en los parámetros
    public static function getTotalResults($params)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(*)')
            ->from('#__content AS a')
            ->join('INNER', '#__categories AS c ON c.id = a.catid')
            ->where('a.state = 1');

        // Filtra por categoría
        $category = $params->get('category');
        if ($category) {
            $query->where('a.catid = ' . $db->quote($category));
        }

        // Filtra por etiquetas
        $tags = $params->get('tags');
        if ($tags) {
            $query->join('INNER', '#__contentitem_tag_map AS m ON m.content_item_id = a.id')
                ->where('m.tag_id IN (' . implode(',', $tags) . ')');
        }

        // Filtra por rango de fechas
        $startDate = $params->get('start_date');
        $endDate = $params->get('end_date');
        if ($startDate && $endDate) {
            $query->where('a.publish_up >= ' . $db->quote($startDate) . ' AND a.publish_up <= ' . $db->quote($endDate));
        }

        $db->setQuery($query);
        return $db->loadResult();
    }

    // Obtiene el objeto de paginación
    public static function getPagination($params, $total)
    {
        $limit = $params->get('limit', 10);
        $limitStart = JFactory::getApplication()->input->getInt('limitstart', 0);
        return new JPagination($total, $limitStart, $limit);
    }

    // Obtiene el historial de búsqueda desde las cookies
    public static function getSearchHistory()
    {
        $history = JFactory::getApplication()->input->cookie->get('mod_advancedsearch_history', '[]'); // Valor predeterminado como array vacío
        return json_decode($history, true); // Decodificamos la cadena JSON a un array
    }

    // Añade una búsqueda al historial en las cookies
    public static function addSearchToHistory($params)
    {
        $history = self::getSearchHistory();
        $history[] = $params->getProperties(); // Usamos getProperties() en lugar de toArray()
        JFactory::getApplication()->input->cookie->set('mod_advancedsearch_history', json_encode($history)); // Convertimos el array a JSON
    }

    // Obtiene el "Path de lo buscado" (breadcrumb)
    public static function getSearchPath($params)
    {
        $path = '';

        // Añade la ruta de la categoría
        $category = $params->get('category');
        if ($category) {
            $category = JCategories::getInstance('content')->get($category);
            if ($category) {
                $path .= $category->title . ' > ';
            }
        }

    // Añade la ruta de las etiquetas
    $tags = $params->get('tags');
    if ($tags) {
        $tagTitles = array();
        foreach ($tags as $tagId) {
            // Usar TagsHelper en lugar de Tag::getInstance
            $tagsHelper = new TagsHelper();
            $tagItems = $tagsHelper->getItemTags('com_tags.tag', $tagId);
            if (!empty($tagItems)) {
                $tagTitles[] = $tagItems[0]->title;
            }
        }
        if (!empty($tagTitles)) {
            $path .= implode(', ', $tagTitles) . ' > ';
        }
    }

        // Añade el rango de fechas
        $startDate = $params->get('start_date');
        $endDate = $params->get('end_date');
        if ($startDate && $endDate) {
            $path .= $startDate . ' - ' . $endDate;
        }

        return $path;
    }
}
