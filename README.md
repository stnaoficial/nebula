![image info](./docs/Banner%202.jpg)

# Getting started 🔭

First of all, you must have PHP 8.3.4 or higher installed in your machine.
This is the language behind the tool.

# Installation

### 1. Clone the repository

Clone the repository and move to the default destination:
```bash
git clone git@github.com:stnaoficial/nebula.git .nebula && mv .nebula ~
```

### 2. Configure the CLI aliases
Configure the CLI aliases in the bash configuration file:
```bash
echo -e "\nalias nebula=\"php $HOME/.nebula/console\"" >> ~/.bashrc && echo "alias neb=\"nebula\"" >> ~/.bashrc
```

After executing these commands, you may need to restart your bash to apply the changes:
```bash
source ~/.bashrc
```

And that's it!

You can now execute `nebula --help` for a more specific information about the usage of the tool. 

# Unistallation
For unistall the whole tool you can run this single command:
```bash
sed -i '/alias nebula=/d' ~/.bashrc && sed -i '/alias neb=/d' ~/.bashrc && rm -rf ~/.nebula
```

After executing these commands, you may need to restart your bash to apply the changes:
```bash
source ~/.bashrc
```

---
![image info](./docs/Banner%201.jpg)