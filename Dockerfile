FROM php:8.2-cli

# Instalar extensión ZIP
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install zip

# Crear directorio de trabajo
WORKDIR /app

# Copiar archivos del módulo
COPY . .

# Comando por defecto
CMD ["php", "build.php"] 