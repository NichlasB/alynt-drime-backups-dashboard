#!/bin/bash
# Copy this file to deploy.sh and customize deploy.sh locally.
# Keep deploy.sh gitignored; commit only deploy.example.sh.
# Deploy script for alynt-drime-backups-dashboard.
set -e

# Use your SSH config alias - do NOT hardcode username here.
# Configure in ~/.ssh/config: Host, HostName, User, IdentityFile.
REMOTE_HOST="your-ssh-alias"
REMOTE_PATH="/var/www/your-site/htdocs/wp-content/plugins/alynt-drime-backups-dashboard"

# Keep rollback archives outside wp-content/plugins (for example in a private
# site directory). WordPress discovers every plugin-like folder here, and a
# copied dashboard uninstall handler must never be an operational rollback.

echo "Deploying alynt-drime-backups-dashboard..."
rsync -avz --delete \
	--exclude='.git' \
	--exclude='.github' \
	--exclude='node_modules' \
	--exclude='vendor' \
	--exclude='tests' \
	--exclude='docs' \
	--exclude='scripts/' \
	--exclude='build/' \
	--exclude='assets/src/' \
	--exclude='coverage' \
	--exclude='.DS_Store' \
	--exclude='.env' \
	--exclude='.env.local' \
	--exclude='composer.phar' \
	--exclude='composer.json' \
	--exclude='composer.lock' \
	--exclude='package.json' \
	--exclude='package-lock.json' \
	--exclude='.phpcs.xml' \
	--exclude='.phpcs.xml.dist' \
	--exclude='.gitignore' \
	--exclude='.gitattributes' \
	--exclude='.editorconfig' \
	--exclude='phpunit.xml' \
	--exclude='phpunit.xml.dist' \
	--exclude='deploy.sh' \
	--exclude='deploy.example.sh' \
	--exclude='session-context.tmp.md' \
	--exclude='session-handoff.tmp.md' \
	--exclude='README.md' \
	--exclude='CHANGELOG.md' \
	--exclude='*.map' \
	./ "${REMOTE_HOST}:${REMOTE_PATH}/"
echo "Deployment complete!"
echo "Remote path: ${REMOTE_PATH}"
