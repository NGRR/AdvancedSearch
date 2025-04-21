<?php
// No direct access
defined('_JEXEC') or die;

class Mod_advancedsearchInstallerScript
{
    /**
     * Método ejecutado durante la instalación del módulo.
     *
     * @param   string  $parent  Nombre del módulo.
     * @return  void
     */
    public function install($parent)
    {
        // Puedes agregar aquí cualquier tarea que necesites realizar durante la instalación.
        // Por ejemplo, crear tablas de base de datos, copiar archivos, etc.
        echo '<p>' . \Joomla\CMS\Language\Text::_('MOD_ADVANCEDSEARCH_INSTALL_SUCCESS') . '</p>';
    }

    /**
     * Método ejecutado durante la desinstalación del módulo.
     *
     * @param   string  $parent  Nombre del módulo.
     * @return  void
     */
    public function uninstall($parent)
    {
        // Puedes agregar aquí cualquier tarea que necesites realizar durante la desinstalación.
        // Por ejemplo, eliminar tablas de base de datos, eliminar archivos, etc.
        echo '<p>' . \Joomla\CMS\Language\Text::_('MOD_ADVANCEDSEARCH_UNINSTALL_SUCCESS') . '</p>';
    }

    /**
     * Método ejecutado durante la actualización del módulo.
     *
     * @param   string  $parent  Nombre del módulo.
     * @return  void
     */
    public function update($parent)
    {
        // Puedes agregar aquí cualquier tarea que necesites realizar durante la actualización.
        // Por ejemplo, migrar datos, actualizar tablas de base de datos, etc.
        echo '<p>' . \Joomla\CMS\Language\Text::_('MOD_ADVANCEDSEARCH_UPDATE_SUCCESS') . '</p>';
    }

    /**
     * Método ejecutado antes de la instalación del módulo.
     *
     * @param   string  $type    Tipo de instalación (install, update o discover_install).
     * @param   string  $parent  Nombre del módulo.
     * @return  void
     */
    public function preflight($type, $parent)
    {
        // Puedes agregar aquí cualquier tarea que necesites realizar antes de la instalación.
        // Por ejemplo, verificar requisitos del sistema, mostrar mensajes de advertencia, etc.
    }

    /**
     * Método ejecutado después de la instalación del módulo.
     *
     * @param   string  $type    Tipo de instalación (install, update o discover_install).
     * @param   string  $parent  Nombre del módulo.
     * @return  void
     */
    public function postflight($type, $parent)
    {
        // Puedes agregar aquí cualquier tarea que necesites realizar después de la instalación.
        // Por ejemplo, limpiar caché, mostrar mensajes de confirmación, etc.
    }
}