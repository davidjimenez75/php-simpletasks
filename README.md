# php-simpletasks

A simple, directory-based project management visualization tool that automatically organizes your filesystem directories into a clean web interface.

## Overview

This application scans your server's directory structure and presents it as a hierarchical project management view:

- First-level directories are treated as **Projects**
- Subdirectories within each project are treated as **Tasks**

It provides a clean, Bootstrap-styled interface to visualize your project organization directly from your filesystem.

## Features

- Automatic discovery of projects and tasks from directory structure
- Clean, responsive Bootstrap interface
- Exclusion of hidden directories (starting with `.`)
- Vendor directory automatically excluded
- Displays task count for each project
- Proper handling of empty projects

## Requirements

- PHP 8.0 or higher
- Web server (Apache, Nginx, etc.)
- Composer (for development/testing)

## Installation

1. Clone the repository to your web server directory:

git clone https://github.com/davidjimenez75/php-simpletasks.git /var/www/html

2. Ensure the web server has appropriate permissions to read the directory structure.

3. Access the application through your web browser.

## License

MIT

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.