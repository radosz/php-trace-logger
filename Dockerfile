FROM php:7.4-fpm

# Set working directory
WORKDIR /var/www

# Set default logs directory (can be overridden with environment variable)
ENV LOGS_DIR=/var/www/html/logs

# Copy existing application directory
COPY . /var/www

CMD ["php", "-S", "0.0.0.0:9000", "-t", "/var/www"]
