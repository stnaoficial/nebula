#!/bin/bash

# Installs the alias in the user's .bashrc file.
echo -e "\nalias nebula=\"php $HOME/.nebula/index.php\"" >> ~/.bashrc
echo "alias neb=\"nebula\"" >> ~/.bashrc

# Restarts the user's bash.
source ~/.bashrc