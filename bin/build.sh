#!/usr/bin/env bash

set -e

# Remove development files from release based on .distignore
if [ ! -f .distignore ]; then
    echo "Error: .distignore file not found"
    exit 1
fi

# Read .distignore into array first (to avoid issues when deleting the file itself)
targets=()
while IFS= read -r line || [ -n "$line" ]; do
    # Skip comments and empty lines
    [[ "$line" =~ ^#.*$ ]] && continue
    [[ -z "$line" ]] && continue
    # Remove trailing slash for consistency
    targets+=("${line%/}")
done < .distignore

# Remove each entry
for target in "${targets[@]}"; do
    if [ -e "$target" ]; then
        rm -rf "$target"
        echo "Removed: $target"
    fi
done
