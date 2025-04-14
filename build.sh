#!/bin/bash

# Construir la imagen Docker
echo "Construyendo imagen Docker..."
docker build -t mod_advancedsearch_builder .

# Ejecutar el contenedor
echo "Ejecutando construcción del módulo..."
docker run --rm -v "$(pwd):/app" mod_advancedsearch_builder

echo "Proceso completado." 