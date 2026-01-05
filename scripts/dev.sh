#!/bin/bash
set -e

echo "🚀 Starting Development Mode..."
echo ""

# Check if production containers are running
if docker-compose ps | grep -q "college-quiz-app"; then
    echo "⚠️  Production containers are running. Stopping them..."
    docker-compose down
fi

# Start development containers
echo "📦 Starting development containers..."
docker-compose -f docker-compose.dev.yml up -d

echo ""
echo "✅ Development mode started!"
echo ""
echo "📍 Services:"
echo "   - Frontend (Vite): http://localhost:3000"
echo "   - Backend API: http://localhost/api"
echo "   - Nginx Proxy: http://localhost"
echo "   - phpMyAdmin: http://localhost:8080"
echo ""
echo "📝 To view logs:"
echo "   docker-compose -f docker-compose.dev.yml logs -f"
echo ""
echo "🛑 To stop:"
echo "   docker-compose -f docker-compose.dev.yml down"
echo ""

