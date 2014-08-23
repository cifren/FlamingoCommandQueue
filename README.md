Overview
========

This Symfony2 bundle aims to provide classes to build you own command management

The idea is:

1. Use CommandManager in your command Classes (start and stop function)
2. Declare its name and group
3. The tool will manage queuing instance for you, based on the group or/and uniqueId
4. Watch performance / log on screen (require to create the interface or use AdminSonata for fast implementation)


Documentation
=============

For installation and how to use the bundle refer to [Resources/doc/index.md](https://github.com/Earls/FlamingoCommandQueue/blob/master/Resources/doc/index.md)
