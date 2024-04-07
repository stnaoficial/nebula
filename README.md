![image info](./docs/Banner%202.jpg)

# Getting started 🔭

First of all, you must have PHP 8.3.4 or higher installed on your machine. This is the language behind the tool.

# Installation

### One-line option
You can install by running this one-line command option (not recommended):
```bash
git clone git@github.com:stnaoficial/nebula.git .nebula && mv .nebula ~ && ~/.nebula/bin/install.sh
```

### 1. Clone the repository

Clone the repository and move it to the default destination:
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

You can now execute `nebula --help` for more specific information about the usage of the tool. 

# Uninstallation

### One-line option
You can uninstall by running this one-line command option (not recommended):
```bash
git clone git@github.com:stnaoficial/nebula.git .nebula && mv .nebula ~ && ~/.nebula/bin/uninstall.sh
```

### 1. Remove the CLI aliases and remove the installed tool directory

To uninstall the whole tool, you can run this single command:
```bash
sed -i "" "/alias nebula=/d" ~/.bashrc && sed -i "" "/alias neb=/d" ~/.bashrc && rm -rf ~/.nebula
```

After executing these commands, you may need to restart your bash to apply the changes:
```bash
source ~/.bashrc
```

# Usage example

Only `.neb` files are supported.

You must add the destination of the `.neb` file inside `[]`.

Create a directory for your Nebula project named MyNebulaProject:
```bash
mkdir MyNebulaProject
```

Change directory to MyNebulaProject/ and create a hidden directory for all the `.neb` files:
```bash
cd  MyNebulaProject/ && mkdir .origin
```

Change directory to `.origin/` and create a new `.neb` file with the specified naming convention:
```bash
cd .origin/ && touch "[src\pages\{{PageName}}.html].neb"
```

Inside the `[src\pages\{{PageName}}.html].neb` file:
```html
<html>
    <body>
        <h1>{{PageTitle}}</h1>
    </body>
<html>
```

Run the CLI tool with the specified configuration directory `.origin/` and the `-p` flag to indicate propagation mode:
```bash
neb .origin/ -p

...

Enter a value for variable [PageName]: HomePage
Enter a value for variable [PageTitle]: My HomePage
```
This will output a file in `/src/pages/HomePage.html`, utilizing the entered values for `PageName` and `PageTitle`.

---
![image info](./docs/Banner%201.jpg)
