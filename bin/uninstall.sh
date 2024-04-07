#!usr/bin/env bash

# Removes the alias from the user's .bashrc file.
sed -i '/alias nebula=/d' ~/.bashrc
sed -i '/alias neb=/d' ~/.bashrc

# Removes the .nebula folder.
rm -rf ~/.nebula

# Restarts the user's bash.
source ~/.bashrc