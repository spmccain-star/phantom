#!/bin/bash
set -e
SERVER="root@192.168.86.101"
SSH_OPTS="-J root@192.168.86.88"
REMOTE_PATH="/var/www/phantom"
LOCAL_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
echo "Deploying phantom site to $SERVER:$REMOTE_PATH..."
ssh $SSH_OPTS $SERVER "mkdir -p $REMOTE_PATH"
rsync -avz --delete -e "ssh $SSH_OPTS" --exclude='.git' --exclude='deploy.sh' --exclude='data/' "$LOCAL_PATH/" "$SERVER:$REMOTE_PATH/"
ssh $SSH_OPTS $SERVER "chown -R www-data:www-data $REMOTE_PATH && chmod -R 755 $REMOTE_PATH && find $REMOTE_PATH -name '*.php' -exec chmod 644 {} \;"
ssh $SSH_OPTS $SERVER "systemctl reload apache2 2>/dev/null || true"
echo "Done. Site live at https://phantom.agavelabs.dev"
