# PHP Logger

A simple PHP-based log viewer application that can display and filter log files in real-time.

## Features

- **Real-time log viewing** - Automatically refreshes and displays new log entries
- **Advanced filtering** - Support for AND/OR operators in search queries
- **Context lines support** - Show lines before/after matches using -a and -b parameters
- **Log file management** - Clear logs and delete profile files
- **Disk space monitoring** - Display current logs directory size
- **Configurable log directory** - Set custom log directory via environment variable
- **Web-based interface** - Access via browser with dark theme
- **Optimized Docker image** - 73MB vs 443MB original (6x smaller)
- **Comprehensive testing suite** - Automated tests to verify functionality
- **Automated deployment** - One-command build, optimize, and deploy

## Configuration

### Logs Directory

The logs directory is configurable via the `LOGS_DIR` environment variable.

**Default**: `/var/www/html/logs`

## How to Use

### 1. Basic Usage (Default Configuration)

Run with default settings - logs will be read from `/var/www/html/logs`:

```bash
docker run -p 9000:9000 radosz88/php-logger
```

Then open your browser and go to: `http://localhost:9000`

### 2. Custom Logs Directory

Specify a custom logs directory:

```bash
docker run -p 9000:9000 -e LOGS_DIR=/custom/logs/path radosz88/php-logger
```

### 3. Mount Host Directory

Mount your host logs directory to the container:

```bash
docker run -p 9000:9000 \
  -e LOGS_DIR=/app/logs \
  -v /path/to/your/logs:/app/logs \
  radosz88/php-logger
```

### 4. Docker Compose (Recommended)

Create a `docker-compose.yml` file:

```yaml
version: '3.8'
services:
  php-logger:
    image: radosz88/php-logger:latest
    container_name: php-logger
    ports:
      - "9000:9000"
    environment:
      - LOGS_DIR=/app/logs
    volumes:
      - ./logs:/app/logs
    restart: unless-stopped
```

Run with docker-compose:

```bash
docker-compose up -d
```

To stop:

```bash
docker-compose down
```

## Web Interface Usage

Once the container is running, access the web interface at `http://localhost:9000`

### Filtering Options

- **Simple search**: Type any text to filter logs
- **AND operator**: Use `&&` to require multiple terms: `error && database`
- **OR operator**: Use `||` for alternative terms: `error || warning`
- **Context lines**: 
  - `-a N` shows N lines after matches: `error -a 5`
  - `-b N` shows N lines before matches: `error -b 3`
  - Combined: `error -a 5 -b 3`

### Buttons

- **Clear Logs**: Clears the display (doesn't delete files)
- **Delete Profiles**: Permanently deletes all log files

## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `LOGS_DIR` | Directory containing log files | `/var/www/html/logs` |

## Requirements

- Docker
- Port 9000 available on host
- Log files in the specified directory

## Troubleshooting

### No logs appearing
- Check that log files exist in the specified directory
- Verify the `LOGS_DIR` environment variable is set correctly
- Ensure proper file permissions on mounted volumes

### Container not starting
- Check if port 9000 is already in use
- Verify Docker is running
- Check container logs: `docker logs php-logger`

## Development & Deployment

### Project Structure

```
.
├── deploy.sh              # Automated deployment script
├── docker-compose.yml      # Production Docker Compose
├── Dockerfile             # Docker image definition
├── index.php              # Main application file
├── profiles.php           # Directory size calculation
├── test/                  # Testing directory
│   ├── docker-compose.test.yml
│   ├── final_test.sh
│   ├── nginx-test.conf
│   ├── test_log_gen.php
│   ├── test_run.sh
│   └── README.md
└── README.md
```

### Deployment

To build, optimize, and deploy the image:

```bash
./deploy.sh
```

This script will:
1. Build the base Docker image
2. Optimize it using MinToolkit (reduces from 443MB to 73MB)
3. Run basic verification tests
4. Push to Docker Hub (with confirmation)
5. Clean up intermediate images

### Testing

Run comprehensive tests:

```bash
# Quick functionality test
cd test && ./final_test.sh

# Extended test suite
cd test && ./test_run.sh
```

See `test/README.md` for detailed testing documentation.

### Requirements for Development

- Docker
- MinToolkit (for optimization): `brew install mintoolkit/tap/mint`
- Docker Hub account (for pushing images)

### Image Optimization

The deployment process uses MinToolkit to optimize the Docker image:
- **Original size**: 443MB
- **Optimized size**: 73MB (6x reduction)
- **Preserved commands**: `tail`, `rm`, `du`
- **Preserved files**: All PHP application files
- **Security**: Includes Seccomp and AppArmor profiles
