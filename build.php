<?php
// Script para construir el paquete del módulo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Función para obtener la versión del XML
function getModuleVersion() {
    echo "Intentando leer el archivo XML...\n";
    if (!file_exists('mod_advancedsearch.xml')) {
        throw new Exception("El archivo mod_advancedsearch.xml no existe");
    }
    
    $xml = simplexml_load_file('mod_advancedsearch.xml');
    if ($xml === false) {
        throw new Exception("Error al leer el archivo XML");
    }
    
    $version = (string)$xml->version;
    if (empty($version)) {
        throw new Exception("No se pudo encontrar la versión en el XML");
    }
    
    return $version;
}

// Función para crear el archivo ZIP
function createZipPackage($version) {
    echo "Iniciando creación del ZIP...\n";
    $zip = new ZipArchive();
    $zipName = "mod_advancedsearch_v{$version}.zip";
    
    echo "Intentando crear el archivo: {$zipName}\n";
    if ($zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        throw new Exception("No se pudo crear el archivo ZIP");
    }
    
    // Archivos principales
    $files = [
        'mod_advancedsearch.php',
        'helper.php',
        'script.php',
        'mod_advancedsearch.xml'
    ];
    
    // Archivos de plantillas
    $templateFiles = [
        'tmpl/default.php',
        'tmpl/results.php',
        'tmpl/helper.php'
    ];
    
    // Archivos de idioma
    $languageFiles = [
        'language/en-GB/en-GB.mod_advancedsearch.ini',
        'language/es-ES/es-ES.mod_advancedsearch.ini'
    ];
    
    // Carpetas
    $folders = [
        'assets'
    ];
    
    // Añadir archivos principales
    echo "Añadiendo archivos principales...\n";
    foreach ($files as $file) {
        if (file_exists($file)) {
            echo "Añadiendo archivo: {$file}\n";
            if (!$zip->addFile($file, $file)) {
                throw new Exception("Error al añadir el archivo: {$file}");
            }
        } else {
            echo "Advertencia: El archivo {$file} no existe\n";
        }
    }
    
    // Añadir archivos de plantillas
    echo "Añadiendo archivos de plantillas...\n";
    foreach ($templateFiles as $file) {
        if (file_exists($file)) {
            echo "Añadiendo archivo: {$file}\n";
            if (!$zip->addFile($file, $file)) {
                throw new Exception("Error al añadir el archivo: {$file}");
            }
        } else {
            echo "Advertencia: El archivo {$file} no existe\n";
        }
    }
    
    // Añadir archivos de idioma
    echo "Añadiendo archivos de idioma...\n";
    foreach ($languageFiles as $file) {
        if (file_exists($file)) {
            echo "Añadiendo archivo: {$file}\n";
            if (!$zip->addFile($file, $file)) {
                throw new Exception("Error al añadir el archivo: {$file}");
            }
        } else {
            echo "Advertencia: El archivo {$file} no existe\n";
        }
    }
    
    // Añadir carpetas
    echo "Añadiendo carpetas...\n";
    foreach ($folders as $folder) {
        if (is_dir($folder)) {
            echo "Añadiendo carpeta: {$folder}\n";
            addFolderToZip($zip, $folder, $folder);
        } else {
            echo "Advertencia: La carpeta {$folder} no existe\n";
        }
    }
    
    echo "Cerrando archivo ZIP...\n";
    $zip->close();
    echo "Paquete creado exitosamente: {$zipName}\n";
}

// Función recursiva para añadir carpetas al ZIP
function addFolderToZip($zip, $folder, $baseFolder) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($folder),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = $baseFolder . '/' . substr($filePath, strlen($folder) + 1);
            echo "Añadiendo archivo: {$relativePath}\n";
            if (!$zip->addFile($filePath, $relativePath)) {
                throw new Exception("Error al añadir el archivo: {$relativePath}");
            }
        }
    }
}

// Ejecutar el proceso
try {
    echo "Iniciando proceso de construcción...\n";
    $version = getModuleVersion();
    echo "Versión actual del módulo: {$version}\n";
    createZipPackage($version);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 