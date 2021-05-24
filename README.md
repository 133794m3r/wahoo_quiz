# Wahoo Quiz
A simple PHP7.4+ based quiz platform. Uses MariaDB currentlyf or the database backend.

# Features
- Modern Fetch for all requests to edit/update questions/answers.
- When taking a quiz all items are done in a refreshless manner.
- CSRF for all authentication routes. Doesn't allow for it to work via anything but POST.
- Simple CAPTCHA for the login system for rate-limiting.
- All passwords require minimum strength to work.
- Authentication is managed before access to any of the privileged pages.
- Uses BootStrap4 so that it doesn't look awful.
- Server/Client communicate with JSON.

# Directory Structure
- "Wahoo Quiz" folder is where all of the code that you'd run on the server lives. It's the folder you'd put in /var/www for Nginx/Apache to see.
  - Static sub folder holds all of the js/css so that you can easily tell apache/nginx those files are static and use zopfli to precompress the files.
- "Server Files" folder is where the server related-items exist(configs script to run to setup the database etc).

#License
Core code AGPLv3.

[Boostrap/Bootswatch MIT](https://github.com/thomaspark/bootswatch/)

[jQuery MIT](https://github.com/jquery/jquery)