#!/bin/bash

# PHP Logger Deployment Script
# This script builds, optimizes, and pushes the php-logger image

set -e

echo "=== PHP Logger Deployment Script ==="
echo "Building, optimizing, and pushing radosz88/php-logger:latest"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Docker is running
if ! docker info >/dev/null 2>&1; then
    print_error "Docker is not running. Please start Docker and try again."
    exit 1
fi

# Check if slim command is available
if ! command -v slim >/dev/null 2>&1; then
    print_error "Slim command not found. Please install MinToolkit first."
    echo "Install with: brew install mintoolkit/tap/mint"
    exit 1
fi

print_status "Starting deployment process..."

# Step 1: Build the base Docker image
print_status "Step 1: Building base Docker image..."
docker build -t radosz88/php-logger:latest .

if [ $? -eq 0 ]; then
    print_status "✅ Base image build successful"
else
    print_error "❌ Base image build failed"
    exit 1
fi

# Get original image size
ORIGINAL_SIZE=$(docker images radosz88/php-logger:latest --format "{{.Size}}")
print_status "Original image size: $ORIGINAL_SIZE"

# Step 2: Optimize with slim
print_status "Step 2: Optimizing image with slim..."
print_status "Including commands: tail, rm, du"
print_status "Including paths: /var/log, /var/www"

slim build \
    --target radosz88/php-logger:latest \
    --include-exe tail \
    --include-exe rm \
    --include-exe du \
    --include-path /var/log \
    --include-path /var/www

if [ $? -eq 0 ]; then
    print_status "✅ Image optimization successful"
else
    print_error "❌ Image optimization failed"
    exit 1
fi

# Step 3: Tag the optimized image
print_status "Step 3: Tagging optimized image..."
docker tag radosz88/php-logger.slim radosz88/php-logger:latest

if [ $? -eq 0 ]; then
    print_status "✅ Image tagging successful"
else
    print_error "❌ Image tagging failed"
    exit 1
fi

# Get optimized image size
OPTIMIZED_SIZE=$(docker images radosz88/php-logger:latest --format "{{.Size}}")
print_status "Optimized image size: $OPTIMIZED_SIZE"

# Step 4: Run basic verification
print_status "Step 4: Running basic verification..."

# Check if container starts
CONTAINER_ID=$(docker run -d --rm radosz88/php-logger:latest)
sleep 5

if docker ps -q --filter "id=$CONTAINER_ID" | grep -q .; then
    print_status "✅ Container starts successfully"
    
    # Test commands
    docker exec $CONTAINER_ID tail --version >/dev/null 2>&1 && print_status "✅ tail command available"
    docker exec $CONTAINER_ID rm --version >/dev/null 2>&1 && print_status "✅ rm command available"
    docker exec $CONTAINER_ID du --version >/dev/null 2>&1 && print_status "✅ du command available"
    
    # Test files
    docker exec $CONTAINER_ID php -r "file_exists('/var/www/index.php') && exit(0) || exit(1);" && print_status "✅ index.php present"
    docker exec $CONTAINER_ID php -r "file_exists('/var/www/profiles.php') && exit(0) || exit(1);" && print_status "✅ profiles.php present"
    
    # Stop container
    docker stop $CONTAINER_ID >/dev/null 2>&1
else
    print_error "❌ Container failed to start"
    exit 1
fi

# Step 5: Push to Docker Hub
print_status "Step 5: Pushing to Docker Hub..."
print_warning "Make sure you're logged in to Docker Hub (docker login)"

if [[ "$1" == "--push" ]]; then
    print_status "Auto-pushing to Docker Hub (--push flag provided)"
    PUSH_REPLY="y"
else
    read -p "Do you want to push to Docker Hub? (y/N): " -n 1 -r
    echo
    PUSH_REPLY=$REPLY
fi

if [[ $PUSH_REPLY =~ ^[Yy]$ ]]; then
    docker push radosz88/php-logger:latest
    
    if [ $? -eq 0 ]; then
        print_status "✅ Image pushed to Docker Hub successfully"
    else
        print_error "❌ Failed to push image to Docker Hub"
        exit 1
    fi
else
    print_warning "Skipping Docker Hub push"
fi

# Step 6: Cleanup
print_status "Step 6: Cleaning up intermediate images..."
docker rmi radosz88/php-logger.slim >/dev/null 2>&1 || true

# Final summary
echo ""
echo "=== Deployment Summary ==="
print_status "✅ Base image built successfully"
print_status "✅ Image optimized with slim"
print_status "✅ Basic verification passed"
print_status "✅ Image size: $ORIGINAL_SIZE → $OPTIMIZED_SIZE"

echo ""
echo "=== Image Details ==="
docker images radosz88/php-logger:latest --format "table {{.Repository}}\t{{.Tag}}\t{{.Size}}\t{{.CreatedAt}}"

echo ""
echo "=== Available Commands in Image ==="
echo "• tail - for reading log files"
echo "• rm - for deleting log files"
echo "• du - for calculating directory sizes"

echo ""
echo "=== Available Files in Image ==="
echo "• /var/www/index.php - main application"
echo "• /var/www/profiles.php - profiles directory size calculation"

echo ""
print_status "Deployment completed successfully!"
print_status "You can now use: docker run -p 9000:9000 radosz88/php-logger:latest"

echo ""
echo "=== Next Steps ==="
echo "1. Test the deployed image: cd test && ./final_test.sh"
echo "2. Run comprehensive tests: cd test && ./test_run.sh"
echo "3. Use in production with your docker-compose.yml"
