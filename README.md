Gate
====

A php based website for viewing [motion](https://github.com/Motion-Project/motion) security camera footage.

Motion is a program for detecting changes in webcam footage and writing videos and saving images to disk with optional database integration. This website package assumes you are writing to a database and you use the 'best' photo option.


Installing this:
Install and configure motion including writing to the database. Setup your web server to serve the docs directory and make sure the docs/media directory exists and is what motion writes into. I symlink it on my setup. Copy docs/config.inc.example to docs/config.inc and update the values for your setup.
