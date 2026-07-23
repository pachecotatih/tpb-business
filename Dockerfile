FROM php:8.1-cli

WORKDIR /var/www/html

# Instalação de dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        gd \
        xml \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia apenas arquivos do Composer primeiro (aproveita cache do Docker)
COPY composer.json composer.lock ./

# Instala dependências sem rodar scripts (evita erros antes de copiar o código)
RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-dev \
    --no-scripts

# Copia o restante do código da aplicação
COPY . .

# Cria os diretórios necessários caso não existam e define permissões
RUN mkdir -p storage/framework/cache/data \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Otimiza o Laravel durante o BUILD (reduz tempo de boot no Render)
RUN php artisan optimize

EXPOSE 10000

# Executa o servidor nativo mapeando a porta correta do Render via shell
CMD sh -c "php artisan serve --host=0.0.0.0 --port=\${PORT:-10000}"
