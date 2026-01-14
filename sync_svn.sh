#!/bin/bash

# Configuration
PLUGIN_SLUG="tablecrafter-wp-data-tables"
SVN_PATH="/Users/isupercoder/websites/tablecrafter/app/public/wp-content/plugins/tablecrafter-svn" 
GIT_PATH="/Users/isupercoder/websites/tablecrafter/app/public/wp-content/plugins/tablecrafter-wp-data-tables"
IGNORE_FILE=".svnignore"

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}Starting Sync from Git to SVN...${NC}"

# Check paths
if [ ! -d "$SVN_PATH" ]; then
    echo -e "${RED}Error: SVN directory not found at $SVN_PATH${NC}"
    exit 1
fi

if [ ! -d "$GIT_PATH" ]; then
    echo -e "${RED}Error: Git directory not found at $GIT_PATH${NC}"
    exit 1
fi

# Ensure SVN is up to date
echo "Updating SVN..."
cd "$SVN_PATH"
svn update

# Sync contents to trunk
echo "Syncing files..."
rsync -rc --exclude-from="$GIT_PATH/$IGNORE_FILE" "$GIT_PATH/" "$SVN_PATH/trunk/" --delete --delete-excluded

# Check status
cd "$SVN_PATH/trunk"
echo "SVN Status:"
svn status

# Prompt for commit
read -p "Do you want to commit these changes to SVN? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    read -p "Enter commit message: " COMMIT_MSG
    svn commit -m "$COMMIT_MSG"
    echo -e "${GREEN}Successfully committed to SVN!${NC}"
    
    # Tagging
    read -p "Do you want to create a new tag? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        read -p "Enter version number (e.g., 2.4.0): " VERSION
        svn copy "$SVN_PATH/trunk" "$SVN_PATH/tags/$VERSION"
        svn commit -m "Tagging version $VERSION"
        echo -e "${GREEN}Successfully tagged version $VERSION!${NC}"
    fi
else
    echo "Aborted."
fi
