# ORASM - OpenRA Server Manager

A simple web based interface to create/delete, start/stop and Monitor OpenRA servers.
Creating new servers will automatically download the AppImage releases from github.

I quickly wrote this "software" to save me some time managing my servers via console.
It does not use any fancy framework, library or complex setup and is meant as a minimalistic tool.
Feel free to contribute!

## Important notice

This tool does not have any authentication yet, nor does it support multiple users.
**Add some kind of authentication like .htaccess / .htpasswd in front!**
Also it does not check whether enough memory is available for additional servers.

## Requirements

- Linux
- ```fuse``` availabe (AppImage requirement)
- ```screen``` installed (required for starting, stopping, monitoring)
- PHP for the API to actually work
- Write access to the ```servers``` folder and the ```config.json``` file
