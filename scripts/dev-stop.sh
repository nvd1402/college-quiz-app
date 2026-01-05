#!/bin/bash
set -e

echo "🛑 Stopping Development Mode..."
docker-compose -f docker-compose.dev.yml down
echo "✅ Development containers stopped!"

